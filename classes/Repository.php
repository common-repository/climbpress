<?php

namespace ClimbPress;

use ClimbPress\Model\Grade;
use ClimbPress\Model\GradingSystem\AbsGradingSystem;
use ClimbPress\Model\IGradingSystem;
use ClimbPress\Model\Route;
use ClimbPress\Model\RouteQueryArgs;
use ClimbPress\Model\RouteState;
use ClimbPress\Store\GradingSystemsSource;
use ClimbPress\Store\IdGenerator;
use ClimbPress\Store\RoutesSource;
use ClimbPress\Store\UniqueIdGenerator;

class Repository {

	private IdGenerator $votingIdGenerator;

	public function __construct(
		private RoutesSource $routesSource,
		private GradingSystemsSource $gradingSystemsSource
	) {
		$this->votingIdGenerator = apply_filters( Plugin::FILTER_VOTE_ID_GENERATOR, new UniqueIdGenerator() );
	}

	/**
	 * @return Model\RouteState[]
	 */
	public function getRoutes( RouteQueryArgs $args = null ): array {
		$routes  = $this->routesSource->getAll( $args ?? new RouteQueryArgs() );
		$systems = $this->gradingSystemsSource->getGradingSystems();

		/**
		 * @var Model\GradingSystem\[] $systemsMap
		 */
		$systemsMap = [];
		foreach ( $systems as $system ) {
			$systemsMap[ $system->getId() ] = $system;
		}

		return array_map( function ( $route ) use ( $systemsMap ) {
			/**
			 * @var IGradingSystem|null $system
			 */
			$system = $systemsMap[ $route->gradingSystem ] ?? null;

			$grade = null;
			if ( $system instanceof IGradingSystem ) {
				$grade = $this->findGradeInSystem( $route->difficulty, $system );
			}

			return new RouteState( $route, $grade );
		}, $routes );
	}

	public function findGradeInSystem( int $difficulty, IGradingSystem $system ): Grade|null {
		$closest = null;

		foreach ( $system->getGrades() as $item ) {
			if ( $closest == null ) {
				$closest = $item;
				continue;
			}
			$distanceToClosest    = abs( $item->getDifficulty() - $closest->getDifficulty() );
			$distanceToDifficulty = abs( $item->getDifficulty() - $difficulty );
			if ( $distanceToClosest > $distanceToDifficulty ) {
				$closest = $item;
			}
		}

		return $closest;
	}

	public function getGradingSystem( int|Route $route ): IGradingSystem|false {
		if ( is_int( $route ) ) {
			$route = $this->routesSource->get( $route );
		}
		if ( ! ( $route instanceof Route ) ) {
			return false;
		}
		$systems    = $this->gradingSystemsSource->getGradingSystems();
		$systemId   = $route->gradingSystem;
		$candidates = array_filter( $systems, function ( $system ) use ( $systemId ) {
			return $system->getId() == $systemId;
		} );
		if ( count( $candidates ) != 1 ) {
			return false;
		}

		return array_values( $candidates )[0];
	}

	public function addVoting( int $route_id, int $difficulty ): string|false {
		$id     = $this->votingIdGenerator->generate();
		$system = $this->getGradingSystem( $route_id );
		if ( ! ( $system instanceof IGradingSystem ) ) {
			return false;
		}

		$grade   = $this->findGradeInSystem( $difficulty, $system );
		$success = $this->routesSource->insertVoting( $id, $route_id, $grade->getDifficulty() );

		return $success ? $id : false;
	}

	public function updateVoting( string $voting_id, int $difficulty ): bool {
		$route = $this->routesSource->getRouteByVoting( $voting_id );
		if ( ! ( $route instanceof Route ) ) {
			error_log("could not find route for voting $voting_id");
			return false;
		}
		$system = $this->getGradingSystem( $route );
		if ( ! ( $system instanceof AbsGradingSystem ) ) {
			error_log("could not find grading system for route $route->id");
			return false;
		}
		$grade = $this->findGradeInSystem( $difficulty, $system );
		if ( ! ( $grade instanceof Grade ) ) {
			error_log("could not find grade for route $route->id");
			return false;
		}

		return $this->routesSource->updateVoting( $voting_id, $grade->getDifficulty() );
	}
}
