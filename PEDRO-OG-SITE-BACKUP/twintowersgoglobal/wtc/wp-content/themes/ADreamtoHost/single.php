<?php /**/ ?><?php get_header(); ?>

	<?php if (have_posts()) : while (have_posts()) : the_post(); ?>
		
		<div class="post">
	
			<h1 class="posttitle" id="post-<?php the_ID(); ?>"><a href="<?php the_permalink() ?>" rel="bookmark" title="<?php _e('Permanent link to'); ?> <?php the_title(); ?>"><?php the_title(); ?></a></h1>
			
			<div class="postentry">
				<?php the_content("<p>Read the rest of this entry &raquo;</p>"); ?>
			<?php wp_link_pages(); ?>
			</div>

			<p class="postmeta">
			<?php the_time('F j, Y') ?>  
			&#183; <?php _e('Filed under'); ?> <?php the_category(', ') ?>
			<?php edit_post_link(__('Edit'), ' &#183; ', ''); ?>
			</p>
			
		</div>
		
		<?php comments_template(); ?>
				
	<?php endwhile; else : ?>

		<h2><?php _e('Not Found'); ?></h2>

		<p><?php _e('Sorry, but the page you requested cannot be found.'); ?></p>
		
		<h3><?php _e('Search'); ?></h3>
		
		<?php include (TEMPLATEPATH . '/searchform.php'); ?>

	<?php endif; ?>

<?php get_sidebar(); ?>

<?php get_footer(); ?>