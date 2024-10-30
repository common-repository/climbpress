<?php

namespace ClimbPress\Model;

class RouteMetaOption {

	public function __construct(
		public string $key,
		public string $label
	) {
	}

	public static function build( string $key, string $label ): self {
		return new static( $key, $label );
	}
}
