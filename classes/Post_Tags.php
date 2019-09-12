<?php

namespace Spaces_Global_Tags;

/**
 * Class Post_Tags
 * @package Spaces_Global_Tags
 * @since 0.7.0
 */
class Post_Tags extends Hashtag_Parser {

	/**
	 * @var string $taxonomy the multisite taxonomy used.
	 *
	 * @since 0.8.0
	 */
	static $taxonomy;

	/**
	 * Comment_Tags constructor.
	 *
	 * @param string $taxonomy the multisite taxonomy used.
	 *
	 * @since 0.8.0
	 */
	public function __construct( $taxonomy = 'global_post_tag' ) {

		self::$taxonomy = $taxonomy;

		self::register();
	}

	/**
	 * Register with WordPress hooks.
	 *
	 * @since 0.8.0
	 */
	public function register() {

		//add_action( 'transition_post_status', [ $this, 'process_tags' ], 620, 3 );
		add_action( 'wp_insert_post', [ $this, 'process_tags' ], 15, 3 );

		/**
		 * When displaying a tag, update the markup with a link to the tag.
		 */
		add_filter( 'the_content',         [ '\Spaces_Global_Tags\Post_Tags', 'tag_post_links'], 15 );

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
	static function tag_post_links( $content ) {

		if ( ! is_single() || ! in_the_loop() || ! is_main_query() ) return $content;

		$taxonomy = self::$taxonomy;
		return parent::tag_links( $content, $taxonomy );
	}

	/**
	 * Fires when the post is published or edited and
	 * sets the tags accordingly.
	 *
	 * @param int $post_id current post being edited.
	 * @param object $post a WP Post object.
	 * @param bool $updated Whether this is an existing post being updated or not.
	 *
	 * @return void
	 * @since 0.8.0
	 *
	 */
	public function process_tags( $post_id, $post, $updated ) {

		if ( wp_is_post_revision( $post_id ) ) {
			return;
		}
		$tags = self::find_tags( $post->post_content );

		set_post_multisite_terms( $post_id, $tags, self::$taxonomy, get_current_blog_id(), false );
	}

}
