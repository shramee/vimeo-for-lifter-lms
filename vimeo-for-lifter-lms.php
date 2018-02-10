<?php
/*
Plugin Name: Vimeo for LifterLMS
Plugin URI: http://shramee.me/
Description: Simple plugin starter for quick delivery
Author: Shramee
Version: 1.0.0
Author URI: http://shramee.me/
@developer shramee <shramee.srivastav@gmail.com>
*/

/** Plugin public class */
require 'inc/class-integration.php';

/**
 * Vimeo for LifterLMS main class
 * @static string $token Plugin token
 * @static string $file Plugin __FILE__
 * @static string $url Plugin root dir url
 * @static string $path Plugin root dir path
 * @static string $version Plugin version
 */
class Vimeo_LLMS {

	/** @var Vimeo_LLMS Instance */
	private static $_instance = null;

	/** @var string Token */
	public static $token;

	/** @var string Version */
	public static $version;

	/** @var string Plugin main __FILE__ */
	public static $file;

	/** @var string Plugin directory url */
	public static $url;

	/** @var string Plugin directory path */
	public static $path;

	/**
	 * Return class instance
	 * @return Vimeo_LLMS instance
	 */
	public static function instance( $file ) {
		if ( null == self::$_instance ) {
			self::$_instance = new self( $file );
		}
		return self::$_instance;
	}

	/**
	 * Return class instance
	 * @return LLMS_Abstract_Integration instance
	 */
	public static function integration() {
		return LLMS()->integrations()->get_integration( 'vimeo' );
	}

	public static function vimeo_api( $endpoint = '/me/videos', $post_fields = '{"upload":{"approach":"post","redirect":""}}' ) {

		$ch = curl_init( "https://api.vimeo.com$endpoint" );

		curl_setopt( $ch, CURLOPT_CUSTOMREQUEST, 'POST' );

		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );

		curl_setopt( $ch, CURLOPT_POSTFIELDS, $post_fields );

		curl_setopt( $ch, CURLOPT_HTTPHEADER, [
			'Authorization:Bearer ' . get_option( 'vimeo-llms-access-token' ),
			'Content-Type:application/json',
		] );

		$response = curl_exec( $ch );

		if ( $response ) {
			$response = json_decode( $response, 'array' );
		}

		return $response;
	}

	/**
	 * Constructor function.
	 * @param string $file __FILE__ of the main plugin
	 * @access  private
	 * @since   1.0.0
	 */
	private function __construct( $file ) {

		self::$token   = 'vimeo-llms';
		self::$file    = $file;
		self::$url     = plugin_dir_url( $file );
		self::$path    = plugin_dir_path( $file );
		self::$version = '1.0.0';

		add_action( 'lifterlms_integrations',	array( $this, 'lifterlms_integrations' ) );
	}

	/**
	 * Initiates public class and adds public hooks
	 */
	public function lifterlms_integrations( $integrations ) {
		$integrations[] = 'Vimeo_LLMS_Integration';
		return $integrations;
	}

}

/** Intantiating main plugin class */
Vimeo_LLMS::instance( __FILE__ );
