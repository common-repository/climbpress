<?php

namespace ClimbPress\Model\GradingSystem;

use ClimbPress\Model\Grade;


class CustomGradingSystem extends AbsGradingSystem {

	/**
	 * @param Grade[] $grades
	 */

	public function __construct(
		private string $id,
		private string $name,
		private array $grades
	) {

	}

	public function getId(): string {
		return $this->id;
	}

	public function getName(): string{
		return $this->name;
	}

	public function getGrades(): array {
		return $this->grades;
	}

	public function toArray(): array {
		$value = parent::toArray();
		$value["editable"] = true;
		return $value;
	}
}
