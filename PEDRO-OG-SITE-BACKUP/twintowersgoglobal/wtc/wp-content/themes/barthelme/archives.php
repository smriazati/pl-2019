<?php /**/ ?><?php
/*
Template Name: Archives Page
*/
?>
<?php get_header() ?>

	<div id="container">
		<div id="content" class="hfeed">

<?php the_post() ?>

			<div id="post-<?php the_ID() ?>" class="<?php barthelme_post_class() ?>">
				<h2 class="entry-title"><?php the_title() ?></h2>
				<?php if ( get_post_custom_values('authorlink') ) printf(__('<div class="author-meta">By %1$s</div>', 'barthelme'), barthelme_author_link() ) // Add a key/value of "authorlink" to show an author byline on a page ?>
				<div class="entry-content">
<?php the_content(); ?>

					<ul class="xoxo">
						<li>
							<h3><?php _e('Archives by Category', 'barthelme') ?></h3>
							<ul>
								<?php wp_list_categories('title_li=&sort_column=name&optioncount=1&feed=RSS&show_count=1') ?> 
							</ul>
						</li>
						<li>
							<h3><?php _e('Archives by Month', 'barthelme') ?></h3>
							<ul>
								<?php wp_get_archives('type=monthly&show_post_count=1') ?>
							</ul>
						</li>
						<li>
							<h3><?php _e('Archives by Tag', 'barthelme') ?></h3>
							<p><?php wp_tag_cloud() ?></p>
						</li>
					</ul>
<?php edit_post_link(__('Edit this entry.', 'barthelme'),'<p class="entry-edit">','</p>') ?>

				</div>
			</div><!-- .post -->

<?php if ( get_post_custom_values('comments') ) comments_template() // Add a key/value of "comments" to load comments on a page ?>

		</div><!-- #content .hfeed -->
	</div><!-- #container -->

<?php get_sidebar() ?>
<?php get_footer() ?>