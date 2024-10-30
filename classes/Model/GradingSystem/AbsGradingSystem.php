<?php

namespace ClimbPress\Model\GradingSystem;

use ClimbPress\Model\IGradingSystem;

abstract class AbsGradingSystem implements IGradingSystem {
	public function toArray():array {
		return [
			"id" => $this->getId(),
			"name" => $this->getName(),
			"grades" => array_map(function($grade){
				return $grade->toArray();
			},$this->getGrades()),
			"editable" => false,
		];
	}
}
