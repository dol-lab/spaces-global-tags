<?php

namespace Spaces_Global_Tags;

/**
 * Class Comment_Tags
 *
 * @package Spaces_Global_Tags
 * @since 0.7.0
 */
class Comment_Tags extends Hashtag_Parser {
	/**
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

		self::$taxonomy = GLOBAL_COMMENT_TAG_TAX;

		self::register();
	}

	/**
	 * Register with WordPress hooks.
	 *
	 * @since 0.8.0
	 */
	public function register() {

		add_action( 'wp_insert_comment', array( $this, 'update_comment' ) );
		add_action( 'edit_comment', array( $this, 'update_comment' ) );

		/**
		 * When displaying a tag, update the markup with a link to the tag.
		 */
		add_filter( 'comment_text', array( '\Spaces_Global_Tags\Comment_Tags', 'tag_comment_links' ), 15 );
	}

	/**
	 * Markup tags in comments with links to the archive page.
	 *
	 * @param string $content Comment content.
	 *
	 * @since 0.8.0
	 *
	 * @return string|void
	 */
	public static function tag_comment_links( $content ) {

		$taxonomy = self::$taxonomy;

		return parent::tag_links( $content, $taxonomy );
	}

	/**
	 * Update new or existing comment.
	 *
	 * @param int $comment_id ID of a comment.
	 *
	 * @since 0.8.0
	 */
	public function update_comment( $comment_id ) {

		/**
		 * Get the comment object.
		 */
		$comment = get_comment( $comment_id );

		/**
		 * Find raw tags in the comment_content.
		 */
		$tags = self::find_tags( $comment->comment_content );

		$this->update_post( $comment->comment_post_ID, $tags );
	}

	/**
	 * Update post with tags from comment.
	 *
	 * @param int   $post_id ID of a post.
	 * @param array $terms Array of terms for $this->taxonomy.
	 */
	public function update_post( $post_id, $terms ) {

		/**
		 * Append the comment tags on the associated post.
		 */
		set_post_multisite_terms( $post_id, $terms, self::$taxonomy, get_current_blog_id(), true );
	}

}
