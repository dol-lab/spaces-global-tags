<?php
/**
 * Widget API: Widget_Global_Tags class
 *
 * @package Spaces_Global_Tags
 * @since 0.20.0
 */

namespace Spaces_Global_Tags;

use WP_Widget;

/**
 * Core class used to implement a Tag cloud widget.
 *
 * @since 0.20.0
 *
 * @see WP_Widget
 */
class Widget_Global_Tags extends WP_Widget {

	/**
	 * Sets up a new Global Tag Cloud widget instance.
	 *
	 * @since 0.20.0
	 */
	public function __construct() {
		$widget_ops = array(
			'description'                 => __( 'A cloud of your most used global tags.', 'spaces-global-tags' ),
			'customize_selective_refresh' => true,
		);
		parent::__construct( 'global_tag_cloud', __( 'Global Tag Cloud', 'spaces-global-tags' ), $widget_ops );
	}

	/**
	 * Outputs the content for the current Global Tag Cloud widget instance.
	 *
	 * @since 0.20.0
	 *
	 * @param array $args     Display arguments including 'before_title', 'after_title',
	 *                        'before_widget', and 'after_widget'.
	 * @param array $instance Settings for the current Tag Cloud widget instance.
	 */
	public function widget( $args, $instance ) {
		$current_taxonomy = $this->_get_current_taxonomy( $instance );

		if ( ! empty( $instance['title'] ) ) {
			$title = $instance['title'];
		} else {
			if ( 'post_tag' === $current_taxonomy ) {
				$title = __( 'Global Tags', 'spaces-global-tags' );
			} else {
				$tax   = get_multisite_taxonomy( $current_taxonomy );
				$title = $tax->labels->name;
			}
		}

		$show_count = ! empty( $instance['count'] );

		$term_args = [
			'taxonomy'   => $current_taxonomy,
			'number'     => apply_filters( 'spaces_global_tags_number_of_related_tags', 30 ),
			'orderby'    => 'count',
			'order'      => 'DESC',
			'hide_empty' => true,
			'echo'       => false,
			'show_count' => $show_count,
			'largest'    => 20,
		];

		// Make the multisite term cloud defaults editable.
		$term_args = apply_filters( 'spaces_global_tags_term_cloud_args', $term_args );

		/**
		 * Filters the taxonomy used in the Global Tag Cloud widget.
		 *
		 * @since 0.20.0
		 *
		 * @see multisite_term_cloud()
		 *
		 * @param array $args     Args used for the tag cloud widget.
		 * @param array $instance Array of settings for the current widget.
		 */
		$tag_cloud = multisite_term_cloud( $term_args );

		if ( empty( $tag_cloud ) ) {
			return;
		}

		/** This filter is documented in wp-includes/widgets/class-wp-widget-pages.php */
		$title = apply_filters( 'widget_title', $title, $instance, $this->id_base );

		echo $args['before_widget'];
		if ( $title ) {
			echo $args['before_title'] . $title . $args['after_title'];
		}

		echo '<div class="spaces-global-tags tagcloud card-section s-fg-a-c1-parent">';

		echo $tag_cloud;

		echo "</div>\n";
		echo $args['after_widget'];
	}

	/**
	 * Handles updating settings for the current Global Tag Cloud widget instance.
	 *
	 * @since 0.20.0
	 *
	 * @param array $new_instance New settings for this instance as input by the user via
	 *                            WP_Widget::form().
	 * @param array $old_instance Old settings for this instance.
	 * @return array Settings to save or bool false to cancel saving.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance             = array();
		$instance['title']    = sanitize_text_field( $new_instance['title'] );
		$instance['count']    = ! empty( $new_instance['count'] ) ? 1 : 0;
		$instance['taxonomy'] = stripslashes( $new_instance['taxonomy'] );
		return $instance;
	}

	/**
	 * Outputs the Tag Cloud widget settings form.
	 *
	 * @since 0.20.0
	 *
	 * @param array $instance Current settings.
	 */
	public function form( $instance ) {
		$current_taxonomy  = $this->_get_current_taxonomy( $instance );
		$title_id          = $this->get_field_id( 'title' );
		$count             = isset( $instance['count'] ) ? (bool) $instance['count'] : false;
		$instance['title'] = ! empty( $instance['title'] ) ? esc_attr( $instance['title'] ) : '';

		echo '<p><label for="' . $title_id . '">' . __( 'Title:', 'spaces-global-tags' ) . '</label>
			<input type="text" class="widefat" id="' . $title_id . '" name="' . $this->get_field_name( 'title' ) . '" value="' . $instance['title'] . '" />
		</p>';

		$taxonomies = get_multisite_taxonomies( [], 'object' );
		$id         = $this->get_field_id( 'taxonomy' );
		$name       = $this->get_field_name( 'taxonomy' );
		$input      = '<input type="hidden" id="' . $id . '" name="' . $name . '" value="%s" />';

		$count_checkbox = sprintf(
			'<p><input type="checkbox" class="checkbox" id="%1$s" name="%2$s"%3$s /> <label for="%1$s">%4$s</label></p>',
			$this->get_field_id( 'count' ),
			$this->get_field_name( 'count' ),
			checked( $count, true, false ),
			__( 'Show tag counts', 'spaces-global-tags' )
		);

		switch ( count( $taxonomies ) ) {

			// No tag cloud supporting taxonomies found, display error message
			case 0:
				echo '<p>' . __( 'The global tag cloud will not be displayed since there are no global taxonomies that support the global tag cloud widget.', 'spaces-global-tags' ) . '</p>';
				printf( $input, '' );
				break;

			// Just a single tag cloud supporting taxonomy found, no need to display a select.
			case 1:
				$keys     = array_keys( $taxonomies );
				$taxonomy = reset( $keys );
				printf( $input, esc_attr( $taxonomy ) );
				echo $count_checkbox;
				break;

			// More than one tag cloud supporting taxonomy found, display a select.
			default:
				printf(
					'<p><label for="%1$s">%2$s</label>' .
					'<select class="widefat" id="%1$s" name="%3$s">',
					$id,
					__( 'Taxonomy:', 'spaces-global-tags' ),
					$name
				);

				foreach ( $taxonomies as $taxonomy => $tax ) {
					printf(
						'<option value="%s"%s>%s</option>',
						esc_attr( $taxonomy ),
						selected( $taxonomy, $current_taxonomy, false ),
						$tax->labels->name
					);
				}

				echo '</select></p>' . $count_checkbox;
		}
	}

	/**
	 * Retrieves the taxonomy for the current Tag cloud widget instance.
	 *
	 * @since 0.20.0
	 *
	 * @param array $instance Current settings.
	 * @return string Name of the current taxonomy if set, otherwise 'global_post_tag'.
	 */
	public function _get_current_taxonomy( $instance ) {
		if ( ! empty( $instance['taxonomy'] ) && multisite_taxonomy_exists( $instance['taxonomy'] ) ) {
			return $instance['taxonomy'];
		}

		return 'global_post_tag';
	}
}
