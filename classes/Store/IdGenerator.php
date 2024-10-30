<?php

namespace ClimbPress\Store;

interface IdGenerator {
	public function generate():String;
}