<?php

if ( ! defined( 'ABSPATH' ) ) { exit; }

class Vimeo_LLMS_Integration extends LLMS_Abstract_Integration {

	public $id = 'vimeo';

	/**
	 * Display order on Integrations tab
	 * @var  integer
	 */
	protected $priority = 5;

	/**
	 * Configure the integration
	 * Do things like configure ID and title here
	 * @return   void
	 * @since    3.12.0
	 * @version  3.12.0
	 */
	protected function configure() {

		$this->title       = __( 'Vimeo', 'lifterlms' );
		$this->description = __( 'Add you vimeo key here to get uploading videos working.', 'lifterlms' );

		if ( $this->is_available() ) {
			add_action( 'admin_enqueue_scripts',	array( $this, 'enqueue' ) );
			add_filter( 'llms_page_restricted_before_check_access', array( $this, 'restriction_checks' ), 40, 1 );
		}
	}

	/**
	 * Checks if the BuddyPress plugin is installed & activated
	 * @return boolean
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	public function is_installed() {
		return class_exists( 'Vimeo_LLMS' );
	}

	/**
	 * Adds front end stylesheet and js
	 * @action wp_enqueue_scripts
	 */
	public function enqueue() {
		$token = Vimeo_LLMS::$token;
		$url = Vimeo_LLMS::$url;


		wp_register_style( $token . '-css', $url . '/assets/admin.css' );
		wp_register_script( $token . '-js', $url . '/assets/admin.js', array( 'jquery' ) );
	}

	/**
	 * Get additional settings specific to the integration
	 * extending classes should override this with the settings
	 * specific to the integration
	 * @return   array
	 * @since    3.8.0
	 * @version  3.8.0
	 */
	protected function get_integration_settings() {
		return array(
			array(
				'id'      => 'vimeo-llms-access-token',
				'type'    => 'text',
				'title'   => __( 'Access token', 'lifterlms' ),
				'default' => '',
			),
			array(
				'id'      => 'vimeo-llms-video-recommendation',
				'type'    => 'text',
				'title'   => __( 'Video recommendations', 'lifterlms' ),
				'default' => '',
			),
		);
	}
}
