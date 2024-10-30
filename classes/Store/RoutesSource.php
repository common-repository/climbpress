<?php

namespace ClimbPress\Store;

use ClimbPress\Components\Database;
use ClimbPress\Model\IntegerQuery;
use ClimbPress\Model\Route;
use ClimbPress\Model\RouteQueryArgs;
use ClimbPress\Model\StringQuery;

class RoutesSource extends Database {

	const STATUS_PLANNED = "planned";
	const STATUS_SCREWED_ON = "screwed-on";
	const STATUS_SCREWED_OFF = "screwed-off";
	const STATUS_SCREWED_ANY = "any";
	public string $table;
	public string $tableMeta;
	public string $tableVoting;
	/**
	 * @var string[]
	 */
	private array $validStatus;

	function init() {
		$this->validStatus = [ self::STATUS_PLANNED, self::STATUS_SCREWED_OFF, self::STATUS_SCREWED_ON ];
		$this->table       = $this->wpdb->prefix . "climbpress_routes";
		$this->tableMeta   = $this->wpdb->prefix . "climbpress_routes_meta";
		$this->tableVoting = $this->wpdb->prefix . "climbpress_routes_voting";
	}

	public function get( int $id ): ?Route {
		$sql  = $this->wpdb->prepare(
			"SELECT * FROM $this->table as routes 
    
         LEFT JOIN (
             SELECT route_id, JSON_OBJECTAGG(meta_key, meta_value) as metaData from $this->tableMeta GROUP BY route_id
         ) as metas ON ( routes.id = metas.route_id )
    
         LEFT JOIN (
         	SELECT route_id, count(*) as votesCount, avg(difficulty) as averageDifficulty, STDDEV(difficulty) standardDeviationDifficulty FROM $this->tableVoting GROUP BY route_id
         ) as votings ON (routes.id = votings.route_id)

         WHERE id = %d",
			$id
		);

		$rows = $this->wpdb->get_results( $sql );
		if ( ! is_array( $rows ) || count( $rows ) == 0 ) {
			return null;
		}

		$row = $rows[0];

		$route = new Route(
			$row->route_name,
			$row->route_status,
			$row->created,
			$row->difficulty,
			$row->grading_system,
			$row->id,
			communityVotesCount: intval($row->votesCount),
			communityAverageDifficulty: round(floatval($row->averageDifficulty)),
		);

		$route->metas = $row->metaData != null ? json_decode($row->metaData, JSON_OBJECT_AS_ARRAY) : [];

		return $route;
	}

	/**
	 *
	 * @return Route[]
	 */
	public function getAll( ?RouteQueryArgs $args = null ): array {

		if ( ! ( $args instanceof RouteQueryArgs ) ) {
			$args = new RouteQueryArgs();
		}

		$conditions = $this->buildConditions( $args );

		$where = ! empty( $conditions ) ? "WHERE $conditions" : "";

		$limit = $this->buildLimit( $args );

		$orderJoin = "";
		if(str_starts_with($args->orderBy, "metas.")){
			$metaValue = substr($args->orderBy, strlen("metas."));
			$orderJoin = $this->wpdb->prepare("LEFT JOIN (
				SELECT route_id, meta_value FROM $this->tableMeta WHERE meta_key = %s
			) as orderValue ON (routes.id = orderValue.route_id)", $metaValue);
			$orderBy = "orderValue.meta_value";
		} else {
			$orderBy = $args->orderBy;
		}

		$sql = "
			SELECT * FROM 
			             
			(
			    SELECT * FROM $this->table WHERE id IN
	            (
		            SELECT id from $this->table as routes
					LEFT JOIN $this->tableMeta as metas ON (routes.id = metas.route_id)
		            $where
		        )
			) as routes
			
			LEFT JOIN 
			(
				SELECT route_id, JSON_OBJECTAGG(meta_key, meta_value) as metaData from $this->tableMeta GROUP BY route_id
			) as metas ON (routes.id = metas.route_id)
			           
			LEFT JOIN (
	            SELECT route_id, count(*) as votesCount, avg(difficulty) as averageDifficulty, STDDEV(difficulty) standardDeviationDifficulty FROM $this->tableVoting GROUP BY route_id
	        ) as votings ON (routes.id = votings.route_id)
			           
			$orderJoin
			         
			ORDER BY $orderBy $args->orderDirection, id desc $limit
             ";

		$results = $this->wpdb->get_results( $sql );

		$routes = [];
		foreach ( $results as $row ) {

			$route = new Route(
				$row->route_name,
				$row->route_status,
				$row->created,
				$row->difficulty,
				$row->grading_system,
				$row->id,
				communityVotesCount: intval($row->votesCount),
				communityAverageDifficulty: round(floatval($row->averageDifficulty)),
			);

			$route->metas = $row->metaData != null ? json_decode($row->metaData, JSON_OBJECT_AS_ARRAY) : [];

			$routes[] = $route;
		}

		return $routes;
	}

	public function count( ?RouteQueryArgs $args = null ): int {

		if ( ! ( $args instanceof RouteQueryArgs ) ) {
			$args = new RouteQueryArgs();
		}

		$conditions = $this->buildConditions( $args );

		$where = ! empty( $conditions ) ? "where $conditions" : "";


		$sql = "
			SELECT count(id) FROM $this->table WHERE id IN
	            (
		            SELECT id from $this->table as routes
					LEFT JOIN $this->tableMeta as metas ON (routes.id = metas.route_id)
		            $where 
		        ) 
		";

		return intval( $this->wpdb->get_var( $sql ) );
	}

	private function buildConditions( RouteQueryArgs $args ): string {

		$conditions = [];

		if ( $args->name instanceof StringQuery ) {
			$conditions[] = $this->buildStringQuery( $args->name, "route_name" );
		}

		if ( $args->year > 1900 ) {
			$conditions[] = $this->wpdb->prepare( "YEAR(created) = $args->year" );
		}

		if ( ! empty( $args->status ) && $args->status != self::STATUS_SCREWED_ANY ) {
			$conditions[] = $this->wpdb->prepare( "route_status = %s", $args->status );
		}

		$difficultyQuery = $args->difficulty;
		if ( $difficultyQuery instanceof IntegerQuery ) {
			$conditions[] = $this->buildIntegerQuery( $difficultyQuery, "difficulty" );
		}

		$metaConditions = [];
		foreach ( $args->metas as $key => $query ) {

			$parts = [];
			$parts[] = $this->wpdb->prepare("metas.meta_key = %s", $key);
			$parts[] = $this->buildStringQuery( $query, "metas.meta_value" );
			$metaConditions[] = " ( ".implode(" AND ", $parts)." ) ";
		}

		if(count($metaConditions) > 0){
			$conditions[] = " ( ".implode(" OR ", $metaConditions)." ) ";
		}

		return implode( " AND ", $conditions );
	}

	private function buildIntegerQuery( IntegerQuery $query, string $columnName ): string {
		if ( count( $query->values ) == 2 ) {
			$diffA = $query->values[0];
			$diffB = $query->values[1];

			return $this->wpdb->prepare( "($columnName between %d AND %d)", $diffA, $diffB );
		} else if ( count( $query->values ) == 1 ) {
			$comparator = $query->comparator;
			$difficulty = $query->values[0];

			return $this->wpdb->prepare( "($columnName $comparator %d)", $difficulty );
		}

		return "1 = 1"; // just to ignore errors
	}

	private function buildStringQuery( StringQuery $query, string $columnName ): string {
		$comparator = $query->comparator;
		$value      = $comparator == "LIKE" ? "%" . strtolower( $query->value ) . "%" : $query->value;
		$col        = $comparator == "LIKE" ? "LOWER($columnName)" : $columnName;

		return $this->wpdb->prepare( "$col $comparator %s", $value );
	}

	private function buildLimit( RouteQueryArgs $args ): string {

		if (
			$args->perPage <= 0 || $args->page < 1
		) {
			return "";
		}
		$perPage = $args->perPage;
		$page    = $args->page;
		$offset  = $perPage * ( $page - 1 );

		return "LIMIT $perPage OFFSET $offset";
	}

	public function add( Route $route ): int {

		$result = $this->routeToValues( $route );

		$this->wpdb->insert(
			$this->table,
			$result->values,
			$result->format
		);

		$routeId = $this->wpdb->insert_id;

		foreach ( $route->metas as $key => $value ) {
			$this->wpdb->replace(
				$this->tableMeta,
				[
					"route_id"   => $routeId,
					"meta_key"   => $key,
					"meta_value" => $value,
				]
			);
		}

		return $routeId;
	}

	public function update( Route $route ) {

		$result = $this->routeToValues( $route );

		foreach ( $route->metas as $key => $value ) {
			$this->wpdb->replace(
				$this->tableMeta,
				[
					"route_id"   => $route->id,
					"meta_key"   => $key,
					"meta_value" => $value,
				]
			);
		}

		return $this->wpdb->update(
			$this->table,
			$result->values,
			[ "id" => $route->id ],
			$result->format,
			[ "%d" ]
		);
	}

	public function delete( $id ) {
		return $this->wpdb->delete(
			$this->table,
			[ "id" => $id ],
			[ "%d" ]
		);
	}

	public function getAvailableYears(): array {
		$years = $this->wpdb->get_col( "
SELECT distinct year(created) as created_year FROM $this->table ORDER BY created_year DESC
" );

		return array_values(
			array_unique(
				array_filter(
					array_merge( [ date( "Y" ) ], $years ),
					function ( $year ) {
						return $year != "0";
					}
				)
			)
		);

	}

	private function routeToValues( Route $route ): \stdClass {
		$result         = new \stdClass();
		$result->values = [
			"route_name"     => $route->name,
			"route_status"   => in_array( $route->status, $this->validStatus ) ? $route->status : self::STATUS_SCREWED_OFF,
			"created"        => $route->created,
			"difficulty"     => $route->difficulty,
			"grading_system" => $route->gradingSystem,
		];
		$result->format = [ "%s", "%s", "%s", "%d", "%s" ];

		return $result;
	}

	public function insertVoting( string $voting_id, int $route_id, int $difficulty ): bool {

		$success = $this->wpdb->insert(
			$this->tableVoting,
			[
				"id"         => $voting_id,
				"route_id"   => $route_id,
				"difficulty" => $difficulty,
			],
			[ "%s", "%d", "%d" ]
		);

		return $success !== false && $success !== 0;
	}

	public function updateVoting( string $voting_id, int $value ): bool {

		$success = $this->wpdb->update(
			$this->tableVoting,
			[
				"difficulty" => $value,

			],
			[ "id" => $voting_id ],
			[ "%d" ],
			[ "%s" ],
		);

		return $success !== false && $success !== 0;
	}

	public function getRouteByVoting( string $voting_id ): Route|null {
		$route_id = $this->wpdb->get_var(
			$this->wpdb->prepare(
				"SELECT route_id FROM $this->tableVoting WHERE id = %s",
				$voting_id
			)
		);
		if ( ! is_string( $route_id ) ) {
			return null;
		}

		return $this->get( intval( $route_id ) );
	}

	public function votingExists( string $id ): bool {
		$table = $this->tableVoting;
		$count = $this->wpdb->get_var(
			$this->wpdb->prepare(
				"SELECT count(id) from $table WHERE id = %s",
				$id
			)
		);

		return intval( $count ) > 0;
	}

	public function deleteVoting( string $voting_id ): bool {
		$success = $this->wpdb->delete(
			$this->tableVoting,
			[ "id" => $voting_id ],
			[ "%s" ]
		);

		return $success !== false && $success !== 0;
	}

	/**
	 * @param int $route
	 *
	 * @return array[]
	 */
	public function getVotings( int $route ) {
		$results = $this->wpdb->get_results(
			$this->wpdb->prepare(
				"SELECT count(difficulty) as votes, difficulty FROM $this->tableVoting WHERE route_id = %d GROUP BY difficulty ORDER BY difficulty",
				$route
			)
		);

		if(!is_array($results)) return [];

		return array_map(function($row){
			return [
				"votes" => intval($row->votes),
				"difficulty" => intval($row->difficulty),
			];
		}, $results);
	}

	public function createTables() {
		parent::createTables();

		\dbDelta( "CREATE TABLE IF NOT EXISTS $this->table
			(
			 id bigint unsigned auto_increment,
    		 route_name varchar(160) NOT NULL,
    		 created date NOT NULL,
			 route_status varchar(80) NOT NULL default '',
    		 difficulty int unsigned NOT NULL,
    		 grading_system varchar(80) NOT NULL default '',
			 primary key (id),
    		 key (route_name),
    		 key (created),
    		 key (route_status),
			 key (difficulty),
    		 key (grading_system)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;"
		);
		\dbDelta( "CREATE TABLE IF NOT EXISTS $this->tableMeta
			(
    		 route_id bigint unsigned NOT NULL,
    		 meta_key varchar(80) NOT NULL,
			 meta_value varchar(160) NOT NULL default '',
			 primary key (route_id, meta_key),
    		 key (route_id),
			 key (meta_key),
    	     key (meta_value),
    		 foreign key (route_id) references $this->table (id) ON DELETE CASCADE
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;"
		);
		\dbDelta( "CREATE TABLE IF NOT EXISTS $this->tableVoting
			(
			 id varchar(100) NOT NULL,
    		 route_id bigint unsigned NOT NULL,
    		 difficulty int NOT NULL,
    		 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    		 primary key (id),
    		 key (route_id),
    		 foreign key (route_id) references $this->table (id) ON DELETE CASCADE
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;"
		);

	}


}
