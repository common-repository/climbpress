<?php

namespace ClimbPress\Model\GradingSystem;

use ClimbPress\Model\Grade;

class Fontainebleau extends AbsGradingSystem {

	public function getId(): string {
		return "fontainebleau";
	}

	public function getName(): string {
		return "Fontainebleau";
	}

	public function getGrades(): array {
		return [
			Grade::build( 70, "3" ),
			Grade::build( 90, "4a" ),
			Grade::build( 110, "4b" ),
			Grade::build( 140, "4c" ),
			Grade::build( 150, "5a" ),
			Grade::build( 160, "5b" ),
			Grade::build( 170, "5c" ),
			Grade::build( 180, "5c+" ),
			Grade::build( 190, "6a" ),
			Grade::build( 205, "6b" ),
			Grade::build( 215, "6c" ),
			Grade::build( 225, "7a" ),
			Grade::build( 235, "7a+" ),
			Grade::build( 245, "7b" ),
			Grade::build( 255, "7b+" ),
			Grade::build( 265, "7c" ),
			Grade::build( 275, "7c+" ),
			Grade::build( 285, "8a" ),
			Grade::build( 295, "8a+" ),
			Grade::build( 305, "8b" ),
			Grade::build( 315, "8b+" ),
			Grade::build( 325, "8c" ),
			Grade::build( 330, "8c+" ),
		];
	}
}
