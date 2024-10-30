<?php

/**
 * Plugin Name: ClimbPress
 * Plugin URI: https://www.climbpress.com/
 * Description: Route management for climbing and boulder gyms
 * Version: 0.7.0
 * Author: Edward Bock <hi@edwardbock.de>
 * Author URI: https://www.edwardbock.de
 * Requires at least: 5.0
 * Tested up to: 6.5.0
 * Requires PHP: 8.0
 * Text Domain: climbpress
 * License: http://www.gnu.org/licenses/gpl-2.0.html GPLv2
 * @copyright Copyright by Edward Bock
 * @package ClimbPress
 *
 */

namespace ClimbPress;

use ClimbPress\Components\Templates;
use ClimbPress\Store\GradingSystemsSource;
use ClimbPress\Store\RoutesSource;

require_once __DIR__ . "/vendor/autoload.php";

class Plugin extends Components\Plugin {

	const DOMAIN = "climbpress";

	const HANDLE_PUBLIC_API_SCRIPT = "climbpress-public-api";

	const REST_NAMESPACE = "climbpress/v1";

	const OPTION_DEFAULT_GRADING_SYSTEM = "_climbpress_default_grading_system";
	const OPTION_API_KEY = "_climbpress_api_key";
	const OPTION_VERSION = "climbpress_version";
	const FILTER_GRADING_SYSTEMS = "climbpress_grading_systems";
	const FILTER_ROUTE_METAS = "climbpress_route_metas";
	const FILTER_VOTE_ID_GENERATOR = "climbpress_vote_id_generator";
	const FILTER_VOTING_PAGE_PARAM = "climbpress_voting_page_param";
	public GradingSystemsSource $gradingSystemsSource;
	public RoutesSource $routesSource;
	public RouteMetaController $routeMetaController;
	public REST $rest;
	public Assets $assets;
	public Menu $menu;
	public Export $export;
	public Templates $templates;
	public Repository $repository;
	public Permissions $permissions;
	public VotePage $votingPage;

	function onCreate() {

		$this->loadTextdomain( self::DOMAIN, "languages" );

		$this->templates = new Templates( $this->path );
		$this->templates->useThemeDirectory( "plugin-parts" );

		$this->gradingSystemsSource = new GradingSystemsSource();
		$this->routesSource         = new RoutesSource();
		$this->repository           = new Repository( $this->routesSource, $this->gradingSystemsSource );

		$this->permissions         = new Permissions();
		$this->routeMetaController = new RouteMetaController( $this );
		$this->rest                = new REST( $this );
		$this->assets              = new Assets( $this );

		$this->menu   = new Menu( $this );
		$this->export = new Export( $this );
		$this->votingPage = new VotePage( $this );

		new Gutenberg( $this );

		if ( WP_DEBUG ) {
			$this->routesSource->createTables();
			$this->gradingSystemsSource->createTables();
		}

	}

	public function onSiteActivation() {
		parent::onSiteActivation();
		$this->routesSource->createTables();
		$this->gradingSystemsSource->createTables();
		$this->permissions->init();
	}
}

Plugin::instance();

include_once __DIR__."/public-functions.php";
