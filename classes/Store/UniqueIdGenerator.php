<?php

namespace ClimbPress\Store;

class UniqueIdGenerator implements IdGenerator {
	public function generate(): string {
		return uniqid("v_", true);
	}
}