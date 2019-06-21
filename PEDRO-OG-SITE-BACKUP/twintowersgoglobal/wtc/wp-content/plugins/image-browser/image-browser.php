<?php /**/ ?><?php
/*
Plugin Name: Image Browser
Plugin URI: http://blog.robfelty.com/plugins/image-browser
Description: Allows you to browse all images on your blog easily <a href='options-general.php?page=image-browser'>Settings</a>
Author: Robert Felty
Version: 0.2
Author URI: http://robfelty.com
*/ 
global $ImageBrowserVersion;
$ImageBrowserVersion = '0.2';

class ImageBrowser {
  function get_images($atts, $count=false) {
    global $wpdb;
    extract($atts);
    $offset = $limit * ($page-1);

    if ($year) {
      if ($year=='current') {
        $yearQuery ="AND YEAR($wpdb->posts.post_date) = YEAR(CURDATE())";
      } else {
        $yearQuery ="AND YEAR($wpdb->posts.post_date) = '$year'";
      }
    }
    if ($month) {
      if ($month=='current') {
        $monthQuery ="AND MONTH($wpdb->posts.post_date) = month(CURDATE())";
      } else {
        $monthQuery ="AND MONTH($wpdb->posts.post_date) = '$month'";
      }
    }
    if ($keywords!='' AND $keywords!='keyword') {
      $keyword_array = explode(' ', $keywords);
      $keywordQuery = 'AND (';
      $keywordI=1;
      foreach ($keyword_array as $keyword) {
        $keywordQuery .= "($wpdb->posts.post_content LIKE '%$keyword%' OR 
            $wpdb->posts.post_excerpt LIKE '%$keyword%')";
        if ($keywordI<count($keyword_array)) {
          $keywordQuery .= ' OR ';
        }
        $keywordI++;
      }
      $keywordQuery .= ')';
    }
    if ($category) {
      $catQuery ="AND $wpdb->terms.term_id='$category'";
    }
    $sort = "ORDER BY $sortby $sortorder";
    $post_attrs .= " post_type = 'attachment' AND post_mime_type In
        ('image/gif', 'image/png', 'image/jpeg', 'image/jpg', 'jpg', 'jpeg', 
        'png', 'gif')";
    if (!$count) {
      $limitOffset = "LIMIT $limit OFFSET $offset";
    }

    $query= "SELECT $wpdb->posts.ID, $wpdb->terms.term_id,
      $wpdb->posts.post_name, $wpdb->posts.post_excerpt,
      $wpdb->posts.post_content, $wpdb->posts.post_parent, 
      $wpdb->posts.post_title, $wpdb->posts.post_author,
      $wpdb->posts.post_date, YEAR($wpdb->posts.post_date) AS 'year',
      MONTH($wpdb->posts.post_date) AS 'month' 
      FROM $wpdb->posts LEFT JOIN $wpdb->term_relationships ON
      $wpdb->posts.post_parent =
      $wpdb->term_relationships.object_id 
      LEFT JOIN $wpdb->term_taxonomy ON $wpdb->term_taxonomy.term_taxonomy_id =
                                        $wpdb->term_relationships.term_taxonomy_id
      LEFT JOIN $wpdb->terms ON $wpdb->terms.term_id = 
                                $wpdb->term_taxonomy.term_id 
      WHERE $post_attrs  $yearQuery $monthQuery $catQuery $keywordQuery
    GROUP BY $wpdb->posts.ID
    $sort
    $limitOffset
    ";

    //echo $query;
    if ($count) {
      $result=$wpdb->query($query);
    } else {
      $result=$wpdb->get_results($query);
    }
    return($result);
  }

  /* returns an array of all years for which there are posts. The 0th index is
  'All'
  */
  function years_dropdown($img_year = NULL, $name='img_year') {
    global $wpdb;
    $query = "SELECT YEAR($wpdb->posts.post_date) as 'year' 
    FROM $wpdb->posts
    GROUP BY year 
    ORDER BY year ASC";
    $year_results=$wpdb->get_col($query);
    $years = array();
    foreach ($year_results as $year) {
      $years[$year] = $year;
    }
    $years[0] = __('All');
    $years['current'] = __('Current');
    $dropdown = "<select id='year' name='$name'>";
    foreach ($years as $year => $label) {
      if ( (string)$year == (string)$img_year) {
        $selected = 'selected="selected"';
      } else {
        $selected = '';
      }
      $dropdown .= "<option $selected value='$year'>$label</option>";
    }
    $dropdown .= "</select>";
    return($dropdown);
  }
  function months_dropdown($img_month = NULL, $name='img_month') {
    $months = array(
              '0' => __('All'),
              '01' => __('January'),
              '02' => __('February'),
              '03' => __('March'),
              '04' => __('April'),
              '05' => __('May'),
              '06' => __('June'),
              '07' => __('July'),
              '08' => __('August'),
              '09' => __('September'),
              '10' => __('October'),
              '11' => __('November'),
              '12' => __('December'),
              );
    $months['current'] = __('Current');
    $dropdown = "<select id='month' name='$name'>";
    foreach($months as $month => $label) {
      if ( (string)$month == (string)$img_month) {
        $selected =  'selected="selected"';
      } else {
        $selected = '';
      }
      $dropdown .= "<option $selected value='$month'>$label</option>";
    }
    $dropdown .= "</select>";
    return $dropdown;
  }

  /* displays the form for browsing images */
  function form($args) {
    extract($args);
    $form = "<form name='imagebrowser'>
    <label for='year'>year</label>" . 
       $this->years_dropdown($year) . 
    "<label for='month'>month</label>" .
    $this->months_dropdown($month)  .
    "<label for='img_cat'>Category</label>" .
      wp_dropdown_categories("name=img_cat&hierarchical=1&selected=$category&hide_empty=1&show_option_all=All&orderby=name&echo=0") .
    "<br />
    <label for='keywords'>" .  __('keywords') . "</label> " .
    "<input type='text' name='keywords' value='$keywords' />" . 
    "<label for='sortby'>" .  __('Sort by')  ."</label>" .
    "<select name='sortby'>";
    $sort_options = array(
                  'post_title' => 'Post Title',
                  'post_date' => 'Date',
                  'post_excerpt' => 'Caption'
    );
    foreach ($sort_options as $value => $label) {
      if ($sortby == $value) {
        $selected = "selected='selected'";
      } else {
        $selected = '';
      }
      $form .= "<option value='$value' $selected>$label</option>\n";
    }
    $form .="</select>
    <select name='sortorder'>";
      $selected = ($sortorder=='ASC') ? "selected='selected'" : '';
      $form .="<option $selected value='ASC'>" .  __('Ascending') . "</option>";
      $selected = ($sortorder=='DESC') ? "selected='selected'" : '';
      $form .="<option $selected value='DESC'>" .  __('Descending') . "</option>";
      $form .="</select>
      <br />
    <label for='limit'>" .  __('items per page')  . "</label>" .
    "<input type='text' name='limit' value='$limit' size='3' />" .
    "<input type='submit' value='browse images' />
    </form>";
    return $form;
  }

  /* outputs the images */
  function display_images($images, $args, $image_count, $limit, $type='list') {
    extract($args);
    $offset = $limit * ($page-1);
    $dlwidth = $this->calc_width($cols);
    if (empty($images))
      return __("No images were found for the criteria you selected");
    $text.=$this->nav_links($image_count, $args, $offset);
    $text .= "<div id='image-browser-gallery' class='gallery'>\n";
    $col=1;
    foreach ($images as $image) {
      $text .= "
      <dl class='gallery-item' style='width:$dlwidth'>
        <dt class='gallery-icon'>
          <a href='" . get_attachment_link($image->ID) . "' " .
             "title='" . $image->post_title . "'>" . 
             wp_get_attachment_image($image->ID, $size) .
          "</a>
        </dt>
          <dd class='gallery-caption'>" . 
          $image->post_excerpt .
          "</dd>
      </dl>";
      if ($col==$cols) {
        $text.='<br style="clear:both" />';
        $col=0;
      }
      $col++;
    }
    $text .="</div>\n";
    $text.=$this->nav_links($image_count, $args, $offset);
    return($text);
  }

  /* display navigation for pagination */
  function nav_links($image_count, $args, $offset) {
    extract($args);
    $min = $offset +1;
    if ($image_count < $limit) {
      $max = $image_count;
    } elseif($offset == 0) {
      $max = $limit;
    } elseif (($offset+$limit)<$image_count) {
      $max = $offset + $limit;
    } else {
      $max = $image_count;
    }
    $args['img_year'] = $args['year'];
    $args['img_month'] = $args['month'];
    $args['img_cat'] = $args['category'];
    $oldquery = "?" . http_build_query($args);
    $num_pages = ceil($image_count/$limit);
    $text.="<div class='navigation' style='display:block;clear:both'>\n";
    $text .= "<div style='float:left'>Displaying images $min-$max of
        $image_count images</div>\n";
    if ($image_count > $limit+$offset) {
      $text .= "<div style='float:right'><span style='padding:0 .5em'><a href='" . add_query_arg('page',
      $page+1, $oldquery) . "'>" . __('Older images') . "</a></span>";
      $text .= '<select onchange="
      location.href=this[this.selectedIndex].value;">';
      for ($i=1; $i<=$num_pages; $i++) {
        if ($page == $i) {
          $selected = "selected='selected'";
        } else {
          $selected = '';
        }
        $text .= "<option $selected value='" . add_query_arg('page', $i,
        $oldquery) . "'>" .
            "Page $i</option>\n";
      }
      $text .= '</select>';
      if ($offset>0) {
        $text .= "<span style='padding:0 .5em'><a href='" . add_query_arg('page',
        $page-1, $oldquery) . "'>" . __('Newer images') . "</a></span>";
      }
      $text .="</div>\n";
    }
    $text .="</div>\n";
    return $text;
  }

  /* compute the width in percentage based on the number of columns */
  function calc_width($cols) {
    return (floor(100 * (1/$cols)). '%');
  }

  /* called when plugin is activated 
    Sets up default options
  */
  function init() {
    $defaults = array(
      'limit' => 30,
      'cols' => 3,
      'size' => 'thumbnail',
      'year' => 0,
      'month' => 0,
      'category' => 0,
      'keywords' => '',
      'sortby' => 'post_date',
      'sortorder' => 'DESC',
      'page' => 1
    );
    if (!get_option('image_browser')) {
			add_option( 'image_browser', $defaults);
		}
    if (!get_option('ImageBrowserVersion')) {
      add_option( 'ImageBrowserVersion', $ImageBrowserVersion);
		}
  }

  /* removes options from database when plugin is deleted */
  function delete() {
    delete_option('image_browser');
    delete_option('ImageBrowserVersion');
  }

  /* renames query variables so that $_GET overrides shortcode atts. Necessary
  because wordpress doesn't like me using year in the uri query string
  */
  function get_query($get) {
    if (!empty($get)) {
      $get['year'] = $get['img_year'];
      $get['month'] = $get['img_month'];
      $get['category'] = $get['img_cat'];
    }
    return($get); 
  }

  function settings() {
    $settings = new ImageBrowser();
    include('settings.php');
  }

  function admin_init() {
    register_setting('image_browser_options', 'image_browser');
  }
}


function imagebrowsershortcode($atts) {
  $browser = new ImageBrowser();
  $defaults = get_option('image_browser');
  $get = $browser->get_query($_GET);
  $args = wp_parse_args($get, shortcode_atts($defaults, $atts));
  //print_r($args);
  extract($args);
  $form = $browser->form($args);
  $image_count = $browser->get_images($args, $count=true);
  $images = $browser->get_images($args);
  $images_html = $browser->display_images($images, $args, $image_count, $limit);
  return "$form\n$images_html";
}


function image_browser_settings_page() {
	add_options_page('Image Browser', 'Image Browser', 'manage_options',
  'image-browser', array('ImageBrowser', 'settings'));
}

register_activation_hook(__FILE__, array('ImageBrowser', 'init'));
if ( function_exists('register_uninstall_hook') )
  register_uninstall_hook(__FILE__, array('ImageBrowser', 'delete'));
add_shortcode('imagebrowser', 'imagebrowsershortcode');
add_action('admin_menu', 'image_browser_settings_page');
add_action('admin_init', array('ImageBrowser', 'admin_init'));
?>
