<?php

namespace ClimbPress\Model;

class Grade {

	public function __construct( private int $difficulty, private string $label ) {

	}

	public static function build(int $difficulty, string $label): Grade {
		return new Grade($difficulty, $label);
	}

	public function getDifficulty(): int {
		return $this->difficulty;
	}

	public function getLabel(): string {
		return $this->label;
	}

	public function toArray(): array{
		return [
			"label" => $this->getLabel(),
			"difficulty" => $this->getDifficulty(),
		];
	}
}
