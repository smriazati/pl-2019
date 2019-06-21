=== Image Browser ===
Contributors: robfelty
Donate link: http://blog.robfelty.com/plugins
Plugin URI: http://blog.robfelty.com/plugins
Tags: image, images, meta, search, shortcode
Requires at least: 2.8
Tested up to: 3.0.1
Stable tag: 0.2

Allows you to browse all images on your blog by date and/or category, with the
ability to also search image captions for keywords.

== Description ==

Allows you to browse all images on your blog by date and/or category, with the
ability to also search image captions for keywords.

== Installation ==

Activate the plugin. Add [imagebrowser] shortcode to a new or existing page.

== Other Notes ==
= Options =
The image browser plugin has 3 places where you can specify options. 

1. adminstration menu
2. shortcode
3. url query parameters

These are listed in ascending order of precedence, that is, url query
parameters override any shortcode parameters, which override default options
specified in the administration settings page.

Default options are listed below
`
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
`
* limit - how many images to display at one time

* cols - the number of columns for the image browser

* size - size of picture to display. Available options:
    * thumbnail (default)
    * medium
    * large
    * full

* year - retrieve images from this year
    * 0 means all years (default)
    * current displays the current year

* month - retrieve images from this month
    * 0 means all months (default)
    * current displays the current month

* category - retrieve images whose post_parent belongs to this category
    * 0 means all categories (default)

* keywords - Search image captions for these keywords
    * 0 means all categories (default)

* sortby - Field to sort by. Available options:
    * post_date (The image date) (default)
    * post_title (The image title)
    * post_excerpt (The image caption)

* sortorder - Order to sort by. Available options:
    * ASC - ascending order
    * DESC - descending order

* page - which page to show

= Example =
Here is an example shortcode to display all images from 2009 that belong to
posts with category 10
[imagebrowser year=2009 category=10]


== Screenshots ==

1. browsing images
2. Options

== Frequently Asked Questions ==

= is this plugin free? =

yes.

== CHANGELOG ==

= 0.2 (2010.08.04) =
* fixed form display

= 0.1 (2010.08.04) =
* Initial release
