<?php /**/ ?><?php get_header() ?>

	<div id="container">
		<div id="content" class="hfeed">

<?php the_post() ?>

			<div id="post-<?php the_ID(); ?>" class="<?php barthelme_post_class() ?>">
				<h2 class="entry-title"><?php the_title(); ?></h2>
				<?php if ( get_post_custom_values('authorlink') ) printf(__('<div class="author-meta">By %1$s</div>', 'barthelme'), barthelme_author_link() ) // Add a key/value of "authorlink" to show an author byline on a page ?>
				<div class="entry-content">
<?php the_content() ?>

<?php link_pages('<div class="page-link">'.__('Pages: ', 'barthelme'), '</div>', 'number'); ?>

<?php edit_post_link(__('Edit this entry.', 'barthelme'),'<p class="entry-edit">','</p>') ?>

				</div>
			</div><!-- .post -->

<?php if ( get_post_custom_values('comments') ) comments_template() // Add a key/value of "comments" to load comments on a page ?>

		</div><!-- #content .hfeed -->
	</div><!-- #container -->

<?php get_sidebar() ?>
<?php get_footer() ?>