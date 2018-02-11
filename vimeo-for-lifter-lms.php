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

	public static function vimeo_api( $endpoint = '/me/videos', $post_fields = array() ) {

		$ch = curl_init( "https://api.vimeo.com$endpoint" );

		curl_setopt( $ch, CURLOPT_CUSTOMREQUEST, 'POST' );

		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );

		if ( $post_fields ) {
			curl_setopt( $ch, CURLOPT_POSTFIELDS, json_encode( $post_fields ) );
		}

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
		add_action( 'init',	array( $this, 'init' ) );
		register_activation_hook( __FILE__, array( $this, 'rewrite_flush' ) );
	}

	/**
	 * Initiates public class and adds public hooks
	 */
	public function lifterlms_integrations( $integrations ) {
		$integrations[] = 'Vimeo_LLMS_Integration';
		return $integrations;
	}

	public function init() {
		$labels = array(
			'name'               => _x( 'Videos', 'post type general name', 'vimeo-llms' ),
			'singular_name'      => _x( 'Video', 'post type singular name', 'vimeo-llms' ),
			'menu_name'          => _x( 'Videos', 'admin menu', 'vimeo-llms' ),
			'name_admin_bar'     => _x( 'Video', 'add new on admin bar', 'vimeo-llms' ),
			'add_new'            => _x( 'Add New', 'video', 'vimeo-llms' ),
			'add_new_item'       => __( 'Add New Video', 'vimeo-llms' ),
			'new_item'           => __( 'New Video', 'vimeo-llms' ),
			'edit_item'          => __( 'Edit Video', 'vimeo-llms' ),
			'view_item'          => __( 'View Video', 'vimeo-llms' ),
			'all_items'          => __( 'All Videos', 'vimeo-llms' ),
			'search_items'       => __( 'Search Videos', 'vimeo-llms' ),
			'parent_item_colon'  => __( 'Parent Videos:', 'vimeo-llms' ),
			'not_found'          => __( 'No videos found.', 'vimeo-llms' ),
			'not_found_in_trash' => __( 'No videos found in Trash.', 'vimeo-llms' )
		);

		$args = array(
			'labels'             => $labels,
			'description'        => __( 'All your vimeo videos here.', 'vimeo-llms' ),
			'menu_icon'          => 'dashicons-video-alt2',
			'public'             => true,
			'query_var'          => true,
			'rewrite'            => array( 'slug' => 'vimeo-video' ),
			'capability_type'    => 'page',
			'hierarchical'       => false,
			'menu_position'      => null,
			'supports'           => array( 'title', 'author' )
		);

		register_post_type( 'vimeo-video', $args );

		register_taxonomy(
			'video-tags',
			'vimeo-video',
			array(
				'label' => __( 'Video tags', 'vimeo-llms' ),
				'rewrite' => array( 'slug' => 'video-tags' ),
				'hierarchical' => false,
			)
		);

		register_taxonomy(
			'video-genre',
			'vimeo-video',
			array(
				'label' => __( 'Video genre', 'vimeo-llms' ),
				'rewrite' => array( 'slug' => 'video-genre' ),
				'hierarchical' => false,
			)
		);

		register_taxonomy(
			'video-author',
			'vimeo-video',
			array(
				'label' => __( 'Video author', 'vimeo-llms' ),
				'rewrite' => array( 'slug' => 'video-author' ),
				'hierarchical' => false,
			)
		);

	}

	public function rewrite_flush() {
		$this->init();
		flush_rewrite_rules();
	}
}

/** Intantiating main plugin class */
Vimeo_LLMS::instance( __FILE__ );
