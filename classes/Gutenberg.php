<?php

namespace ClimbPress;

use ClimbPress\Components\Component;
use ClimbPress\Model\IGradingSystem;

class Gutenberg extends Component {

	const CATEGORY = "climbpress";

	public function onCreate() {
		parent::onCreate();
		add_action( 'init', [ $this, 'register_blocks' ], 20 );
		add_filter( 'block_categories_all', [ $this, 'block_categories_all' ] );
		add_filter( 'script_loader_tag', [ $this, 'add_type_attribute' ], 10, 3 );
	}

	public function add_type_attribute( $tag, $handle, $src ) {
		// if not your script, do nothing and return original $tag
		if (
			'climbpress-lit-dev' !== $handle
			&&
			"wc-climbpress-routes" !== $handle
		) {
			return $tag;
		}
		// change the script tag by adding type="module" and return it.
		$tag = '<script type="module" src="' . esc_url( $src ) . '"></script>';

		return $tag;
	}

	public function register_blocks() {
		$assets = new Components\Assets( $this->plugin->path, $this->plugin->url );

		$assets->registerScript(
			Plugin::HANDLE_PUBLIC_API_SCRIPT,
			"dist/public-api.js"
		);
		$this->localize( Plugin::HANDLE_PUBLIC_API_SCRIPT );

		$assets->registerScript(
			"wc-climbpress-routes",
			"web-components/routes.js",
			[ Plugin::HANDLE_PUBLIC_API_SCRIPT ]
		);
		$assets->registerStyle(
			"wc-climbpress-routes",
			"web-components/web-components.css",
		);

		$assets->registerScript(
			"climbpress-gutenberg",
			"dist/gutenberg.js",
		);
		$assets->registerStyle(
			"climbpress-gutenberg",
			"dist/gutenberg.css"
		);

		register_block_type(
			$this->plugin->path . "/blocks/routes",
			[
				'render_callback' => function () {
					ob_start();
					$i18n = [
						"searchPlaceholder" => __( "Search...", Plugin::DOMAIN ),
					];
					$routeMetaStructure = $this->plugin->routeMetaController->getRouteMetaStructure()->toArray();
					$routes            = $this->plugin->routesSource->getAll();

					$routes = array_map(function($route){
						$votings = $this->plugin->routesSource->getVotings($route->id);
						if(count($votings) > 0){
							$overallVotes = 0;
							$overallDifficultySum = 0;
							foreach ($votings as $voting){
								$overallVotes += $voting["votes"];
								$overallDifficultySum += ($voting["votes"] * $voting["difficulty"]);
							}
							$route->communityVotesCount = $overallVotes;
							$route->communityAverageDifficulty = round($overallDifficultySum / $overallVotes);
						}
						return $route;
					}, $routes);

					$allGradingSystems = $this->plugin->gradingSystemsSource->getGradingSystems();
					$gradingSystems    = array_map(
						function ( IGradingSystem $system ) {
							return $system->toArray();
						},
						array_filter(
							$allGradingSystems,
							function ( $system ) use ( $routes ) {
								foreach ( $routes as $route ) {
									if ( $route->gradingSystem == $system->getId() ) {
										return true;
									}
								}

								return false;
							}
						)
					);
					include $this->plugin->templates->get_template_path( "climbpress-routes.php" );
					$content = ob_get_contents();
					ob_end_clean();

					return $content;
				},
			]
		);
	}

	public function block_categories_all( $categories ) {
		array_unshift( $categories, [
			"slug"  => Gutenberg::CATEGORY,
			"title" => "ClimbPress",
		] );

		return $categories;
	}

	private function localize( $handle ) {
		wp_localize_script(
			$handle,
			"ClimbPressPublic",
			[
				"RESTNamespace"  => Plugin::REST_NAMESPACE,
			]
		);
	}
}
