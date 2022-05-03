<?php
/**
 * Get menu & submenu.
 *
 * @package Ultimate_Dashboard
 */

namespace Udb\AdminMenu\Ajax;

defined( 'ABSPATH' ) || die( "Can't access directly" );

use Udb\Helpers\Content_Helper;
use Udb\Helpers\Array_Helper;
use Udb\AdminMenu\Admin_Menu_Module;

/**
 * Class to get menu & submenu.
 */
class Get_Menu {
	/**
	 * Whether to get menu by role or by user_id.
	 *
	 * @var string
	 */
	public $by = 'role';

	/**
	 * The role value.
	 *
	 * @var string
	 */
	public $role = '';

	/**
	 * The user_id value.
	 *
	 * @var int
	 */
	public $user_id = 0;

	/**
	 * The saved recent menu.
	 *
	 * @var array
	 */
	public $recent_menus = array();

	/**
	 * Get menu & submenu.
	 */
	public function ajax() {

		$nonce         = isset( $_POST['nonce'] ) ? sanitize_text_field( $_POST['nonce'] ) : '';
		$this->role    = isset( $_POST['role'] ) ? sanitize_text_field( $_POST['role'] ) : '';
		$this->by      = isset( $_POST['by'] ) ? sanitize_text_field( $_POST['by'] ) : '';
		$this->user_id = isset( $_POST['user_id'] ) ? absint( $_POST['user_id'] ) : 0;

		if ( ! wp_verify_nonce( $nonce, 'udb_admin_menu_get_menu' ) ) {
			wp_send_json_error( __( 'Invalid token', 'ultimate-dashboard' ) );
		}

		if ( ! $this->role && ! $this->user_id ) {
			wp_send_json_error( __( 'User role or id must be specified', 'ultimate-dashboard' ) );
		}

		if ( $this->user_id ) {
			$this->by   = 'user_id';
			$user       = get_userdata( $this->user_id );
			$this->role = $user->roles[0];
		}

		/**
		 * This hook is used in the admin menu output in the pro version (multisite & non-multisite).
		 * It's used to remove the output so that the builder is able to get the original output.
		 *
		 * @see wp-content/plugins/ultimate-dashboard-pro/modules/admin-menu/class-admin-menu-output.php
		 * @see wp-content/plugins/ultimate-dashboard-pro/modules/multisite/output/class-ms-admin-menu-output.php
		 */
		do_action( 'udb_ajax_before_get_admin_menu' );

		/**
		 * This hook is used in the free version and in the pro version (the multisite module).
		 * It's used to provide menu and submenu for the builder.
		 *
		 * @see wp-content/plugins/ultimate-dashboard/modules/admin-menu/class-admin-menu-module.php
		 * @see wp-content/plugins/ultimate-dashboard-pro/modules/multisite/output/class-ms-admin-menu-output.php
		 */
		do_action( 'udb_ajax_get_admin_menu', $this, $this->role );

	}

	/**
	 * Manually load menu & submenu.
	 *
	 * This method is called by `get_admin_menu` method inside modules/admin-menu/admin-menu-module.php file.
	 * The `get_admin_menu` in that file it self is hooked into `udb_ajax_get_admin_menu`action
	 * which will be called by `do_action` in this file (inside `ajax` method above).
	 *
	 * @see wp-content/plugins/ultimate-dashboard/modules/admin-menu/class-admin-menu-module.php
	 */
	public function load_menu() {

		global $menu, $submenu;

		$wp_menu_file = ABSPATH . 'wp-admin/menu.php';

		if ( ( is_null( $menu ) || is_null( $submenu ) ) && file_exists( $wp_menu_file ) ) {
			global $menu_order, $default_menu_order, $_wp_last_object_menu, $_wp_submenu_nopriv;

			$menu_order = $default_menu_order = array();

			require $wp_menu_file;
		}

		/**
		 * This is related to TablePress support.
		 *
		 * The value of `wp_doing_ajax` was set to `false` in class-admin-menu-module.php file
		 * inside `support_tablepress` function.
		 *
		 * @see wp-content/plugins/ultimate-dashboard/modules/admin-menu/class-admin-menu-module.php
		 */
		add_filter( 'wp_doing_ajax', '__return_true' );

		$admin_menu_module = Admin_Menu_Module::get_instance();
		$admin_menu_module->direct_save_recent_menu( $this->role, $menu, $submenu );

		$this->recent_menus = get_option( 'udb_recent_admin_menu', array() );

		$this->compare_default_menu_with_recent_menu();
		$this->compare_default_submenu_with_recent_menu();
		$this->check_menu_items_capability();

	}

	/**
	 * Format the response by merging default menu & saved menu.
	 *
	 * This method is called by `get_admin_menu` method inside modules/admin-menu/admin-menu-module.php file.
	 * The `get_admin_menu` in that file it self is hooked into `udb_ajax_get_admin_menu`action
	 * which will be called by `do_action` in this file (inside `ajax` method above).
	 *
	 * @see wp-content/plugins/ultimate-dashboard/modules/admin-menu/class-admin-menu-module.php
	 *
	 * @param string $role The specified role.
	 * @return array $response The formatted response.
	 */
	public function format_response( $role ) {

		global $menu, $submenu;

		$merged_default_menu = $this->merge_default_menu_submenu( $menu, $submenu );
		$merged_default_menu = $this->format_merged_default_menu( $merged_default_menu );

		$saved_menu = get_option( 'udb_admin_menu', array() );
		$saved_menu = empty( $saved_menu ) ? array() : $saved_menu;

		if ( 'user_id' === $this->by ) {
			$custom_menu = ! empty( $saved_menu ) && isset( $saved_menu[ 'user_id_' . $this->user_id ] ) && ! empty( $saved_menu[ 'user_id_' . $this->user_id ] ) ? $saved_menu[ 'user_id_' . $this->user_id ] : array();
		} else {
			$custom_menu = ! empty( $saved_menu ) && isset( $saved_menu[ $role ] ) && ! empty( $saved_menu[ $role ] ) ? $saved_menu[ $role ] : array();
		}

		if ( empty( $custom_menu ) ) {
			$response = $this->parse_response_without_custom_menu( $merged_default_menu );
		} else {
			$custom_menu = $this->get_new_default_menu_items( $merged_default_menu, $custom_menu );
			$response    = $this->parse_response_with_custom_menu( $merged_default_menu, $custom_menu );
		}

		return $response;

	}

	/**
	 * Check menu items capability.
	 *
	 * This function will be called in this file.
	 * But it's only intended to provide support for 3rd parties with specific condition.
	 * If the `udb_admin_menu_show_ui_capabilities` filter returning empty, this function will stop earlier.
	 * So that we don't waste more resource :).
	 *
	 * Sometimes the menu item's capability is accessible by $role but the `show_ui` argument is set to false.
	 * This makes the menu being rendered in the builder but not being displayed in side menu, which is fine.
	 * The problem was, after the admin menu is saved, that item will be displayed in side menu, but broken.
	 * It was like the issue with LifterLMS menu (Engagement & Order).
	 *
	 * I thought, we need to check the post type's `show_ui` argument.
	 * If the `show_ui` argument is false, then unset it from the global $menu.
	 * So those menu items won't be rendered in the builder.
	 *
	 * But turned out I was wrong.
	 * The `register_post_type` already run when the ajax handler run.
	 * That means, the `show_ui` argument has been decided before we simulate the role.
	 * Which means, checking the `show_ui` argument of the post type doesn't solve the problem.
	 *
	 * The solution for now is, by checking the capability manually.
	 * Defining what capability is needed per-plugin (which is not efficient, but it works).
	 * We provide a filter for this, so that other plugin's author / team
	 * can also add support for UDB admin menu editor.
	 *
	 * How if we haven't defined a capability check for a specific plugin (which has the issue)?
	 * Then it might be displayed in the builder, but don't worry.
	 * Because we already place a fix somewhere
	 * so it wouldn't be displayed in the wp-admin's side menu if it's intended to be not displayed.
	 */
	public function check_menu_items_capability() {
		global $menu;

		// The `show_ui` capability by specific post types.
		$show_ui_capabilities = array();

		/**
		 * Let's provide filter for this.
		 * So other plugin's author / team can add support for UDB admin menu editor.
		 */
		$show_ui_capabilities = apply_filters( 'udb_admin_menu_show_ui_capabilities', $show_ui_capabilities );

		// If the filter is empty (which is default), then stop here.
		if ( empty( $show_ui_capabilities ) ) {
			return;
		}

		foreach ( $menu as $menu_order => $menu_item ) {
			if ( false !== stripos( $menu_item[2], 'edit.php?post_type=' ) ) {
				$explode_full = explode( 'edit.php?post_type=', $menu_item[2] );

				if ( isset( $explode_full[1] ) ) {
					$explode_partial = explode( '&', $explode_full[1] );

					if ( count( $explode_partial ) === 1 ) {
						$post_type_slug = $explode_partial[0];

						foreach ( $show_ui_capabilities as $post_type => $capability ) {
							if ( $post_type_slug === $post_type ) {
								if ( ! current_user_can( $capability ) ) {
									unset( $menu[ $menu_order ] );
								}
							}
						} // End of foreach $show_ui_capabilities.
					} // End of count( $explode_partial ) === 1.
				} // End of isset( $explode_full[1] ).
			} // End of stripos.
		} // End of $menu foreach.
	}

	/**
	 * Compare default global $menu with saved udb_recent_menu.
	 * If some menu exists in recent menu but not in default menu,
	 * then add it to the default menu.
	 *
	 * The `udb_recent_menu` was set in `save_recent_menu` method
	 * in the class-admin-menu-module.php in the free version.
	 *
	 * @see wp-content/plugins/ultimate-dashboard/modules/admin-menu/class-admin-menu-module.php
	 */
	public function compare_default_menu_with_recent_menu() {

		global $menu;

		$recent_menus = $this->recent_menus;
		$role         = $this->role;

		if ( empty( $recent_menus ) || ! isset( $recent_menus[ $role ] ) || empty( $recent_menus[ $role ] ) ) {
			return;
		}

		if ( ! isset( $recent_menus[ $role ]['menu'] ) || empty( $recent_menus[ $role ]['menu'] ) ) {
			return;
		}

		$recent_menu = $recent_menus[ $role ]['menu'];

		$array_helper = new Array_Helper();

		foreach ( $recent_menu as $menu_index => $menu_item ) {
			$menu_type = empty( $menu_item[0] ) && empty( $menu_item[3] ) ? 'separator' : 'menu';

			$menu_search_key = 'separator' === $menu_type ? 2 : 5; // 2 = slug, 5 = index.
			$value_to_search = $menu_item[ $menu_search_key ];

			$matched_menu_index = $array_helper->find_assoc_array_index_by_value( $menu, $menu_search_key, $value_to_search );
			$matched_menu_item  = false !== $matched_menu_index ? $menu[ $matched_menu_index ] : null;

			if ( ! $matched_menu_item ) {
				$new_menu_index = $this->generate_unique_index( $menu, $menu_index );

				$menu[ $new_menu_index ] = $menu_item;

			}
		}

		ksort( $menu );

	}

	/**
	 * Compare default global $submenu with saved udb_recent_menu.
	 * If some submenu exists in recent menu but not in default submenu,
	 * then add it to the default submenu.
	 *
	 * The `udb_recent_menu` was set in `save_recent_menu` method
	 * in the class-admin-menu-module.php in the free version.
	 *
	 * @see wp-content/plugins/ultimate-dashboard/modules/admin-menu/class-admin-menu-module.php
	 */
	public function compare_default_submenu_with_recent_menu() {

		global $submenu;

		$recent_menus = $this->recent_menus;
		$role         = $this->role;

		if ( empty( $recent_menus ) || ! isset( $recent_menus[ $role ] ) || empty( $recent_menus[ $role ] ) ) {
			return;
		}

		if ( ! isset( $recent_menus[ $role ]['submenu'] ) || empty( $recent_menus[ $role ]['submenu'] ) ) {
			return;
		}

		$recent_submenu = $recent_menus[ $role ]['submenu'];

		$array_helper = new Array_Helper();

		foreach ( $recent_submenu as $submenu_key => $submenu_group ) {
			if ( ! isset( $submenu[ $submenu_key ] ) ) {
				$submenu[ $submenu_key ] = $submenu_group;
			} else {
				$submenu_search_key = 2; // 2 = slug.

				foreach ( $submenu_group as $submenu_index => $submenu_item ) {
					$value_to_search = $submenu_item[ $submenu_search_key ];

					$matched_submenu_index = $array_helper->find_assoc_array_index_by_value( $submenu[ $submenu_key ], $submenu_search_key, $value_to_search );
					$matched_submenu_item  = false !== $matched_submenu_index ? $submenu[ $submenu_key ][ $matched_submenu_index ] : null;

					if ( ! $matched_submenu_item ) {
						$new_submenu_index = $this->generate_unique_index( $submenu[ $submenu_key ], $submenu_index );

						$submenu[ $submenu_key ][ $new_submenu_index ] = $submenu_item;

					}
				}

				ksort( $submenu[ $submenu_key ] );
			}
		}

	}

	/**
	 * Generate unique index from provided $array based on provided $index.
	 *
	 * @param array $array The provided array.
	 * @param int   $index The provided index.
	 *
	 * @return int
	 */
	public function generate_unique_index( $array, $index ) {

		if ( isset( $array[ $index ] ) ) {
			if ( is_string( $index ) ) {
				$index = (float) $index;
			}

			$index += 0.05;
			$index  = (string) $index;

			if ( isset( $array[ $index ] ) ) {
				$index = $this->generate_unique_index( $array, $index );
			}
		}

		return $index;

	}

	/**
	 * Merge default menu & submenu into 1 array.
	 *
	 * @param array $menu The default menu.
	 * @param array $submenu The default submenu.
	 *
	 * @return array The merged menu.
	 */
	public function merge_default_menu_submenu( $menu, $submenu ) {

		$merged_menu = array();

		foreach ( $menu as $index => $menu_item ) {
			$cap_allowed = true;

			if ( ! empty( $menu_item[1] ) ) {
				$cap_allowed = current_user_can( $menu_item[1] );
			}

			if ( $cap_allowed ) {
				if ( isset( $submenu[ $menu_item[2] ] ) ) {
					$menu_item['submenu'] = $submenu[ $menu_item[2] ];
				}

				$merged_menu[ $index ] = $menu_item;
			}
		}

		return $merged_menu;

	}

	/**
	 * Format the default menu to our expected format.
	 *
	 * @param array $menu The default menu in a merged format (default menu & submenu merged into 1 array).
	 * @return array $formatted_menus The formatted menu.
	 */
	public function format_merged_default_menu( $menu ) {

		$formatted_menus = array();

		foreach ( $menu as $index => $menu_item ) {
			$menu_type = empty( $menu_item[0] ) && empty( $menu_item[3] ) ? 'separator' : 'menu';

			$formatted_menu = array();

			$content_helper = new Content_Helper();

			$formatted_menu['title'] = $content_helper->strip_tags_content( $menu_item[0] );
			$formatted_menu['url']   = $menu_item[2];
			$formatted_menu['class'] = $menu_item[4];
			$formatted_menu['type']  = $menu_type;

			$formatted_menu['dashicon'] = '';
			$formatted_menu['icon_svg'] = '';

			if ( 'menu' === $menu_type ) {
				$formatted_menu['id'] = $menu_item[5];

				if ( false !== stripos( $menu_item[6], 'dashicons-' ) ) {
					$formatted_menu['icon_type'] = 'dashicon';
					$formatted_menu['dashicon']  = $menu_item[6];
				} else {
					$formatted_menu['icon_type'] = 'icon_svg';
					$formatted_menu['icon_svg']  = $menu_item[6];
				}
			}

			if ( 'menu' === $menu_type && isset( $menu_item['submenu'] ) && ! empty( $menu_item['submenu'] ) ) {
				$formatted_submenus = array();

				foreach ( $menu_item['submenu'] as $submenu_index => $submenu_item ) {
					$formatted_submenu = array();

					$content_helper = new Content_Helper();

					$formatted_submenu['title'] = $content_helper->strip_tags_content( $submenu_item[0] );
					$formatted_submenu['url']   = $submenu_item[2];

					array_push( $formatted_submenus, $formatted_submenu );
				}

				$formatted_menu['submenu'] = $formatted_submenus;
			}

			array_push( $formatted_menus, $formatted_menu );
		}

		return $formatted_menus;

	}

	/**
	 * Parse response without custom menu (when custom menu is empty).
	 *
	 * @param array $default_menu The well formatted default menu.
	 * @return $response The parsed response.
	 */
	public function parse_response_without_custom_menu( $default_menu ) {

		$response = array();

		foreach ( $default_menu as $menu_index => $menu_item ) {
			$new_menu_item = $this->build_custom_menu_item( $menu_item, true );

			array_push( $response, $new_menu_item );
		}

		return $response;

	}

	/**
	 * Build custom menu item from default menu item.
	 *
	 * @param array $default_menu_item The default menu item.
	 * @param bool  $clone Whether or not to totally clone the item.
	 *
	 * @return array The custom menu item.
	 */
	public function build_custom_menu_item( $default_menu_item, $clone = false ) {

		$custom_menu_item = array();

		$custom_menu_item['is_hidden'] = 0;
		$custom_menu_item['was_added'] = 0;

		foreach ( $default_menu_item as $menu_item_key => $menu_item_value ) {
			if ( 'submenu' !== $menu_item_key ) {
				$default_menu_item_key = $menu_item_key . '_default';

				if ( $clone ) {
					if ( 'type' !== $menu_item_key ) {
						$custom_menu_item[ $default_menu_item_key ] = $menu_item_value;
						$custom_menu_item[ $menu_item_key ]         = '';
					} else {
						$custom_menu_item[ $menu_item_key ] = $menu_item_value;
					}
				} else {
					if ( 'url' === $menu_item_key || 'id' === $menu_item_key ) {
						$custom_menu_item[ $default_menu_item_key ] = $menu_item_value;
						$custom_menu_item[ $menu_item_key ]         = '';
					} else {
						if ( 'type' === $menu_item_key ) {
							$custom_menu_item[ $menu_item_key ] = $menu_item_value;
						} else {
							$custom_menu_item[ $menu_item_key ] = '';
						}
					}
				}
			} else {
				$new_submenu = array();

				foreach ( $menu_item_value as $submenu_index => $submenu_item ) {
					$new_submenu_item = array();

					$new_submenu_item['is_hidden'] = 0;
					$new_submenu_item['was_added'] = 0;

					foreach ( $submenu_item as $submenu_item_key => $submenu_item_value ) {
						$default_submenu_item_key = $submenu_item_key . '_default';

						if ( $clone ) {
							if ( 'type' !== $menu_item_key ) {
								$new_submenu_item[ $default_submenu_item_key ] = $submenu_item_value;
								$new_submenu_item[ $submenu_item_key ]         = '';
							} else {
								$new_submenu_item[ $submenu_item_key ] = $submenu_item_value;
							}
						} else {
							if ( 'url' === $submenu_item_key ) {
								$new_submenu_item[ $default_submenu_item_key ] = $submenu_item_value;
								$new_submenu_item[ $submenu_item_key ]         = '';
							} else {
								if ( 'type' === $submenu_item_key ) {
									$new_submenu_item[ $submenu_item_key ] = $submenu_item_value;
								} else {
									$new_submenu_item[ $submenu_item_key ] = '';
								}
							}
						}
					}

					array_push( $new_submenu, $new_submenu_item );
				}

				$custom_menu_item['submenu'] = $new_submenu;
			}
		}

		return $custom_menu_item;

	}

	/**
	 * Build custom submenu item from default submenu item.
	 *
	 * @param array $default_submenu_item The default submenu item.
	 * @return array The custom submenu item.
	 */
	public function build_custom_submenu_item( $default_submenu_item ) {

		$custom_submenu_item = array();

		$custom_submenu_item['is_hidden'] = 0;
		$custom_submenu_item['was_added'] = 0;

		foreach ( $default_submenu_item as $submenu_item_key => $submenu_item_value ) {
			$default_submenu_item_key = $submenu_item_key . '_default';

			if ( 'url' === $submenu_item_key ) {
				$custom_submenu_item[ $default_submenu_item_key ] = $submenu_item_value;
				$custom_submenu_item[ $submenu_item_key ]         = '';
			} else {
				if ( 'type' === $submenu_item_key ) {
					$custom_submenu_item[ $submenu_item_key ] = $submenu_item_value;
				} else {
					$custom_submenu_item[ $submenu_item_key ] = '';
				}
			}
		}

		return $custom_submenu_item;

	}

	/**
	 * Parse response with custom menu.
	 *
	 * @param array $default_menu The well formatted default menu.
	 * @param array $custom_menu The custom menu.
	 *
	 * @return $response The parsed response.
	 */
	public function parse_response_with_custom_menu( $default_menu, $custom_menu ) {

		$response     = array();
		$array_helper = new Array_Helper();

		foreach ( $custom_menu as $menu_index => $menu_item ) {
			$new_menu_item = array();

			$menu_search_key      = 'separator' === $menu_item['type'] ? 'url' : 'id';
			$default_menu_index   = $array_helper->find_assoc_array_index_by_value( $default_menu, $menu_search_key, $menu_item[ $menu_search_key . '_default' ] );
			$matched_default_menu = false !== $default_menu_index ? $default_menu[ $default_menu_index ] : false;

			foreach ( $menu_item as $menu_item_key => $menu_item_value ) {

				if ( 'submenu' !== $menu_item_key ) {
					$default_menu_item_key = $menu_item_key . '_default';

					if ( 'type' !== $menu_item_key && 'is_hidden' !== $menu_item_key && 'was_added' !== $menu_item_key && 'id_default' !== $menu_item_key && 'url_default' !== $menu_item_key ) {
						if ( isset( $matched_default_menu[ $menu_item_key ] ) ) {
							$new_menu_item[ $default_menu_item_key ] = $matched_default_menu[ $menu_item_key ];
							$new_menu_item[ $menu_item_key ]         = $menu_item_value;
						} else {
							if ( 1 === absint( $menu_item['was_added'] ) ) {
								if ( isset( $menu_item[ $default_menu_item_key ] ) ) {
									$new_menu_item[ $default_menu_item_key ] = $menu_item[ $default_menu_item_key ];
								} else {
									$new_menu_item[ $default_menu_item_key ] = $menu_item_value;
								}

								$new_menu_item[ $menu_item_key ] = $menu_item_value;
							}
						}
					} else {
						$new_menu_item[ $menu_item_key ] = $menu_item_value;
					}
				} else {
					$new_submenu = array();

					foreach ( $menu_item_value as $submenu_index => $submenu_item ) {
						if ( isset( $submenu_item['url_default'] ) ) {
							$new_submenu_item = array();

							$original_url_default = $submenu_item['url_default'];

							// Let's turn the formatting to use "&amp;" instead of "&".
							$submenu_item['url_default'] = str_ireplace( '&', '&amp;', $submenu_item['url_default'] );

							if ( isset( $matched_default_menu['submenu'] ) ) {
								$default_submenu_index = $array_helper->find_assoc_array_index_by_value( $matched_default_menu['submenu'], 'url', $submenu_item['url_default'] );

								/**
								 * In some cases like the submenu of "Analytics" menu item in WooCommerce,
								 * the menu item's slug is still using raw "&" sign instead of "&amp;".
								 * For this case, let's try to search using the original "&" sign.
								 */
								if ( false === $default_submenu_index ) {
									// $default_submenu_index = $array_helper->find_assoc_array_index_by_value( $matched_default_menu['submenu'], 'url', $original_url_default );
								}

								$matched_default_submenu = false !== $default_submenu_index ? $matched_default_menu['submenu'][ $default_submenu_index ] : false;
							} else {
								$matched_default_submenu = false;
							}

							foreach ( $submenu_item as $submenu_item_key => $submenu_item_value ) {
								$default_submenu_item_key = $submenu_item_key . '_default';

								if ( 'type' !== $submenu_item_key && 'is_hidden' !== $submenu_item_key && 'was_added' !== $submenu_item_key && 'url_default' !== $submenu_item_key ) {
									if ( isset( $matched_default_submenu[ $submenu_item_key ] ) ) {
										$new_submenu_item[ $default_submenu_item_key ] = $matched_default_submenu[ $submenu_item_key ];
										$new_submenu_item[ $submenu_item_key ]         = $submenu_item_value;
									} else {
										if ( 1 === absint( $submenu_item['was_added'] ) ) {
											if ( isset( $submenu_item[ $default_submenu_item_key ] ) ) {
												$new_submenu_item[ $default_submenu_item_key ] = $submenu_item[ $default_submenu_item_key ];
											} else {
												$new_submenu_item[ $default_submenu_item_key ] = $submenu_item_value;
											}

											$new_submenu_item[ $submenu_item_key ] = $submenu_item_value;
										}
									}
								} else {
									$new_submenu_item[ $submenu_item_key ] = $submenu_item_value;
								}
							}

							if ( ! $submenu_item['was_added'] ) {
								if ( $matched_default_submenu ) {
									array_push( $new_submenu, $new_submenu_item );
								}
							} else {
								array_push( $new_submenu, $new_submenu_item );
							} // End of $matched_default_submenu checking.
						} // End of $submenu_item['url_default'] checking.
					} // End of $menu_item_value foreach.

					$new_menu_item['submenu'] = $new_submenu;
				}
			} // End of $menu_item foreach.

			if ( ! $menu_item['was_added'] ) {
				if ( $matched_default_menu ) {
					array_push( $response, $new_menu_item );
				}
			} else {
				array_push( $response, $new_menu_item );
			}
		} // End of $custom_menu foreach.

		return $response;

	}

	/**
	 * Get new items from default menu, add it to our custom menu if exist.
	 *
	 * @see https://stackoverflow.com/questions/3797239/insert-new-item-in-array-on-any-position-in-php
	 *
	 * @param array $default_menu The default menu.
	 * @param array $custom_menu The custom menu.
	 *
	 * @return array The modified custom menu.
	 */
	public function get_new_default_menu_items( $default_menu, $custom_menu ) {

		$array_helper = new Array_Helper();

		foreach ( $default_menu as $menu_index => $menu_item ) {
			$menu_search_key     = 'separator' === $menu_item['type'] ? 'url' : 'id';
			$custom_menu_index   = $array_helper->find_assoc_array_index_by_value( $custom_menu, $menu_search_key . '_default', $menu_item[ $menu_search_key ] );
			$matched_custom_menu = false !== $custom_menu_index ? $custom_menu[ $custom_menu_index ] : false;

			if ( ! $matched_custom_menu ) {
				$custom_menu_item = $this->build_custom_menu_item( $menu_item );

				array_splice( $custom_menu, $menu_index, 0, array( $custom_menu_item ) );
			} else {
				if ( isset( $menu_item['submenu'] ) ) {
					foreach ( $menu_item['submenu'] as $submenu_index => $submenu_item ) {
						$custom_submenu_index   = $array_helper->find_assoc_array_index_by_value( $matched_custom_menu['submenu'], 'url_default', $submenu_item['url'] );
						$matched_custom_submenu = false !== $custom_submenu_index ? $matched_custom_menu['submenu'][ $custom_submenu_index ] : false;

						if ( ! $matched_custom_submenu ) {
							$custom_submenu_item = $this->build_custom_submenu_item( $submenu_item );

							array_splice( $custom_menu[ $custom_menu_index ]['submenu'], $submenu_index, 0, array( $custom_submenu_item ) );
						}
					}
				}
			}
		}

		return $custom_menu;

	}

}
