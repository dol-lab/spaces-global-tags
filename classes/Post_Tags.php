<?php

namespace Spaces_Global_Tags;

/**
 * Class Post_Tags
 *
 * @package Spaces_Global_Tags
 * @since 0.7.0
 */
class Post_Tags extends Hashtag_Parser {

	/**
	 * The Post_Tags taxonomy string.
	 *
	 * @var string $taxonomy the multisite taxonomy used.
	 *
	 * @since 0.8.0
	 */
	public static $taxonomy;

	/**
	 * Comment_Tags constructor.
	 *
	 * @since 0.8.0
	 */
	public function __construct() {

		self::$taxonomy = GLOBAL_POST_TAG_TAX;

		self::register();
	}

	/**
	 * Register with WordPress hooks.
	 *
	 * @since 0.8.0
	 */
	public function register() {

		add_action( 'wp_insert_post', array( $this, 'process_tags' ), 15, 3 );

		/**
		 * When displaying a tag, update the markup with a link to the tag.
		 */
		add_filter( 'the_content', array( '\Spaces_Global_Tags\Post_Tags', 'tag_post_links' ), 15 );

	}

	/**
	 * Markup tags in posts with links to the archive page.
	 *
	 * @param string $content The Content of the post.
	 *
	 * @since 0.8.0
	 *
	 * @return string|void
	 */
	public static function tag_post_links( $content ) {

		if ( ! is_main_query() || is_admin() ) {
			return $content;
		}

		$taxonomy = self::$taxonomy;

		return parent::tag_links( $content, $taxonomy );
	}

	/**
	 * Fires when the post is published or edited and
	 * sets the tags accordingly.
	 *
	 * @param int    $post_id current post being edited.
	 * @param object $post a WP Post object.
	 * @return void
	 * @since 0.8.0
	 */
	public function process_tags( $post_id, $post ) {

		if ( wp_is_post_revision( $post_id ) ) {
			return;
		}

		/**
		 * Only ever create tags when we are publishing.
		 */
		if ( 'publish' !== get_post_status( $post_id ) ) {
			return;
		}

		/**
		 * Check that we are working on an allowed post type.
		 *
		 * @since 0.15.0
		 */
		if ( ! in_array( get_post_type( $post_id ), apply_filters( 'spaces_global_tags_post_types', array( 'post' ) ) ) ) {
			return;
		}

		$tags = self::find_tags( $post->post_content );

		set_post_multisite_terms( $post_id, $tags, self::$taxonomy, get_current_blog_id(), false );
	}

}
