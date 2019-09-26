<?php
/**
 * Main page with post stream
 *
 * @package KISDspaces
 * @subpackage defaultspace
 * @since defaultspace 3.0b
 */

get_header();

?>
	<div class="space grid-container margin-top-1">
		<div class="grid-x grid-margin-x grid-margin-y">

			<?php
			echo get_spaces_blog_header( 'large static current' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			?>

			<div class="spaces-content cell large-12 swap-container-parent">
				<div class="spaces-stream first-swap-container">
					<div class='grid-x grid-margin-x grid-margin-y'>
						<?php

						/* Start the Loop */
						while ( have_posts() ) :
							the_post();
							?>
							<div class="cell">
								<div class="card">
									<h1><?php the_title(); ?></h1>
								</div>
							</div>
							<div class="cell">
								<?php the_content(); ?>
							</div>
							<?php

							// If comments are open or we have at least one comment, load up the comment template.
							if ( comments_open() || get_comments_number() ) {
								comments_template();
							}
						endwhile; // End of the loop.
						?>



					</div>
				</div>
			</div>

		</div>
	</div>
<?php
get_footer();
