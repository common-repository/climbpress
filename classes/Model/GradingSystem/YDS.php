<?php

namespace ClimbPress\Model\GradingSystem;

use ClimbPress\Model\Grade;

class YDS extends AbsGradingSystem {

	public function getId(): string {
		return "yds";
	}

	public function getName(): string{
		return "Yosemite Decimal System";
	}

	public function getGrades(): array{
		return [
			Grade::build(10, "3-4"),
			Grade::build(20, "5.0"),
			Grade::build(30, "5.1"),
			Grade::build(40, "5.2"),
			Grade::build(50, "5.3"),
			Grade::build(60, "5.4"),
			Grade::build(70, "5.5"),
			Grade::build(80, "5.6"),
			Grade::build(90, "5.7"),
			Grade::build(100, "5.8"),
			Grade::build(110, "5.9"),
			Grade::build(120, "5.10a"),
			Grade::build(130, "5.10b"),
			Grade::build(140, "5.10c"),
			Grade::build(150, "5.10d"),
			Grade::build(160, "5.11a"),
			Grade::build(170, "5.11b"),
			Grade::build(180, "5.11c"),
			Grade::build(190, "5.11d"),
			Grade::build(200, "5.12a"),
			Grade::build(210, "5.12b"),
			Grade::build(220, "5.12c"),
			Grade::build(230, "5.12d"),
			Grade::build(240, "5.13a"),
			Grade::build(250, "5.13b"),
			Grade::build(260, "5.13c"),
			Grade::build(270, "5.13d"),
			Grade::build(280, "5.14a"),
			Grade::build(290, "5.14b"),
			Grade::build(300, "5.14c"),
			Grade::build(310, "5.14d"),
			Grade::build(320, "5.15a"),
			Grade::build(330, "5.15b"),
			Grade::build(340, "5.15c"),
			Grade::build(350, "5.15d"),
		];
	}
}
