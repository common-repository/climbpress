<?php

use ClimbPress\Model\IGradingSystem;
use ClimbPress\Model\Route;
use ClimbPress\Plugin;

function climbpress_plugin() {
	return Plugin::instance();
}

function climbpress_get_voting_page_route_id(): int {
	return climbpress_plugin()->votingPage->getVotingPageRouteId();
}

function climbpress_get_voting_page_route(): ?Route {
	return climbpress_plugin()->votingPage->getVotingPageRoute();
}

/**
 * @return IGradingSystem[]
 */
function climbpress_get_grading_systems(): array {
	return climbpress_plugin()->gradingSystemsSource->getGradingSystemsAsArray();
}

function climbpress_get_route_meta_structure(): array {
	return climbpress_plugin()->routeMetaController->getRouteMetaStructure()->toArray();
}