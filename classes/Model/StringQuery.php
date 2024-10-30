<?php

namespace ClimbPress\Model;

use WP_Error;

class StringQuery {

	private static array $validOperators = [ "eq", "like" ];
	private static array $operatorToComparator = [
		"eq"   => "=",
		"like" => "LIKE",
	];
	public string $comparator;
	public string $value;

	public function __construct( string $value, string $comparator = "=" ) {
		$this->comparator = $comparator;
		$this->value      = $value;
	}

	/**
	 * @param string $query
	 *
	 * @return StringQuery|WP_Error
	 */
	public static function parse( string $query ) {

		if ( strpos( $query, ":" ) > 0 ) {
			$parts = explode( ":", $query );
			if ( count( $parts ) != 2 ) {
				return new WP_Error( 400, "More than two parts of the string query" );
			}

			if ( ! in_array( $parts[0], static::$validOperators ) ) {
				$operators = implode( ", ", static::$validOperators );

				return new WP_Error( 400, "Invalid string query syntax. Operator needs to be one of: $operators" );
			}

			return new StringQuery(
				 $parts[1],
				static::$operatorToComparator[ $parts[0] ]
			);
		}

		return new StringQuery($query);
	}
}
