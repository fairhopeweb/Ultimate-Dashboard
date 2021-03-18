/**
 * Used global objects:
 * - jQuery
 * - ajaxurl
 * - udbAdminBar
 * - existingAdminBarMenu
 */
(function ($) {
	if (window.NodeList && !NodeList.prototype.forEach) {
		NodeList.prototype.forEach = Array.prototype.forEach;
	}

	if (!String.prototype.includes) {
		String.prototype.includes = function (search, start) {
			'use strict';

			if (search instanceof RegExp) {
				throw TypeError('first argument must not be a RegExp');
			}
			if (start === undefined) { start = 0; }
			return this.indexOf(search, start) !== -1;
		};
	}

	var state = {};
	var elms = {};
	var usersData;

	/**
	 * Init the script.
	 * Call the main functions here.
	 */
	function init() {
		loadUsers();

		document.querySelector('.udb-admin-bar--edit-form').addEventListener('submit', submitForm);
		document.querySelector('.udb-admin-bar--reset-button').addEventListener('click', resetMenu);

		$(document).on('click', '.udb-admin-bar--tab-menu-item', switchTab);
		$(document).on('click', '.udb-admin-bar--expand-menu', expandCollapseMenuItem);
		$(document).on('click', '.hide-menu', showHideMenuItem);
	}

	/**
	 * Load users as select2 data.
	 */
	function loadUsers() {
		$.ajax({
			type: 'get',
			url: ajaxurl,
			cache: false,
			data: {
				action: 'udb_admin_bar_get_users',
				nonce: udbAdminBar.nonces.getUsers
			}
		}).done(function (r) {
			if (!r.success) return;

			usersData = r.data;

			buildMenu(udbAdminBarRender.parsedMenu);
		}).fail(function () {
			console.log('Failed to load users');
		}).always(function () {
			//
		});
	}

	/**
	 * Switch tabs.
	 */
	function switchTab(e) {
		if (e.target.classList.contains('delete-icon')) return;
		var tabArea = this.parentNode.parentNode;
		var tabId = this.dataset.udbTabContent;

		var tabHasIdByDefault = false;

		if (tabArea.id) {
			tabHasIdByDefault = true;
		} else {
			tabArea.id = 'udb-admin-bar--tab' + Math.random().toString(36).substring(7);
		}

		var menus = document.querySelectorAll('#' + tabArea.id + ' > .udb-admin-bar--tab-menu > .udb-admin-bar--tab-menu-item');
		var contents = document.querySelectorAll('#' + tabArea.id + ' > .udb-admin-bar--tab-content > .udb-admin-bar--tab-content-item');

		if (!tabHasIdByDefault) tabArea.removeAttribute('id');

		menus.forEach(function (menu) {
			if (menu.dataset.udbTabContent !== tabId) {
				menu.classList.remove('is-active');
			} else {
				menu.classList.add('is-active');
			}
		});

		contents.forEach(function (content) {
			if (content.id !== tabId) {
				content.classList.remove('is-active');
			} else {
				content.classList.add('is-active');
			}
		});
	}

	/**
	 * Build menu list.
	 *
	 * @param {array} menuList List of menu object.
	 */
	function buildMenu(menuList) {
		var editArea = document.querySelector('#udb-admin-bar--workspace');
		if (!editArea) return;
		var listArea = editArea.querySelector('.udb-admin-bar--menu-list');
		var builtMenu = '';

		for (var menu in menuList) {
			if (menuList.hasOwnProperty(menu)) {
				builtMenu += replaceMenuPlaceholders(menuList[menu]);
			}
		}

		listArea.innerHTML = builtMenu;

		setupMenuItems(listArea);

		var submenuList = listArea.querySelectorAll('.udb-admin-bar--submenu-list');

		if (submenuList.length) {
			submenuList.forEach(function (submenu) {
				setupMenuItems(submenu, true);
			});
		}

		var select2Fields = listArea.querySelectorAll('.udb-admin-bar--select2-field');
		
		select2Fields.forEach(function (selectbox) {
			if (selectbox.dataset.name !== 'disallowed_roles' && selectbox.dataset.name !== 'disallowed_users') return;

			var select2Data = [];
			var disallowedRoles = [];
			var disallowedUsers = [];

			if ('disallowed_roles' === selectbox.dataset.name) {
				disallowedRoles = selectbox.dataset.disallowedRoles.split(', ');
				
				udbAdminBar.roles.forEach(function (role) {
					if (disallowedRoles.indexOf(role.id) > -1) {
						role.selected = true;
					}

					select2Data.push(role);
				});
			} else if ('disallowed_users' === selectbox.dataset.name) {
				disallowedUsers = selectbox.dataset.disallowedUsers.split(', ');
				disallowedUsers = disallowedUsers.map(function (user) {
					return parseInt(user, 10);
				});

				usersData.forEach(function (userData) {
					if (disallowedUsers.indexOf(userData.id) > -1) {
						userData.selected = true;
					}

					select2Data.push(userData);
				});
			}

			$(selectbox).select2({
				data: select2Data
			});
		});
	}

	/**
	 * Replace menu placeholders.
	 *
	 * @param {Object} menu The menu item.
	 */
	function replaceMenuPlaceholders(menu) {
		var template;
		var submenuTemplate;
		var icon;

		template = udbAdminBar.templates.menuList;
		template = template.replace(/{menu_title}/g, menu.title);
		template = template.replace(/{default_menu_title}/g, menu.title_default);
		template = template.replace(/{encoded_default_menu_title}/g, menu.title_default_encoded);

		var parsedTitle;

		if ('wp-logo' === menu.id || 'menu-toggle' === menu.id || 'comments' === menu.id || false === menu.title_default) {
			template = template.replace(/{menu_title_is_disabled}/g, 'disabled');
			parsedTitle = menu.id;
		} else {
			template = template.replace(/{menu_title_is_disabled}/g, '');

			if ('updates' === menu.id) {
				parsedTitle = menu.meta.title ? menu.meta.title : menu.id;
			} else {
				parsedTitle = menu.title ? menu.title_clean : menu.title_default_clean;
			}
		}

		template = template.replace(/{parsed_menu_title}/g, parsedTitle);

		template = template.replace(/{menu_href}/g, menu.href);
		template = template.replace(/{default_menu_href}/g, menu.href_default);

		if (false === menu.href_default) {
			template = template.replace(/{menu_url_is_disabled}/g, 'disabled');
		} else {
			template = template.replace(/{menu_url_is_disabled}/g, '');
		}

		template = template.replace(/{menu_id}/g, menu.id);
		template = template.replace(/{default_menu_id}/g, menu.id_default);

		template = template.replace(/{menu_dashicon}/g, menu.dashicon);
		template = template.replace(/{default_menu_dashicon}/g, menu.dashicon_default);

		template = template.replace(/{menu_icon_is_disabled}/g, (menu.was_added ? '' : 'disabled'));

		template = template.replace(/{trash_icon}/g, '');
		
		var disallowedRoles = menu.disallowed_roles.join(', ');
		var disallowedUsers = menu.disallowed_users.join(', ');
		
		template = template.replace(/{disallowed_roles}/g, disallowedRoles);
		template = template.replace(/{disallowed_users}/g, disallowedUsers);

		if (menu.was_added) {
			template = template.replace(/{menu_icon_field_is_hidden}/g, '');
		} else {
			template = template.replace(/{menu_icon_field_is_hidden}/g, 'is-hidden');

			if (menu.icon) {
				icon = '<i class="dashicons ' + menu.icon + '"></i>';
				template = template.replace(/{menu_icon}/g, icon);
			} else {
				template = template.replace(/{menu_icon}/g, '');
			}
		}

		if (menu.submenu && Object.keys(menu.submenu).length) {
			submenuTemplate = buildSubmenu({
				menu: menu,
				depth: 1
			});

			template = template.replace(/{submenu_template}/g, submenuTemplate);
		} else {
			template = template.replace(/{submenu_template}/g, '');
		}

		return template;
	}

	/**
	 * Build submenu list.
	 *
	 * @param {Object} param The submenu parameter containing some arguments.
	 *
	 * @param {array} param.menu The menu item which contains the submenu list.
	 * @param {int} param.depth The submenu depth level.
	 * 
	 * @return {string} template The submenu template.
	 */
	function buildSubmenu(param) {
		var menu = param.menu;
		var depth = param.depth;

		var template = '';

		for (var submenu in menu.submenu) {
			if (menu.submenu.hasOwnProperty(submenu)) {
				template += replaceSubmenuPlaceholders({
					menu: menu,
					// Current submenu item.
					submenu: menu.submenu[submenu],
					depth: depth
				});
			}
		}

		return template;
	}

	/**
	 * Replace submenu placeholders.
	 *
	 * @param {Object} param The parameter containing some arguments.
	 *
	 * @param {Object} param.menu The menu item which contains the submenu list.
	 * @param {Object} param.submenu The current submenu item.
	 * @param {int} param.depth The submenu depth level.
	 */
	function replaceSubmenuPlaceholders(param) {
		var menu = param.menu;
		var submenu = param.submenu;
		var depth = param.depth;

		var template = udbAdminBar.templates.submenuList;

		template = template.replace(/{default_menu_id}/g, menu.id_default);

		template = template.replace(/{submenu_id}/g, submenu.id);
		template = template.replace(/{default_submenu_id}/g, submenu.id_default);

		template = template.replace(/{submenu_level}/g, depth.toString());
		template = template.replace(/{submenu_title}/g, submenu.title);
		template = template.replace(/{default_submenu_title}/g, submenu.title_default);
		template = template.replace(/{encoded_default_submenu_title}/g, submenu.title_default_encoded);

		var parsedTitle;

		if ('wp-logo' === submenu.id || 'menu-toggle' === submenu.id || 'comments' === submenu.id || false === submenu.title_default) {
			template = template.replace(/{submenu_title_is_disabled}/g, 'disabled');
			parsedTitle = submenu.id;
		} else {
			template = template.replace(/{submenu_title_is_disabled}/g, '');
			parsedTitle = submenu.title ? submenu.title_clean : submenu.title_default_clean;
		}

		template = template.replace(/{parsed_submenu_title}/g, parsedTitle);

		template = template.replace(/{submenu_href}/g, submenu.href);
		template = template.replace(/{default_submenu_href}/g, submenu.href_default);

		template = template.replace(/{submenu_tab_is_hidden}/g, (3 === depth ? 'is-hidden' : ''));
		template = template.replace(/{trash_icon}/g, '');
		template = template.replace(/{submenu_was_added}/g, submenu.was_added);

		var disallowedRoles = submenu.disallowed_roles.join(', ');
		var disallowedUsers = submenu.disallowed_users.join(', ');

		template = template.replace(/{disallowed_roles}/g, disallowedRoles);
		template = template.replace(/{disallowed_users}/g, disallowedUsers);

		if (submenu.submenu && Object.keys(submenu.submenu).length) {
			submenuTemplate = buildSubmenu({
				menu: submenu,
				depth: depth + 1
			});
			template = template.replace(/{submenu_template}/g, submenuTemplate);
		} else {
			template = template.replace(/{submenu_template}/g, '');
		}

		return template;
	}

	/**
	 * Setup menu items.
	 */
	function setupMenuItems(listArea, isSubmenu) {
		setupSortable(listArea);

		if (!isSubmenu) {
			setupItemChanges(listArea);
			$(listArea).find('.dashicons-picker').dashiconsPicker();
		}
	}

	/**
	 * Sortable setup for both active & available widgets.
	 */
	function setupSortable(listArea) {
		$(listArea).sortable({
			receive: function (e, ui) {
				//
			},
			update: function (e, ui) {
				//
			}
		});
	}

	/**
	 * Expand / collapse menu item.
	 * @param {Event} e The event object.
	 */
	function expandCollapseMenuItem(e) {
		var parent = this.parentNode.parentNode;
		var target = parent.querySelector('.udb-admin-bar--expanded-panel');

		if (parent.classList.contains('is-expanded')) {
			$(target).stop().slideUp(350, function () {
				parent.classList.remove('is-expanded');
			});
		} else {
			$(target).stop().slideDown(350, function () {
				parent.classList.add('is-expanded');
			});
		}
	}

	/**
	 * show / hide menu item.
	 *
	 * @param {Event} listArea The event object.
	 */
	function showHideMenuItem(e) {
		var parent = this.parentNode.parentNode.parentNode;
		var isHidden = parent.dataset.hidden === '1' ? true : false;

		if (isHidden) {
			this.classList.add('dashicons-visibility');
			this.classList.remove('dashicons-hidden');
			parent.dataset.hidden = 0;
		} else {
			parent.dataset.hidden = 1;
			this.classList.remove('dashicons-visibility');
			this.classList.add('dashicons-hidden');
		}
	}

	/**
	 * Setup item changes.
	 * @param {HTMLElement} listArea The list area element.
	 */
	function setupItemChanges(listArea) {
		var menuItems = listArea.querySelectorAll('.udb-admin-bar--menu-item');
		if (!menuItems.length) return;

		menuItems.forEach(function (menuItem) {
			setupItemChange(menuItem);
		});
	}

	/**
	 * Setup item change.
	 * @param {HTMLElement} menuItem The menu item element.
	 */
	function setupItemChange(menuItem) {
		var iconFields = menuItem.querySelectorAll('.udb-admin-bar--icon-field');
		iconFields = iconFields.length ? iconFields : [];

		iconFields.forEach(function (field) {
			field.addEventListener('change', function () {
				var iconWrapper = menuItem.querySelector('.udb-admin-bar--menu-icon');
				var iconOutput;

				if (this.dataset.name === 'dashicon') {
					iconOutput = '<i class="dashicons ' + this.value + '"></i>';
				} else if (this.dataset.name === 'icon_svg') {
					iconOutput = '<img alt="" src="' + this.value + '">';
				}

				iconWrapper.innerHTML = iconOutput;
			});
		});

		var titleFields = menuItem.querySelectorAll('[data-name="menu_title"]');
		titleFields = titleFields.length ? titleFields : [];

		titleFields.forEach(function (field) {
			field.addEventListener('change', function () {
				menuItem.querySelector('.udb-admin-bar--menu-name').innerHTML = this.value;
			});
		});
	}

	/**
	 * Function to execute on form submission.
	 *
	 * @param {Event} e The on submit event.
	 */
	function submitForm(e) {
		e.preventDefault();

		var workspace = this.querySelector('#udb-admin-bar--workspace');
		var menuArray = {};
		
		var menuList = [];

		var menuItems = document.querySelectorAll('#' + workspace.id + ' > .udb-admin-bar--menu-list > .udb-admin-bar--menu-item');
		menuItems = menuItems.length ? menuItems : [];

		menuItems.forEach(function (menuItem) {
			var menuData = {};

			menuData.was_added = menuItem.dataset.added;

			menuData.id = '';
			menuData.href_default = menuItem.dataset.defaultHref;

			menuData.id_default = menuItem.dataset.defaultId;
			menuData.title = menuItem.querySelector('[data-name="menu_title"]').value;
			menuData.href = menuItem.querySelector('[data-name="menu_href"]').value;
			menuData.icon = menuItem.querySelector('[data-name="menu_icon"]').value;

			if (parseInt(menuItem.dataset.added, 10)) {
				menuData.id = menuItem.dataset.defaultId;
				menuData.class_default = 'menu-top menu-icon-custom udb-menu-top udb-menu-icon-custom';
			}

			var submenuItems = menuItem.querySelectorAll('.udb-admin-bar--submenu-item');
			submenuItems = submenuItems.length ? submenuItems : [];
			var submenuList = [];

			submenuItems.forEach(function (submenuItem) {
				var submenuData = {};

				submenuData.was_added = submenuItem.dataset.added;
				submenuData.title = submenuItem.querySelector('[data-name="submenu_title"]').value;
				submenuData.href = submenuItem.querySelector('[data-name="submenu_href"]').value;
				submenuData.href_default = submenuItem.dataset.defaultHref;

				submenuList.push(submenuData);
			});

			if (submenuList) menuData.submenu = submenuList;
			menuList.push(menuData);
		});

		menuArray[workspace.dataset.role] = menuList;

		saveMenu(menuArray);
	}

	/**
	 * Send ajax request to save the menu list.
	 *
	 * @param {array} menuArray The menu array.
	 */
	function saveMenu(menuArray) {
		if (state.isSaving) return;
		state.isSaving = true;

		loading.start(elms.saveButton);

		$.ajax({
			url: ajaxurl,
			type: 'post',
			dataType: 'json',
			data: {
				action: 'udb_admin_bar_save_menu',
				nonce: udbAdminBar.nonces.saveMenu,
				menu: JSON.stringify(menuArray)
			}
		}).done(function (r) {
			location.reload();
		}).always(function () {
			loading.stop(elms.saveButton);
			state.isSaving = false;
		});
	}

	/**
	 * Send ajax request to reset the menu list.
	 */
	function resetMenu() {
		var button = this;
		var role = this.dataset.role;

		if (state.isSaving) return;
		state.isSaving = true;

		loading.start(button);

		$.ajax({
			url: ajaxurl,
			type: 'post',
			dataType: 'json',
			data: {
				action: 'udb_admin_bar_reset_menu',
				nonce: udbAdminMenu.nonces.resetMenu,
				role: role
			}
		}).done(function (r) {
			location.reload();
		}).always(function () {
			loading.stop(button);
			state.isSaving = false;
		});
	}

	init();
})(jQuery);