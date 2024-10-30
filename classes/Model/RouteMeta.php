<?php

namespace ClimbPress\Model;

class RouteMeta {

	/**
	 * @var RouteMetaOption[]
	 */
	private array $options = [];

	public function __construct(
		public string $key,
		public string $label
	) {

	}

	public static function build( $key, $label ) {
		return new static( $key, $label );
	}

	/**
	 * @param RouteMetaOption[] $options
	 *
	 */
	public function setOptions( array $options ): self {
		$this->options = array_values( array_filter( $options, function ( $option ) {
			return $option instanceof RouteMetaOption;
		} ) );

		return $this;
	}

	public function getOptions(): array {
		return $this->options;
	}

	public function toArray(): array {
		return [
			"key"     => $this->key,
			"label"   => $this->label,
			"options" => array_map( function ( RouteMetaOption $option ) {
				return [
					"key"   => $option->key,
					"label" => $option->label,
				];
			}, $this->options ),
		];
	}
}
