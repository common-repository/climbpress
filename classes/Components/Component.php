<?php


namespace ClimbPress\Components;

/**
 * Class Component
 *
 * @version 0.1.3
 */
abstract class Component {
	protected \ClimbPress\Plugin $plugin;

	/**
	 * _Component constructor.
	 */
	public function __construct(\ClimbPress\Plugin $plugin) {
		$this->plugin = $plugin;
		$this->onCreate();
	}

	public function getPlugin(): \ClimbPress\Plugin {
		return $this->plugin;
	}

	/**
	 * overwrite this method in component implementations
	 */
	public function onCreate(){
		// init your hooks and stuff
	}
}
