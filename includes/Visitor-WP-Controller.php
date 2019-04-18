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
				'args'          => array( $this->get_collection_params() ),
			),
			array(
				'methods'       => WP_REST_Server::CREATABLE,
				'callback'      => array ( $this, 'create_item' ),
				//'permission_callback' => array ($this, 'create_item_permissions_check' ),
				'args'          => $this->get_endpoint_args_for_item_schema(WP_REST_Server::CREATABLE),
			),
			'schema' => array( $this, 'get_public_item_schema' ),
		));
		register_rest_route( $namespace, '/' . $base . '/(?P<id>[\d]+)' , array(
			array(
				'methods'      => WP_REST_Server::EDITABLE,
				'callback'     => array( $this, 'update_item' ),
				//'permission_callback' => array( $this, 'create_item_permissions_check' ),
				'args'         => $this->get_endpoint_args_for_item_schema(WP_REST_Server::EDITABLE),
			),
			array(
				'methods'       => WP_REST_Server::DELETABLE,
				'callback'      => array ( $this, 'delete_item' ),
				//'permission_callback' => array( $this, 'create_item_permission_check' ),
				'args'          => array(
					'force' => array(
						'default' => false,
					),
				),
			),
			'schema' => array( $this, 'get_public_item_schema' ),
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
				return new WP_Error( 'cant-create', __( 'something went wrong, try again', 'text-domain' ), array( 'status' => 500 ) );
			}
		/*} else {
			return new WP_Error( 'cant-create', __( 'something went wrong, try again', 'text-domain' ), array( 'status' => 500 ) );
		}*/
	}

	public function update_item( $request ) {
		$item = $this->prepare_item_for_database($request);

		$data = $this->integration_update_visitor( $item );
		if ( is_array( $data ) ) {
			return new WP_REST_Response( $data, 201 );
		} else {
			return new WP_Error( 'not-updated', __('Something went wrong, check your data and try again', 'text-domain' ), array( 'status' => 500, 'item' => $item ,'data' => $data ) );
		}
	}

	public function delete_item( $request ) {
		$item = $this->prepare_item_for_database( $request );

		$deleted= $this->integration_delete_visitor( $item );
		if ( $deleted == true ) {
			return new WP_REST_Response( true, 200);
		} else {
			return new WP_Error( 'cant-delete', __( 'Something went wrong, check your data and try again', 'text-domain' ), array( 'status' => 500 ) );
		}
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

		if (isset($request[ 'id'] ) ) {
			$id = sanitize_text_field( $request->get_param('id' ) );
		} else {
			$id = '';
		}
		if ( isset($request[ 'uuid' ] ) ) {
			$visitor_uuid = sanitize_text_field( $request->get_param('uuid' ) );
		} else {
			$visitor_uuid = null;
		}
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
		if ( isset( $request[ 'banned' ] ) ) {
			$banned = sanitize_text_field( $request->get_param('banned' ) );
		} else {
			$banned = 0;
		}
		if ( isset( $request[ 'birthdate' ] ) ) {
			$birthdate = sanitize_text_field( $request->get_param( 'birthdate' ) );
		} else {
			$birthdate = '1990-01-01';
		}
		if ( isset( $request[ 'btw_nummer' ] ) ) {
			$btw = sanitize_text_field( $request->get_param('btw_nummer' ) );
		} else {
			$btw = "none";
		}
		if ( isset( $request[ 'gsm_nummer' ] ) ) {
			$gsm = sanitize_text_field( $request->get_param('gsm_nummer' ) );
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
		if ( isset( $request[ 'extra' ] ) ) {
			$extra = sanitize_text_field( $request->get_param('extra' ) );
		} else {
			$extra = null;
		}

		$item = array(
			'id' => $id,
			'uuid' => $visitor_uuid,
			'firstname' => $visitor_fname,
			'lastname' => $visitor_lname,
			'email' => $email,
			'timestamp' => $time,
			'version' => $version,
			'isActive' => $is_active,
			'banned' => $banned,
			'birthdate' => $birthdate,
			'btw_nummer' => $btw,
			'gsm_nummer' => $gsm,
			'sender' => $sender,
			'gdpr' => $gdpr,
			'extra' => $extra,

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
			'banned' => array(
				'description'       => 'flag to detect if visitor is banned',
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			),
			'isActive' => array(
				'description'       => 'Flag to see if visitor is active or not',
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			),
			'birthdate' => array(
				'description'       => 'Birthday of visitor',
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			),
			'btw_nummer' => array(
				'description'       => 'Optional tva number of visitor',
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			),
			'gsm_nummer' => array(
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
			'extra' => array(
				'description'       => 'Extra field',
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			),
		);
	}

	public function get_item_schema() {
		$schema = array(
			'$schema'       => 'http://json-schema.org/draft-04/schema#',
			'title'         => 'visitor',
			'type'          => 'object',
			'properties'    => array(
				'id' => array(
					'description'   => 'The id for the visitor',
					'type'          => 'integer',
					'context'       => array( 'view', 'edit', 'embed' ),
					'readonly'      => 'true',
				),
				'uuid' => array(
					'description'   => 'The universal id for the visitor',
					'type'          => 'string',
					'context'       => array( 'view', 'edit', 'embed' ),
					'arg_options'   => array (
						'sanitize_callback' => 'sanitize_text_field',
					),
				),
				'firstname' => array(
					'description'   => 'Visitor first name',
					'type'          => 'string',
					'context'       => array( 'view', 'edit', 'embed' ),
					'arg_options'   => array (
						'required'          => true,
						'sanitize_callback' => 'sanitize_text_field',
					),
				),
				'lastname' => array(
					'description'   => 'Visitor last name',
					'type'          => 'string',
					'context'       => array( 'view', 'edit', 'embed' ),
					'arg_options'   => array (
						'required'          => true,
						'sanitize_callback' => 'sanitize_text_field',
					),
				),
				'email' => array(
					'description'   => 'Visitor email',
					'type'          => 'string',
					'context'       => array( 'view', 'edit', 'embed' ),
					'arg_options'   => array (
						'required'          => true,
						'sanitize_callback' => 'sanitize_text_field',
					),
				),
				'timestamp' => array(
					'description'   => 'Date and time when visitor was created',
					'type'          => 'string',
					'context'       => array( 'view', 'edit', 'embed' ),
					'arg_options'   => array (
						'required'          => true,
						'sanitize_callback' => 'sanitize_text_field',
						'default'           => current_time( 'mysql' ),
					),
				),
				'version' => array(
					'description'   => 'Version of visitor. When updated, the version has to increment by 1',
					'type'          => 'integer',
					'context'       => array( 'view', 'edit', 'embed' ),
					'arg_options'   => array (
						'required'          => true,
						'sanitize_callback' => 'absint',
					),
				),
				'isActive' => array(
					'description'   => 'Flag that shows if a visitor is active or not',
					'type'          => 'integer',
					'context'       => array( 'view', 'edit', 'embed' ),
					'arg_options'   => array (
						'required'          => true,
						'sanitize_callback' => 'absint',
						'default'           => 0,
					),
				),
				'banned' => array(
					'description'   => 'Flag that shows if a visitor is banned or not',
					'type'          => 'integer',
					'context'       => array( 'view', 'edit', 'embed' ),
					'arg_options'   => array (
						'required'          => true,
						'sanitize_callback' => 'absint',
						'default'           => 0,
					),
				),
				'birthdate' => array(
					'description'   => 'Visitor birthdate',
					'type'          => 'string',
					'context'       => array( 'view', 'edit', 'embed' ),
					'arg_options'   => array (
						'sanitize_callback' => 'sanitize_text_field',
						'default'           => '1990-01-01',
					),
				),
				'btw_nummer' => array(
					'description'   => 'Visitor vta number',
					'type'          => 'string',
					'context'       => array( 'view', 'edit', 'embed' ),
					'arg_options'   => array (
						'sanitize_callback' => 'sanitize_text_field',
						'default'           => 'none',
					),
				),
				'gsm_nummer' => array(
					'description'   => 'Visitor telephone number',
					'type'          => 'string',
					'context'       => array( 'view', 'edit', 'embed' ),
					'arg_options'   => array (
						'sanitize_callback' => 'sanitize_text_field',
						'default'           => 'none',
					),
				),
				'sender' => array(
					'description'   => 'Which system sent the message',
					'type'          => 'string',
					'context'       => array( 'view', 'edit', 'embed' ),
					'arg_options'   => array (
						'sanitize_callback' => 'sanitize_text_field',
						'default'           => 'Front-end',
					),
				),
				'gdpr' => array(
					'description'   => 'Flag that shows if the visitor wants his data to be deleted from our database',
					'type'          => 'integer',
					'context'       => array( 'view', 'edit', 'embed' ),
					'arg_options'   => array (
						'sanitize_callback' => 'sanitize_text_field',
						'default'           => 0,
					),
				),
				'extra' => array(
					'description'   => 'Extra field',
					'type'          => 'string',
					'context'       => array( 'view', 'edit', 'embed' ),
					'arg_options'   => array (
						'sanitize_callback' => 'sanitize_text_field',
						'default'           => null,
					),
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
				'banned'    => $visitor->banned,
				'birthdate' => $visitor->birthdate,
				'btw_nummer'=> $visitor->btw_nummer,
				'gsm_nummer'=> $visitor->gsm_nummer,
				'sender'    => $visitor->sender,
				'gdpr'      => $visitor->gdpr,
				'extra'     => $visitor->extra,

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
		$test = $wpdb->get_results( "SELECT * FROM $table_name WHERE email = $visitor_email");

		if( is_array( $test ) ) {
			$result = $wpdb->insert( $table_name, $item );
			if ( $result != null ) {
				return $item;
			} else {
				return new WP_Error( 'error_visitor_create1', __( 'An error occured during creation of visitor, check code, please', 'visitordb'), array( 'status' => 500, 'result' => $result, 'test' => $test, 'item' => $item ) );
			}
		} else {
			return new WP_Error( 'error_visitor_create2', __( 'This visitor already exists in the database.', 'visitordb' ), array( 'status' => 500 ) );
		}
	}

	function integration_update_visitor( $item ) {
		global $wpdb;
		$table_name = $wpdb->prefix . 'visitordb';

		$visitor_id = $item[ 'id' ];
		$test = $wpdb->get_results( "SELECT * FROM $table_name WHERE id = $visitor_id" );

		if ( is_array( $test ) ) {
			$result = $wpdb->update( $table_name, $item, array( 'id' => $item[ 'id' ] ) );
			if ( $result ) {
				return $item;
			} else {
				return new WP_Error( 'update-error', __( 'Visitor could not be updated. Check your data and try again.', 'visitordb' ), array( 'status' => 500, 'result' => $result, 'id' => $item['id'] ) );
			}
		} else {
			return new WP_Error( 'not-exists', __( 'This visitor does not exists in the database.', 'visitordb' ), array( 'stauts' => 500 ) );
		}
	}

	function integration_delete_visitor( $item ) {
		global $wpdb;
		$table_name = $wpdb->prefix . 'visitordb';

		$visitor_id = $item[ 'id' ];
		$test = $wpdb->get_results( "SELECT * FROM $table_name WHERE id = $visitor_id" );

		if ( is_array( $test ) ) {
			$result = $wpdb->delete( $table_name, array( 'id' => $visitor_id ), array( '%d' ));
			if ( $result == false ) {
				return new WP_Error( 'error-deletion', __( 'An error occured when deleting visitor, please check your data.', 'visitordb' ), array( 'status' => 500 ) );
			} else {
				return true;
			}
		} else {
			return new WP_Error( 'not-exists', __( 'This visitor does not exists in the database.', 'visitordb' ), array( 'stauts' => 500 ) );
		}
}
}
