<?php
/**
 * Kata_Theme_Updater Class.
 *
 * @author  Webnus
 * @package Kata
 * @since   1.0.0
 */

// Don't load directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Kata_Theme_Updater' ) ) {
	class Kata_Theme_Updater {
		public $remote_address;
		public $current_version;
		public $site_url;
		public $itemid;
		public $theme_url;
		public $slug;
		public $cache_key;
		public $response_transient_key;

		/**
		 * Initialize a new instance of the WordPress Auto-Update class
		 *
		 * @param string $current_version
		 * @param string $slug
		 */
		function __construct( $current_version, $slug ) {
			// Set the class public variables
			$this->current_version        = $current_version;
			$this->slug                   = $slug;
			$this->remote_address         = 'https://katademosbackup.katademos.com/wp-json/kata-demo-webservice/v1/check-license/?doAction=';
			$this->theme_url              = 'https://webnus.net/kata/';
			$this->itemid                 = array( '34519734', '34519738', '34519741', '34519748', '34519749', '34519750', '34519752', '34519974', '34519968' );
			$this->site_url               = get_site_url();
			$this->cache_key              = 'kata_theme_update_cache';
			$this->response_transient_key = 'kata_theme_update_response';

			// Disable WordPress default update system
			add_filter( 'http_request_args', array( $this, 'disable_w_org_theme_updates' ), 5, 2 );

			// Define the alternative API for updating checking
			add_filter( 'pre_set_site_transient_update_themes', array( $this, 'check_update' ) );

			// Define the alternative response for information checking
			add_filter( 'themes_api', array( $this, 'check_info' ), 10, 3 );
		}

		/**
		 * Add our self-hosted description to the filter
		 */
		public function check_info( $false, $action, $arg ) {
			if ( isset( $arg->slug ) && $arg->slug === $this->slug ) {
				$response = get_site_transient( $this->cache_key );

				if ( ! $response ) {
					$remote_version_response = $this->getRemote_version();
					$remote_package          = $this->getRemote_package();

					if ( ! $remote_version_response || ! $remote_package ) {
						return false;
					}

					$response                = new \stdClass();
					$response->name          = 'Kata';
					$response->slug          = $this->slug;
					$response->author        = '<a href="https://webnus.net/">webnus.net</a>';
					$response->homepage      = 'https://webnus.net/';
					$response->version       = $remote_version_response->params->new_version;
					$response->download_link = $remote_package;
					$response->sections      = [ 'description' => 'Official theme for Kata Suite.' ];
					$response->banners       = [ 'high' => 'https://ps.w.org/kata/assets/screenshot-1.png' ];

					set_site_transient( $this->cache_key, $response, DAY_IN_SECONDS );
				}
				return $response;
			}
			return false;
		}

		/**
		 * Return the remote version
		 */
		public function getRemote_version( $license = 'kata-theme-free' ) {
			foreach ( $this->itemid as $id ) {
				$version_url = $this->remote_address . 'version&itemId=' . $id . '&slug=' . $this->slug . '&license=' . $license . '&siteURL=' . $this->site_url;
				$response    = wp_remote_get( $version_url, [ 'timeout' => 30 ] );

				if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
					continue;
				}

				$result = json_decode( wp_remote_retrieve_body( $response ) );
				if ( isset( $result->params->new_version ) ) {
					return $result;
				}
			}
			return false;
		}

		/**
		 * Return the remote package
		 */
		public function getRemote_package() {
			foreach ( $this->itemid as $id ) {
				$package_url = $this->remote_address . 'download&itemId=' . $id . '&slug=' . $this->slug . '&license=kata-theme-free&siteURL=' . $this->site_url;
				$response    = wp_remote_get( $package_url, [ 'timeout' => 30 ] );

				if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
					continue;
				}

				$result = json_decode( wp_remote_retrieve_body( $response ) );
				if ( isset( $result->params->download_link ) ) {
					return $result->params->download_link;
				}
			}
			return false;
		}

		/**
		 * Check for updates
		 */
		public function check_update( $_transient_data ) {
			if ( ! is_object( $_transient_data ) ) {
				$_transient_data = new \stdClass();
			}

			$remote_version_response = $this->getRemote_version();
			if ( ! $remote_version_response || ! isset( $remote_version_response->params->new_version ) ) {
				return $_transient_data;
			}
			$remote_version = $remote_version_response->params->new_version;

			if ( version_compare( $this->current_version, $remote_version, '<' ) ) {
				$remote_package = $this->getRemote_package();
				if ( ! $remote_package ) {
					return $_transient_data;
				}

				$update_data                        = [];
				$update_data['theme']               = $this->slug;
				$update_data['new_version']         = $remote_version;
				$update_data['url']                 = $this->theme_url;
				$update_data['package']             = $remote_package;
				$_transient_data->response[ $this->slug ] = $update_data;
			}

			return $_transient_data;
		}

		/**
		 * Disable w.org update for this theme.
		 */
		public function disable_w_org_theme_updates( $r, $url ) {
			if ( 0 !== strpos( $url, 'https://api.wordpress.org/themes/update-check/1.1/' ) ) {
				return $r;
			}
			$themes = json_decode( $r['body']['themes'], true );
			unset( $themes['themes'][ $this->slug ] );
			$r['body']['themes'] = wp_json_encode( $themes );
			return $r;
		}
	}
}
