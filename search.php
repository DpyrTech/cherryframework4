<?php
/**
 * The template for displaying Search Results pages.
 *
 * @package Cherry Framework
 */

if ( have_posts() ) : ?>

	<header class="page-header">
		<h1 class="page-title"><?php printf( __( 'Search Results for: %s', 'cherry' ), '<span>' . get_search_query() . '</span>' ); ?></h1>
	</header><!-- .page-header -->

	<?php /* Start the Loop */ ?>
	<?php while ( have_posts() ) : the_post(); ?>

		<?php get_template_part( 'templates/content', 'search' ); ?>

	<?php endwhile; ?>

	<?php cherry_paging_nav(); ?>

<?php else : ?>

	<?php get_template_part( 'templates/content', 'none' ); ?>

<?php endif; ?>