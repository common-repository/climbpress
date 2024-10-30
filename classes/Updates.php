<?php

namespace ClimbPress;

use ClimbPress\Components\Update;

class Updates extends Update {

	const CURRENT_VERSION = 1;

	function getVersion(): int {
		return intval(get_option(Plugin::OPTION_VERSION, '0'));
	}

	function getCurrentVersion(): int {
		return self::CURRENT_VERSION;
	}

	function setCurrentVersion( int $version ) {
		update_option(Plugin::OPTION_VERSION, $version);
	}


}