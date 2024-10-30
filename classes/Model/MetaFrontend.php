<?php

namespace ClimbPress\Model;

class MetaFrontend {

	private function __construct(
		private string $key,
		private bool $hide = false,
		private bool $searchable = true,
	) {
	}

	public static function build(string $key): self {
		return new self($key);
	}

	public function setHidden(bool $value): self {
		$this->hide = $value;
		return $this;
	}

	public function setSearchable(bool $value): self {
		$this->searchable = $value;
		return $this;
	}


}
