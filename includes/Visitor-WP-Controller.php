<?php

/**
 *
 * Visitor-WP-Controller.php
 *
 * REST API Controller for Visitors
 *
 * @Author Fernando Andrade
 *
 * @Version 1.0
 *
 */

class Visitor_REST_Controller extends WP_REST_Controller {

	public function register_routes() {

		$namespace = 'integration';
		$base = 'visitordb';
		register_rest_route( $namespace, '/' . $base, array (
			array(
				'methods'       => WP_REST_Server::READABLE,
				'callback'      => array ( $this, 'get_items' ),
				'permission_callback' => array( $this, 'get_items_permissions_check' ),
				'args'          => array ( $this->get_collection_params() ),
			),
			array(
				'methods'       => WP_REST_Server::CREATABLE,
				'callback'      => array ( $this, 'create_item' ),
				//'permission_callback' => array ($this, 'create_item_permissions_check' ),
				'args'          => array( $this->get_collection_params() ),
			),
		));
	}

	public function get_items( $request ) {

		$items = $this->integration_get_visitors();
		$data = array();
		foreach( $items as $item ) {
			$itemdata = $this->prepare_item_for_response( $item, $request );
			$data[] = $this->prepare_response_for_collection( $itemdata );
		}

		return new WP_REST_Response( $data, 200 );

	}

	public function create_item( $request ) {

		$item = $this->prepare_item_for_database( $request );

		//if ( function_exists( 'integration_add_visitor' ) ) {
			$data = $this->integration_add_visitor( $item );
			if ( is_array( $data ) ) {
				return new WP_REST_Response( $data, 201 );
			} else {
				echo 'not created';
				return $data;
			}
		/*} else {
			return new WP_Error( 'cant-create', __( 'something went wrong, try again', 'text-domain' ), array( 'status' => 500 ) );
		}*/
	}

	public function get_items_permissions_check( $request ) {
		return true;
	}

	public function create_item_permissions_check( $request ) {
		return current_user_can( 'edit_post' );
	}

	public function prepare_item_for_database( $request ) {
		//global $wpdb;
		//$table_name = $wpdb->prefix . 'visitordb';

		if ( isset( $request[ 'firstname' ] ) ) {
			$visitor_fname = sanitize_text_field( $request->get_param('firstname' ) );
		} else {
			$visitor_fname = 'none';
		}
		if ( isset( $request[ 'lastname' ] ) ) {
			$visitor_lname = sanitize_text_field( $request->get_param('lastname') );
		} else {
			$visitor_lname = 'none';
		}
		if ( isset( $request[ 'email' ] ) ) {
			$email = sanitize_text_field( $request->get_param('email' ) );
		} else {
			$email = 'none';
		}
		if ( isset( $request[ 'timestamp' ] ) ) {
			$time = sanitize_text_field( $request->get_param('timestamp' ) );
		} else {
			$time = current_time( 'mysql' );
		}
		if ( isset( $request[ 'version' ] ) ) {
			$version = sanitize_text_field( $request->get_param('version' ) );
		} else {
			$version = 1;
		}
		if ( isset( $request[ 'isActive' ] ) ) {
			$is_active = sanitize_text_field( $request->get_param('isActive' ) );
		} else {
			$is_active = 0;
		}
		if ( isset( $request[ 'gsm' ] ) ) {
			$gsm = sanitize_text_field( $request->get_param('gsm' ) );
		} else {
			$gsm = "none";
		}
		if ( isset( $request[ 'sender' ] ) ) {
			$sender = sanitize_text_field( $request->get_param('sender' ) );
		} else {
			$sender = "Front-end";
		}
		if ( isset( $request[ 'gdpr' ] ) ) {
			$gdpr = sanitize_text_field( $request->get_param('gdpr' ) );
		} else {
			$gdpr = 0;
		}
		if ( isset( $request[ 'banned' ] ) ) {
			$banned = sanitize_text_field( $request->get_param('banned' ) );
		} else {
			$banned = 0;
		}

		$item = array(
			'firstname' => $visitor_fname,
			'lastname' => $visitor_lname,
			'email' => $email,
			'timestamp' => $time,
			'version' => $version,
			'isActive' => $is_active,
			'gsm' => $gsm,
			'sender' => $sender,
			'gdpr' => $gdpr,
			'banned' => $banned,
		);

		return $item;
	}

	public function prepare_item_for_response( $item, $request ) {
		$schema = $this->get_item_schema();
		$data = array();
		$data = $item;

		return $data;
	}

	public function get_collection_params() {
		return array(
			'id' => array(
				'description'       => 'Visitor id.',
				'type'              => 'integer',
				'sanitize_callback' => 'absint',
			),
			'uuid' => array(
				'description'       => 'Universal id of the visitor',
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			),
			'firstname' => array(
				'description'       => 'First name of the visitor',
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			),
			'lastname' => array(
				'description'       => 'Last name of the visitor',
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			),
			'email' => array(
				'description'       => 'Email of the visitor',
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			),
			'timestamp' => array(
				'description'       => 'Date and time of creation of visitor',
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			),
			'version' => array(
				'description'       => 'Version of created visitor',
				'type'              => 'integer',
				'sanitize_callback' => 'absint',
			),
			'isActive' => array(
				'description'       => 'Flag to see if visitor is active or not',
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			),
			'gsm' => array(
				'description'       => 'telephone number of the visitor',
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			),
			'sender' => array(
				'description'       => 'Who sends the creation message',
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			),
			'gdpr' => array(
				'description'       => 'Flag to detect if gdpr is active or not for the visitor',
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			),
			'banned' => array(
				'description'       => 'flag to detect if visitor is banned',
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			),
		);
	}

	public function get_item_schema() {
		$schema = array(
			'$schema'       => 'http://json-schema.org/draft-04/schema#',
			'title'         => 'entry',
			'type'          => 'object',
			'properties'    => array(
				'id' => array(
					'description'   => __('The id for the visitor', 'visitordb' ),
					'type'          => 'integer',
					'readonly'      => 'true',
				),
			),
		);

		return $schema;
	}

	function integration_get_visitors () {
		global $wpdb;
		$table_name = $wpdb->prefix . 'visitordb';
		$query = "SELECT * FROM $table_name";
		$visitors = $wpdb->get_results( $query );
		$visitor_list = [];

		foreach ( $visitors as $visitor ) {

			$return_visitor = array (
				'id'        => $visitor->id,
				'uuid'      => $visitor->uuid,
				'firstname' => $visitor->firstname,
				'lastname'  => $visitor->lastname,
				'email'     => $visitor->email,
				'timestamp' => $visitor->timestamp,
				'version'   => $visitor->version,
				'isActive'  => $visitor->isActive,
				'gsm'       => $visitor->gsm,
				'sender'    => $visitor->sender,
				'gdpr'      => $visitor->gdpr,
				'banned'    => $visitor->banned,
			);
			array_push( $visitor_list, $return_visitor );
		}
		$response = $visitor_list;

		return $response;
	}

	function integration_add_visitor( $item ) {
		global $wpdb;
		$table_name = $wpdb->prefix . 'visitordb';
		$visitor_email = $item[ 'email' ];
		$test = $wpdb->get_results( "SELECT * FROM $table_name WHERE email LIKE $visitor_email");

		if( $test == [] ) {
			$result = $wpdb->insert( $table_name, $item );
			//echo $wpdb-json_last_error();
			//echo $result;
			if ( $result != null ) {
				return $item;
			} else {
				return new WP_Error( 'error_visitor_create1', __( 'An error occured during creation of visitor, check code, please', 'visitordb'), array( 'status' => 500, 'result' => $result, 'test' => $test, 'item' => $item ) );
			}
		} else {
			return new WP_Error( 'error_visitor_create2', __( 'This visitor already exists in the database.', 'visitordb' ), array( 'status' => 500 ) );
		}
	}
}
