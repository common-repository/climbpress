<?php

namespace ClimbPress;

use ClimbPress\Components\Component;
use ClimbPress\Model\IGradingSystem;
use ClimbPress\Model\Route;
use ClimbPress\Model\RouteMeta;
use ClimbPress\Model\RouteMetaStructure;
use ClimbPress\Model\RouteQueryArgs;

class Export extends Component {
	public function onCreate() {
		parent::onCreate();
		add_action( 'wp_ajax_climbpress_export', [ $this, "export" ] );
	}

	public function getExportUrl(){
		return admin_url("admin-ajax.php")."?action=climbpress_export";
	}

	public function export() {

		// TODO: reuse from rest api?
		$args = new RouteQueryArgs();
		if(!empty($_GET["year"])){
			$args->year = intval($_GET["year"]);
		}
		if(!empty($_GET["order_by"])){
			$args->orderBy = sanitize_text_field($_GET["order_by"]);
		}
		if(!empty($_GET["order_direction"])){
			$args->orderDirection = sanitize_text_field($_GET["order_direction"]);
		}
		if(!empty($_GET["status"])){
			$args->setStatus(sanitize_text_field($_GET["status"]));
		}

		$filenameSuffix = date("Y-m-d_h-i");

		$host = str_replace(".","-",sanitize_file_name(parse_url(get_home_url(), PHP_URL_HOST)));

		$output = fopen("php://output",'w') or die("Can't open php://output");
		header("Content-Type:application/csv");
		header("Content-Disposition:attachment;filename=$host-climbpress-$filenameSuffix.csv");

		$fields = [
			"status",
			"name",
			"created",
			"grade",
		];
		$structure = $this->plugin->routeMetaController->getRouteMetaStructure();

		$metaLabels = array_map(function($meta){
			return $meta->label;
		},$structure->get());

		fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
		fputcsv($output, array_merge($fields, $metaLabels));

		$routes = $this->plugin->routesSource->getAll($args);

		$metaKeys = array_map(function($meta){
			return $meta->key;
		},$structure->get());

		$gradingSystems = $this->plugin->gradingSystemsSource->getGradingSystems();
		foreach ( $routes as $route ) {
			$routeValues = [
				$route->status,
				$route->name,
				$route->created,
				$this->getGrade($route, $gradingSystems),
			];

			$metaValues = [];
			foreach ($metaKeys as $key){
				$metaValues[] = $this->getMetaValue($route, $key, $structure);
			}

			fputcsv($output, array_merge($routeValues, $metaValues));
		}

		exit;
	}

	/**
	 * @param IGradingSystem[] $systems
	 */
	private function findSystem(array $systems, string $id): ?IGradingSystem {
		foreach ($systems as $system){
			if($system->getId() == $id) return $system;
		}
		return null;
	}

	/**
	 * @param IGradingSystem[] $systems
	 */
	private function getGrade(Route $route, array $systems): string {
		$system = $this->findSystem($systems, $route->gradingSystem);
		if(!($system instanceof IGradingSystem)) return  "";

		foreach ($system->getGrades() as $grade){
			if($grade->getDifficulty() >= $route->difficulty){
				return $grade->getLabel();
			}
		}

		return  "";
	}

	private function getMetaValue(Route $route, string $metaKey, RouteMetaStructure $structure): string {

		$value = $route->metas[$metaKey] ?? "";
		if(empty($value)) return "";

		$meta = $structure->getMeta($metaKey);
		if(!($meta instanceof RouteMeta)) return "";

		if(count($meta->getOptions()) === 0) return $value;

		foreach ($meta->getOptions() as $option){
			if($option->key === $value) return $option->label;
		}

		return $value;
	}
}
