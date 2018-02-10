<?php
/**
 * Vimeo for LifterLMS Admin class
 */
class Vimeo_LLMS_Admin {

	/** @var Vimeo_LLMS_Admin Instance */
	private static $_instance = null;

	/* @var string $token Plugin token */
	public $token;

	/* @var string $url Plugin root dir url */
	public $url;

	/* @var string $path Plugin root dir path */
	public $path;

	/* @var string $version Plugin version */
	public $version;

	/**
	 * Main Vimeo for LifterLMS Instance
	 * Ensures only one instance of Storefront_Extension_Boilerplate is loaded or can be loaded.
	 * @return Vimeo_LLMS_Admin instance
	 * @since 	1.0.0
	 */
	public static function instance() {
		if ( null == self::$_instance ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	} // End instance()

	/**
	 * Constructor function.
	 * @access  private
	 * @since 	1.0.0
	 */
	private function __construct() {
		$this->token   =   Vimeo_LLMS::$token;
		$this->url     =   Vimeo_LLMS::$url;
		$this->path    =   Vimeo_LLMS::$path;
		$this->version =   Vimeo_LLMS::$version;
	} // End __construct()

	/**
	 * Adds front end stylesheet and js
	 * @action wp_enqueue_scripts
	 */
	public function enqueue() {
		$token = $this->token;
		$url = $this->url;

		wp_enqueue_style( $token . '-css', $url . '/assets/admin.css' );
		wp_enqueue_script( $token . '-js', $url . '/assets/admin.js', array( 'jquery' ) );
	}
}