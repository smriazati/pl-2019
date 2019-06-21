<?php /**/ ?><div class="wrap"> 
<h2>
<?php
	_e('Image Browser Options', 'image_browser');
		?>
</h2>
<form name="image_browser_options" method="post" action='options.php'>
	<?php settings_fields( 'image_browser_options' ); ?>
  <?php $options = get_option('image_browser');
        extract($options);
  ?>
<table class='form-table'>
  <tr>
    <th>
      <label for='year'><?php _e('Year:', 'image_browser') ?></label>
    </th>
    <td>
    <?php echo $settings->years_dropdown($year=$year, $name='image_browser[year]'); ?>
    </td>
  </tr>
  <tr>
    <th>
      <label for='month'><?php _e('Month:', 'image_browser') ?></label>
    </th>
    <td>
    <?php echo $settings->months_dropdown($month=$month, $name='image_browser[month]'); ?>
    </td>
  </tr>
  <tr>
    <th>
      <label for='cols'><?php _e('Number of columns:', 'image_browser') ?></label>
    </th>
    <td>
    <input type='text' name='image_browser[cols]' value='<?=$cols?>' />
    </td>
  </tr>
  <tr>
    <th>
      <label for='limit'><?php _e('Images per page:', 'image_browser') ?></label>
    </th>
    <td>
    <input type='text' name='image_browser[limit]' value='<?=$limit?>' />
    </td>
  </tr>
  <tr>
    <th>
      <label for='page'><?php _e('Start at page:', 'image_browser') ?></label>
    </th>
    <td>
    <input type='text' name='image_browser[page]' value='<?=$page?>' />
    </td>
  </tr>
  <th>
  <label for='size'><?php _e('Image Size:', 'image_browser') ?></label>
  </th>
  <td>
    <select id='size' name='image_browser[size]'>
    <option value='thumbnail' <?php if ($size=='thumbnail') echo
    "selected='selected'" ?>><?php _e('Thumbnail', 'image_browser') ?></option>
    <option value='medium' <?php if ($size=='medium') echo
    "selected='selected'" ?>><?php _e('Medium', 'image_browser') ?></option>
    <option value='large' <?php if ($size=='large') echo
    "selected='selected'" ?>><?php _e('Large', 'image_browser') ?></option>
    <option value='full' <?php if ($size=='full') echo
    "selected='selected'" ?>><?php _e('Full', 'image_browser') ?></option>
    </select>
  </td>
  </tr>
  <tr>
  <th>
  <label for='sortby'><?php _e('Sort by:', 'image_browser') ?></label>
  </th>
  <td>
    <select name='image_browser[sortby]'>
    <option value='post_date' <?php if ($sortby=='post_date') echo
    "selected='selected'" ?>><?php _e('Date', 'image_browser') ?></option>
    <option value='post_title' <?php if ($sortby=='post_title') echo
    "selected='selected'" ?>><?php _e('Title', 'image_browser') ?></option>
    <option value='post_excerpt' <?php if ($sortby=='post_excerpt') echo
    "selected='selected'" ?>><?php _e('Caption', 'image_browser') ?></option>
    </select>
  </td>
  </tr>
  <tr>
  <th>
  <label for='sortorder'><?php _e('Sort in:', 'image_browser') ?></label>
  </th>
  <td>
    <select name='image_browser[sortorder]'>
    <option value='ASC' <?php if ($sortorder=='ASC') echo
    "selected='selected'" ?>><?php _e('Ascending order', 'image_browser') ?></option>
    <option value='DESC' <?php if ($sortorder=='DESC') echo
    "selected='selected'" ?>><?php _e('Descending order', 'image_browser') ?></option>
    </select>
  </td>
  </tr>
  <tr>
  <th>
  <label for='category'><?php _e('Category:', 'image_browser') ?></label>
  </th>
  <td>
  <?php
  wp_dropdown_categories("name=image_browser[category]&hierarchical=1&selected=$category&hide_empty=1&show_option_all=All&orderby=name");
  ?>
  </td>
  </tr>
  <tr>
    <th>
      <label for='keywords'><?php _e('Keywords:', 'image_browser') ?></label>
    </th>
    <td>
    <input type='text' name='image_browser[keywords]' value='<?=$keywords?>' />
    </td>
  </tr>
</table>
<input type="submit" name="Submit" value="<?php _e('Save Changes') ?>" />
  </form>
</div>
