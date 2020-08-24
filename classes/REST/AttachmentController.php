<?php

namespace StackonetSupportTicket\REST;

use Exception;
use Stackonet\WP\Framework\Media\UploadedFile;
use Stackonet\WP\Framework\Media\Uploader;
use WP_Post;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

defined( 'ABSPATH' ) || exit;

class AttachmentController extends ApiController {

	/**
	 * The instance of the class
	 *
	 * @var self
	 */
	private static $instance;

	/**
	 * Only one instance of the class can be loaded
	 *
	 * @return self
	 */
	public static function init() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();

			add_action( 'rest_api_init', array( self::$instance, 'register_routes' ) );
		}

		return self::$instance;
	}

	/**
	 * Registers the routes for the objects of the controller.
	 */
	public function register_routes() {
		register_rest_route( $this->namespace, '/attachments', [
			[ 'methods' => WP_REST_Server::READABLE, 'callback' => [ $this, 'get_items' ], ],
			[ 'methods' => WP_REST_Server::CREATABLE, 'callback' => [ $this, 'create_item' ], ],
		] );
		register_rest_route( $this->namespace, '/attachments/(?P<id>\d+)', [
			[ 'methods' => WP_REST_Server::DELETABLE, 'callback' => [ $this, 'delete_logo' ], ],
		] );
	}

	/**
	 * Retrieves a collection of items.
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_REST_Response Response object on success, or WP_Error object on failure.
	 */
	public function get_items( $request ) {
		$author = get_current_user_id();

		$args        = array(
			'posts_per_page' => 25,
			'orderby'        => 'date',
			'order'          => 'DESC',
			'post_type'      => 'attachment',
			'post_status'    => 'inherit',
			'author'         => $author,
		);
		$posts_array = get_posts( $args );

		$response = [];

		if ( ! count( $posts_array ) ) {
			return $this->respondOK( $response );
		}

		foreach ( $posts_array as $item ) {
			$response[] = $this->prepare_item_for_response( $item->ID, $request );
		}

		return $this->respondOK( $response );
	}

	/**
	 * Upload a file to a collection of items.
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_REST_Response Response object.
	 * @throws Exception
	 */
	public function create_item( $request ) {
		if ( ! current_user_can( 'read' ) ) {
			return $this->respondUnauthorized();
		}

		$files = UploadedFile::getUploadedFiles();

		if ( ! isset( $files['file'] ) ) {
			return $this->respondForbidden();
		}

		if ( ! $files['file'] instanceof UploadedFile ) {
			return $this->respondForbidden();
		}

		$attachments = Uploader::upload( $files['file'] );
		$ids         = wp_list_pluck( $attachments, 'attachment_id' );
		$files_paths = wp_list_pluck( $attachments, 'attachment_path' );

		$image_id = $ids[0];

		$token = wp_generate_password( 20, false, false );
		update_post_meta( $image_id, '_delete_token', $token );

		$response = $this->prepare_item_for_response( $image_id, $request );

		return $this->respondOK( $response );
	}

	/**
	 * Prepares the item for the REST response.
	 *
	 * @param mixed $item WordPress representation of the item.
	 * @param WP_REST_Request $request Request object.
	 *
	 * @return array
	 */
	public function prepare_item_for_response( $item, $request ) {
		$image_id       = $item;
		$title          = get_the_title( $image_id );
		$token          = get_post_meta( $image_id, '_delete_token', true );
		$attachment_url = wp_get_attachment_url( $image_id );
		$image          = wp_get_attachment_image_src( $image_id, 'thumbnail' );
		$full_image     = wp_get_attachment_image_src( $image_id, 'full' );

		$response = [
			'image_id'       => $image_id,
			'title'          => $title,
			'token'          => $token,
			'attachment_url' => $attachment_url,
			'thumbnail'      => [ 'src' => $image[0], 'width' => $image[1], 'height' => $image[2], ],
			'full'           => [ 'src' => $full_image[0], 'width' => $full_image[1], 'height' => $full_image[2], ],
		];

		return $response;
	}

	/**
	 * @param WP_REST_Request $request
	 *
	 * @return WP_REST_Response
	 */
	public function delete_logo( WP_REST_Request $request ) {
		$id    = $request->get_param( 'id' );
		$token = $request->get_param( 'token' );

		$_post = get_post( $id );

		if ( ! $_post instanceof WP_Post ) {
			return $this->respondNotFound( null, 'Image not found!' );
		}

		if ( self::can_delete_logo( $_post, $token ) ) {
			return $this->respondUnauthorized();
		}

		wp_delete_post( $id, true );

		return $this->respondOK( null, [ 'deleted' => true ] );
	}

	/**
	 * If current user can delete media
	 *
	 * @param WP_Post $post
	 * @param string $token
	 *
	 * @return bool
	 */
	private static function can_delete_logo( $post, $token = '' ) {
		$delete_token = get_post_meta( $post->ID, '_delete_token', true );

		if ( current_user_can( 'manage_options' ) ) {
			return true;
		}

		if ( $token == $delete_token ) {
			return true;
		}

		if ( get_current_user_id() == $post->post_author ) {
			return true;
		}

		return false;
	}
}
