<?php /**/ ?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head profile="http://gmpg.org/xfn/11">

	<title><?php bloginfo('name'); ?><?php wp_title(); ?></title>

	<meta http-equiv="Content-Type" content="<?php bloginfo('html_type'); ?>; charset=<?php bloginfo('charset'); ?>" />	
	<meta name="generator" content="WordPress <?php bloginfo('version'); ?>" /> <!-- leave this for stats please -->

	<link rel="stylesheet" href="<?php bloginfo('stylesheet_url'); ?>" type="text/css" media="screen" />
	<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="<?php bloginfo('rss2_url'); ?>" />
	<link rel="alternate" type="text/xml" title="RSS .92" href="<?php bloginfo('rss_url'); ?>" />
	<link rel="alternate" type="application/atom+xml" title="Atom 0.3" href="<?php bloginfo('atom_url'); ?>" />
	<link rel="pingback" href="<?php bloginfo('pingback_url'); ?>" />

	<?php wp_get_archives('type=monthly&format=link'); ?>
	<?php //comments_popup_script(); // off by default ?>
	<?php wp_head(); ?>
</head>
<body>
	
	<div id="container">
		
		<?php get_header(); ?>

		<div id="posts">

			<?php if(have_posts()) : ?><?php while(have_posts()) : the_post(); ?>

				<div class="post" id="post-<?php the_ID(); ?>">

					<h2><a href="<?php the_permalink(); ?>" title="<?php the_title(); ?>"><?php the_title(); ?></a></h2>
					
					<div class="date">
						
						<?php the_time('F j, Y'); ?>
						<?php edit_post_link('Edit', ' &#124; ', ''); ?>
					
					</div>
					
					<div class="tags">
						<?php the_tags('Tags: ', ', ', ''); ?>
					</div>
					
					<div class="entry">

						<?php the_content(); ?>

						<?php link_pages('<p><strong>Pages:</strong>','</p>','number'); ?>
						<?php edit_post_link('Edit','<p>','</p>'); ?>

					</div>

				</div>

			<?php endwhile; ?>

			<?php else : ?>

				<div class="post" id="post-<?php the_ID(); ?>">
					<h2><?php_e('Not Found'); ?></h2>
				</div>

			<?php endif; ?>

		</div>

		<?php get_sidebar(); ?>

		<?php get_footer(); ?>

		<?php wp_footer(); ?>

	</div>

</body>
</html>