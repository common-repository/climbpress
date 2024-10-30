<?php

namespace ClimbPress;

use ClimbPress\Components\Component;
use ClimbPress\Model\RouteMeta;
use ClimbPress\Model\RouteMetaStructure;

class RouteMetaController extends Component {

	/**
	 * @return RouteMetaStructure
	 */
	public function getRouteMetaStructure(): RouteMetaStructure {
		$defaultStructure = RouteMetaStructure::build();
		$defaultStructure->set(
			RouteMeta::build(
				"routeSetter",
				__( "Route setter", Plugin::DOMAIN ),
			)
		);
		$defaultStructure->set(
			RouteMeta::build(
				"holdColor",
				__( "Hold color", Plugin::DOMAIN ),
			)
		);

		return apply_filters( Plugin::FILTER_ROUTE_METAS, $defaultStructure );
	}

	public function getRouteMetaKeys(): array {
		return array_map(
			function ( $item ) {
				return $item["key"];
			},
			$this->getRouteMetaStructure()->toArray()
		);
	}
}
