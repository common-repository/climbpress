<?php

namespace ClimbPress\Model;

class RouteState {
	public function __construct(public Route $route, public Grade|null $grade = null) {

	}
}
