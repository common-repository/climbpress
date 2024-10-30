<?php

namespace ClimbPress;

use WP_Role;

class Permissions {

	const ROLE_MANAGER = "climbpress_manager";

	const CAPABILITY_MANAGE = "manage_climbpress";

	public function __construct() {
		add_action( 'admin_init', [ $this, 'init' ] );
		add_filter( 'user_has_cap', [ $this, 'user_has_cap' ] );
	}

	private function getManagerCaps() {
		return [ "read", static::CAPABILITY_MANAGE ];
	}

	public function init() {
		$role = get_role( static::ROLE_MANAGER );
		if ( ! ( $role instanceof WP_Role ) ) {
			$role = add_role(
				static::ROLE_MANAGER,
				'ClimbPress manager',
				[
					"read"                    => true,
					static::CAPABILITY_MANAGE => true,
				]
			);
		}
		foreach ( $this->getManagerCaps() as $cap ) {
			if ( ! $role->has_cap( $cap ) ) {
				$role->add_cap( $cap );
			}
		}
	}

	public function user_has_cap( $allCaps ) {
		if ( $allCaps["manage_options"] ?? false ) {
			$allCaps[ static::CAPABILITY_MANAGE ] = true;
		}

		return $allCaps;
	}
}
