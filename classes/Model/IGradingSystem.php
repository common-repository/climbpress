<?php

namespace ClimbPress\Model;

interface IGradingSystem {

	public function getId():string;

	public function getName(): string;

	/**
	 * @return Grade[]
	 */
	public function getGrades(): array;

	public function toArray(): array;

}
