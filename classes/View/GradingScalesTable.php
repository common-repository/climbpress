<?php

namespace ClimbPress\View;

use ClimbPress\Model\IGradingSystem;

class GradingScalesTable implements IView {
	/**
	 * @var IGradingSystem[]
	 */
	private array $systems;

	/**
	 * @param IGradingSystem[] $gradingSystems
	 */
	public function __construct(array $gradingSystems) {
		$this->systems = $gradingSystems;
	}

	public function render(): void {
		$maxSystemValues = array_map(function($system){
			$max = 0;
			foreach ($system->getGrades() as $grade){
				$max = max($grade->getDifficulty(), $max);
			}
			return $max;
		}, $this->systems);

		$maxValue = max($maxSystemValues);

		$table = [];

		for ($i = 0; $i <= $maxValue; $i++){
			$table[$i] = [];
			foreach ($this->systems as $system){
				$found = array_filter($system->getGrades(), function($grade) use ( $i ) {
					return $grade->getDifficulty() == $i;
				});
				if(count($found) >= 1){
					$table[$i][$system->getId()] = array_values($found)[0]->getLabel();
				}
			}
		}

		echo "<table class='wp-list-table widefat fixed striped posts'>";
		echo "<thead>";
		echo "<tr>";

			echo "<th>Wertebereich</th>";
		foreach ($this->systems as $system){
			echo "<th>";
			echo esc_attr($system->getName());
			echo "</th>";
		}
		echo "</tr>";
		echo "</thead>";
		foreach ($table as $value => $row){
			if(empty($row)) continue;
			echo "<tr>";
			echo "<td>";
			echo esc_attr($value);
			echo "</td>";
			foreach ($this->systems as $system) {
				echo  "<td>";
				if(isset($row[$system->getId()])){
					echo esc_attr($row[$system->getId()]);
				}
				echo "</td>";
			}
			echo "</tr>";

		}
		echo "</table>";

	}
}
