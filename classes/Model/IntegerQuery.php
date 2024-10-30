<?php

namespace ClimbPress\Model;

use WP_Error;

/**
 */
class IntegerQuery {

	private static array $validOperators = ["eq", "ne","gt","gte","lt","lte"];
	private static array $operatorToComparator = [
		"eq" => "=",
		"ne" => "!=",
		"gt" => ">",
		"gte" => ">=",
		"lt" => "<",
		"lte" => "<="
	];
	public string $comparator;
	/**
	 * @var numeric[]
	 */
	public array $values;

	public function __construct( array $values, string $comparator = "=" ) {
		$this->comparator = $comparator;
		$this->values     = $values;
	}

	/**
	 * @param string $query
	 *
	 * @return IntegerQuery|WP_Error
	 */
	public static function parse( string $query ) {

		if ( strpos( $query, "-" ) > 0 ) {
			$parts = explode( "-", $query );
			if ( count( $parts ) !== 2 ) {
				return new WP_Error( 400, "More than two parts of the integer between query" );
			}

			if(!is_numeric($parts[0]) || !is_numeric($parts[1])) {
				return new WP_Error( 400, "Values need to be of type integer" );
			}

			$difficulty = new IntegerQuery(
				array_map( function ( $part ) {
					return intval( $part );
				}, $parts ),
				"between"
			);

			if ( $difficulty->values[0] > $difficulty->values[1] ) {
				return new WP_Error( 400, "First value needs to be smaller than second" );
			}

			return $difficulty;
		}

		if(strpos($query,":") > 0) {
			$parts = explode( ":", $query );
			if ( count( $parts ) != 2 ) {
				return new WP_Error( 400, "More than two parts of the integer query" );
			}

			if(!in_array($parts[0], static::$validOperators)){
				$operators = implode(", ",static::$validOperators);
				return new WP_Error(400, "Invalid integer query syntax. Operator needs to be one of: $operators");
			}


			if( !is_numeric($parts[1])) {
				return new WP_Error( 400, "Values need to be of type integer: $parts[1]" );
			}

			return new IntegerQuery(
				[intval($parts[1])],
				static::$operatorToComparator[$parts[0]]
			);

		}

		if(!is_numeric($query)){
			return new WP_Error(400, "Value is not a number.");
		}

		$value = intval( $query );

		return $value > 0 ?
			new IntegerQuery( [ $value ], "=" ) :
			new WP_Error( 400, "Value is invalid" );
	}
}
