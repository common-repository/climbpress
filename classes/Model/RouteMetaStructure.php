<?php

namespace ClimbPress\Model;

class RouteMetaStructure {

	/**
	 * @var RouteMeta[]
	 */
	private array $metas = [];

	public static function build(): RouteMetaStructure {
		return new static();
	}

	public function set( RouteMeta $meta ): RouteMetaStructure {
		$this->metas[ $meta->key ] = $meta;

		return $this;
	}

	public function get(): array {
		return $this->metas;
	}

	public function getMeta(string $key): ?RouteMeta {
		foreach ($this->metas as $metaKey => $meta) {
			if($key == $metaKey) return $meta;
		}
		return null;
	}

	/**
	 * @param RouteMeta[] $metas
	 *
	 * @return RouteMetaStructure
	 */
	public function setAll( array $metas ): RouteMetaStructure {
		foreach ( $metas as $meta ) {
			$this->set( $meta );
		}

		return $this;
	}

	public function toArray(): array {
		return array_values(array_map( function ( RouteMeta $meta ) {
			return $meta->toArray();
		}, $this->metas ));
	}

}
