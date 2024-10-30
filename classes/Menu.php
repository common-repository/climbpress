<?php

namespace ClimbPress;

use ClimbPress\Components\Component;

class Menu extends Component {

	private string $settingsPage;
	private string $settingsSection;

	public function onCreate() {
		parent::onCreate();
		add_action( 'admin_init', [ $this, 'init' ] );
		add_action( 'admin_menu', [ $this, 'admin_menu' ] );
		$this->settingsPage       = Plugin::DOMAIN . '-settings';
		$this->settingsSection    = Plugin::DOMAIN . "-settings-section";
	}

	public function init() {
        // register all options
		register_setting(
			Plugin::DOMAIN,
			Plugin::OPTION_DEFAULT_GRADING_SYSTEM,
		);
		register_setting(
			Plugin::DOMAIN,
			Plugin::OPTION_API_KEY,
		);

        // add sections and fields
		add_settings_section(
			$this->settingsSection,
			__( "Settings", Plugin::DOMAIN ),
			function () {
			},
			$this->settingsPage
		);
		add_settings_field(
			Plugin::OPTION_DEFAULT_GRADING_SYSTEM,
			__( "Default grading system", Plugin::DOMAIN ),
			[ $this, 'field_default_grading_system' ],
			$this->settingsPage,
			$this->settingsSection,
		);
		add_settings_field(
			Plugin::OPTION_API_KEY,
			__( "API Key", Plugin::DOMAIN ),
			[ $this, 'field_api_key' ],
			$this->settingsPage,
			$this->settingsSection,
		);

	}

	public function admin_menu() {
		$routesMenuSlug = Plugin::DOMAIN . "-routes";
		add_menu_page(
			__( "Routes ‹ ClimbPress", Plugin::DOMAIN ),
			"ClimbPress",
			Permissions::CAPABILITY_MANAGE,
			$routesMenuSlug,
			'',
			'data:image/svg+xml;base64,'.base64_encode(file_get_contents($this->plugin->path."/assets/climbpress.svg")),
			30
		);
		add_submenu_page(
			Plugin::DOMAIN . "-routes",
			__( 'Routes ‹ ClimbPress', Plugin::DOMAIN ),
			__( 'Routes', Plugin::DOMAIN ),
			Permissions::CAPABILITY_MANAGE,
			$routesMenuSlug,
			[ $this, 'routes' ],
			9
		);
		add_submenu_page(
			$routesMenuSlug,
			__( 'Statistics ‹ ClimbPress', Plugin::DOMAIN ),
			__( 'Statistics', Plugin::DOMAIN ),
			'manage_options',
			Plugin::DOMAIN . '-statistics',
			[ $this, 'statistics' ],
			9
		);
		add_submenu_page(
			$routesMenuSlug,
			__( 'Grading-Systems ‹ ClimbPress', Plugin::DOMAIN ),
			__( 'Grading-Systems', Plugin::DOMAIN ),
			'manage_options',
			Plugin::DOMAIN . '-grading-systems',
			[ $this, 'grades' ],
			9
		);
		add_submenu_page(
			$routesMenuSlug,
			__( 'Settings ‹ ClimbPress', Plugin::DOMAIN ),
			__( 'Settings', Plugin::DOMAIN ),
			'manage_options',
			Plugin::DOMAIN . '-settings',
			[ $this, 'settings' ],
			10
		);
	}

	public function routes() {
		$rootId = "climbpress-routes";
		$this->plugin->assets->enqueueRoutes( $rootId );
		$this->renderRoot( $rootId );
	}

	public function statistics() {
		$rootId = "climbpress-statistics";
		$this->plugin->assets->enqueueStats( $rootId );
		$this->renderRoot( $rootId );
	}

	public function grades() {
		$rootId = "climbpress-grades";
		$this->plugin->assets->enqueueGrades( $rootId );
		$this->renderRoot( $rootId );
	}

	private function renderRoot( $id ) {
		echo "<div class='wrap'>";
		echo "<div id='" . esc_attr( $id ) . "'></div>";
		echo "</div>";
	}

	public function settings() {
		?>
        <div class='wrap'>
            <form method="post" action="options.php">
				<?php
				settings_fields( Plugin::DOMAIN );
				do_settings_sections( $this->settingsPage );
				?>
				<?php submit_button(); ?>
            </form>
        </div>
		<?php


	}

	public function field_default_grading_system() {
		$defaultGradingSystem = $this->plugin->gradingSystemsSource->getDefaultGradingSystem();
		$systems              = $this->plugin->gradingSystemsSource->getGradingSystems();
		?>
        <select
                name="<?= esc_attr( Plugin::OPTION_DEFAULT_GRADING_SYSTEM ); ?>"
                id="<?= esc_attr( Plugin::OPTION_DEFAULT_GRADING_SYSTEM ); ?>"
        >
			<?php
			foreach ( $systems as $system ) {
				$selected = $system->getId() == $defaultGradingSystem ? "selected='selected'" : "";
				echo "<option $selected value='" . esc_attr( $system->getId() ) . "'>" . esc_html( $system->getName() ) . "</option>";
			}
			?>
        </select>
		<?php
	}

	public function field_api_key() {
		?>
        <input
                class="regular-text"
                disabled
                name="<?= esc_attr( Plugin::OPTION_API_KEY ); ?>"
                id="<?= esc_attr( Plugin::OPTION_API_KEY ); ?>"
                value="<?= get_option( Plugin::OPTION_API_KEY ) ?>"
        />
        <p class="description"><?= _x("This option will be available soon. It will provide additional functionallity.", "settings", Plugin::DOMAIN); ?></p>
		<?php
	}
}
