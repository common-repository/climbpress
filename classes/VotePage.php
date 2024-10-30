<?php

namespace ClimbPress;

use ClimbPress\Components\Component;
use ClimbPress\Model\Route;

class VotePage extends Component {

	public function onCreate() {
		parent::onCreate();
		add_filter( 'template_include', [ $this, 'template_include' ] );
	}

	public function getVotingPageParam() {
		return apply_filters( Plugin::FILTER_VOTING_PAGE_PARAM, "climbpress-voting" );
	}

	private function hasVotingPageRouteId() {
		return isset( $_GET[ $this->getVotingPageParam() ] );
	}

	public function getVotingPageRouteId(): int {
		return $this->hasVotingPageRouteId() ? intval( $_GET[ $this->getVotingPageParam() ] ) : 0;
	}

	private ?Route $route = null;

	public function getVotingPageRoute(): ?Route {
		if ( $this->route instanceof Route ) {
			return $this->route;
		}
		$id = $this->getVotingPageRouteId();
		if ( $id <= 0 ) {
			return null;
		}
		$route = $this->plugin->routesSource->get( $this->getVotingPageRouteId() );
		if ( $route == null ) {
			return null;
		}
		$this->route = $route;

		return $this->route;
	}

	public function template_include( $template ) {
		if(!$this->hasVotingPageRouteId()) return $template;
		$route = $this->getVotingPageRoute();

		if(!($route instanceof Route)){
			global $wp_query;
			$wp_query->set_404();
			status_header( 404 );
			return get_404_template();
		}

		return $this->plugin->templates->get_template_path("climbpress-voting-page.php");
	}
}