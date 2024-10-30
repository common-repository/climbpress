<?php

namespace ClimbPress\Model\GradingSystem;

use ClimbPress\Model\Grade;

class French extends AbsGradingSystem {

	public function getId(): string {
		return "french-numerical";
	}

	public function getName(): string{
		return "French Numerical";
	}

	public function getGrades(): array{
		return [
			Grade::build(10, "1"),
			Grade::build(30, "2"),
			Grade::build(50, "3"),
			Grade::build(60, "4a"),
			Grade::build(70, "4b"),
			Grade::build(80, "4c"),
			Grade::build(90, "5a"),
			Grade::build(100, "5b"),
			Grade::build(110, "5c"),
			Grade::build(120, "6a"),
			Grade::build(130, "6a+"),
			Grade::build(140, "6b"),
			Grade::build(150, "6b+"),
			Grade::build(165, "6c"),
			Grade::build(175, "6c+"),
			Grade::build(190, "7a"),
			Grade::build(200, "7a+"),
			Grade::build(210, "7b"),
			Grade::build(220, "7b+"),
			Grade::build(230, "7c"),
			Grade::build(240, "7c+"),
			Grade::build(250, "8a"),
			Grade::build(260, "8a+"),
			Grade::build(270, "8b"),
			Grade::build(280, "8b+"),
			Grade::build(290, "8c"),
			Grade::build(300, "8c+"),
			Grade::build(310, "9a"),
			Grade::build(320, "9a+"),
			Grade::build(330, "9b"),
			Grade::build(340, "9b+"),
			Grade::build(350, "9c"),
		];
	}
}
