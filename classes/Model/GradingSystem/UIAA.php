<?php

namespace ClimbPress\Model\GradingSystem;

use ClimbPress\Model\Grade;

class UIAA extends AbsGradingSystem {

	public function getId(): string {
		return "uiaa";
	}

	public function getName(): string{
		return "UIAA";
	}

	public function getGrades(): array{
		return [
			Grade::build(10, "1"),
			Grade::build(30, "2"),
			Grade::build(50, "3"),
			Grade::build(60, "4"),
			Grade::build(70, "5-"),
			Grade::build(80, "5"),
			Grade::build(90, "5+"),
			Grade::build(100, "6-"),
			Grade::build(110, "6"),
			Grade::build(120, "6+"),
			Grade::build(130, "7-"),
			Grade::build(140, "7"),
			Grade::build(150, "7+"),
			Grade::build(170, "8-"),
			Grade::build(190, "8"),
			Grade::build(200, "8+"),
			Grade::build(220, "9-"),
			Grade::build(230, "9"),
			Grade::build(240, "9+"),
			Grade::build(260, "10-"),
			Grade::build(270, "10"),
			Grade::build(280, "10+"),
			Grade::build(300, "11-"),
			Grade::build(310, "11"),
			Grade::build(320, "11+"),
			Grade::build(340, "12-"),
			Grade::build(350, "12"),
		];
	}
}
