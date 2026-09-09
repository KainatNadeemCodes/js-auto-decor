<?php
/**
 * Install Plugins Class.
 *
 * @author  Webnus
 * @package Kata Plus
 * @since   1.0.0
 */

// Don't load directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Kata_Plus_Install_Plugins' ) ) {
	class Kata_Plus_Install_Plugins {
		/**
		 * The directory of the file.
		 *
		 * @access  public
		 * @var     string
		 */
		public static $dir;

		/**
		 * The url of the file.
		 *
		 * @access  public
		 * @var     string
		 */
		public static $url;

		/**
		 * The directory of the plugins.
		 *
		 * @access  private
		 * @var     string
		 */
		public $plugins_dir;

		/**
		 * The url of the images.
		 *
		 * @access  private
		 * @var     string
		 */
		public $images_url;

		/**
		 * Required plugins data.
		 *
		 * @access  private
		 * @var     string
		 */
		public $plugins_data;

		/**
		 * Instance of this class.
		 *
		 * @since   1.0.0
		 * @access  public
		 * @var     Kata_Plus_Install_Plugins
		 */
		public static $instance;

		/**
		 * Whether the fatal-error shutdown handler is registered.
		 *
		 * @var bool
		 */
		private static $plugin_action_shutdown_registered = false;

		/**
		 * Provides access to a single instance of a module using the singleton pattern.
		 *
		 * @since   1.0.0
		 * @return  object
		 */
		public static function get_instance() {
			if ( self::$instance === null ) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		/**
		 * Constructor.
		 *
		 * @access      public
		 * @return      void
		 */
		public function __construct() {
			$this->definitions();
			$this->actions();
			$this->dependencies();
		}

		/**
		 * Global definitions.
		 *
		 * @since   1.0.0
		 */
		public function definitions() {
			self::$dir         = Kata_Plus::$dir . 'includes/install-plugins/';
			self::$url         = Kata_Plus::$url . 'includes/install-plugins/';
			$this->images_url  = Kata_Plus::$assets . 'images/admin/plugins/';
			$this->plugins_dir = 'https://katademos.com/requirements/pp/';
		}

		/**
		 * Add actions.
		 *
		 * @since   1.0.0
		 */
		public function actions() {
			add_action( 'tgmpa_register', array( $this, 'register_plugins' ) );
			add_filter( 'tgmpa_load', array( $this, 'tgmpa_load' ), 10 );
			add_action( 'wp_ajax_kata_plus_plugin_actions', array( $this, 'plugin_actions' ) );
			if ( isset( $_GET['page'] ) && $_GET['page'] == 'kata-plus-plugins' ) {
				add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_styles' ) );
			}
			if ( isset( $_GET['page'] ) && ( $_GET['page'] == 'kata-plus-plugins' || $_GET['page'] == 'kata-plus-demo-importer' ) ) {
				add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
			}
		}

		/**
		 * Load dependencies.
		 *
		 * @since   1.0.0
		 */
		public function dependencies() {
			Kata_Plus_Autoloader::load( self::$dir . 'core', 'class-tgm-plugin-activation' );
		}

		/**
		 * Register recommended plugins.
		 *
		 * @access  public
		 * @param   null
		 * @return  void
		 */
		public function register_plugins() {
			tgmpa(
				$this->configuration_plugins(),
				array(
					'id'           => 'kata-plus',                   // Unique ID for hashing notices for multiple instances of TGMPA.
					'default_path' => '',                       // Default absolute path to bundled plugins.
					'menu'         => 'kata-plus-plugins',      // Menu slug.
					'parent_slug'  => 'admin.php',              // Parent menu slug.
					'capability'   => 'edit_theme_options',     // Capability needed to view plugin install page, should be a capability associated with the parent menu used.
					'has_notices'  => false,                    // Show admin notices or not.
					'dismissable'  => true,                    // If false, a user cannot dismiss the nag message.
					'dismiss_msg'  => '',                      // If 'dismissable' is false, this message will be output at top of nag.
					'is_automatic' => true,                   // Automatically activate plugins after installation or not.
					'message'      => '',                      // Message to output right before the plugins table.
				)
			);
		}

		/**
		 * Configuration recommended plugins.
		 *
		 * @access  private
		 * @param   null
		 * @return  array
		 */
		private function configuration_plugins() {
			$this->plugins_data = require_once self::$dir . 'config/install-plugins-data.php';
			$plugins = apply_filters( 'kata_plus_plugins', $this->plugins_data );
			return $plugins;
		}

		/**
		 * By default TGMPA only loads on the WP back-end and not in an Ajax call.
		 * Enable TGMP in AJAX call.
		 *
		 * @access  public
		 * @param   bool $load Whether or not TGMPA should load.
		 * @return  bool
		 */
		public function tgmpa_load( $load ) {
			return true;
		}

		/**
		 * AJAX callback method. Used to activate/deactivate/install plugin.
		 *
		 * Heavy plugins (e.g. Revolution Slider) need extended PHP execution time and HTTP download timeouts.
		 *
		 * @access  public
		 * @return  void
		 */
		public function plugin_actions() {
			if ( ! current_user_can( Kata_Plus_Helpers::capability() ) ) {
				wp_die( -1 );
			}

			$plugin_action = isset( $_REQUEST['plugin-action'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['plugin-action'] ) ) : '';

			if ( ! $plugin_action || empty( $_REQUEST['tgmpa-nonce'] ) ) {
				wp_die( -1 );
			}

			check_admin_referer( 'tgmpa-' . $plugin_action, 'tgmpa-nonce' );

			$tgmpa = TGM_Plugin_Activation::$instance;

			if ( 'activate' === $plugin_action || 'deactivate' === $plugin_action ) {
				$slug = isset( $_REQUEST['plugin'] ) ? $tgmpa->sanitize_key( urldecode( wp_unslash( $_REQUEST['plugin'] ) ) ) : '';

				if ( ! $slug || ! isset( $tgmpa->plugins[ $slug ] ) ) {
					echo 'plugin_action_error:' . rawurlencode( __( 'Invalid plugin.', 'kata-plus' ) );
					wp_die();
				}

				$plugin = $tgmpa->plugins[ $slug ];

				if ( 'activate' === $plugin_action ) {
					if ( ! function_exists( 'activate_plugin' ) ) {
						require_once ABSPATH . 'wp-admin/includes/plugin.php';
					}

					$this->begin_plugin_action_capture();

					ob_start();
					$activated = activate_plugin( $plugin['file_path'] );
					ob_end_clean();

					$this->end_plugin_action_capture();

					if ( is_wp_error( $activated ) ) {
						$this->send_plugin_action_error( $activated->get_error_message() );
					}

					if ( ! is_plugin_active( $plugin['file_path'] ) ) {
						$this->send_plugin_action_error(
							__( 'Plugin could not be activated. Please check Plugins in WordPress admin for details.', 'kata-plus' )
						);
					}

					$convert_action = 'deactivate';
				} else {
					deactivate_plugins( $plugin['file_path'] );
					$convert_action = 'activate';
				}

				$url = wp_nonce_url(
					add_query_arg(
						array(
							'plugin'                   => rawurlencode( $slug ),
							'tgmpa-' . $convert_action => $convert_action . '-plugin',
						),
						$tgmpa->get_tgmpa_url()
					),
					'tgmpa-' . $convert_action,
					'tgmpa-nonce'
				);

				if ( 'activate' === $plugin_action ) {
					echo 'deactivate_href:' . htmlspecialchars_decode( $url );
				} else {
					echo 'activate_href:' . htmlspecialchars_decode( $url );
				}

				wp_die();
			}

			if ( 'install' !== $plugin_action && 'update' !== $plugin_action ) {
				wp_die( -1 );
			}

			$slug = isset( $_REQUEST['plugin'] ) ? $tgmpa->sanitize_key( urldecode( wp_unslash( $_REQUEST['plugin'] ) ) ) : '';

			if ( ! $slug || ! isset( $tgmpa->plugins[ $slug ] ) ) {
				echo 'plugin_action_error:' . rawurlencode( __( 'Invalid plugin.', 'kata-plus' ) );
				wp_die();
			}

			$was_installed = $tgmpa->is_plugin_installed( $slug );

			$this->begin_large_plugin_operation();

			ob_start();
			$tgmpa->install_plugins_page();
			ob_end_clean();

			$this->end_large_plugin_operation();

			if ( 'install' === $plugin_action && ! $was_installed && ! $tgmpa->is_plugin_installed( $slug ) ) {
				echo 'plugin_action_error:' . rawurlencode(
					__( 'Installation failed or was interrupted. Try again, or install the plugin from Plugins → Add New.', 'kata-plus' )
				);
				wp_die();
			}

			$url = wp_nonce_url(
				add_query_arg(
					array(
						'plugin'         => rawurlencode( $slug ),
						'tgmpa-activate' => 'activate-plugin',
					),
					$tgmpa->get_tgmpa_url()
				),
				'tgmpa-activate',
				'tgmpa-nonce'
			);

			echo 'activate_href:' . htmlspecialchars_decode( $url );

			wp_die();
		}

		/**
		 * Register shutdown handler so fatal errors during activate return a parseable AJAX body.
		 *
		 * @return void
		 */
		private function begin_plugin_action_capture() {
			$GLOBALS['kata_plus_plugin_action_capture'] = true;

			if ( ! self::$plugin_action_shutdown_registered ) {
				register_shutdown_function( array( $this, 'handle_plugin_action_fatal' ) );
				self::$plugin_action_shutdown_registered = true;
			}
		}

		/**
		 * Stop fatal capture (successful completion).
		 *
		 * @return void
		 */
		private function end_plugin_action_capture() {
			$GLOBALS['kata_plus_plugin_action_capture'] = false;
		}

		/**
		 * Send a machine-readable plugin action error for the importer UI.
		 *
		 * @param string $message Error message.
		 * @return void
		 */
		private function send_plugin_action_error( $message ) {
			$this->end_plugin_action_capture();

			while ( ob_get_level() > 0 ) {
				ob_end_clean();
			}

			if ( ! headers_sent() ) {
				status_header( 200 );
				nocache_headers();
				header( 'Content-Type: text/plain; charset=UTF-8' );
			}

			echo 'plugin_action_error:' . rawurlencode( wp_strip_all_tags( (string) $message ) );

			wp_die();
		}

		/**
		 * Convert PHP fatal/parse errors during plugin activation into importer-friendly output.
		 *
		 * @return void
		 */
		public function handle_plugin_action_fatal() {
			if ( empty( $GLOBALS['kata_plus_plugin_action_capture'] ) ) {
				return;
			}

			$error = error_get_last();

			if ( ! $error || ! in_array( $error['type'], array( E_ERROR, E_PARSE, E_COMPILE_ERROR, E_CORE_ERROR ), true ) ) {
				return;
			}

			$GLOBALS['kata_plus_plugin_action_capture'] = false;

			while ( ob_get_level() > 0 ) {
				@ob_end_clean();
			}

			if ( ! headers_sent() ) {
				status_header( 200 );
				nocache_headers();
				header( 'Content-Type: text/plain; charset=UTF-8' );
			}

			echo 'plugin_action_error:' . rawurlencode(
				sprintf(
					'%s in %s on line %d',
					wp_strip_all_tags( $error['message'] ),
					basename( $error['file'] ),
					(int) $error['line']
				)
			);

			exit;
		}

		/**
		 * Relax PHP / HTTP limits for long plugin installs (large ZIP downloads, unzip).
		 *
		 * @return void
		 */
		private function begin_large_plugin_operation() {
			if ( function_exists( 'wp_raise_memory_limit' ) ) {
				wp_raise_memory_limit( 'admin' );
			}

			ignore_user_abort( true );

			if ( function_exists( 'set_time_limit' ) ) {
				set_time_limit( 0 );
			}

			add_filter( 'http_request_timeout', array( $this, 'filter_http_request_timeout_long' ), 999 );
		}

		/**
		 * Restore filters after TGMPA install flow.
		 *
		 * @return void
		 */
		private function end_large_plugin_operation() {
			remove_filter( 'http_request_timeout', array( $this, 'filter_http_request_timeout_long' ), 999 );
		}

		/**
		 * Ensure wp_remote_request allows enough time for large plugin packages.
		 *
		 * @param int $timeout Existing timeout in seconds.
		 * @return int
		 */
		public function filter_http_request_timeout_long( $timeout ) {
			return max( (int) $timeout, 600 );
		}

		/**
		 * Enqueue Styles.
		 *
		 * @access public
		 * @return void
		 */
		public function enqueue_styles() {
			wp_enqueue_style( 'kata-plus-plugins', Kata_Plus::$assets . 'css/backend/install-plugins.css' );
			if ( is_rtl() ) {
				wp_enqueue_style( 'kata-plus-plugins-rtl', Kata_Plus::$assets . 'css/backend/install-plugins-rtl.css' );
			}
		}

		/**
		 * Enqueue JavaScripts.
		 *
		 * @since   1.0.0
		 */
		public function enqueue_scripts() {
		}
	} // class

	Kata_Plus_Install_Plugins::get_instance();
}
