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

		add_action( 'transition_post_status', [ $this, 'process_tags' ], 12, 3 );

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
		$taxonomy = self::$taxonomy;
		return parent::tag_links( $content, $taxonomy );
	}

	/**
	 * Fires when the post is published or edited and
	 * sets the tags accordingly.
	 *
	 * @param boolean $new Status being switched to
	 * @param boolean $old Status being switched from
	 * @param object $post The full Post object
	 *
	 * @since 0.8.0
	 *
	 * @return void
	 */
	public function process_tags( $new, $old, $post ) {

		if ( 'publish' !== $new ) {
			return;
		}
		$tags = self::find_tags( $post->post_content );
		// TODO: Needs fixing, creates the tags, but not yet adds them to the post.
		set_post_multisite_terms( $post->ID, $tags, self::$taxonomy, get_current_blog_id(), true );
	}

}
