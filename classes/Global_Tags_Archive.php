<?php

namespace Spaces_Global_Tags;

use Multisite_Term;
use Multisite_WP_Query;

/**
 * Class Global_Tags_Archive
 *
 * @package Spaces_Global_Tags
 * @since 0.12.0
 */
class Global_Tags_Archive {

	/**
	 * Global_Tags_Archive constructor.
	 *
	 * @since 0.12.0
	 */
	public function __construct() {
		self::register();
	}

	/**
	 * Register function.
	 *
	 * @access public
	 * @return void
	 */
	public function register() {

		// Add archive pages related query vars.
		add_filter( 'query_vars', array( $this, 'archive_pages_query_vars' ) );

		// Add rewrite rules for the archive pages.
		add_action( 'init', array( $this, 'add_archive_pages_rewrite_rules' ) );

		// Use the page template for our archive pages.
		add_filter( 'template_include', array( $this, 'archive_pages_template_include' ) );

		// When needed we inject the content of our archive pages.
		add_filter( 'the_content', array( $this, 'archive_pages_content' ) );

		// Hack to avoid the content of archive pages to display multiple times.
		add_filter( 'pre_get_posts', array( $this, 'filtering_posts' ), 620 );

		// Filter the title of archive pages.
		add_filter( 'the_title', array( $this, 'archive_pages_title' ), 20, 2 );

		// Filter for the body class for the archive pages.
		add_filter( 'body_class', array( $this, 'archive_pages_body_class' ) );

		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles_and_scripts' ) );
	}

	/**
	 * Enqueue the frontend styles and scripts.
	 *
	 * @return void
	 */
	public function enqueue_styles_and_scripts() {
		wp_enqueue_script( 'hsph-plugin-tagging', SPACES_GLOBAL_TAGS_ASSETS_URL . '/js/hsph-plugin-tagging.js', array( 'jquery' ), get_plugin_version(), true );
		wp_enqueue_style( 'hsph-plugin-tagging-topics-pages', SPACES_GLOBAL_TAGS_ASSETS_URL . '/css/topics.css', array(), get_plugin_version() );
	}

	/**
	 * Hack to avoid the content of archive pages to display multiple times.
	 *
	 * @access public
	 * @param WP_Query $wp_query The WP_Query.
	 * @return void
	 */
	public function filtering_posts( $wp_query ) {
		if ( is_multitaxo() ) {
			$wp_query->set( 'posts_per_page', 1 );
			return;
		}
	}

	/**
	 * Add rewrite rules for the archive pages.
	 *
	 * @access public
	 * @return void
	 */
	public function add_archive_pages_rewrite_rules() {
		// TODO: move $base_rewrite as class property.
		// Our base rewrite for all multisite tax plugins.
		$base_rewrite = apply_filters( 'multisite_taxonomy_base_url_slug', 'multitaxo' ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals

		// This cannot be empty.
		if ( empty( $base_rewrite ) ) {
			$base_rewrite = 'multitaxo';
		}

		// add our rewrite rules.
		add_rewrite_rule( $base_rewrite . '/([^/]+)/([^/]+)/([0-9]{1,})/?$', 'index.php?multitaxo&multisite_taxonomy=$matches[1]&multisite_term=$matches[2]&mpage=$matches[3]', 'top' );
		add_rewrite_rule( $base_rewrite . '/([^/]+)/([^/]+)/?$', 'index.php?multitaxo&multisite_taxonomy=$matches[1]&multisite_term=$matches[2]', 'top' );
		add_rewrite_rule( $base_rewrite . '/([^/]+)/?$', 'index.php?multitaxo&multisite_taxonomy=$matches[1]', 'top' );
		add_rewrite_rule( $base_rewrite . '/?$', 'index.php?multitaxo', 'top' );
	}

	/**
	 * Add archive pages related query vars.
	 *
	 * @access public
	 * @param array $query_vars Query vars already existing.
	 * @return array Filtered Query vars.
	 */
	public function archive_pages_query_vars( $query_vars ) {
		$query_vars[] = 'multitaxo';
		$query_vars[] = 'multisite_taxonomy';
		$query_vars[] = 'multisite_term';
		$query_vars[] = 'mpage';
		return $query_vars;
	}

	/**
	 * Filter the post content to inject the archive pages content when viwewing them.
	 *
	 * @access public
	 * @param object $post_content The current post content.
	 * @return string The filtered post content.
	 */
	public function archive_pages_content( $post_content ) {
		if ( is_multitaxo() && in_the_loop() ) {
			if ( is_multisite_term() ) {
				return $this->do_multisite_term_archive_page_content();
			} elseif ( is_multisite_taxonomy() ) {
				return $this->do_multisite_taxonomy_archive_page_content();
			} else {
				return $this->do_multisite_taxonomies_archive_page_content();
			}
		} else {
			return $post_content;
		}
	}

	/**
	 * Filter for the body class for the archive pages.
	 *
	 * @access public
	 * @param array $body_classes Current body clases.
	 * @return array Filtered array of body classes.
	 */
	public function archive_pages_body_class( $body_classes ) {
		// this is a single topic page.
		if ( is_multisite_term() ) {
			$body_classes[] = 'multitaxo multisite-term-archive';
		} elseif ( is_multisite_taxonomy() ) {
			$body_classes[] = 'multitaxo multisite-taxonomy-archive';
		} elseif ( is_multisite_taxonomies() ) {
			$body_classes[] = 'multitaxo multisite-taxonomies-archive';
		}

		return $body_classes;
	}

	/**
	 * Use the page template for our archive pages.
	 *
	 * @access public
	 * @param array $template The template determined by WordPress.
	 * @return string The filtered template file.
	 */
	public function archive_pages_template_include( $template ) {

		if ( is_multitaxo() ) {
			$template = apply_filters( 'spaces_global_tags_archive_template_path', plugin_dir_path( __DIR__ ) . 'templates/template.php' );
		}

		return $template;
	}

	/**
	 * Filter the title of the archive pages.
	 *
	 * @access public
	 * @param string $title The current title.
	 * @return string The filtered title.
	 */
	public function archive_pages_title( $title ) {
		global $wp_query;

		if ( is_multitaxo() && in_the_loop() ) {
			if ( is_multisite_term() ) {
				// TODO: move $multisite_taxonomy as class property.
				$multisite_term = get_multisite_term_by( 'slug', sanitize_key( get_query_var( 'multisite_term' ) ), sanitize_key( get_query_var( 'multisite_taxonomy' ) ), OBJECT );
				if ( is_a( $multisite_term, 'Multisite_Term' ) ) {
					/**
					 * Display related terms after archive title.
					 *
					 * @since 0.13.0
					 */
					add_action(
						'spaces_global_tags_below_archive_title',
						function() use ( $multisite_term ) {
							echo self::do_multisite_term_related_terms_list( $multisite_term ); // phpcs:ignore WordPress.Security.EscapeOutput
						}
					);
					// translators: The multisite term name on a multisite term archive page.
					return wp_sprintf( __( 'All articles related to #%s: ', 'spaces-global-tags' ), $multisite_term->name );
				} else {
					// TODO: move check to constructor and return proper 404.
					return __( 'Invalid Multisite Term', 'spaces-global-tags' );
				}
				// TODO: move check to constructor and return proper 404.
				return __( 'Multisite term achive page', 'spaces-global-tags' );
			} elseif ( is_multisite_taxonomy() ) {
				// TODO: move $multisite_taxonomy as class property.
				$multisite_taxonomy = get_multisite_taxonomy( sanitize_key( get_query_var( 'multisite_taxonomy' ) ) );
				if ( is_a( $multisite_taxonomy, 'Multisite_Taxonomy' ) ) {
					// translators: The multisite taxonomy name on a multisite taxonomy archive page.
					return wp_sprintf( __( 'All %s: ', 'spaces-global-tags' ), $multisite_taxonomy->labels->name );
				} else {
					// TODO: move check to constructor and return proper 404.
					return __( 'Invalid Multisite Taxonomy', 'spaces-global-tags' );
				}
			} else {
				return __( 'All Multisite Taxonomies:', 'spaces-global-tags' );
			}
		}

		// if we reach here then something went oddly wrong.
		return $title;
	}

	/**
	 * Generate an alphabetical list of multisite terms for the current
	 * multisite taxonomy archive page content.
	 *
	 * @access public
	 * @return string The archive page content
	 */
	public function do_multisite_taxonomy_archive_page_content() {
		// Get the taxonomy.
		$multisite_taxonomy = sanitize_key( get_query_var( 'multisite_taxonomy' ) );

		// Check that our tax exists.
		if ( false === get_multisite_taxonomy( $multisite_taxonomy ) ) {
			return;
		}

		$topics = get_multisite_terms(
			array(
				'get'        => 'all',
				'orderby'    => 'name',
				'hide_empty' => true,
				'taxonomy'   => $multisite_taxonomy,
			)
		);

		// We make sure we have at least one multisite term.
		if ( is_array( $topics ) && ! empty( $topics ) ) :
			$terms_by_letter = array();
			// We iterate throught all of them and group them by their first letter.
			foreach ( $topics as $topic ) {
				if ( is_a( $topic, 'Multisite_Term' ) ) {
					$terms_by_letter[ strtolower( substr( $topic->name, 0, 1 ) ) ][] = $topic;
				}
			}

			$letters = range( 'a', 'z' );

			// We start buffering the page content.
			ob_start();
			?>
			<div class="cell">
			<div class="card static">
			<div class="alphabetical_index">
			<ul>
				<?php // We create an anchor navigation index. ?>
				<?php
				foreach ( $letters as $letter ) {
					if ( array_key_exists( $letter, $terms_by_letter ) ) {
						?>
						<li><a href="#<?php echo esc_attr( strtolower( $letter ) ); ?>" title="<?php echo esc_attr__( 'View all links in ', 'spaces-global-tags' ) . esc_attr( strtoupper( $letter ) ); ?>"><?php echo esc_attr( strtoupper( $letter ) ); ?></a></li>
						<?php
					} else {
						?>
						<li><span><?php echo esc_attr( strtoupper( $letter ) ); ?></span></li>
						<?php
					}
				}
				?>
			</ul>
		</div>
		<div class="topic-container">
			<?php // Display topic links grouped by letter. ?>
			<?php foreach ( $terms_by_letter as $letter => $topics ) : ?>
				<div class="topic-block">
				<h2 id="<?php echo esc_attr( $letter ); ?>" class="topic-letter"><?php echo esc_attr( strtoupper( $letter ) ); ?></h2>
					<ul class="topic-list">
						<?php if ( is_array( $topics ) && ! empty( $topics ) ) : ?>
							<?php foreach ( $topics as $topic ) : ?>
								<li><a href="<?php echo esc_url( get_multisite_term_link( $topic->multisite_term_id, $topic->multisite_taxonomy ) ); ?>"><?php echo esc_attr( '#' . $topic->name ) . esc_html( ' (' . $topic->count . ')' ); ?></a></li>
							<?php endforeach; ?>
						<?php endif; ?>
					</ul>
				</div>
			<?php endforeach; ?>
		</div>
			</div>
			</div>

			<?php
		else :
			?>
			<p class="exploreWidget"><?php esc_html_e( 'No topics to display.', 'spaces-global-tags' ); ?></a></p>
			<?php
		endif;
		// Get our generated page content.
		$page_content = ob_get_clean();
		return $page_content;
	}

	/**
	 * Generate a list of all registered multisite taxonomies.
	 *
	 * @access public
	 * @return string The archive page content
	 */
	public function do_multisite_taxonomies_archive_page_content() {
		// Get the list of taxonomies.
		$taxonomies = get_multisite_taxonomies( array(), 'objects' );

		// We start buffering the page content.
		ob_start();

		// loop and loop.
		foreach ( $taxonomies as $tax ) {
			$hierarchical = ( true === $tax->hierarchical ) ? 'hierarchical' : 'flat';

			?>
			<div class="card">
				<h2><a href="<?php echo esc_attr( $tax->name ); ?>"><?php echo esc_html( $tax->labels->name ); ?></a></h2>
			</div>
			<?php
		}

		// Get our generated page content.
		$page_content = ob_get_clean();
		return $page_content;
	}

	/**
	 * Generate a list of posts for the multisite term archive page content.
	 *
	 * @access public
	 * @return string The archive page content
	 */
	public function do_multisite_term_archive_page_content() {
		$page_content   = '';
		$multisite_term = get_multisite_term_by( 'slug', sanitize_key( get_query_var( 'multisite_term' ) ), sanitize_key( get_query_var( 'multisite_taxonomy' ) ), OBJECT );
		if ( is_a( $multisite_term, 'Multisite_Term' ) ) {
			// $page_content .= self::do_multisite_term_related_terms_list( $multisite_term );
			// TODO: Move this to the class constructor.
			// Get the posts for our multisite term using Multisite_WP_Query.
			$query = new Multisite_WP_Query(
				array(
					'multisite_term_ids' => array( $multisite_term->multisite_term_id ),
					'nopaging'           => true,
					'orderby'            => 'post_date',
					'order'              => 'DESC',
				)
			);
			if ( isset( $query->posts ) && is_array( $query->posts ) && ! empty( $query->posts ) ) {
				// TODO: Move this to the class constructor.
				$posts_per_page  = (int) get_option( 'posts_per_page', 10 );
				$number_of_posts = count( $query->posts );
				$current_page    = (int) get_query_var( 'mpage', 1 );
				$offset          = ( $current_page - 1 ) * $posts_per_page;

				// We split the full list of posts into multiple arrays of $posts_per_page number of posts.
				$posts           = array_chunk( $query->posts, $posts_per_page );
				$number_of_pages = (int) ceil( $number_of_posts / $posts_per_page );

				// Substract 1 to page number to match php array keys.
				$current_page_key = $current_page - 1;
				if ( isset( $posts[ $current_page_key ] ) ) {
					$page_content .= self::do_multisite_term_posts_list( $posts[ $current_page_key ] );
					$page_content .= self::do_multisite_term_archive_page_pagination( $current_page, $number_of_pages, $multisite_term );
				} else {
					// TODO: Once the query and pagination var are moved to constructor check this earlier and return proper 404.
					$page_content .= __( 'This multisite term currently has no post.', 'spaces-global-tags' );
				}
			} else {
				// TODO: Once the query and pagination var are moved to constructor check this earlier and return proper 404.
				$page_content .= __( 'This multisite term currently has no post.', 'spaces-global-tags' );
			}

			return $page_content;
		} else {
			return __( 'This multisite term doesn\'t seem to exist', 'spaces-global-tags' );
		}
	}

	/**
	 * Get an array of arrays of related multisite terms grouped per multisite taxonomies for a given multisite term.
	 *
	 * @access public
	 * @param int $multisite_term_id The multisite term for which we display related terms.
	 * @return array Related multisite terms grouped per multisite taxonomies.
	 */
	public static function get_multisite_term_related_multisite_terms( $multisite_term_id ) {

		$related_terms = array();

		// Get the related terms as a list of multisite terms ids grouped per multisite taxonomies.
		$related_terms_ids = get_multisite_term_meta( $multisite_term_id, 'related_topics', true );

		if ( is_array( $related_terms_ids ) && ! empty( $related_terms_ids ) ) {
			foreach ( $related_terms_ids as $multisite_taxonomy => $multisite_terms ) {
				foreach ( $multisite_terms as $multisite_term ) {
					$current_multisite_term = get_multisite_term( $multisite_term, $multisite_taxonomy );
					if ( is_a( $current_multisite_term, 'Multisite_Term' ) ) {
						$related_terms[] = $current_multisite_term;
					}
				}
			}
		}

		return $related_terms;
	}

	/**
	 * Generate a list of related mulsite terms for a given term.
	 *
	 * @access public
	 * @param Multisite_Term $multisite_term The multisite term for which we display related terms.
	 * @return string The archive page content.
	 */
	public static function do_multisite_term_related_terms_list( $multisite_term ) {
		global $wp;
		// get the related topics.
		$related_terms = get_multisite_terms(
			array(
				'taxonomy' => $multisite_term->multisite_taxonomy,
				'order'    => 'DESC',
				'orderby'  => 'count',
				'number'   => apply_filters(
					'spaces_global_tags_number_of_related_tags',
					30
				),
			)
		);

		// We start buffering the page content.
		ob_start();
		if ( is_array( $related_terms ) && ! empty( $related_terms ) ) :
			?>
			<div class="topic-block">
				<h2 class="topic-letter">
				<?php
					$taxonomy_label = get_multisite_taxonomy( $multisite_term->multisite_taxonomy )->label;
					/* Translators: show the taxonomy name */
					printf( esc_html__( 'More %s', 'spaces-global-tags' ), esc_html( $taxonomy_label ) );
				?>
				</h2>
				<ul class="topic-list">
					<?php foreach ( $related_terms as $related_multisite_term ) : ?>
						<li class="<?php echo $multisite_term->name === $related_multisite_term->name ? 'current' : ''; ?>">
							<a href="<?php echo esc_url( get_multisite_term_link( $related_multisite_term ) ); ?>">
							<?php echo esc_html( '#' . $related_multisite_term->name ) . esc_html( ' (' . $related_multisite_term->count . ')' ); ?>
							</a>

						</li>
					<?php endforeach; ?>
					<?php
					if ( true === apply_filters( 'spaces_global_tags_show_link_to_all_tags', true ) ) :
						$taxonomy_url = home_url( $wp->request );
						$taxonomy_url = str_replace( $multisite_term->slug, '', $taxonomy_url );

						echo '<li class="current show-all"><a href="' . esc_url( $taxonomy_url ) . '">';
							/* Translators: show the taxonomy name */
							printf( esc_html__( 'All %s', 'spaces-global-tags' ), esc_html( $taxonomy_label ) );
						echo '</a></li>';
						endif;
					?>
				</ul>
			</div>
			<?php
		endif;
		// Get our generated page content.
		$page_content = ob_get_clean();
		return $page_content;
	}

	/**
	 * Generate a list of posts in the mulsitite archive page context.
	 *
	 * @access public
	 * @param array $posts  An array of posts to be displayed as a list.
	 * @return string HTML list of posts.
	 */
	public static function do_multisite_term_posts_list( $posts ) {
		$page_content = '';
		if ( is_array( $posts ) ) {
			ob_start();
			// We display the posts of the current page.
			foreach ( $posts as $post ) :
				?>
					<div class="cell">
					<article id="post-<?php multitaxo_the_id( $post->ID ); ?>" aria-label="<?php esc_attr_e( 'Excerpt of the article:', 'spaces-global-tags' ); ?> <?php echo esc_attr( multitaxo_get_the_title( $post->post_title ) ); ?>"  class="post-<?php multitaxo_the_id( $post->ID ); ?> post card multisite-term">
						<header class="card-header entry-header">
							<small class="flex flex-dir-row align-middle">
								<span class="dot-after">
									<?php
									// translators: see context, poste in space.
									printf( _x( 'In %s', 'Posted in space', 'spaces-global-tags' ), get_site( $post->blog_id )->blogname ); // phpcs:ignore WordPress.Security.EscapeOutput
									?>
								</span>
								<span>
									<?php echo esc_html( mysql2date( get_option( 'date_format' ), $post->post_date, false ) ); ?>
								</span>
							</small>
							<h4 class="card-title entry-title"><a href="<?php multitaxo_the_permalink( $post->post_permalink ); ?>" rel="bookmark" title="<?php echo esc_attr( __( 'Permalink to ', 'spaces-global-tags' ) . multitaxo_get_the_title( $post->post_title ) ); ?>"><?php multitaxo_the_title( $post->post_title ); ?></a></h4>
						</header><!-- .entry-header -->
						<div class="entry-summary post-content card-section s-fg-a-c1-parent post-excerpt">
							<?php echo preg_replace( '#\[[^\]]+\]#', '', multitaxo_get_the_excerpt( $post ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
						</div><!-- .entry-summary -->
					</article>
					</div>
				<?php
			endforeach; // Post loop.
			// Get our generated page content.
			$page_content = ob_get_clean();
		}
		return $page_content;
	}

	/**
	 * Return the html page pagination for the multisite term archive page.
	 *
	 * @param  int            $current_page The current page number.
	 * @param  int            $number_of_pages The total number of pages.
	 * @param  Multisite_Term $multisite_term The multisite term for which we display the archive page.
	 * @return string The html string for page pagination.
	 */
	public static function do_multisite_term_archive_page_pagination( $current_page, $number_of_pages, $multisite_term ) {
		// setup the params for pagination.
		$pagination_args = array(
			'format'    => '%#%',
			'total'     => absint( $number_of_pages ),
			'current'   => absint( $current_page ),
			'show_all'  => false,
			'end_size'  => 3,
			'mid_size'  => 2,
			'prev_next' => true,
			'prev_text' => __( '<i class="fa fa-angle-left" aria-hidden="true"></i><span class="sr-only">« prev</span></a>', 'spaces-global-tags' ),
			'next_text' => __( '<i class="fa fa-angle-right" aria-hidden="true"></i><span class="sr-only">next »</span>', 'spaces-global-tags' ),
			'type'      => 'plain',
		);

		$pagination_args ['base'] = get_multisite_term_link( $multisite_term ) . '%_%/';

		// add the pagination to the page.
		ob_start();
		?>
		<div class="pagination cell">
			<h3 class="assistive-text screen-reader-text"><?php esc_html_e( 'Post navigation', 'spaces-global-tags' ); ?></h3>
			<?php echo paginate_links( $pagination_args ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
		</div>
		<?php
		// Get our generated page content.
		$page_content = ob_get_clean();
		return $page_content;
	}
}
