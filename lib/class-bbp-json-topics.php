<?php
/**
 * Topic post type handlers
 *
 * @package bbPress
 * @subpackage JSON API
 */

/**
 * Topic post type handlers
 *
 * This class serves as a small addition on top of the basic post handlers to
 * add small functionality on top of the existing API.
 *
 * In addition, this class serves as a sample implementation of building on top
 * of the existing APIs for custom post types.
 *
 * @package bbPress
 * @subpackage bbPress JSON API
 */
class BBP_JSON_Topics extends WP_JSON_CustomPostType {

	/**
	 * Base route
	 *
	 * @var string
	 */
	protected $base = '/topics';

	/**
	 * Post type
	 *
	 * @var string
	 */
	protected $type = 'topic'; // bbp_get_topic_post_type()

	/**
	 * Register the page-related routes
	 *
	 * @param array $routes Existing routes
	 * @return array Modified routes
	 */
	public function registerRoutes( $routes ) {

		$routes = parent::registerRoutes( $routes );

		// Add topic's replies route
		$routes[ $this->base . '/(?P<id>\d+)/replies'] = array(
			array( array( $this, 'getPostsByParent' ), WP_JSON_Server::READABLE ),
		);

		// Add user's topics route
		$routes['/users/(?P<user>\d+)' . $this->base] = array(
			array( array( $this, 'getUsersPosts' ), WP_JSON_Server::READABLE ),
		);

		return $routes;
	}

	/**
	 * Prepare post data
	 *
	 * @param array $post The unprepared post data
	 * @param array $fields The subset of post type fields to return
	 * @return array The prepared post data
	 */
	protected function prepare_post( $post, $context = 'view' ) {
		$_post = parent::prepare_post( $post, $context );

		// Override entity meta keys with the correct links
		$_post['meta']['links']['self'] = json_url( $this->base . '/' . get_page_uri( $post['ID'] ) );

		if ( ! empty( $post['post_parent'] ) )
			$_post['meta']['links']['up'] = json_url( $this->base . '/' . get_page_uri( (int) $post['post_parent'] ) );

		return apply_filters( 'json_prepare_page', $_post, $post, $context );
	}

	/**
	 * Get the topics that a user created
	 *
	 * Kind of like bbp_get_user_topics_started()
	 *
	 * @see WP_JSON_Posts::getPosts()
	 */
	public function getUsersPosts( $user, $context = 'view' ) {

		return parent::getPosts( array( 'author' => $user ), $context );
	}

	/**
	 * Get the topics that a user created
	 *
	 * Kind of like bbp_get_user_topics_started()
	 *
	 * @see WP_JSON_Posts::getPosts()
	 */
	public function getPostsByParent( $id, $context = 'view' ) {
		global $bbp_json_replies;

		return $bbp_json_replies->getPosts( array( 'post_parent' => $id ), $context );
	}

}