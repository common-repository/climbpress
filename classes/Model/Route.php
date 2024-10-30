<?php

namespace ClimbPress\Model;

class Route {

	public function __construct(
		public string $name,
		public string $status,
		public string $created,
		public int $difficulty,
		public string $gradingSystem,
		public int $id = -1,
		public array $metas = [],
		public int $communityVotesCount = 0,
		public int $communityAverageDifficulty = 0,
	) {

	}
}
