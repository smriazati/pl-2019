<?php /**/ ?><?php get_header(); ?>

	<?php if (have_posts()) : while (have_posts()) : the_post(); ?>

		<h1 id="post-<?php the_ID(); ?>"><?php the_title(); ?></h1>
			
		<?php the_content("<p>__('Read the rest of this page &raquo;')</p>"); ?>
		<?php wp_link_pages(); ?>
		
		<?php edit_post_link(__('Edit'), '<p>', '</p>'); ?>
	
	<?php endwhile; endif; ?>

<?php get_sidebar(); ?>

<?php get_footer(); ?>