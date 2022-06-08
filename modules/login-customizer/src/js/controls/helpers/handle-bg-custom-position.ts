declare var wp: any;

const handleBgCustomPostion = (keyPrefix: string, position: string) => {
	if (position == "custom") {
		wp.customize
			.control("udb_login[" + keyPrefix + "bg_horizontal_position]")
			.activate();

		wp.customize
			.control("udb_login[" + keyPrefix + "bg_vertical_position]")
			.activate();
	} else {
		wp.customize
			.control("udb_login[" + keyPrefix + "bg_horizontal_position]")
			.deactivate();

		wp.customize
			.control("udb_login[" + keyPrefix + "bg_vertical_position]")
			.deactivate();
	}
}

export default handleBgCustomPostion;