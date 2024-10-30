<?php

namespace ClimbPress;

use ClimbPress\Components\Component;

class Assets extends Component {

	const HANDLE_ROUTES_SCRIPT = "climbpress-routes-script";
	const HANDLE_ROUTES_STYLES = "climbpress-routes-styles";
	const HANDLE_STATS_SCRIPT = "climbpress-stats-script";
	const HANDLE_STATS_STYLES = "climbpress-stats-styles";
	const HANDLE_GRADES_SCRIPT = "climbpress-grades-script";
	const HANDLE_GRADES_STYLES = "climbpress-grades-styles";

	private Components\Assets $assets;

	public function onCreate() {
		parent::onCreate();
		$this->assets = new Components\Assets($this->plugin->path, $this->plugin->url);

	}

	public function enqueueRoutes($rootId){
		$this->assets->registerScript(
			self::HANDLE_ROUTES_SCRIPT,
			"dist/routes.js"
		);
		wp_enqueue_script(self::HANDLE_ROUTES_SCRIPT);
		$this->localize(self::HANDLE_ROUTES_SCRIPT, $rootId);
		$this->assets->registerStyle(
			self::HANDLE_ROUTES_STYLES,
			"dist/routes.css"
		);
		wp_enqueue_style(self::HANDLE_ROUTES_STYLES);
	}

	public function enqueueStats($rootId){
		wp_deregister_style('wp-admin');
		$this->assets->registerScript(
			self::HANDLE_STATS_SCRIPT,
			"dist/stats.js"
		);
		wp_enqueue_script(self::HANDLE_STATS_SCRIPT);
		$this->localize(self::HANDLE_STATS_SCRIPT, $rootId);
		$this->assets->registerStyle(
			self::HANDLE_STATS_STYLES,
			"dist/stats.css"
		);
		wp_enqueue_style(self::HANDLE_STATS_STYLES);
	}

	public function enqueueGrades($rootId){
		$this->assets->registerScript(
			self::HANDLE_GRADES_SCRIPT,
			"dist/grades.js"
		);
		wp_enqueue_script(self::HANDLE_GRADES_SCRIPT);
		$this->localize(self::HANDLE_GRADES_SCRIPT, $rootId);
		$this->assets->registerStyle(
			self::HANDLE_GRADES_STYLES,
			"dist/grades.css"
		);
		wp_enqueue_style(self::HANDLE_GRADES_STYLES);
	}

	private function localize($handle, $rootId) {
		wp_localize_script(
			$handle,
			"ClimbPress",
			[
				"domain" => Plugin::DOMAIN,
				"rootId" => $rootId,
				"RESTNamespace" => Plugin::REST_NAMESPACE,
				"gradingSystems" => $this->plugin->gradingSystemsSource->getGradingSystemsAsArray(),
				"defaultGradingSystemId" => $this->plugin->gradingSystemsSource->getDefaultGradingSystem(),
				"routeMetaStructure" => $this->plugin->routeMetaController->getRouteMetaStructure()->toArray(),
				"exportUrl" => $this->plugin->export->getExportUrl(),
				"availableYears" => $this->plugin->routesSource->getAvailableYears(),
				"votingPageParam" => $this->plugin->votingPage->getVotingPageParam(),
				"i18n" => [
					"All" => __("All", Plugin::DOMAIN),
					"New Route" => __("New Route", Plugin::DOMAIN),
					"Columns" => __("Columns", Plugin::DOMAIN),
					"All routes" => __("All routes", Plugin::DOMAIN),
					"All years" => __("All years", Plugin::DOMAIN),
					"Export" => __("Export", Plugin::DOMAIN),
					'Permanently delete "%s"?' => __('Permanently delete "%s"?', Plugin::DOMAIN),
					"Screwed on" => __("Screwed on", Plugin::DOMAIN),
					"Screwed off" => __("Screwed off", Plugin::DOMAIN),
					"Route name" => __("Route name", Plugin::DOMAIN),
					"Grading-System" => __("Grading-System", Plugin::DOMAIN),
					"Grade" => __("Grade", Plugin::DOMAIN),
					"Date" => __("Date", Plugin::DOMAIN),
					"Status" => __("Status", Plugin::DOMAIN),
					"Cancel" => __("Cancel", Plugin::DOMAIN),
					"Save" => __("Save", Plugin::DOMAIN),
					"Delete" => __("Delete", Plugin::DOMAIN),
					"Range of values" => __("Range of values", Plugin::DOMAIN),
					"Grades" => __("Grades", Plugin::DOMAIN),
					"- no indication -" => __("- no indication -", Plugin::DOMAIN),
					"These statistics contain all screwed on routes." =>
						__("These statistics contain all screwed on routes.", Plugin::DOMAIN)
				]
			]
		);
		wp_set_script_translations(
			$handle,
			Plugin::DOMAIN,
		);
	}



}
