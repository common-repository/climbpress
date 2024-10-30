<?php

namespace ClimbPress\Model;

use ClimbPress\Store\RoutesSource;

class RouteQueryArgs {

	public function __construct(
		public int $page = 1,
		public int $perPage = -1,

		public string $orderDirection = "desc",
		public string $orderBy = "created",

		public string $status = RoutesSource::STATUS_SCREWED_ON,
		public int $year = 0,

		public ?StringQuery $name = null,
		public ?IntegerQuery $difficulty = null,
		/**
		 * @var StringQuery[]
		 */
		public array $metas = [],
	) {

	}


	function setStatus( string $status ): void {
		if ( in_array( $status, [
			RoutesSource::STATUS_SCREWED_ON,
			RoutesSource::STATUS_SCREWED_OFF,
			RoutesSource::STATUS_SCREWED_ANY
		] ) ) {
			$this->status = $status;
		} else {
			$this->status = RoutesSource::STATUS_SCREWED_ON;
		}
	}

}
