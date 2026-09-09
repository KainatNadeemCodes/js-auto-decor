<?php
/**
 * Kata_Plus_License_Activation Class.
 *
 * @author  Webnus
 * @package Kata Plus
 * @since   1.0.0
 */

// Don't load directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Kata_Plus_License_Activation' ) ) {
	class Kata_Plus_License_Activation {
		/**
		 * The remote address
		 *
		 * @var string
		 */
		public $remote_address;

		/**
		 * The plugin current version
		 *
		 * @var string
		 */
		public $current_version;

		/**
		 * The site url
		 *
		 * @var string
		 */
		public $site_url;

		/**
		 * The kata options
		 *
		 * @var string
		 */
		public $itemid;

		/**
		 * Maintains the url of the plugin.
		 *
		 * @access  public
		 * @var     string
		 */
		public $plugin_url;

		/**
		 * Plugin Slug ( plugin_directory/plugin_file.php )
		 *
		 * @var string
		 */
		public $plugin_slug;

		/**
		 * Plugin name ( plugin_file )
		 *
		 * @var string
		 */
		public $slug;

		/**
		 * Plugin file ( plugin_directory/plugin_file.php )
		 *
		 * @var string
		 */
		public $plugin_file;

		/**
		 * cache_key
		 *
		 * @var string
		 */
		public $cache_key;

		/**
		 * Plugin response transient key
		 *
		 * @var string
		 */
		public $response_transient_key;

		/**
		 * Initialize a new instance of the WordPress Auto-Update class
		 *
		 * @param string $current_version
		 * @param string $plugin_slug
		 */
		function __construct( $current_version, $plugin_slug, $plugin_file ) {
			// Set the class public variables
			$this->current_version        = $current_version;
			$this->remote_address         = 'https://katademosbackup.katademos.com/wp-json/kata-demo-webservice/v1/check-license/?doAction=';
			$this->plugin_url             = 'https://webnus.net/kata/';
			$this->itemid                 = array( '34519734', '34519738', '34519741', '34519748', '34519749', '34519750', '34519752', '34519974', '34519968' );
			$this->site_url               = get_site_url();
			$this->plugin_slug            = $plugin_slug;
			$this->slug                   = $plugin_slug;
			$this->plugin_file            = $plugin_file;
			$this->cache_key              = 'climax-' . $plugin_slug;
			$this->response_transient_key = md5( sanitize_key( $this->plugin_file ) . 'response_transient' );

			/**
			 * Disable WordPress default update system
			 */
			add_filter( 'http_request_args', array( $this, 'disable_w_org_plugin_updates' ), 5, 2 );

			/**
			 * define the alternative API for updating checking
			 */
			add_filter( 'pre_set_site_transient_update_plugins', array( $this, 'check_update' ), 50 );

			/**
			 * Define the alternative response for information checking
			 */
			add_filter( 'plugins_api', array( $this, 'check_info' ), 10, 3 );

			add_action( 'delete_site_transient_update_plugins', array( $this, 'delete_transients' ) );

			remove_action( 'after_plugin_row_' . $this->plugin_slug . '/' . $this->plugin_slug . '.php', 'wp_plugin_update_row' );
			add_action( 'after_plugin_row_' . $this->plugin_slug . '/' . $this->plugin_slug . '.php', array( $this, 'show_update_notification' ), 9, 2 );

			$this->maybe_delete_transients();
		}

		/**
		 * Add our self-hosted description to the filter
		 *
		 * @param boolean $false
		 * @param array   $action
		 * @param object  $arg
		 * @return bool|object
		 */
		public function check_info( $false, $action, $arg ) {

			if ( isset( $arg->slug ) && $arg->slug === $this->slug ) {

				$plugin_info = get_site_transient( $this->cache_key );

				if ( ! empty( $plugin_info ) ) {
					return $plugin_info;
				}

				/**
				 * Get the remote version
				 */
				$remote_version = $this->getRemote_version();

				/**
				 * Get Remote Package
				 */
				$remote_package = $this->getRemote_package();

				/**
				 * Get Remote information
				 */
				$information = $this->getRemote_information();
				$response    = new \stdClass();

				$response->name          = Kata_Plus::$plugin_name;
				$response->slug          = Kata_Plus::$slug;
				$response->author        = '<a href="https://webnus.net/">webnus.net</a>';
				$response->homepage      = 'https://webnus.net/';
				$response->requires      = $information->requires;
				$response->tested        = $information->tested;
				$response->version       = $remote_version;
				$response->last_updated  = $information->last_updated;
				$response->download_link = $remote_package;
				$response->sections      = $information->sections;
				$response->banners       = [
					'high' => 'https://ps.w.org/kata-plus/assets/banner-772x250.png',
					'low'  => 'https://ps.w.org/kata-plus/assets/banner-772x250.png',
				];

				// Expires in 1 day
				set_site_transient( $this->cache_key, $response, DAY_IN_SECONDS );

				return $response;
			}

			return false;
		}

		public function is_expired( $license_data ) {

			$expired      = false;
			$expiration   = $license_data['expires'] ?? '';
			if ( is_numeric( $expiration ) && time() > $expiration ) {
				$expired = true;
			}

			return $expired;
		}

		/**
		 * Return the remote version
		 *
		 * @return object $remote_version
		 */
		public function getRemote_version( $license = 'kata-plus-free' ) {

			foreach ( $this->itemid as $id ) {
				$version = $this->remote_address . 'version&itemId=' . $id . '&slug=' . $this->slug . '&license=' . $license . '&siteURL=' . $this->site_url;
				$request = wp_remote_retrieve_body(
					wp_remote_get(
						$version,
						array(
							'headers'     => array( 'apikey' => 'trusted' ),
							'body'        => null,
							'timeout'     => '30',
							'redirection' => '10',
							'user-agent'  => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/83.0.4103.116 Safari/537.36',
						)
					)
				);

				$request = json_decode( $request );

				if ( isset( $request->message ) ) {
					return $request;
				}
			}
			return false;
		}

		/**
		 * Return the remote package
		 *
		 * @return string $remote_package
		 */
		public function getRemote_package() {

			foreach ( $this->itemid as $id ) {
				$version = $this->remote_address . 'download&itemId=' . $id . '&slug=' . $this->slug . '&license=kata-plus-free&siteURL=' . $this->site_url;
				$request = wp_remote_retrieve_body(
					wp_remote_get(
						$version,
						array(
							'headers'     => array( 'apikey' => 'trusted' ),
							'body'        => null,
							'timeout'     => '30',
							'redirection' => '10',
							'user-agent'  => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/83.0.4103.116 Safari/537.36',
						)
					)
				);

				$request = json_decode( $request );

				if ( isset( $request->params->download_link ) ) {
					return $request->params->download_link;
				}
			}
			return false;
		}

		/**
		 * Get information about the remote version
		 *
		 * @return bool|object
		 */
		public function getRemote_information() {
			$request = wp_remote_post( 'https://api.wordpress.org/plugins/info/1.0/kata-plus.json', array( 'timeout' => 30 ) );

			if ( ! is_wp_error( $request ) || wp_remote_retrieve_response_code( $request ) === 200 ) {
				$information = json_decode( wp_remote_retrieve_body( $request ), true );

				if ( is_array( $information ) ) {
					// Object casting is required in order to match the info/1.0 format.
					$information = (object) $information;
				}

				return $information;
			}

			return false;
		}

		/**
		 * Return license data
		 *
		 * @param string $license
		 *
		 * @since 1.4.2
		 *
		 * @return array|false
		 */
		public function getRemote_license( $license = '' ) {
			return array( 'error' => 'valid' );
		}

		public function delete_transients() {
			$this->delete_transient( $this->response_transient_key );
		}

		private function maybe_delete_transients() {
			global $pagenow;

			if ( 'update-core.php' === $pagenow && isset( $_GET['force-check'] ) ) {
				$this->delete_transients();
			}
		}

		/**
		 * Check the transient data and update if necessary.
		 *
		 * @param object $_transient_data The transient data to check.
		 * @return object Returns the updated transient data.
		 */
		private function check_transient_data( $_transient_data ) {

			if ( ! is_object( $_transient_data ) ) {
				$_transient_data = new \stdClass();
			}

			if ( empty( $_transient_data->checked ) ) {
				return $_transient_data;
			}


			$version_info = $this->get_transient( $this->response_transient_key );
			if ( false === $version_info ) {
				$version_info = $this->getRemote_information();

				if ( is_wp_error( $version_info ) ) {
					$version_info        = new \stdClass();
					$version_info->error = true;
				}

				$this->set_transient( $this->response_transient_key, $version_info );
			}

			if ( ! empty( $version_info->error ) ) {
				return $_transient_data;
			}

			// include an unmodified $wp_version
			include ABSPATH . WPINC . '/version.php';

			/**
			 * Get the remote version
			 */
			$remote_version_response = $this->getRemote_version();
			if ( ! $remote_version_response || ! isset( $remote_version_response->params->new_version ) ) {
				return $_transient_data;
			}
			$remote_version = $remote_version_response->params->new_version;

			if ( version_compare( $wp_version, $version_info->requires, '<' ) ) {
				return $_transient_data;
			}

			// If a newer version is available, add the update
			if ( version_compare( $this->current_version, $remote_version, '<' ) ) {
				// Package
				$remote_package = $this->getRemote_package();
				if ( ! $remote_package ) {
					return $_transient_data;
				}
				$obj            = new stdClass();
				$obj->id        = '';
				$obj->slug      = $this->slug;
				$obj->plugin    = $this->plugin_file;
				$obj->new_version = $remote_version;
				$obj->url         = $this->plugin_url;
				$obj->package     = str_replace( '\/', '/', $remote_package );
				$obj->icons       = array(
					'2x' => 'https://ps.w.org/kata-plus/assets/icon-256x256.png',
					'1x' => 'https://ps.w.org/kata-plus/assets/icon-256x256.png',
				);
				$obj->banners     = array(
					'2x' => 'https://ps.w.org/kata-plus/assets/banner-772x250.png',
					'1x' => 'https://ps.w.org/kata-plus/assets/banner-772x250.png',
				);
				$obj->banners_rtl = array();
				$obj->sections    = array(
					'description'     => 'Kata Plus functionality',
					'another_section' => 'This is another section',
					'changelog'       => 'Kata Plus functionality',
				);
				$_transient_data->response[ $this->plugin_file ] = $obj;
			}

			$_transient_data->last_checked                                                     = current_time( 'timestamp' );
			$_transient_data->checked[ $this->plugin_file ] = $this->current_version;

			if ( ! isset( $_transient_data->translations ) ) {
				$_transient_data->translations = array();
			}

			$_transient_data->translations = array_filter(
				$_transient_data->translations,
				function( $translation ) {
					return ( $translation['slug'] !== $this->plugin_slug );
				}
			);

			if ( ! empty( $version_info->translations ) ) {
				foreach ( $version_info->translations as $translation ) {
					$_transient_data->translations[] = array(
						'type'       => 'plugin',
						'slug'       => $this->plugin_slug,
						'language'   => $translation['language'],
						'version'    => $version_info->new_version,
						'updated'    => $translation['updated'],
						'package'    => $translation['package'],
						'autoupdate' => true,
					);
				}
			}

			return $_transient_data;
		}

		public function check_update( $_transient_data ) {
			global $pagenow;

			if ( ! is_object( $_transient_data ) ) {
				$_transient_data = new \stdClass();
			}

			if ( 'plugins.php' === $pagenow && is_multisite() ) {
				return $_transient_data;
			}

			return $this->check_transient_data( $_transient_data );
		}

		public function show_update_notification( $file, $plugin ) {
			if ( is_network_admin() ) {
				return;
			}

			if ( ! current_user_can( 'update_plugins' ) ) {
				return;
			}

			if ( ! is_multisite() ) {
				return;
			}

			if ( $this->plugin_slug . '/' . $this->plugin_slug . '.php' !== $file ) {
				return;
			}

			// Remove our filter on the site transient
			remove_filter( 'pre_set_site_transient_update_plugins', array( $this, 'check_update' ) );

			$update_cache = get_site_transient( 'update_plugins' );
			$update_cache = $this->check_transient_data( $update_cache );
			set_site_transient( 'update_plugins', $update_cache );

			// Restore our filter
			add_filter( 'pre_set_site_transient_update_plugins', array( $this, 'check_update' ) );
		}

		/**
		 * Disable w.org update for this plugin.
		 *
		 * @param array  $r
		 * @param string $url
		 * @return array
		 */
		public function disable_w_org_plugin_updates( $r, $url ) {
			// If it's not a plugins update request, stop.
			if ( 0 !== strpos( $url, 'https://api.wordpress.org/plugins/update-check/1.1/' ) ) {
				return $r;
			}

			$plugins = json_decode( $r['body']['plugins'], true );
			if ( isset( $plugins['plugins'][ $this->plugin_file ] ) ) {
				unset( $plugins['plugins'][ $this->plugin_file ] );
			}
			$r['body']['plugins'] = wp_json_encode( $plugins );

			return $r;
		}

		/**
		 * Retrieves the transient value for the given cache key.
		 *
		 * @param datatype $cache_key The key used to retrieve the transient value.
		 * @throws Some_Exception_Class Description of the exception thrown.
		 * @return Some_Return_Value The value associated with the cache key.
		 */
		protected function get_transient( $cache_key ) {
			$cache_data = get_option( $cache_key );

			if ( empty( $cache_data['timeout'] ) || current_time( 'timestamp' ) > $cache_data['timeout'] ) {
				// Cache is expired.
				return false;
			}

			return $cache_data['value'];
		}

		protected function set_transient( $cache_key, $value, $expiration = 0 ) {
			if ( empty( $expiration ) ) {
				$expiration = strtotime( '+64 hours', current_time( 'timestamp' ) );
			}

			$data = array(
				'timeout' => $expiration,
				'value'   => $value,
			);

			update_option( $cache_key, $data, 'no' );
		}

		protected function delete_transient( $cache_key ) {
			delete_option( $cache_key );
		}
	}
	new Kata_Plus_License_Activation( Kata_Plus::$version, Kata_Plus::$slug, Kata_Plus::$name );
}
