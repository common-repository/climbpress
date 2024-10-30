<?php

namespace ClimbPress\Store;

use ClimbPress\Components\Database;
use ClimbPress\Model\Grade;
use ClimbPress\Model\GradingSystem\AbsGradingSystem;
use ClimbPress\Model\GradingSystem\CustomGradingSystem;
use ClimbPress\Model\GradingSystem\Fontainebleau;
use ClimbPress\Model\GradingSystem\French;
use ClimbPress\Model\GradingSystem\Hueco;
use ClimbPress\Model\GradingSystem\UIAA;
use ClimbPress\Model\GradingSystem\YDS;
use ClimbPress\Model\IGradingSystem;
use ClimbPress\Plugin;

class GradingSystemsSource extends Database {

	private string $table;
	private string $table_values;

	function init() {
		$this->table        = $this->wpdb->prefix . "climbpress_grading_systems";
		$this->table_values = $this->wpdb->prefix . "climbpress_grading_system_values";

		add_filter( Plugin::FILTER_GRADING_SYSTEMS, [ $this, 'add_custom_grading_systems' ] );
	}

	public function getDefaultGradingSystem(): string {
		return get_option( Plugin::OPTION_DEFAULT_GRADING_SYSTEM, "uiaa" );
	}

	public function addGradingSystem( $name ) {
		$slug = sanitize_title_with_dashes( $name );
		return $this->wpdb->insert(
			$this->table,
			[
				"id"          => $slug,
				"system_name" => $name,
			],
			[ "%s", "%s" ]
		);

	}

	/**
	 * @return IGradingSystem[]
	 */
	public function getGradingSystems(): array {
		return apply_filters( Plugin::FILTER_GRADING_SYSTEMS, [
			new YDS(),
			new UIAA(),
			new French(),
			new Fontainebleau(),
			new Hueco(),
		] );
	}

	/**
	 * @param AbsGradingSystem[] $systems
	 *
	 * @return array
	 */
	public function add_custom_grading_systems( array $systems ): array {
		$dbSystems = $this->wpdb->get_results( "SELECT * FROM $this->table" );
		foreach ( $dbSystems as $system ) {
			$dbGrades = $this->wpdb->get_results(
				$this->wpdb->prepare( "SELECT * FROM $this->table_values WHERE system_id = %s ORDER BY difficulty_value ASC", $system->id )
			);
			$grades   = [];
			foreach ( $dbGrades as $grade ) {
				$grades[] = new Grade( $grade->difficulty_value, $grade->difficulty_label );
			}

			array_unshift( $systems, new CustomGradingSystem( $system->id, $system->system_name, $grades ) );
		}

		return $systems;
	}

	/**
	 * @return array
	 */
	public function getGradingSystemsAsArray(): array {
		return array_map( function ( $system ) {
			return $system->toArray();
		}, $this->getGradingSystems() );
	}

	public function createTables() {
		parent::createTables();

		\dbDelta( "CREATE TABLE IF NOT EXISTS $this->table
			(
    		 id varchar(60) NOT NULL,
    		 system_name varchar(160) NOT NULL,
			 primary key (id),
    		 key (system_name)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;" );

		\dbDelta( "CREATE TABLE IF NOT EXISTS $this->table_values
			(
			 id bigint unsigned auto_increment,
    		 system_id varchar(60) NOT NULL,
			 difficulty_value int unsigned NOT NULL,
    		 difficulty_label varchar(40) NOT NULL,
			 
			 primary key (id),
    		 key (system_id),
			 unique key system_difficulty (system_id, difficulty_value),
    		 foreign key (system_id) references $this->table (id) ON DELETE CASCADE
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;" );
	}


}
