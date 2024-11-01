<?php
/**
 * Simple Header Footer Settings.
 *
 * @package   simple-header-footer-scripts
 * @author    Bharat Mandava
 * @version   1.0.0
 * @copyright Copyright (c) 2018, WPSquare
 * 
 */

if ( ! class_exists( 'SHF_Settings' ) ) {

	/**
	 * Settings.
	 */
	class SHF_Settings {

		/**
		 * Member Variable
		 *
		 * @var instance
		 */
		private static $instance;

		/**
		 * Initiate.
		 *
		 * @return instance
		 */
		public static function get_instance() {

			if ( ! isset( self::$instance ) ) {
				self::$instance = new self;
			}

			return self::$instance;

		}

		/**
		 * Contructor.
		 */
		public function __construct() {

			/* Initialize hooks and filters */
			$this->init_hooks();

		}

		/**
		 * Init Hooks.
		 *
		 * @return void
		 */
		public function init_hooks() {
			add_action( 'admin_init'        , array( $this, 'admin_init_hooks' ) );
			add_action( 'shf_after_options' , array( $this, 'shf_display_save_reset_settings' ) );
			add_action( 'shf_after_options' , array( $this, 'shf_display_import_export_settings' ) );
		}

		/**
		 * Admin Init Hooks.
		 *
		 * @return void
		 */
		public function admin_init_hooks() {

			/* Reset hooks saved settings. */
			$this->settings_reset();

			/* Export hooks settings. */
			$this->settings_export();

			/* Import hooks settings. */
			$this->settings_import();
		}

		/**
		 * Display save / reset settings.
		 *
		 * @return void
		 */
		public function shf_display_save_reset_settings() {
			?>
			<div class="postbox shf-postbox shf-postbox-save-reset">
				<h3 class="hndle"><?php esc_attr_e('Save / Reset Settings', 'simple-header-footer-scripts'); ?></h3>
				<div class="inside">
					<p><?php esc_attr_e('Insert your scripts in header or footer section and click on Save Settings.', 'simple-header-footer-scripts'); ?></p>
	
					<p><input type="submit" name="save-hooks" id="shf-save-settings" class="button button-primary" value="<?php echo esc_attr('Save Settings', 'simple-header-footer-scripts'); ?>"></p>
											
					<form method="post">
						<p><input type="hidden" name="shf_settings_reset" value="settings_reset" /></p>
						<p>
						<?php
							$warning = 'return confirm("' . esc_attr__( 'Warning: Are you sure you want to reset the settings?', 'simple-header-footer-scripts' ) . '")';
							wp_nonce_field( 'shf_settings_reset_nonce', 'shf_settings_reset_nonce' );
							submit_button( esc_attr__( 'Reset Settings', 'simple-header-footer-scripts' ), 'secondary', 'reset', false, array( 'onclick' => $warning ) );
						?>
						</p>
					</form>
				</div>
			</div>
			<?php
		}

		/**
		 * Display import / export settings.
		 *
		 * @return void
		 */
		public function shf_display_import_export_settings() {
			?>
			<div class="postbox shf-postbox shf-postbox-import-export">
				<h3 class="hndle"><?php esc_attr_e( 'Import / Export Settings', 'simple-header-footer-scripts' ); ?></h3>
				<div class="inside">
					<p><?php esc_attr_e( 'Import your settings by uploading your exported .json file.','simple-header-footer-scripts' ); ?></p>
					<form method="post" enctype="multipart/form-data">
						<p>
							<input type="file" name="import_file"/>
						</p>
						<p>
							<input type="hidden" name="shf_settings_import" value="settings_import" />
							<?php wp_nonce_field( 'shf_settings_import_nonce', 'shf_settings_import_nonce' ); ?>
							<?php submit_button( esc_attr__( 'Import Settings', 'simple-header-footer-scripts' ), 'primary', 'import', false ); ?>
						</p>
					</form>
					<form method="post">
						<p><input type="hidden" name="shf_settings_export" value="settings_export" /></p>
						<p>
							<?php wp_nonce_field( 'shf_settings_export_nonce', 'shf_settings_export_nonce' ); ?>
							<?php submit_button( esc_attr__( 'Export Settings', 'simple-header-footer-scripts' ), 'secondary', 'export', false ); ?>
						</p>
					</form>
				</div>
			</div>
			<?php
		}

		/**
		 * Reset Settings.
		 *
		 * @return void
		 */
		public function settings_reset() {

			// Check if the request params are set.
			if ( ! isset( $_POST['shf_settings_reset'], $_POST['shf_settings_reset_nonce'] ) ) {
				return;
			}

			// Verify the nonce.
			if ( ! wp_verify_nonce( sanitize_key( $_POST['shf_settings_reset_nonce'] ), 'shf_settings_reset_nonce' ) ) {
				return;
			}

			// Verify the reset request.
			if ( 'settings_reset' !== $_POST['shf_settings_reset'] ) {
				return;
			}

			// Check if current user can manage options.
			if ( ! current_user_can( 'manage_options' ) ) {
				return;
			}

			// Delete option.
			delete_option( SHF_OPTIONS );

			// Redirect back to admin page.
			wp_safe_redirect( admin_url( 'options-general.php?page=' . SHF_PLUGIN_SLUG . '&reset=true' ) );

			exit;
		}

		/**
		 * Export Settings.
		 *
		 * @return void
		 */
		public function settings_export() {

			// Check if the request params are set.
			if ( ! isset( $_POST['shf_settings_export'], $_POST['shf_settings_export_nonce'] ) ) {
				return;
			}

			// Verify the nonce.
			if ( ! wp_verify_nonce( sanitize_key( $_POST['shf_settings_export_nonce'] ), 'shf_settings_export_nonce' ) ) {
				return;
			}

			// Verify the reset request.
			if ( 'settings_export' !== $_POST['shf_settings_export'] ) {
				return;
			}

			// Check if current user can manage options.
			if ( ! current_user_can( 'manage_options' ) ) {
				return;
			}

			$settings = get_option( SHF_OPTIONS );

			ignore_user_abort( true );

			nocache_headers();
			header( 'Content-Type: application/json; charset=utf-8' );
			header( 'Content-Disposition: attachment; filename=' . SHF_PLUGIN_SLUG . '-' . date( 'm-d-Y' ) . '.json' );
			header( 'Expires: 0' );

			echo wp_json_encode( $settings );
			exit;
		}

		/**
		 * Import Settings.
		 *
		 * @return void
		 */
		public function settings_import() {

			// Check if the request params are set.
			if ( ! isset( $_POST['shf_settings_import'], $_POST['shf_settings_import_nonce'], $_FILES['import_file'] ) ) {
				return;
			}

			// Verify the nonce.
			if ( ! wp_verify_nonce( sanitize_key( $_POST['shf_settings_import_nonce'] ), 'shf_settings_import_nonce' ) ) {
				return;
			}

			// Verify the import request.
			if ( 'settings_import' !== $_POST['shf_settings_import'] ) {
				return;
			}

			// Check if current user can manage options.
			if ( ! current_user_can( 'manage_options' ) ) {
				return;
			}

			$file = wp_kses_post( wp_unslash( $_FILES['import_file'] ) );

			$import_file = $file['tmp_name'];

			if ( empty( $import_file ) ) {
				wp_die( esc_attr__( 'Please upload a file to import', 'simple-header-footer-scripts' ) );
			}

			$filename = sanitize_file_name( $file['name'] );
			$extension = explode( '.', $file['name'] );
			$extension = end( $extension );

			if ( 'json' !== $extension ) {
				wp_die( esc_attr__( 'Please upload a valid .json file', 'simple-header-footer-scripts' ) );
			}

			// Retrieve settings from the file.
			$contents = file_get_contents( $import_file );

			// Convert the json object to an array.
			$settings = json_decode( $contents, true );

			// Sanitize imported settings.
			$sanitized_settings = SHF_Admin::sanitize_settings( $settings );

			// Update option.
			if ( $sanitized_settings ) {
				update_option( SHF_OPTIONS, $sanitized_settings );
			}

			wp_safe_redirect( admin_url( 'options-general.php?page=' . SHF_PLUGIN_SLUG . '&imported=true' ) );

			exit;

		}

	}
} // End if().

// Get Instance.
SHF_Settings::get_instance();