<?php /**/ ?><?php get_header(); ?>

	<?php if (have_posts()) : ?>
	
		<h2><?php _e('Search Results'); ?></h2>
		
		<?php while (have_posts()) : the_post(); ?>
		
			<div class="post">
	
				<h2 class="posttitle" id="post-<?php the_ID(); ?>"><a href="<?php the_permalink() ?>" rel="bookmark" title="<?php _e('Permanent link to'); ?> <?php the_title(); ?>"><?php the_title(); ?></a></h2>
			
	
				<div class="postentry">
				<?php the_excerpt() ?>
				
				<?php the_content("&raquo; Continue reading " . the_title('"','"',false), 0); ?>
				</div>
			
				<p class="postfeedback">
				<a href="<?php the_permalink() ?>" rel="bookmark" title="<?php _e('Permanent link to'); ?> <?php the_title(); ?>" class="permalink"><?php _e('Permalink'); ?></a>
				<?php comments_popup_link(__('Leave a Comment'), __('Comments (1)'), __('Comments (%)'), 'commentslink', __('Comments off')); ?>
				</p>
				
				<p class="postmeta"> 
				<?php the_time('F j, Y') ?>
				&#183; <?php _e('Filed under'); ?> <?php the_category(', ') ?>
				<?php edit_post_link(__('Edit'), ' &#183; ', ''); ?>
				</p>
				
				<!--
				<?php trackback_rdf(); ?>
				-->
			
			</div>
				
		<?php endwhile; ?>
		<div class="footnav">
			<div class="alignleft"><?php next_posts_link('&laquo; Older Posts') ?></div>
			<div class="alignright"><?php previous_posts_link('Newer Posts &raquo;') ?></div>
		</div>
		
	<?php else : ?>

		<h2><?php _e('Not Found'); ?></h2>

		<p><?php _e('Sorry, but no posts matched your criteria.'); ?></p>
		
		<h3><?php _e('Search'); ?></h3>
		
		<?php include (TEMPLATEPATH . '/searchform.php'); ?>

	<?php endif; ?>

<?php get_sidebar(); ?>

<?php get_footer(); ?>