<?php

namespace ClimbPress;

use ClimbPress\Model\IntegerQuery;
use ClimbPress\Model\Route;
use ClimbPress\Model\RouteQueryArgs;
use ClimbPress\Model\StringQuery;
use ClimbPress\Store\RoutesSource;

/**
 *
 */
class REST extends Components\Component {

	public function onCreate() {
		parent::onCreate();
		add_action( 'rest_api_init', [ $this, 'rest_api_init' ] );
	}

	public function rest_api_init() {

		register_rest_route(
			Plugin::REST_NAMESPACE,
			"/grading-systems",
			[
				"methods"             => \WP_REST_Server::READABLE,
				'permission_callback' => '__return_true',
				"callback"            => function () {
					return $this->plugin->gradingSystemsSource->getGradingSystemsAsArray();
				}
			]
		);

		register_rest_route(
			Plugin::REST_NAMESPACE,
			"/grading-systems",
			[
				"methods"             => \WP_REST_Server::CREATABLE,
				'permission_callback' => function () {
					return current_user_can( "manage_options" );
				},
				"args"                => [
					"name" => [
						"required" => true,
						"type"     => "string",
					],
				],
				'callback'            => function ( \WP_REST_Request $request ) {
					$name = $request->get_param( "name" );

					// TODO: add to database
					$this->plugin->gradingSystemsSource->addGradingSystem( $name );

					return true;
				}
			]
		);

		register_rest_route(
			Plugin::REST_NAMESPACE,
			"/grading-systems/(?P<id>[a-zA-Z0-9-]+)",
			[
				"methods"             => \WP_REST_Server::READABLE,
				'permission_callback' => '__return_true',
				"args"                => [
					"id" => [
						"validate_callback" => function ( $value ) {
							foreach ( $this->plugin->gradingSystemsSource->getGradingSystems() as $system ) {
								if ( $system->getId() == $value ) {
									return true;
								}
							}

							return false;
						},
					]
				],
				"callback"            => function ( \WP_REST_Request $request ) {
					$id = $request->get_param( "id" );
					foreach ( $this->plugin->gradingSystemsSource->getGradingSystems() as $system ) {
						if ( $system->getId() == $id ) {
							return $system->toArray();
						}
					}

					return [];
				}
			]
		);

		$stringQueryValidate  = function ( $value ) {
			$string = StringQuery::parse( sanitize_text_field( $value ) );

			return $string instanceof StringQuery ? true : $string;
		};
		$stringQuerySanitize  = function ( $value ) {
			return StringQuery::parse( sanitize_text_field( $value ) );
		};
		$integerQueryValidate = function ( $value ) {
			$integer = IntegerQuery::parse( $value );

			return $integer instanceof IntegerQuery ? true : $integer;
		};
		$integerQuerySanitize = function ( $value ) {
			return IntegerQuery::parse( $value );
		};

		register_rest_route(
			Plugin::REST_NAMESPACE,
			"/route-meta-structure",
			[
				"methods"             => \WP_REST_Server::READABLE,
				'permission_callback' => '__return_true',
				"callback"            => function () {
					return $this->plugin->routeMetaController->getRouteMetaStructure()->toArray();
				}
			]
		);

		// READ
		register_rest_route(
			Plugin::REST_NAMESPACE,
			"/routes",
			[
				"methods"             => \WP_REST_Server::READABLE,
				'permission_callback' => '__return_true',
				'args'                => [
					"page"            => [
						"required"          => false,
						"default"           => 1,
						"validate_callback" => function ( $value ) {
							return intval( $value ) > 0;
						},
						"sanitize_callback" => function ( $value ) {
							return intval( $value );
						}
					],
					"per_page"        => [
						"required"          => false,
						"default"           => - 1,
						"validate_callback" => function ( $value ) {
							return intval( $value ) > 0 || intval( $value ) == - 1;
						},
						"sanitize_callback" => function ( $value ) {
							return intval( $value );
						}
					],
					"route_status"          => [
						"required"          => false,
						"default"           => RoutesSource::STATUS_SCREWED_ON,
						"validate_callback" => function ( $value ) {
							return in_array(
								$value,
								[
									RoutesSource::STATUS_SCREWED_ON,
									RoutesSource::STATUS_SCREWED_OFF,
									RoutesSource::STATUS_SCREWED_ANY,
								]
							);
						},
					],
					"year"            => [
						"required"          => false,
						"type"              => "integer",
						"default"           => 0,
						"sanitize_callback" => function ( $value ) {
							return intval( $value );
						}
					],
					"route_name"            => [
						"required"          => false,
						"validate_callback" => $stringQueryValidate,
						"sanitize_callback" => $stringQuerySanitize,
					],
					"difficulty"      => [
						"required"          => false,
						"validate_callback" => $integerQueryValidate,
						"sanitize_callback" => $integerQuerySanitize,
					],
					"grading_system"  => [
						"required"          => false,
						"validate_callback" => $stringQueryValidate,
						"sanitize_callback" => $stringQuerySanitize,
					],
					"order_by"        => [
						"required"          => false,
						"default"           => "created",
						"validate_callback" => function ( $value ) {

							$fields = [
								"route_name",
								"created",
								"route_status",
								"difficulty",
								"grading_system"
							];

							$metaKeys = array_map( function ( $key ) {
								return "metas.$key";
							}, $this->plugin->routeMetaController->getRouteMetaKeys() );

							$allowedKeys = array_merge( $fields, $metaKeys );

							return ( in_array( $value, $allowedKeys ) );
						},
						"sanitize_callback" => 'sanitize_text_field',
					],
					"order_direction" => [
						"required"          => false,
						"default"           => "desc",
						"validate_callback" => function ( $value ) {
							return in_array( $value, [ "desc", "asc" ] );
						},
					],
					"metas"           => [
						"required"          => false,
						"default"           => [],
						"validate_callback" => function ( $value ) use ( $stringQueryValidate ) {
							if ( ! is_array( $value ) ) {
								return false;
							}
							$keys     = array_keys( $value );
							$metaKeys = $this->plugin->routeMetaController->getRouteMetaKeys();
							foreach ( $keys as $key ) {
								if ( ! in_array( $key, $metaKeys ) ) {
									return false;
								}
								if ( ! $stringQueryValidate( $value[ $key ] ) ) {
									return false;
								}
							}

							return true;
						},
						"sanitize_callback" => function ( array $value ) use ( $stringQuerySanitize ) {
							$sanitized = [];
							foreach ( $value as $key => $curr ) {
								$sanitized[ sanitize_text_field( $key ) ] = $stringQuerySanitize( $curr );
							}

							return $sanitized;
						}
					],

				],
				"callback"            => function ( \WP_REST_Request $request ) {
					$args = new RouteQueryArgs();

					$args->page    = $request->get_param( "page" );
					$args->perPage = $request->get_param( "per_page" );

					$args->orderBy        = $request->get_param( "order_by" );
					$args->orderDirection = $request->get_param( "order_direction" );

					$args->setStatus( $request->get_param( "status" ) ?? "" );

					$args->year = $request->get_param( "year" );

					$name = $request->get_param( "name" );
					if ( $name instanceof StringQuery ) {
						$args->name = $name;
					}

					$difficultyQuery = $request->get_param( "difficulty" );

					if ( $difficultyQuery instanceof IntegerQuery ) {
						$args->difficulty = $difficultyQuery;
					}

					$args->metas = $request->get_param( "metas" );

					$count = $this->plugin->routesSource->count( $args );

					$totalPages = ($count == 0) ? 0 : (
						($args->perPage > 0) ? ceil( $count / $args->perPage ) : 1
					);

					$routes = $this->plugin->routesSource->getAll( $args );

					$response = new \WP_REST_Response( $routes );
					$response->header( "X-WP-Total", $count );
					$response->header( "X-WP-TotalPages", $totalPages );

					return $response;
				}
			]
		);

		// CREATE
		register_rest_route(
			Plugin::REST_NAMESPACE,
			"/routes",
			[
				"methods"             => \WP_REST_Server::CREATABLE,
				'permission_callback' => function () {
					return current_user_can( Permissions::CAPABILITY_MANAGE );
				},
				"args"                => [
					"name"           => [
						"required" => true,
						"type"     => "string",
					],
					"status"         => [
						"required"          => false,
						"default"           => RoutesSource::STATUS_SCREWED_ON,
						"validate_callback" => function ( $value ) {
							return in_array(
								$value,
								[
									RoutesSource::STATUS_SCREWED_ON,
									RoutesSource::STATUS_SCREWED_OFF,
								]
							);
						}
					],
					"difficulty"     => [
						"required"          => true,
						"type"              => "integer",
						"validate_callback" => function ( $value ) {
							return $value > 0;
						}
					],
					"grading_system" => [
						"required"          => false,
						"type"              => "string",
						"default"           => $this->plugin->gradingSystemsSource->getDefaultGradingSystem(),
						"validate_callback" => function ( $value ) {
							$systems = $this->plugin->gradingSystemsSource->getGradingSystems();

							return count( array_filter( $systems, function ( $system ) use ( $value ) {
									return $system->getId() == $value;
								} ) ) == 1;
						}
					],
					"created"        => [
						"required"          => false,
						"type"              => "string",
						"default"           => date( "Y-m-d" ),
						"validate_callback" => function ( $value ) {
							return date_parse( $value ) != false;
						}
					],
					"metas"          => [
						"required" => false,
						"default"  => [],
					],
				],
				'callback'            => function ( \WP_REST_Request $request ) {
					$route = new Route(
						$request->get_param( "name" ),
						$request->get_param( "status" ),
						$request->get_param( "created" ),
						$request->get_param( "difficulty" ),
						$request->get_param( "grading_system" ),
					);

					$route->metas = $request->get_param( "metas" ); // TODO: validate and sanitize
					$route->id    = $this->plugin->routesSource->add( $route );

					return $route;
				}
			]
		);

		// READ
		register_rest_route(
			Plugin::REST_NAMESPACE,
			"/routes/(?P<id>[\d]+)",
			[
				"methods"             => \WP_REST_Server::READABLE,
				'permission_callback' => '__return_true',
				'args'                => [
					"id" => [
						"validate_callback" => function ( $id ) {
							$id = intval( $id );
							if ( $id <= 0 ) {
								return false;
							}
							$route = $this->plugin->routesSource->get( $id );

							return $route instanceof Route;
						},
					]
				],
				"callback"            => function ( \WP_REST_Request $request ) {
					$id = intval( $request->get_param( "id" ) );

					return new \WP_REST_Response(
						$this->plugin->routesSource->get( $id )
					);
				}
			]
		);

		// EDIT
		register_rest_route(
			Plugin::REST_NAMESPACE,
			"/routes/(?P<id>[\d]+)",
			[
				"methods"             => \WP_REST_Server::EDITABLE,
				'permission_callback' => function () {
					return current_user_can( Permissions::CAPABILITY_MANAGE );
				},
				'args'                => [
					"id"             => [
						"validate_callback" => function ( $id ) {
							$id = intval( $id );
							if ( $id <= 0 ) {
								return false;
							}
							$route = $this->plugin->routesSource->get( $id );

							return $route instanceof Route;
						},
					],
					"name"           => [
						"required" => false,
						"type"     => "string",
					],
					"status"         => [
						"required"          => false,
						"validate_callback" => function ( $value ) {
							return in_array(
								$value,
								[
									RoutesSource::STATUS_SCREWED_ON,
									RoutesSource::STATUS_SCREWED_OFF,
								]
							);
						}
					],
					"difficulty"     => [
						"required"          => false,
						"type"              => "integer",
						"validate_callback" => function ( $value ) {
							return $value > 0;
						}
					],
					"grading_system" => [
						"required"          => false,
						"type"              => "string",
						"default"           => $this->plugin->gradingSystemsSource->getDefaultGradingSystem(),
						"validate_callback" => function ( $value ) {
							$systems = $this->plugin->gradingSystemsSource->getGradingSystems();

							return count( array_filter( $systems, function ( $system ) use ( $value ) {
									return $system->getId() == $value;
								} ) ) == 1;
						}
					],
					"created"        => [
						"required"          => false,
						"type"              => "string",
						"default"           => date( "Y-m-d" ),
						"validate_callback" => function ( $value ) {
							return date_parse( $value ) != false;
						}
					],
					"metas"          => [
						"required" => false,
						"default"  => [],
					],
				],
				"callback"            => function ( \WP_REST_Request $request ) {
					$id = intval( $request->get_param( "id" ) );

					$route = $this->plugin->routesSource->get( $id );

					$updates = [
						"name"           => "name",
						"status"         => "status",
						"difficulty"     => "difficulty",
						"grading_system" => "gradingSystem",
						"created"        => "created",
					];

					foreach ( $updates as $param => $prop ) {
						if ( $request->has_param( $param ) ) {
							$route->{$prop} = $request->get_param( $param );
						}
					}
					$route->metas = $request->get_param( "metas" );

					$this->plugin->routesSource->update( $route );

					return new \WP_REST_Response( $route );
				}
			]
		);

		// DELETE
		register_rest_route(
			Plugin::REST_NAMESPACE,
			"/routes/(?P<id>[\d]+)",
			[
				"methods"             => \WP_REST_Server::DELETABLE,
				'permission_callback' => function () {
					return current_user_can( Permissions::CAPABILITY_MANAGE );
				},
				"args"                => [
					"id" => [
						"validate_callback" => function ( $id ) {
							$id = intval( $id );
							if ( $id <= 0 ) {
								return false;
							}
							$route = $this->plugin->routesSource->get( $id );

							return $route instanceof Route;
						},
					],
				],
				"callback"            => function ( \WP_REST_Request $request ) {
					$id = $request->get_param( "id" );

					$this->plugin->routesSource->delete( $id );

					return true;
				}
			]
		);

		// READ
		register_rest_route(
			Plugin::REST_NAMESPACE,
			"/routes/(?P<id>[\d]+)/votes",
			[
				"methods"             => \WP_REST_Server::READABLE,
				'permission_callback' => '__return_true',
				'args'                => [
					"id" => [
						"validate_callback" => function ( $id ) {
							$id = intval( $id );
							if ( $id <= 0 ) {
								return false;
							}
							$route = $this->plugin->routesSource->get( $id );

							return $route instanceof Route;
						},
					]
				],
				"callback"            => function ( \WP_REST_Request $request ) {
					// get votes
					$id = intval( $request->get_param( "id" ) );

					return new \WP_REST_Response(
						$this->plugin->routesSource->getVotings( $id ),
					);
				}
			]
		);

		// EDIT
		register_rest_route(
			Plugin::REST_NAMESPACE,
			"/votes",
			[
				"methods"             => \WP_REST_Server::EDITABLE,
				'permission_callback' => function () {
					return true;
				},
				"args"                => [
					"voting_id"  => [
						"type"              => "string",
						"default"           => "",
						"validate_callback" => function ( $id ) {
							if ( $_SERVER['REQUEST_METHOD'] == "POST" && $id == "" ) {
								return true;
							}
							if ( $_SERVER["REQUEST_METHOD"] == "PATCH" ) {
								return $this->plugin->routesSource->votingExists( $id );
							}

							return false;
						}
					],
					"route_id"   => [
						"validate_callback" => function ( $id ) {
							$id = intval( $id );
							if ( $id <= 0 ) {
								return false;
							}
							$route = $this->plugin->routesSource->get( $id );

							return $route instanceof Route;
						},
					],
					"difficulty" => [
						"required" => true,
						"type"     => "integer",
					],
				],
				'callback'            => function ( \WP_REST_Request $request ) {
					$route_id   = $request->get_param( "route_id" );
					$voting_id  = $request->get_param( "voting_id" );
					$difficulty = $request->get_param( "difficulty" );
					if ( ! empty( $voting_id ) ) {
						return [
							"success" => $this->plugin->repository->updateVoting( $voting_id, $difficulty ),
						];
					}

					return [
						"voting_id" => $this->plugin->repository->addVoting( $route_id, $difficulty ),
					];
				}
			]
		);

		// DELETE
		register_rest_route(
			Plugin::REST_NAMESPACE,
			"/votes",
			[
				"methods"             => \WP_REST_Server::DELETABLE,
				'permission_callback' => function () {
					return true;
				},
				"args"                => [
					"id" => [
						"sanitize_callback" => function ( $value ) {
							return sanitize_text_field( $value );
						},
					],
				],
				"callback"            => function ( \WP_REST_Request $request ) {
					$vote_id = $request->get_param( "id" );

					return [
						"success" => $this->plugin->routesSource->deleteVoting( $vote_id ),
					];
				}
			]
		);
	}


}
