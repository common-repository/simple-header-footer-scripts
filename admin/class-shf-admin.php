<?php
/**
 * Simple Header Footer Admin.
 *
 * @package   simple-header-footer-scripts
 * @author    Bharat Mandava
 * @version   1.0.0
 * @copyright Copyright (c) 2018, WPSquare
 * 
 */

if ( ! class_exists( 'SHF_Admin' ) ) {

	/**
	 * Admin.
	 */
	class SHF_Admin {

		/**
		 * Member Variable
		 *
		 * @var instance
		 */
		private static $instance;

		/**
		 * Options.
		 *
		 * @var array
		 */
		private static $options = array();

		/**
		 * Hooks.
		 *
		 * @var array
		 */
		private static $hooks = array();

		/**
		 * Hook suffix.
		 *
		 * @var string
		 */
		public static $hook_suffix;

		/**
		 * Dismiss Notice.
		 *
		 * @var string
		 */
		public static $dismiss_notice ;

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

			/* Load Files */
			$this->load_files();

		}

		/**
		 * Init Hooks.
		 *
		 * @return void
		 */
		public function init_hooks() {
			add_action( 'init'                               , array( $this, 'init_plugin' ) );
			add_action( 'plugins_loaded'                     , array( $this, 'load_textdomain' ) );
			add_action( 'admin_menu'                         , array( $this, 'register_menu' ) );
			add_action( 'admin_init'                         , array( $this, 'register_settings' ) );
			add_action( 'admin_enqueue_scripts'              , array( $this, 'load_scripts' ) );
			add_action( 'admin_notices'                      , array( $this, 'admin_notices' ) );
			add_filter( 'plugin_action_links_' . SHF_PATH    , array( $this, 'add_action_links' ) );
			add_action( 'wp_ajax_shf_dismiss_welcome_notice' , array( $this, 'dismiss_welcome_notice' ) );
		}

		/**
		 * Get options.
		 *
		 * @return array
		 */
		public static function get_options() {
			return self::$options;
		}

		/**
		 * Init.
		 *
		 * @return void
		 */
		public function init_plugin() {

			$options = get_option( SHF_OPTIONS );
			$welcome_dismiss = get_option( SHF_WELCOME_DISMISS );
			$hooks = self::get_hooks_list();

			// Set options.
			self::$options = $this->parse_args( $options, $hooks );
			self::$dismiss_notice = $welcome_dismiss;

			// Execute hooks.
			$this->execute_hooks();

		}

		/**
		 * Get Hooks List.
		 *
		 * @param array $action_hooks Action hooks list.
		 * @return array
		 */
		public static function get_hooks_list() {
			
			$action_hooks = array(
				'wp_head'      => array(
					'title' => __( 'Scripts in Header', 'simple-header-footer-scripts' ),
					'description' => __( 'These scripts will be inserted above the <code>&lt;/head&gt;</code> tag.', 'simple-header-footer-scripts' ),
				),
				'wp_footer'    => array(
					'title' => __( 'Scripts in Footer', 'simple-header-footer-scripts' ),
					'description' => __( 'These scripts will be inserted above the <code>&lt;/body&gt;</code> tag.', 'simple-header-footer-scripts' ),
				),
			);

			return apply_filters( 'shf_hooks_config', $action_hooks );
		}

		/**
		 * Parse incoming $args into an array and merge it with $defaults
		 *
		 * @param array $args Args array.
		 * @param array $defaults Defaults array.
		 * @return array
		 */
		public static function parse_args( &$args, $defaults ) {

			$args     = (array) $args;
			$defaults = (array) $defaults;
			$result   = $defaults;

			foreach ( $args as $args_key => &$args_value ) {

				if ( is_array( $args_value ) && isset( $result[ $args_key ] ) ) {
					$result[ $args_key ] = self::parse_args( $args_value, $result[ $args_key ] );
				} else {
					$result[ $args_key ] = $args_value;
				}
				
			}

			return $result;
		}

		/**
		 * Add Admin Menu
		 *
		 * @return void
		 */
		public function register_menu() {
			self::$hook_suffix = add_submenu_page( 'options-general.php', 'Simple Header Footer Scripts', 'Simple Header Footer Scripts', 'manage_options', SHF_PLUGIN_SLUG, array( $this, 'shf_display_options_panel' ) );
		}

		/**
		 * Settings meta boxes.
		 *
		 * @return void
		 */
		public function shf_display_options_panel() {
		?>
		<div class="wrap" id="shf-settings">
			<h1 class="wp-heading-inline"><?php esc_attr_e( 'Simple Header Footer Scripts', 'simple-header-footer-scripts' ); ?></h1>
			<div id="poststuff">
				<div id="post-body" class="metabox-holder columns-2">
					<?php do_action( 'shf_before_options' ); ?>
					<div id="post-body-content" style="position: relative;">
						<form action="options.php" method="post" id="shf-form-submit">
							<div id="post-body-content">
								<?php settings_fields( 'shf_settings' ); ?>
								<table class="form-table">
									<?php do_action( 'shf_before_fields' ); ?>

									<?php do_settings_fields( 'shf_settings', 'default' ); ?>

									<?php do_action( 'shf_after_fields' ); ?>
								</table><!-- /form-table -->
							</div><!-- /post-body-content -->
							<?php submit_button( esc_attr__( 'Save Settings', 'simple-header-footer-scripts' ), 'primary', 'save', false ); ?>
						</form>
					</div>
					<div id="postbox-container-1" class="postbox-container">
						<?php do_action( 'shf_after_options' ); ?>
					</div><!-- /postbox-container-1 -->
				</div><!-- /post-body -->
			</div><!-- /poststuff -->
		</div><!-- /wrap -->
		<?php
		}

		/**
		 * Register Settings.
		 *
		 * @return void
		 */
		public function register_settings() {

			register_setting( 'shf_settings', SHF_OPTIONS, array( $this, 'sanitize_settings' ) );

			$hooks = SHF_Admin::get_options();

			foreach ( $hooks as $hook => $hook_data ) {

				if ( ! $hook || ! is_array( $hook_data ) ) {
					return;
				}

				$hook_id = str_replace( '_', '-', $hook );
				$hook_title = isset( $hook_data['title'] ) ? $hook_data['title'] : '';

				// Add field to page and pass in additional hook data to display field.
				add_settings_field( SHF_OPTIONS . '[' . $hook . ']', $hook_title, array( $this, 'display_fields' ), 'shf_settings',  'default', array( 'hook' => $hook, 'hook_data' => $hook_data, ) );
			}

		}

		/**
		 * Render settings
		 *
		 * @param array $args Field data.
		 * @return void
		 */
		public function display_fields( $args ) {

			// Args.
			$hook        = isset( $args['hook'] ) ? esc_attr( $args['hook'] ) : '';
			$hook_data   = isset( $args['hook_data'] ) ? $args['hook_data'] : '';

			// Hook Data.
			$hook_id     = str_replace( '_', '-', $hook );
			$disabled    = isset( $hook_data['disabled'] ) ? esc_attr( $hook_data['disabled'] ) : '';
			$content     = isset( $hook_data['content'] ) ? esc_attr( $hook_data['content'] ) : '';
			$priority    = isset( $hook_data['priority'] ) ? esc_attr( $hook_data['priority'] ) : 10;
			$description = isset( $hook_data['description'] ) ? $hook_data['description'] : '';

			?>
			<textarea cols="60" rows="10" id="shf-<?php echo $hook_id; ?>" name="<?php echo SHF_OPTIONS ?>[<?php echo $hook; ?>][content]"><?php echo $content; ?></textarea>
			<p><?php echo $description; ?></p>
			<div class="shf-hook-disable">
				<input id="<?php echo $hook_id; ?>-disabled" type='checkbox' name="<?php echo SHF_OPTIONS ?>[<?php echo $hook; ?>][disabled]" <?php checked( $disabled , 1 ); ?> value="1">
				<label for="<?php echo $hook_id; ?>-disabled"><?php esc_attr_e( 'Disable', 'simple-header-footer-scripts' ) ?></label>
			</div>
			<div class="shf-hook-priority">
				<label for="<?php echo $hook_id; ?>-priority"><?php esc_attr_e( 'Priority', 'simple-header-footer-scripts' ) ?></label>
				<input id="<?php echo $hook_id; ?>-priority" type='text' name="<?php echo SHF_OPTIONS ?>[<?php echo $hook; ?>][priority]" value="<?php echo $priority; ?>">
			</div>
			<?php
		}

		/**
		 * Validate Fields data.
		 *
		 * @param array $settings Validate field.
		 * @return array
		 */
		public function sanitize_settings( $settings ) {
			return $settings;
		}

		/**
		 * Execute hooks.
		 *
		 * @return void
		 */
		public function execute_hooks() {

			// Ignore admin, feed, robots or trackbacks.
			if ( is_admin() || is_feed() || is_robots() || is_trackback() ) {
				return;
			}

			$options = ! empty ( SHF_Admin::get_options() ) ? SHF_Admin::get_options() : array();

			foreach ( $options as $hook => $hook_data ) {

				$hook_content  = isset( $options[ $hook ]['content'] ) ? $options[ $hook ]['content'] : null;
				$hook_disabled = isset( $options[ $hook ]['disabled'] ) ? esc_attr( $options[ $hook ]['disabled'] ) : null;
				$hook_priority = isset( $options[ $hook ]['priority'] ) ? esc_attr( $options[ $hook ]['priority'] ) : 10;

				if ( ! $hook_disabled && ! empty( $hook_content ) ) {

					add_action( $hook, array( $this, 'display_hook_content' ), $hook_priority );
				
				}
			}

		}

		/**
		 * Display hook content.
		 *
		 * @return void
		 */
		public function display_hook_content() {
			
			$hook = current_filter();
			$options = SHF_Admin::get_options();
			
			$content = isset ( $options[$hook]['content'] ) ? $options[$hook]['content'] : '';
			echo $content;

		}

		/**
		 * Admin Notices
		 *
		 * @return void
		 */
		public function admin_notices() {

			$screen = get_current_screen();
			
			if ( ! is_admin() ) {
				return;
			}

			if ( ! is_user_logged_in() ) {
				return;
			}

			if ( ! current_user_can( 'update_core' ) ) {
				return;
			}
			
			if ( 'settings_page_' . SHF_PLUGIN_SLUG === $screen->base ) {
				
				if ( isset( $_REQUEST['settings-updated'] ) && 'true' === $_REQUEST['settings-updated'] ) {
					// This notice is already added as a part of settings API. Update the dismiss notice option.
					update_option( SHF_WELCOME_DISMISS, 1 );
				} elseif ( isset( $_REQUEST['reset'] ) && 'true' === $_REQUEST['reset'] ) {
					add_settings_error( 'shf-notices', 'true', esc_attr__( 'Settings reset.', 'simple-header-footer-scripts' ), 'updated' );
				} elseif ( isset( $_REQUEST['imported'] ) && 'true' === $_REQUEST['imported'] ) {
					add_settings_error( 'shf-notices', 'true', esc_attr__( 'Settings imported.', 'simple-header-footer-scripts' ), 'updated' );
				} elseif ( isset( $_REQUEST['error'] ) && 'true' === $_REQUEST['error'] ) {
					add_settings_error( 'shf-notices', 'true', esc_attr__( 'Settings Error.', 'simple-header-footer-scripts' ), 'error' );
				}

			} elseif ( ! self::$dismiss_notice ) { ?>
				<div class="notice notice-success is-dismissible shf-notice-welcome">
					<p><?php echo sprintf( __( 'Thank you for installing %s! <a href="%s">Click here</a> to configure the plugin.', 'simple-header-footer-scripts' ), SHF_PLUGIN_NAME, admin_url( 'options-general.php?page=' . SHF_PLUGIN_SLUG . '' ) ); ?></p>
				</div>
				<?php
			}

		}

		/*
		 * Load Text Domain
		 *
		 * @return void
		 */
		public function load_files() {

			include_once SHF_DIR . 'admin/class-shf-settings.php';
			
		}

		/**
		 * Load Scripts
		 *
		 * @param [type] $hook Current page.
		 * @return void
		 */
		public function load_scripts( $hook ) {

			$dir_name = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : 'min/';
			$file_suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
			
			$css_path = SHF_URL . 'assets/css/' . $dir_name;
			$js_path =  SHF_URL . 'assets/js/' . $dir_name;

			$shf_admin_config 	= array (
				'nonce' => wp_create_nonce( 'shf-nonce' ),
			);

			// Require only if welcome notice is not dismissed.
			if ( ! self::$dismiss_notice ) {
				wp_enqueue_script( 'shf-dismiss-notice', $js_path . 'shf-dismiss-notice' . $file_suffix . '.js', array( 'jquery' ), SHF_VERSION, true );				
				wp_localize_script( 'shf-dismiss-notice', 'shfAdminConfig', $shf_admin_config );
			}

			// Enqueue these scripts only on plugin admin page.
			if ( self::$hook_suffix !== $hook ) {
				return;
			}

			$hooks = SHF_Admin::get_hooks_list();
			
			$shf_hook_list = array();

			foreach ( $hooks as $hook => $hook_data ) {
				$shf_hook_list[] = str_replace( '_', '-', $hook );
			}
			
			/* Enqueue Code Editor */
			wp_enqueue_code_editor(array());

			/* Enqueue Scripts & Styles */
			wp_enqueue_style( 'shf-styling', $css_path . 'shf-scripts' . $file_suffix . '.css' , array(), SHF_VERSION );		
			wp_enqueue_script( 'shf-scripts', $js_path . 'shf-scripts' . $file_suffix . '.js', array( 'jquery' ), SHF_VERSION, true );				
			wp_localize_script( 'shf-scripts', 'shfActiveHooks', $shf_hook_list );
			
		}

		/**
		 * Add plugin Settings link
		 *
		 * @param array $links Plugin Links.
		 * @return array
		 */
		function add_action_links( $links ) {
			$settings_link = array(
				'<a href="' . admin_url( 'options-general.php?page=' . SHF_PLUGIN_SLUG ) . '">' . __( 'Settings', 'simple-header-footer-scripts' ) . '</a>',
			);
			return array_merge( $settings_link, $links );
		}

		/**
		 * AJAX call to Sync Library.
		 *
		 * @return void
		 */
		public function dismiss_welcome_notice() {
			if ( ! isset( $_POST['nonce'] )
			&& ! wp_verify_nonce( sanitize_key( $_POST['nonce'] ), 'shf-nonce' ) ) {
				return;
			}

			update_option( SHF_WELCOME_DISMISS, 1 );

			wp_send_json_success();
			
			wp_die();
		}

		/**
		 * Load Textdomain
		 *	
		 * @since 1.0.0
		 * @return void
		 */
		public function load_textdomain() {
			load_plugin_textdomain( 'simple-header-footer-scripts', false, basename( dirname( __FILE__ ) ) . '/languages/' );
		}

	}
} // End if().

// Get Instance.
SHF_Admin::get_instance();