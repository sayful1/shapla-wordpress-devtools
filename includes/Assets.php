<?php

namespace Shapla\Devtools;
// If this file is called directly, abort.
defined( 'ABSPATH' ) || exit;

class Assets {

	/**
	 * The instance of the class
	 *
	 * @var self
	 */
	private static $instance;

	/**
	 * Plugin name slug
	 *
	 * @var string
	 */
	private $plugin_name;

	/**
	 * plugin version
	 *
	 * @var string
	 */
	private $version;

	/**
	 * Ensures only one instance of the class is loaded or can be loaded.
	 *
	 * @return self
	 */
	public static function init() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();

			add_action( 'wp_loaded', [ self::$instance, 'register' ] );
		}

		return self::$instance;
	}

	/**
	 * Check if script debugging is enabled
	 *
	 * @return bool
	 */
	private function is_script_debug_enabled(): bool {
		return defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG;
	}

	/**
	 * Checks to see if the site has SSL enabled or not.
	 *
	 * @return bool
	 */
	public static function is_ssl(): bool {
		if ( is_ssl() ) {
			return true;
		} elseif ( 0 === stripos( get_option( 'siteurl' ), 'https://' ) ) {
			return true;
		} elseif ( isset( $_SERVER['HTTP_X_FORWARDED_PROTO'] ) && 'https' == $_SERVER['HTTP_X_FORWARDED_PROTO'] ) {
			return true;
		}

		return false;
	}

	/**
	 * Get assets URL
	 *
	 * @param string $path
	 *
	 * @return string
	 */
	public static function get_assets_url( $path = '' ): string {
		$url = SHAPLA_DEVTOOLS_ASSETS;

		if ( static::is_ssl() && 0 === stripos( $url, 'http://' ) ) {
			$url = str_replace( 'http://', 'https://', $url );
		}

		if ( ! empty( $path ) ) {
			return rtrim( $url, '/' ) . '/' . ltrim( $path, '/' );
		}

		return $url;
	}

	/**
	 * Register our app scripts and styles
	 *
	 * @return void
	 */
	public function register() {
		$this->plugin_name = SHAPLA_DEVTOOLS;
		$this->version     = SHAPLA_DEVTOOLS_VERSION;

		if ( $this->is_script_debug_enabled() ) {
			$this->version = $this->version . '-' . time();
		}

		$this->register_scripts( $this->get_scripts() );
		$this->register_styles( $this->get_styles() );
	}

	/**
	 * Register scripts
	 *
	 * @param array $scripts
	 *
	 * @return void
	 */
	private function register_scripts( array $scripts ) {
		foreach ( $scripts as $handle => $script ) {
			$deps      = $script['deps'] ?? false;
			$in_footer = $script['in_footer'] ?? true;
			$version   = $script['version'] ?? $this->version;
			wp_register_script( $handle, $script['src'], $deps, $version, $in_footer );
		}
	}

	/**
	 * Register styles
	 *
	 * @param array $styles
	 *
	 * @return void
	 */
	public function register_styles( array $styles ) {
		foreach ( $styles as $handle => $style ) {
			$deps = $style['deps'] ?? false;
			wp_register_style( $handle, $style['src'], $deps, $this->version );
		}
	}

	/**
	 * Get all registered scripts
	 *
	 * @return array
	 */
	public function get_scripts(): array {
		return [
			"{$this->plugin_name}-admin" => [
				'src'  => static::get_assets_url( 'js/admin.js' ),
				'deps' => [ "jquery" ],
			],
		];
	}

	/**
	 * Get registered styles
	 *
	 * @return array
	 */
	public function get_styles(): array {
		return [
			"{$this->plugin_name}-admin" => [
				'src' => static::get_assets_url( 'css/admin.css' )
			],
		];
	}
}
