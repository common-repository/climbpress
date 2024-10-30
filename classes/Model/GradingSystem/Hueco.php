<?php

namespace ClimbPress\Model\GradingSystem;

use ClimbPress\Model\Grade;

class Hueco extends AbsGradingSystem {

	public function getId(): string {
		return "hueco";
	}

	public function getName(): string{
		return "Hueco";
	}

	public function getGrades(): array{
		return [
			Grade::build(70, "Vb-"),
			Grade::build(90, "Vb"),
			Grade::build(110, "V0-"),
			Grade::build(140, "V0"),
			Grade::build(150, "V1"),
			Grade::build(160, "V2"),
			Grade::build(175, "V3"),
			Grade::build(210, "V4"),
			Grade::build(220, "V5"),
			Grade::build(230, "V6"),
			Grade::build(240, "V7"),
			Grade::build(250, "V8"),
			Grade::build(260, "V9"),
			Grade::build(270, "V10"),
			Grade::build(280, "V11"),
			Grade::build(290, "V12"),
			Grade::build(300, "V13"),
			Grade::build(310, "V14"),
			Grade::build(320, "V15"),
			Grade::build(330, "V16"),
		];
	}
}
