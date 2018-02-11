<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

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

		// Admin scripts
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue' ) );

		// Admin scripts
		add_action( 'the_content', array( $this, 'add_video_to_content' ), 7 );

		// Before title stuff
		add_action( 'edit_form_after_title', array( $this, 'edit_form_after_title' ), 7 );
	}

	/**
	 * Detemine if the integration had been enabled via checkbox
	 * @return   boolean
	 * @since    3.0.0
	 * @version  3.8.0
	 */
	public function is_enabled() {
		return true;
	}

	/**
	 * Adds front end stylesheet and js
	 * @action wp_enqueue_scripts
	 */
	public function enqueue() {
		$token = Vimeo_LLMS::$token;
		$url   = Vimeo_LLMS::$url;


		wp_register_style( $token . '-css', $url . '/assets/admin.css' );
		wp_register_script( $token . '-js', $url . '/assets/admin.js', array( 'jquery-form' ) );
	}

	/**
	 * @param WP_Post $post
	 */
	public function edit_form_after_title( $post ) {
		$this->check_response();
		$method = 'upload_video_button_' . str_replace( '-', '_', $post->post_type );
		if ( method_exists( $this, $method ) ) {
			$this->$method();
		}
	}

	public function upload_video_button( $label = 'Add Video' ) {
		wp_enqueue_style( Vimeo_LLMS::$token . '-css' );
		wp_enqueue_script( Vimeo_LLMS::$token . '-js' );
		$url = get_post_meta( get_the_ID(), 'vimeo_video', 'single' );
		if ( $url ) {
			echo $this->iframe_from_url( $url );
		} else {
			add_action( 'admin_footer', [ $this, 'upload_video_form' ] );
			?>
			<a href="#vimeo-llms-upload-popup" class="button button-hero"><?php echo $label ?></a>
			<?php
		}
	}

	public function upload_video_button_lesson() {
		$this->upload_video_button();
	}

	public function upload_video_button_course() {
		$this->upload_video_button( 'Add preview video' );
	}

	public function upload_video_button_vimeo_video() {
		$this->upload_video_button( 'Attach vimeo video' );
	}

	public function upload_video_form() {
		$data   = [
			'upload'       => [
				'approach' => 'post',
			],
			'redirect_url' => admin_url( 'post.php?post=' . get_the_ID() . '&action=edit' ),
		];
		$res    = Vimeo_LLMS::vimeo_api( '/me/videos', $data );
		$action = $res['upload_link_secure'];
		?>
		<div id='vimeo-llms-upload-popup'>
			<div class='postbox'>
				<h2>Upload video to vimeo</h2>
				<a id='vimeo-llms-close-upload-form' href='#'><i class='dashicons dashicons-no'></i></a>
				<form method='POST' action='<?php echo $action ?>' enctype='multipart/form-data' id='vimeo-llms-upload-form'>
					<label>
						<a class='button button-hero button-pick-file'>Select file</a><input type='file' name='file_data'
																																								 id='vimeo-llms-file-input'>
					</label>
					<a class="button button-hero updating-message"><?php _e( 'Uploading', 'vimeo-llms' ); ?></a>
					<div class="error-message">
						<h4><?php echo get_option( 'vimeo-llms-video-file-error', 'Oops, Something went wrong!' ) ?></h4>
						<p>ARE YOU UPLOADING CORRECT <a href="https://wixbu.com/formatos">FORMAT</a>?</p>
					</div>
				</form>
				<div class="vimeo-llms-upload-status">
					<p><?php echo get_option( 'vimeo-llms-video-recommendation' ) ?></p>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Retrieve an array of integration related settings
	 * NOTE: Not using self::get_integration_settings() as we want to remove default enable field
	 * @return   array
	 * @since    3.8.0
	 * @version  3.8.0
	 */
	protected function get_settings() {

		$settings[] = array(
			'type'  => 'sectionstart',
			'id'    => 'llms_integration_' . $this->id . '_start',
			'class' => 'top',
		);
		$settings[] = array(
			'desc'  => $this->description,
			'id'    => 'llms_integration_' . $this->id . '_title',
			'title' => $this->title,
			'type'  => 'title',
		);
		$settings[] = array(
			'id'      => 'vimeo-llms-access-token',
			'type'    => 'text',
			'title'   => __( 'Access token', 'lifterlms' ),
			'default' => '',
		);
		$settings[] = array(
			'id'      => 'vimeo-llms-video-recommendation',
			'type'    => 'text',
			'title'   => __( 'Video recommendations', 'lifterlms' ),
			'default' => '',
		);
		$settings[] = array(
			'type' => 'sectionend',
			'id'   => 'llms_integration_' . $this->id . '_end',
		);

		return apply_filters( 'llms_integration_' . $this->id . '_get_settings', $settings, $this );
	}


	private function check_response() {
		if ( ! empty( $_GET['video_uri'] ) ) {
			$vid_id = str_replace( '/videos/', '', $_GET['video_uri'] );
			$url    = "https://vimeo.com/$vid_id";
			update_post_meta( get_the_ID(), 'vimeo_video', $url );

			if ( ! get_option( "vimeo_video_$vid_id" ) ) {
				$this->save_video( $vid_id, $url );
			}
		}
	}

	private function save_video( $vid_id, $url ) {
		$author_id = get_post_field( 'post_author', get_the_ID() );
		$author    = get_the_author_meta( 'login', $author_id );

		update_option( "vimeo_video_$vid_id", $author, 'no' );

		$post_id = wp_insert_post( array(
			'post_title'  => $vid_id,
			'post_type'   => 'vimeo-video',
			'meta_input'  => [
				'vimeo_video'    => $url,
				'vimeo_video_id' => $vid_id,
			],
			'post_author' => $author_id,
			'post_status' => 'publish'
		) );

		if ( ! term_exists( $author, 'video-author' ) ) {
			wp_insert_term(
				$author,
				'video-author',
				array(
					'slug' => strtolower( str_ireplace( ' ', '-', $author ) )
				)
			);
		}

		wp_set_object_terms( $post_id, array( $author ), 'video-author' );
	}

	public function add_video_to_content( $content ) {
		$url = get_post_meta( get_the_ID(), 'vimeo_video', 'single' );
		if ( $url ) {
			echo $this->iframe_from_url( $url );
		}
		return $content;
	}

	public function iframe_from_url( $url ) {
		$url = str_replace( 'https://vimeo.com/', 'https://player.vimeo.com/video/', $url );
		return "<div class='vimeo-llms-hd-video-wrap'><div class='vimeo-llms-hd-video'><iframe src='$url' width='1200' height='675'
		frameborder='0' webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe></div></div>";
	}
}
