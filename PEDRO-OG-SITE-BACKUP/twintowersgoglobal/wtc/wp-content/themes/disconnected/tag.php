<?php /**/ ?>	<?php get_header();?>	
	<div id="main">
	<div id="content">

	<?php if (have_posts()) : ?>
		<?php while (have_posts()) : the_post(); ?>
			<h3><?php if (function_exists(single_tag_title)) {
				single_tag_title();
			} else {
				UTW_ShowCurrentTagSet('tagsetcommalist');
			} ?></h3>
			<div class="post-info"><?php _e('Archived Posts with this tag'); ?></div>		
			<br/>	
			<div class="post">
				<?php require('post.php'); ?></div>

		<?php endwhile; ?>

	<?php else : ?>

		<h2 class="center">Not Found</h2>
		<p class="center">Sorry, but you are looking for something that isn't here.</p>
		<?php include (TEMPLATEPATH . "/searchform.php"); ?>

	<?php endif; ?>

	</div>

	<div id="sidebar">		
		<?php get_sidebar(); ?>
	</div>
<?php get_footer()?>
</div>
</div>
</body>
</html>