<?php
/**
 * Plugin Name: WP Dev Remote Request
 * Description: Cache the HTTP reqeust and store into the transient for given expiration time to avoid the remote requests.
 * Plugin URI: https://profiles.wordpress.org/mahesh901122/
 * Author: Mahesh M. Waghmare
 * Author URI: https://maheshwaghmare.com/
 * Version: 1.0.0
 * License: GNU General Public License v2.0
 * Text Domain: wp-dev-remote-request
 *
 * # How to use?
 *
 * ## Example 1)
 *
 * $response = wp_dev_remote_request_get( 'https://maheshwaghmare.com/wp-json/wp/v2/posts/' );
 *
 * ## Example 2: Advanced.
 *
 * $response = wp_dev_remote_request_get( array(
 *                  'url' => 'https://maheshwaghmare.com/wp-json/wp/v2/posts/',
 *                  'query_args' => array(
 *                      'per_page' => 5,
 *                  )
 *              );
 *
 * @package WP Dev Remote Request
 * @since 1.0.0
 */

if ( ! class_exists( 'WP_Dev_Remote_Request' ) ) :

	/**
	 * WP Dev Remote Request API
	 *
	 * @since 1.0.0
	 */
	class WP_Dev_Remote_Request {

		/**
		 * Instance
		 *
		 * @access private
		 * @var object Class object.
		 * @since 1.0.0
		 */
		private static $instance;

		/**
		 * Arguments
		 *
		 * @access private
		 * @var object Class object.
		 * @since 1.0.0
		 */
		private $args = array(
			'url'         => '',
			'query_args'  => array(),
			'remote_args' => array(
				'timeout' => 60,
			),
			'expiration'  => MONTH_IN_SECONDS,
			'force'       => false,

			// Debugging.
			'start_time'  => '',
			'end_time'    => '',
			'duration'    => '',
		);

		/**
		 * Initiator
		 *
		 * @since 1.0.0
		 * @return object initialized object of class.
		 */
		public static function get_instance() {
			if ( ! isset( self::$instance ) ) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		/**
		 * Constructor
		 *
		 * @since 1.0.0
		 */
		public function __construct() {}

		/**
		 * Remote GET API Request
		 *
		 * @since 1.0.0
		 *
		 * @param  mixed $args    Request URL/Array of arguments for the API request.
		 * @return mixed            Return the API request result.
		 */
		public function remote_get( $args = array() ) {

			$this->args['start_time'] = time();

			// Validate argumenents.
			if ( empty( $args ) ) {
				return array(
					'success' => false,
					'message' => __( 'Invalid Paraneters.', 'wp-dev-remote-request' ),
				);
			}

			// Get request rguments.
			$this->set_args( $args );

			// If empty then return.
			if ( empty( $this->args['url'] ) ) {
				return array(
					'success' => false,
					'message' => __( 'Invalid Request Endpoint.', 'wp-dev-remote-request' ),
				);
			}

			$this->log( 'REQUEST URL: ' . $this->args['url'] );
			$this->log( 'ARGS: ' . wp_json_encode( $this->args ) );
			$this->log( 'TRANSIENT_KEY: ' . $this->get_transient_key() );

			/**
			 * If `force` is not set then check request maximum requests count.
			 * If it reach the maximum requests the return transient output.
			 */
			$cached_data = get_transient( $this->get_transient_key() );
			if ( false === $this->args['force'] && false !== $cached_data ) {

				$this->log( 'RESULT: (Cached) ' . wp_json_encode( $cached_data ) );
				$this->log( 'MESSAGE: ' . __( 'Response from transient.', 'wp-dev-remote-request' ) );

				// Duration.
				$this->args['end_time'] = time();
				$this->args['duration'] = $this->args['end_time'] - $this->args['start_time'];
				$this->log( 'DURATION: ' . human_time_diff( $this->args['end_time'], $this->args['start_time'] ) );
				// Duration.

				return array(
					'success'    => true,
					'message'    => __( 'Response from transient.', 'wp-dev-remote-request' ),
					'data'       => $cached_data,
					'expiration' => $this->args['expiration'],
				);

			} else {

				$request = wp_remote_get( $this->args['url'], $this->args['remote_args'] );
				$result  = $this->request( $request );

				$this->log( 'RESULT: (Live) ' . wp_json_encode( $result['data'] ) );

				if ( $result['success'] ) {
					set_transient( $this->get_transient_key(), $result['data'], $this->args['expiration'] );
				}

				$this->log( 'MESSAGE: ' . $result['message'] );

				// Duration.
				$this->args['end_time'] = time();
				$this->args['duration'] = $this->args['end_time'] - $this->args['start_time'];
				$this->log( 'DURATION: ' . human_time_diff( $this->args['end_time'], $this->args['start_time'] ) );
				// Duration.

				return $result;
			}
		}

		/**
		 * Get uniqueue request key
		 */
		public function get_unique_request_key() {
			return md5( $this->args['url'] . wp_json_encode( $this->args['query_args'] ) );
		}

		/**
		 * Get transient key
		 */
		public function get_transient_key() {
			return 'wp-dev-remote-request-' . $this->get_unique_request_key();
		}

		/**
		 * Set Arguments
		 *
		 * @param array $args   Request arguments.
		 */
		public function set_args( $args = array() ) {

			if ( is_string( $args ) && ! is_array( $args ) ) {
				$args = array(
					'url' => $args,
				);
			} else {

				// Merge remote args with default remote args.
				if ( isset( $args['remote_args'] ) ) {
					$args['remote_args'] = wp_parse_args( $args['remote_args'], $this->args['remote_args'] );
				}

				// Merge query args with default query args.
				if ( isset( $args['query_args'] ) ) {
					$query_args         = wp_parse_args( $args['query_args'], $this->args['query_args'] );
					$args['query_args'] = $query_args;
					$args['url']        = add_query_arg( $query_args, trailingslashit( $args['url'] ) );
				}
			}

			$this->args = wp_parse_args( $args, $this->args );
		}

		/**
		 * API Request
		 *
		 * Handle the API request and return the result.
		 *
		 * @since 1.0.0
		 *
		 * @param  array $request    Array of arguments for the API request.
		 * @return mixed           Return the API request result.
		 */
		public function request( $request ) {

			// Is WP Error?
			if ( is_wp_error( $request ) ) {
				return array(
					'success' => false,
					'message' => $request->get_error_message(),
					'data'    => wp_remote_retrieve_body( $request ),
				);
			}

			// Invalid response code.
			if ( wp_remote_retrieve_response_code( $request ) !== 200 ) {
				return array(
					'success' => false,
					'message' => $request['response'],
					'data'    => wp_remote_retrieve_body( $request ),
				);
			}

			// Get body data.
			$body = wp_remote_retrieve_body( $request );

			// Is WP Error?
			if ( is_wp_error( $body ) ) {
				return array(
					'success' => false,
					'message' => $body->get_error_message(),
					'data'    => $request,
				);
			}

			// Decode body content.
			$body_decoded = json_decode( $body, true );

			return array(
				'success' => true,
				'message' => __( 'Response from live site.', 'wp-dev-remote-request' ),
				'data'    => (array) $body_decoded,
			);
		}

		/**
		 * Log
		 *
		 * @param string $message   Log message.
		 */
		public function log( $message = '' ) {
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				error_log( $message ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			}
		}

	}

	// Initialize class object with 'get_instance()' method.
	WP_Dev_Remote_Request::get_instance();

endif;

if ( ! function_exists( 'wp_dev_remote_request_get' ) ) :

	/**
	 * Remote GET API Request
	 *
	 * @since 1.0.0
	 *
	 * @param  mixed $args    Request URL/Array of arguments for the API request.
	 * @return mixed            Return the API request result.
	 */
	function wp_dev_remote_request_get( $args = array() ) {
		return WP_Dev_Remote_Request::get_instance()->remote_get( $args );
	}
endif;
