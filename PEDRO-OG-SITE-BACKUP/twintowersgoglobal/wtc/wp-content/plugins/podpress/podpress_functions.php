<?php /**/ ?><?php
/*
License:
 ==============================================================================

    Copyright 2006  Dan Kuykendall  (email : dan@kuykendall.org)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-107  USA
*/

	if(!function_exists('getmicrotime')) {
		function getmicrotime() {
			list($usec, $sec) = explode(" ",microtime());
			return ((float)$usec + (float)$sec);
		}
	}

	function podPress_WPVersionCheck($input = '2.0.0') {
		GLOBAL $wp_version;
		if(substr($wp_version, 0, 12) == 'wordpress-mu') {
			return true;
		}
		return ((float)$input <= (float) $wp_version);
	}

	function podPress_iTunesLink() {
		GLOBAL $podPress;
		echo $podPress->iTunesLink();
	}

	function podPress_siteurl($noDomain = false) {
		if (!defined('PODPRESSSITEURL') || $noDomain) {
			$result = '';
			$urlparts = parse_url(get_option('siteurl'));
			if(!$noDomain) {
				if(empty($urlparts['scheme'])) {
					$urlparts['scheme'] = 'http';
				}
				$result .= $urlparts['scheme'].'://'.$_SERVER['HTTP_HOST'];
				if($urlparts['port'] != '' && $urlparts['port'] != '80') {
					$result .= ':'.$urlparts['port'];
				}
			}
			if(isset($urlparts['path'])) {
				$result .= $urlparts['path'];
			}

			if(substr($result, -1, 1) != '/') {
				$result .= '/';
			}

			if( TRUE == isset($urlparts['query']) AND '' != $urlparts['query'] ) {
				$result .= '?'.$urlparts['query'];
			}
			if( TRUE == isset($urlparts['fragment']) AND '' != $urlparts['fragment']) {
				$result .= '#'.$urlparts['fragment'];
			}
			if($noDomain) {
				return $result.'wp-content/plugins/';
			}
			define('PODPRESSSITEURL', $result.'wp-content/plugins/');
		}
		return PODPRESSSITEURL;
	}

	function podPress_url($noDomain = false) {
		if($noDomain) {
			if (!defined('PODPRESSURL')) {
				define('PODPRESSURL', podPress_siteurl($noDomain).'podpress/');
			}
			return PODPRESSURL;
		} else {
			//~ $result = get_option('siteurl');
			//~ if(substr($result, -1, 1) != '/') {
				//~ $result .= '/';
			//~ }
			//~ return $result.'wp-content/plugins/podpress/';
			return PODPRESS_URL.'/';
		}
	}

	function podPress_getFileExt($str)
	{
		$pos = strrpos($str, '.');
		$pos = $pos+1;
		return substr(strtolower($str), $pos);
	}

	function podPress_getFileName($str)
	{
		if(strrpos($str, '/')) {
			$pos = strrpos($str, '/');
			$pos = $pos+1;
			return substr($str, $pos);
		} elseif(strrpos($str, ':')) {
			$pos = strrpos($str, ':');
			$pos = $pos+1;
			return substr($str, $pos);
		} else {
			return $str;
		}
	}

	function podPress_wordspaceing($txt, $number = 5, $paddingchar = ' ') {
		$txt_array = array();
		$len = strlen($txt);
		$count=$len/$number;

		$i=0;
		while($i<=$count) {
			if($i==0) {$ib=0;} else {$ib=($i*$number)+1;}
			$txt_array[$i]=substr($txt, $ib, $number);
			$i++;
		}

		$i=0;
		$count_array=count($txt_array)-1; 
		while ($i<=$count_array) {
			if ($i==0) {$txt=$txt_array[$i].$paddingchar;} else {$txt.=''.$txt_array[$i].' ';}
			$i++;
		}
		return $txt;
	}
	
	function podPress_stringLimiter($str, $len, $snipMiddle = false)
	{
		if (strlen($str) > $len) {
			if($snipMiddle) {
				$startlen = $len / 3;
				$startlen = $startlen - 1;
				$endlen = $startlen * 2;
				$endlen = $endlen - $endlen - $endlen;
				return substr($str, 0, $startlen).'...'.substr($str, $endlen);
			} else {
				$len = $len - 3;
				return substr($str, 0, $len).'...';
			}
		} else {
			return $str;
		}
	}

	/**
	* podPress_strlimiter2 - if the input phrase is longer then maxlength then cut out character from the middle of the phrase
	*
	* @package podPress
	* @since 8.8.5 beta 3
	*
	* @param str $phrase input string
	* @param bool $maxlength [optional] - Output a trimmed down version used in Press This.
	* @param bool $abbrev [optional] - use the abbr-tag with the original string as the title element
	* @param bool $paddingchar [optional] - character(s) which should symbolize the shortend string / placed in the middle of the shortend string
	* @param bool $classname [optional] - name(s) of the CSS class(es) of the abbr-tag
	*
	* @return str phrase with max. length
	*/
	function podPress_strlimiter2($phrase, $maxlength = 25, $abbrev = FALSE, $paddingchar = ' ... ', $classname = 'podpress_abbr') {
		$len = strlen($phrase);
		$maxlen = ($maxlength-strlen($paddingchar));
		if ( $len > $maxlen ) {
			$part1_len = floor($maxlen/2);
			$part1 = substr($phrase, 0,  $part1_len);
			$part2_len = ceil($maxlen/2);
			$part2 = substr($phrase, -$part2_len, $len);
			if ($abbrev == TRUE) {
				if ( Trim($classname) != '' ) {
					return '<span class="'.$classname.'" title="'.attribute_escape(str_replace('"', '\'', $phrase)).'">' . $part1 . $paddingchar . $part2 . '</span>';
				} else {
					return '<span title="'.attribute_escape(str_replace('"', '\'', $phrase)).'">' . $part1 . $paddingchar . $part2 . '</span>';
				}
			} else {
				return $part1 . $paddingchar. $part2;
			}
		} else {
			return $phrase;
		}
	}	
		
	if(!function_exists('html_print_r')) {
		function html_print_r($v, $n = '', $ret = false) {
			if($ret) {
				ob_start();
			}	
			echo $n.'<pre>';
			print_r($v);
			echo '</pre>';
			if($ret) {
				$result = ob_get_contents();
				ob_end_clean();
				return $result;
			}
		}
	}

	if(!function_exists('comment_print_r')) {
		function comment_print_r($v, $n = '', $ret = false) {
			$result = "<!-- \n";
			$result .= html_print_r($v, $n, true);
			$result .= " -->\n";
			if($ret) {
				return $result;
			}
			echo $result;
		}
	}

	if(!function_exists('maybe_unserialize')) {
		function maybe_unserialize($original, $ss = false) {
			if($ss) {
				$original = stripslashes($original);
			}
			if ( false !== $gm = @ unserialize($original) ) {
				return $gm;
			} else {
				return $original;
			}
		}
	}

	if(!function_exists('isBase64')) {
		function isBase64($str)
		{
			$_tmp=preg_replace("/[^A-Z0-9\+\/\=]/i",'',$str);
			return (strlen($_tmp) % 4 == 0 ) ? true : false;
		}
	}

	function podPress_mimetypes($ext, $mp4_type = 'audio') {
		$ext = strtolower($ext);
		$ext_list = array (
			'jpg' => 'image/jpeg',
			'jpeg' => 'image/jpeg',
			'jpe' => 'image/jpeg',
			'gif' => 'image/gif',
			'png' => 'image/png',
			'bmp' => 'image/bmp',
			'tif' => 'image/tiff',
			'tiff' => 'image/tiff',
			'ico' => 'image/x-icon',
			'flv' => 'video/flv',
			'asf' => 'video/asf',
			'wmv' => 'video/wmv',
			'asx' => 'video/asf',
			'wax' => 'video/asf',
			'wmx' => 'video/asf',
			'avi' => 'video/avi',
			'qt' => 'video/quicktime',
			'mov' => 'video/quicktime',
			'm4v' => 'video/x-m4v',
			'mpeg' => 'video/mpeg',
			'mpg' => 'video/mpeg',
			'mpe' => 'video/mpeg',
			'txt' => 'text/plain',
			'c' => 'text/plain',
			'cc' => 'text/plain',
			'h' => 'text/plain',
			'rtx' => 'text/richtext',
			'css' => 'text/css',
			'htm' => 'text/html',
			'html' => 'text/html',
			'mp3' => 'audio/mpeg',
			'mp4' => $mp4_type.'/mpeg',
			'm4a' => 'audio/x-m4a',
			'aa' => 'audio/audible',
			'ra' => 'audio/x-realaudio',
			'ram' => 'audio/x-realaudio',
			'wav' => 'audio/wav',
			'ogg' => 'audio/ogg',
			'ogv' => 'video/ogg',
			'mid' => 'audio/midi',
			'midi' => 'audio/midi',
			'wma' => 'audio/wma',
			'rtf' => 'application/rtf',
			'js' => 'application/javascript',
			'pdf' => 'application/pdf',
			'doc' => 'application/msword',
			'pot' => 'application/vnd.ms-powerpoint',
			'pps' => 'application/vnd.ms-powerpoint',
			'ppt' => 'application/vnd.ms-powerpoint',
			'wri' => 'application/vnd.ms-write',
			'xla' => 'application/vnd.ms-excel',
			'xls' => 'application/vnd.ms-excel',
			'xlt' => 'application/vnd.ms-excel',
			'xlw' => 'application/vnd.ms-excel',
			'mdb' => 'application/vnd.ms-access',
			'mpp' => 'application/vnd.ms-project',
			'swf' => 'application/x-shockwave-flash',
			'class' => 'application/java',
			'tar' => 'application/x-tar',
			'zip' => 'application/zip',
			'gz' => 'application/x-gzip',
			'gzip' => 'application/x-gzip',
			'torrent' => 'application/x-bittorrent',
			'exe' => 'application/x-msdownload'
		);
		if(!isset($ext_list[$ext])) {
			return 'application/unknown';
		}
		return $ext_list[$ext];
	}

	function podPress_maxMemory() {
		$max = ini_get('memory_limit');

		if (preg_match('/^([\d\.]+)([gmk])?$/i', $max, $m)) {
			$value = $m[1];
			if (isset($m[2])) {
				switch(strtolower($m[2])) {
					case 'g': $value *= 1024;  # fallthrough
					case 'm': $value *= 1024;  # fallthrough
					case 'k': $value *= 1024; break;
					default: $value = 2048000;
				}
			}
			$max = $value;
		} else {
		  $max = 2048000;
		}
		return $max/2;
	}
	
	/**************************************************************/
	/* Functions for supporting the widgets */
	/**************************************************************/
	/* for WP < 2.8 only */
	function podPress_loadWidgets () {
		global $wp_version;
		
		if ( !function_exists('register_sidebar_widget') || !function_exists('register_widget_control') ) {
			return;
		}
		
		if (TRUE == version_compare($wp_version, '2.2', '>=')) {
			// Feed Buttons widget
			$widget_ops = array(
				'classname' => 'podpress_feedbuttons', 
				'description' => __('Shows buttons for the podcast feeds in the sidebar', 'podpress')
			);
			$control_ops = array('width' => 400, 'height' => 300,	'id_base' => 'podpressfeedbuttons');
			$id = $control_ops['id_base'];
			wp_register_sidebar_widget($id, __('podPress - Feed Buttons','podpress'), 'podPress_feedButtons', $widget_ops);
			wp_register_widget_control($id, __('podPress - Feed Buttons','podpress'), 'podPress_feedButtons_control', $control_ops);
			
			// XSPF Player widget
			$widget_ops = array(
				'classname' => 'podpress_xspfplayer', 
				'description' => __('Shows a XSPF Player in the sidebar which uses e.g. the XSPF playlist of your podcast episodes', 'podpress')
			);
			$control_ops = array('width' => 400, 'height' => 300,	'id_base' => 'podpressxspfPlayer');
			$id = $control_ops['id_base'];
			wp_register_sidebar_widget($id, __('podPress - XSPF Player','podpress'), 'podPress_xspfPlayer', $widget_ops);
			wp_register_widget_control($id, __('podPress - XSPF Player','podpress'), 'podPress_xspfPlayer_control', $control_ops);
		} else {
			// Feed Buttons widget
			register_sidebar_widget(array('podPress - Feed Buttons', 'widgets'), 'podPress_feedButtons', $widget_ops);
			register_widget_control(array('podPress - Feed Buttons', 'widgets'), 'podPress_feedButtons_control', 400, 300);
			
			// XSPF Player widget
			register_sidebar_widget(array('podPress - XSPF Player', 'widgets'), 'podPress_xspfPlayer');
			register_widget_control(array('podPress - XSPF Player', 'widgets'), 'podPress_xspfPlayer_control', 400, 300);
		}

	}

	/* for WP < 2.8 only */
	function podPress_feedButtons_control() {
		GLOBAL $podPress, $wp_version, $wpdb;
		$options = get_option('widget_podPressFeedButtons');
		$newoptions = $options;
		if ( isset($_POST['podPressFeedButtons-submit']) ) {
			$newoptions['blog'] = isset($_POST['podPressFeedButtons-posts']);
			$newoptions['comments'] = isset($_POST['podPressFeedButtons-comments']);
			$newoptions['entries-atom'] = isset($_POST['podPressFeedButtons-entries-atom']);
			$newoptions['comments-atom'] = isset($_POST['podPressFeedButtons-comments-atom']);
			$newoptions['podcast'] = isset($_POST['podPressFeedButtons-podcast']);
			$newoptions['enhancedpodcast'] = isset($_POST['podPressFeedButtons-enhancedpodcast']);
			$newoptions['torrent'] = isset($_POST['podPressFeedButtons-torrent']);
			$newoptions['itunes'] = isset($_POST['podPressFeedButtons-itunes']);
			// iscifi new option for itunes protocol
			$newoptions['iprot'] = isset($_POST['podPressItunesProtocol-iprot']);
			$blog_charset = get_bloginfo('charset');
			$newoptions['title'] = htmlspecialchars(strip_tags(trim($_POST['podPressFeedButtons-title'])), ENT_QUOTES, $blog_charset);
			$newoptions['buttons-or-text'] = $_POST['podPressFeedButtons-buttons-or-text'];
			$newoptions['catcast'] = $_POST['podPressFeedButtons-catcast'];
		}
		if ( $options != $newoptions ) {
			$options = $newoptions;
			update_option('widget_podPressFeedButtons', $options);
		}
		if(!isset($options['blog'])) {
			$options['blog'] = false;
		}
		if(!isset($options['comments'])) {
			$options['comments'] = false;
		}
		if(!isset($options['entries-atom'])) {
			$options['entries-atom'] = false;
		}
		if(!isset($options['comments-atom'])) {
			$options['comments-atom'] = false;
		}
		if(!isset($options['podcast'])) {
			$options['podcast'] = true;
		}
		if(!isset($options['enhancedpodcast'])) {
			$options['enhancedpodcast'] = false;
		}
		if(!isset($options['torrent'])) {
			$options['torrent'] = false;
		}
		if(!isset($options['itunes'])) {
			$options['itunes'] = true;
		}
		if (!isset($options['iprot'])) {
			$options['iprot'] = false;
		}
		if (!isset($options['buttons-or-text'])) {
			$options['buttons-or-text'] = 'buttons';
		}

		$blog = $options['blog'] ? 'checked="checked"' : '';
		$comments = $options['comments'] ? 'checked="checked"' : '';
		$entries_atom = $options['entries-atom'] ? 'checked="checked"' : '';
		$comments_atom = $options['comments-atom'] ? 'checked="checked"' : '';
		$podcast = $options['podcast'] ? 'checked="checked"' : '';
		$enhpodcast = $options['enhancedpodcast'] ? 'checked="checked"' : '';
		$torrent = $options['torrent'] ? 'checked="checked"' : '';
		$itunes  = $options['itunes'] ? 'checked="checked"' : '';
		$iprot   = $options['iprot'] ? 'checked="checked"' :'';
		if ( 'text' == $options['buttons-or-text'] ) {
			$text = 'checked="checked"';
			$buttons = '';
		} else {
			$text = '';
			$buttons = 'checked="checked"';
		}
		
		if(!isset($options['title'])) {
			$options['title'] = __('Podcast Feeds', 'podpress');
		}
		$title = attribute_escape(stripslashes($options['title']));
		?>
		<p class="podpress_widget_settings_legend"><?php _e('A podPress Widget', 'podpress'); ?></p>
		<p><label for="podPressFeedButtons-title"><?php _e('Title:', 'podpress'); ?></label> <input class="podpress_widget_settings_title" id="podPressFeedButtons-title" name="podPressFeedButtons-title" type="text" value="<?php echo $title; ?>" /></p>
		<p><?php _e('Show the buttons for the following feeds:', 'podpress'); ?></p>
			<p class="podpress_widget_settings_row">
			<label for="podPressFeedButtons-itunes"><?php _e('Show iTunes button', 'podpress'); ?></label> <input class="checkbox" type="checkbox" <?php echo $itunes; ?> id="podPressFeedButtons-itunes" name="podPressFeedButtons-itunes" /><br />
			<label for="podPressFeedButtons-iprot"><?php _e('Use iTunes protocol for URL', 'podpress'); ?> <?php _e('(itpc://)', 'podpress'); ?></label> <input class="checkbox" type="checkbox" <?php echo $iprot; ?> id="podPressFeedButtons-iprot" name="podPressItunesProtocol-iprot" /><br /><span class="nonessential"><?php _e('The user subscribes immediatly with the click. Otherwise the iTunes Store page of the podcast will be displayed first and the user can subscribe manually.', 'podpress'); ?></span>
			</p>
		<?php
		if ( version_compare( $wp_version, '2.1', '>=' ) ) { // ntm: the add_feed() functions exists since WP 2.1 and widgets are probably possible in earlier WP versions with a plugin. ?>
			<p class="podpress_widget_settings_row"><label for="podPressFeedButtons-podcast"><?php _e('Podcast Feed', 'podpress'); ?></label> <input class="checkbox" type="checkbox" <?php echo $podcast; ?> id="podPressFeedButtons-podcast" name="podPressFeedButtons-podcast" /></p>
			<p class="podpress_widget_settings_row"><label for="podPressFeedButtons-enhancedpodcast"><?php _e('Enhanced Podcast Feed', 'podpress'); ?></label> <input class="checkbox" type="checkbox" <?php echo $enhpodcast; ?> id="podPressFeedButtons-enhancedpodcast" name="podPressFeedButtons-enhancedpodcast" /><br /><span class="nonessential"><?php _e('This is an ATOM feed which contains only posts with attached .m4a or .m4v files.', 'podpress'); ?></span></p>		
			<p class="podpress_widget_settings_row"><label for="podPressFeedButtons-torrent"><?php _e('Torrent Feed', 'podpress'); ?></label> <input class="checkbox" type="checkbox" <?php echo $torrent; ?> id="podPressFeedButtons-torrent" name="podPressFeedButtons-torrent" /><br /><span class="nonessential"><?php _e('This is an ATOM feed which contains only posts with attached .torrent files.', 'podpress'); ?></span></p>
			<?php 
		} ?>
		<?php
		// ntm: If someone changes the podcastFeedURL then has to save the widgets settings again to let the button in the sidebar disappear. I think that is not explained by a help text and not very intuitively
		// I think it is a good idea to let the podcaster decide which buttons should be visible
		// if ($podPress->settings['podcastFeedURL'] != get_bloginfo('rss2_url')) {
			?>
			<p class="podpress_widget_settings_row"><label for="podPressFeedButtons-posts"><?php _e('Entries RSS Feed', 'podpress'); ?></label> <input class="checkbox" type="checkbox" <?php echo $blog; ?> id="podPressFeedButtons-posts" name="podPressFeedButtons-posts" /></p>
			<?php
		// }
		?>
			<p class="podpress_widget_settings_row"><label for="podPressFeedButtons-comments"><?php _e('Comments RSS Feed', 'podpress'); ?></label> <input class="checkbox" type="checkbox" <?php echo $comments; ?> id="podPressFeedButtons-comments" name="podPressFeedButtons-comments" /></p>
			<p class="podpress_widget_settings_row"><label for="podPressFeedButtons-entries-atom"><?php _e('Entries ATOM Feed', 'podpress'); ?></label> <input class="checkbox" type="checkbox" <?php echo $entries_atom; ?> id="podPressFeedButtons-entries-atom" name="podPressFeedButtons-entries-atom" /></p>
			<p class="podpress_widget_settings_row"><label for="podPressFeedButtons-comments-atom"><?php _e('Comments ATOM Feed', 'podpress'); ?></label> <input class="checkbox" type="checkbox" <?php echo $comments_atom; ?> id="podPressFeedButtons-comments-atom" name="podPressFeedButtons-comments-atom" /></p>
			<?php
			$query_string = 'SELECT option_name, option_value FROM '.$wpdb->prefix.'options WHERE INSTR(option_name, "podPress_category_")';
			$category_feeds = $wpdb->get_results($query_string);			
			if ( isset($category_feeds) AND FALSE == empty($category_feeds) ) {
				foreach ($category_feeds as $feed_options) {
					$feed = maybe_unserialize($feed_options->option_value);
					if ( isset($feed['categoryCasting']) AND 'true' == $feed['categoryCasting'] ) {
						$cat_id = end(explode('_', $feed_options->option_name));
						$checked = $options['catcast'][$cat_id] ? 'checked="checked"' :'';
						echo '<p class="podpress_widget_settings_row"><label for="podPressFeedButtons-catcast_'.$cat_id.'">'.sprintf(__('Category "%1$s" RSS Feed', 'podpress'), get_cat_name($cat_id)).'</label> <input type="checkbox" '.$checked.' id="podPressFeedButtons-catcast_'.$cat_id.'" name="podPressFeedButtons-catcast['.$cat_id.']" /></p>'."\n";
					}
				}
			}
			?>			
			<p><?php _e('Show buttons or text?', 'podpress'); ?></p>
			<p class="podpress_widget_settings_row"><label for="podPressFeedButtons-buttons"><?php _e('Buttons', 'podpress'); ?></label> <input type="radio" <?php echo $buttons; ?> value="buttons" id="podPressFeedButtons-buttons" name="podPressFeedButtons-buttons-or-text" /> <input type="radio" <?php echo $text; ?> value="text" id="podPressFeedButtons-text" name="podPressFeedButtons-buttons-or-text" /> <label for="podPressFeedButtons-text"><?php _e('Text', 'podpress'); ?></label></p>
			<input type="hidden" id="podPressFeedButtons-submit" name="podPressFeedButtons-submit" value="1" />
		<?php
	}

	/* for WP < 2.8 only */
	function podPress_feedButtons ($args) {
		GLOBAL $podPress, $wp_version;
		extract($args);
		$options = get_option('widget_podPressFeedButtons');
		if ( version_compare( $wp_version, '2.2', '>=' ) ) { // the rss.png is in wp_includes since WP 2.2 (this is only necessary until the required  WP version will be changed to e.g 2.3)
			$feed_icon = '<img src="'.get_option('siteurl') . '/' . WPINC . '/images/rss.png" class="podpress_feed_icon" alt="" />';
		} else {
			$feed_icon = apply_filters('podpress_legacy_support_feed_icon', '');
		}
		if(!isset($options['title'])) {
			$options['title'] = __('Podcast Feeds', 'podpress');
		} else {
			$options['title'] = stripslashes($options['title']);
		}
		if(!isset($options['blog'])) {
			$options['blog'] = false;
		}
		if(!isset($options['comments'])) {
			$options['comments'] = false;
		}
		if(!isset($options['entries-atom'])) {
			$options['entries-atom'] = false;
		}
		if(!isset($options['comments-atom'])) {
			$options['comments-atom'] = false;
		}
		if(!isset($options['podcast'])) {
			$options['podcast'] = true;
		}
		if(!isset($options['enhancedpodcast'])) {
			$options['enhancedpodcast'] = false;
		}
		if(!isset($options['torrent'])) {
			$options['torrent'] = false;
		}
		if(!isset($options['itunes'])) {
			$options['itunes'] = true;
		}
		if (!isset($options['iprot'])) {
			$options['iprot'] = false;
		}
		if (!isset($options['buttons-or-text'])) {
			$options['buttons-or-text'] = 'buttons';
		}

		echo $before_widget;
		echo $before_title . $options['title'] . $after_title;
		echo '<ul class="podpress_feed_buttons_list">'."\n";
		switch ($options['buttons-or-text']) {
			default:
			case 'buttons' :
				if ($options['itunes']) {
					// for more info: http://www.apple.com/itunes/podcasts/specs.html#linking
					if ($options['iprot'] ) {
						echo ' <li><a href="itpc://'.preg_replace('/^https?:\/\//i', '', $podPress->settings['podcastFeedURL']).'"';
					} else {
						echo ' <li><a href="http://www.itunes.com/podcast?id='.$podPress->settings['iTunes']['FeedID'].'"';
					}
					echo ' title="'.__('Subscribe to the Podcast Feed with iTunes', 'podpress').'"><img src="'.podPress_url().'images/button_itunes.png" class="podpress_feed_buttons" alt="'.__('Subscribe with iTunes', 'podpress').'" /></a></li>'."\n";
				}
				if($options['podcast']) {
					echo '	<li><a href="'.get_feed_link('podcast').'" title="'.__('Subscribe to the Podcast RSS Feed with any other podcatcher', 'podpress').'"><img src="'.podPress_url().'images/button_rss_podcast.png" class="podpress_feed_buttons" alt="'.__('Subscribe to the Podcast RSS Feed', 'podpress').'" /></a></li>'."\n";
				}
				if($options['enhancedpodcast']) {
					echo '	<li><a href="'.get_feed_link('enhancedpodcast').'" title="'.__('Subscribe to the Enhanced Podcast Feed with a podcatcher which can play .m4a or .m4v files e.g. iTunes', 'podpress').'"><img src="'.podPress_url().'images/feed-enhpodcast.png" class="podpress_feed_buttons" alt="'.__('Subscribe to the Enhanced Podcast Feed', 'podpress').'" /></a></li>'."\n";
				}
				if($options['torrent']) {
					echo '	<li><a href="'.get_feed_link('torrent').'" title="'.__('Subscribe to the Torrent Feed with e.g. Miro or Vluze', 'podpress').'"><img src="'.podPress_url().'images/feed-torrent.png" class="podpress_feed_buttons" alt="'.__('Subscribe to the Torrent Feed', 'podpress').'" /></a></li>'."\n";
				}
				if($options['blog']) {
					echo '	<li><a href="'.get_bloginfo('rss2_url').'" title="'.__('Subscribe to the main RSS Feed with any feed reader', 'podpress').'"><img src="'.podPress_url().'images/button_rss_blog.png" class="podpress_feed_buttons" alt="'.__('Subscribe to the RSS Feed', 'podpress').'" /></a></li>'."\n";
				}
				if($options['comments']) {
					echo '	<li><a href="'.get_bloginfo('comments_rss2_url').'" title="'.__('Subscribe to the comments RSS Feed with any feed reader', 'podpress').'"><img src="'.podPress_url().'images/button_comments_rss_blog.png" class="podpress_feed_buttons" alt="'.__('Subscribe to the comments RSS Feed', 'podpress').'" /></a></li>'."\n";
				}
				if($options['entries-atom']) {
					echo '	<li><a href="'.get_bloginfo('atom_url').'" title="'.__('Subscribe to the main ATOM Feed with any feed reader', 'podpress').'"><img src="'.podPress_url().'images/button_atom_blog.png" class="podpress_feed_buttons" alt="'.__('Subscribe to the ATOM Feed', 'podpress').'" /></a></li>'."\n";
				}
				if($options['comments-atom']) {
					echo '	<li><a href="'.get_bloginfo('comments_atom_url').'" title="'.__('Subscribe to the comments ATOM Feed with any feed reader', 'podpress').'"><img src="'.podPress_url().'images/button_comments_atom_blog.png" class="podpress_feed_buttons" alt="'.__('Subscribe to the comments ATOM Feed', 'podpress').'" /></a></li>'."\n";
				}
				if ( is_array($options['catcast']) AND FALSE == empty($options['catcast']) ) {
					foreach ($options['catcast'] as $cat_id => $value) {
						if (TRUE == version_compare($wp_version, '2.9.3','>') ) {
							$cat_feed_link = get_term_feed_link($cat_id);
						} elseif ( TRUE == version_compare($wp_version, '2.9.3','<=') AND TRUE == version_compare($wp_version, '2.4','>') ) {
							$cat_feed_link = get_category_feed_link($cat_id);
						} else {
							$cat_feed_link = get_option('siteurl').'/?feed=rss2&cat='.$cat_id;
						}
						echo '	<li><a href="'.$cat_feed_link.'" title="'.__('Subscribe to this Category RSS Feed with any feed reader', 'podpress').'">'.$feed_icon.' '.sprintf(__('Category "%1$s" RSS Feed', 'podpress'), get_cat_name($cat_id)).'</a></li>'."\n";
					}
				}
			break;
			case 'text' :
				if ($options['itunes']) {
					// for more info: http://www.apple.com/itunes/podcasts/specs.html#linking
					if ($options['iprot'] ) {
						echo ' <li><a href="itpc://'.preg_replace('/^https?:\/\//i', '', $podPress->settings['podcastFeedURL']).'"';
					} else {
						echo ' <li><a href="http://www.itunes.com/podcast?id='.$podPress->settings['iTunes']['FeedID'].'"';
					}
					echo ' title="'.__('Subscribe to the Podcast Feed with iTunes', 'podpress').'">'.$feed_icon.' '.__('Subscribe with iTunes', 'podpress').'</a></li>'."\n";
				}
				if($options['podcast']) {
					echo '	<li><a href="'.get_feed_link('podcast').'" title="'.__('Subscribe to the Podcast RSS Feed with any other podcatcher', 'podpress').'">'.$feed_icon.' '.__('Podcast Feed', 'podpress').'</a></li>'."\n";
				}
				if($options['enhancedpodcast']) {
					echo '	<li><a href="'.get_feed_link('enhancedpodcast').'" title="'.__('Subscribe to the Enhanced Podcast Feed with a podcatcher which can play .m4a or .m4v files e.g. iTunes', 'podpress').'">'.$feed_icon.' '.__('Enhanced Podcast Feed', 'podpress').'</a></li>'."\n";
				}
				if($options['torrent']) {
					echo '	<li><a href="'.get_feed_link('torrent').'" title="'.__('Subscribe to the Torrent Feed with e.g. Miro or Vluze', 'podpress').'">'.$feed_icon.' '.__('Torrent Feed', 'podpress').'</a></li>'."\n";
				}
				if($options['blog']) {
					echo '	<li><a href="'.get_bloginfo('rss2_url').'" title="'.__('Subscribe to the main RSS Feed with any feed reader', 'podpress').'">'.$feed_icon.' '.__('Entries RSS Feed', 'podpress').'</a></li>'."\n";
				}
				if($options['comments']) {
					echo '	<li><a href="'.get_bloginfo('comments_rss2_url').'" title="'.__('Subscribe to the comments RSS Feed with any feed reader', 'podpress').'">'.$feed_icon.' '.__('Comments RSS Feed', 'podpress').'</a></li>'."\n";
				}
				if($options['entries-atom']) {
					echo '	<li><a href="'.get_bloginfo('atom_url').'" title="'.__('Subscribe to the main ATOM Feed with any feed reader', 'podpress').'">'.$feed_icon.' '.__('Entries ATOM Feed', 'podpress').'</a></li>'."\n";
				}
				if($options['comments-atom']) {
					echo '	<li><a href="'.get_bloginfo('comments_atom_url').'" title="'.__('Subscribe to the comments ATOM Feed with any feed reader', 'podpress').'">'.$feed_icon.' '.__('Comments ATOM Feed', 'podpress').'</a></li>'."\n";
				}
				if ( is_array($options['catcast']) AND FALSE == empty($options['catcast']) ) {
					foreach ($options['catcast'] as $cat_id => $value) {
						if (TRUE == version_compare($wp_version, '2.9.3','>') ) {
							$cat_feed_link = get_term_feed_link($cat_id);
						} elseif ( TRUE == version_compare($wp_version, '2.9.3','<=') AND TRUE == version_compare($wp_version, '2.4','>') ) {
							$cat_feed_link = get_category_feed_link($cat_id);
						} else {
							$cat_feed_link = get_option('siteurl').'/?feed=rss2&cat='.$cat_id;
						}
						echo '	<li><a href="'.$cat_feed_link.'" title="'.__('Subscribe to this Category RSS Feed with any feed reader', 'podpress').'">'.$feed_icon.' '.sprintf(__('Category "%1$s" RSS Feed', 'podpress'), get_cat_name($cat_id)).'</a></li>'."\n";
					}
				}
			break;
		}
		echo "</ul>\n";
		echo $after_widget;
	}

	/* for WP < 2.8 only */
	function podPress_xspfPlayer_control() {
		global $blog_id;
		static $updated = false; // Whether or not we have already updated the data after a POST submit
		$xspf_width_const_msg = '';
		$xspf_height_const_msg = '';
		$xspf_heightslim_const_msg = '';
		$xspf_width_readonly = '';
		$xspf_height_readonly = '';
		$xspf_heightslim_readonly = '';
		$options = get_option('widget_podPressXspfPlayer');
		$blog_charset = get_bloginfo('charset');
		// We need to update the data
		if ( !$updated && !empty($_POST['podPressXspfPlayer-submit']) ) {
			$options['title'] = htmlspecialchars(strip_tags(trim($_POST['podPressXspfPlayer-title'])), ENT_QUOTES, $blog_charset);
			$options['useSlimPlayer'] = isset($_POST['podPressXspfPlayer-useSlimPlayer']);
			$options['PlayerWidth'] = intval(preg_replace('/[^0-9]/', '',$_POST['podPressXspfPlayer-width'])); // only numeric values are allowed
			$options['PlayerHeight'] = intval(preg_replace('/[^0-9]/', '',$_POST['height'])); // only numeric values are allowed
			$options['useSlimPlayer'] = isset($_POST['useSlimPlayer']);
			$options['SlimPlayerHeight'] = intval(preg_replace('/[^0-9]/', '',$_POST['heightslim'])); // only numeric values are allowed
			if ( TRUE == defined('PODPRESS_XSPF_PLAYER_WIDTH_'.$blog_id) ) {
				$options['PlayerWidth'] = intval(constant('PODPRESS_XSPF_PLAYER_WIDTH_'.$blog_id));
			}
			if ( 170 > intval($options['PlayerWidth']) ) {
				$options['PlayerWidth'] = 170; // min width
			}			
			if ( TRUE == defined('PODPRESS_XSPF_PLAYER_HEIGHT_'.$blog_id) ) {
				$options['PlayerHeight'] = intval(constant('PODPRESS_XSPF_PLAYER_HEIGHT_'.$blog_id));
			}
			if ( 210 > intval($options['PlayerHeight']) ) {
				$options['PlayerHeight'] = 210; // min height
			}
			if ( TRUE == defined('PODPRESS_XSPF_SLIM_PLAYER_HEIGHT_'.$blog_id) ) {
				$options['SlimPlayerHeight'] = intval(constant('PODPRESS_XSPF_SLIM_PLAYER_HEIGHT_'.$blog_id));
			}
			if ( 30 > intval($options['SlimPlayerHeight']) ) {
				$options['SlimPlayerHeight'] = 30; // min height slim
			}
			// If this CONST is defined then the skin file should not be overwritten by saving the widgets settings. In this case the height have to come from one of the height CONSTs.
			if ( FALSE == defined('PODPRESS_XSPF_PLAYER_USE_CUSTOM_SKINFILE_'.$blog_id) OR FALSE === constant('PODPRESS_XSPF_PLAYER_USE_CUSTOM_SKINFILE_'.$blog_id) ) { 
				if (TRUE === $options['useSlimPlayer']) {
					podpress_xspf_jukebox_slim_skin_xml($options['PlayerWidth'], $options['SlimPlayerHeight'], $blog_id);
				} else {
					podpress_xspf_jukebox_skin_xml($options['PlayerWidth'], $options['PlayerHeight'], $blog_id);
				}
			}
			if ( isset($_POST['podPressXspfPlayer-xspf_use_custom_playlist']) ) {
				$options['xspf_use_custom_playlist'] = TRUE;
			} else {
				$options['xspf_use_custom_playlist'] = FALSE;
			}
			$options['xspf_custom_playlist_url'] = clean_url($_POST['podPressXspfPlayer-xspf_custom_playlist_url'], array('http', 'https'), 'db');
			update_option('widget_podPressXspfPlayer', $options);
			$updated = true; // So that we don't go through this more than once
		} else {
			if ( TRUE == defined('PODPRESS_XSPF_PLAYER_WIDTH_'.$blog_id) ) {
				$options['PlayerWidth'] = intval(constant('PODPRESS_XSPF_PLAYER_WIDTH_'.$blog_id));
				$xspf_width_const_msg = '<br /><span class="message updated podpress_msg">'.sprintf(__('<strong>Notice:</strong> The value is currently defined via the constant %1$s.', 'podpress'), 'PODPRESS_XSPF_PLAYER_WIDTH_'.$blog_id).'</span>';
				$xspf_width_readonly = ' readonly="readonly"';
			}
			if ( 170 > intval($options['PlayerWidth']) ) {
				$options['PlayerWidth'] = 170; // min width
			}			
			if ( TRUE == defined('PODPRESS_XSPF_PLAYER_HEIGHT_'.$blog_id) ) {
				$options['PlayerHeight'] = intval(constant('PODPRESS_XSPF_PLAYER_HEIGHT_'.$blog_id));
				$xspf_height_const_msg = '<br /><span class="message updated podpress_msg">'.sprintf(__('<strong>Notice:</strong> The value is currently defined via the constant %1$s.', 'podpress'), 'PODPRESS_XSPF_PLAYER_HEIGHT_'.$blog_id).'</span>';
				$xspf_height_readonly = ' readonly="readonly"';
			}
			if ( 210 > intval($options['PlayerHeight']) ) {
				$options['PlayerHeight'] = 210; // min height
			}
			if ( TRUE == defined('PODPRESS_XSPF_SLIM_PLAYER_HEIGHT_'.$blog_id) ) {
				$options['SlimPlayerHeight'] = intval(constant('PODPRESS_XSPF_SLIM_PLAYER_HEIGHT_'.$blog_id));
				$xspf_heightslim_const_msg = '<br /><span class="message updated podpress_msg">'.sprintf(__('<strong>Notice:</strong> The value is currently defined via the constant %1$s.', 'podpress'), 'PODPRESS_XSPF_SLIM_PLAYER_HEIGHT_'.$blog_id).'</span>';
				$xspf_heightslim_readonly = ' readonly="readonly"';
			}
			if ( 30 > intval($options['SlimPlayerHeight']) ) {
				$options['SlimPlayerHeight'] = 30; // min height slim
			}		
		}
		if (!isset($options['title'])) {
			$options['title'] = __('Podcast Player', 'podpress');
		}
		$title = attribute_escape(stripslashes($options['title']));
		$useSlimPlayer = $options['useSlimPlayer'] ? ' checked="checked"' : '';
		?>
		<p class="podpress_widget_settings_legend"><?php _e('A podPress Widget', 'podpress'); ?></p>
		<p><label for="podPressXspfPlayer-title"><?php _e('Title:'); ?></label> <input type="text" id="podPressXspfPlayer-title" name="podPressXspfPlayer-title" value="<?php echo $title; ?>" class="podpress_widget_settings_title" /></p>
		<p><label for="podPressXspfPlayer-width"><?php _e('Player Width:', 'podpress'); ?></label> <input type="text" id="podPressXspfPlayer-width" name="podPressXspfPlayer-width" maxlength="3" value="<?php echo $options['PlayerWidth']; ?>"<?php echo $xspf_width_readonly; ?> class="podpress_widget_settings_3digits" /> <?php _e('px', 'podpress'); echo $xspf_width_const_msg; ?></p>
		<p><label for="podPressXspfPlayer-height"><?php _e('Player Height:', 'podpress'); ?></label> <input type="text" id="podPressXspfPlayer-width" name="podPressXspfPlayer-height" maxlength="3" value="<?php echo $options['PlayerHeight']; ?>"<?php echo $xspf_height_readonly; ?> class="podpress_widget_settings_3digits" /> <?php _e('px', 'podpress'); echo $xspf_height_const_msg; ?></p>
		<p><label for="podPressXspfPlayer-useSlimPlayer"><?php _e('Use Slim Player', 'podpress'); ?></label> <input type="checkbox" id="podPressXspfPlayer-useSlimPlayer" name="podPressXspfPlayer-useSlimPlayer"<?php echo $useSlimPlayer; ?> class="checkbox" /></p>
		<p><label for="podPressXspfPlayer-heightslim"><?php _e('Slim Player Height:', 'podpress'); ?></label> <input type="text" id="podPressXspfPlayer-width" name="podPressXspfPlayer-heightslim" maxlength="2" value="<?php echo $options['SlimPlayerHeight']; ?>"<?php echo $xspf_heightslim_readonly; ?> class="podpress_widget_settings_3digits" /> <?php _e('px', 'podpress'); echo $xspf_heightslim_const_msg; ?></p>
		<?php
		if ( defined('PODPRESS_CUSTOM_XSPF_URL_'.$blog_id) AND '' !== constant('PODPRESS_CUSTOM_XSPF_URL_'.$blog_id) ) {
			$xspf_custom_playlist_url_readonly = ' readonly="readonly"';
			$xspf_custom_playlist_url = attribute_escape(constant('PODPRESS_CUSTOM_XSPF_URL_'.$blog_id));
			$xspf_use_custom_playlist_disabled = ' disabled="disabled"';
			$xspf_use_custom_playlist_checked = ' checked="checked"';
			$xspf_custom_playlist_msg = '<p class="message updated podpress_msg">'.sprintf(__('<strong>Notice:</strong> The custom playlist URL is currently defined via the constant PODPRESS_CUSTOM_XSPF_URL_%1$s and this constant overwrites the custom XSPF playlist settings.', 'podpress'), $blog_id).'</p>';
		} else {
			$xspf_custom_playlist_url_readonly = '';
			$xspf_custom_playlist_url = attribute_escape($options['xspf_custom_playlist_url']);
			$xspf_use_custom_playlist_disabled = '';
			if ( TRUE === $options['xspf_use_custom_playlist'] ) {
				$xspf_use_custom_playlist_checked = ' checked="checked"';
			} else {
				$xspf_use_custom_playlist_checked = '';
			}
			$xspf_custom_playlist_msg = '';
		}
		echo '<p><label for="xspf_use_custom_playlist">'.__('use a custom XSPF playlist:', 'podpress').'</label> <input type="checkbox" name="podPressXspfPlayer-xspf_use_custom_playlist" id="xspf_use_custom_playlist"'.$xspf_use_custom_playlist_checked.$xspf_use_custom_playlist_disabled.' /></p>'."\n";
		echo '<p><label for="xspf_custom_playlist_url">'.__('custom playlist URL:', 'podpress').'</label><br /><input type="text" name="podPressXspfPlayer-xspf_custom_playlist_url" id="xspf_custom_playlist_url" class="podpress_full_width_text_field" size="40" value="'.$xspf_custom_playlist_url.'"'.$xspf_custom_playlist_url_readonly.' /><span class="nonessential">'.__('The custom playlist URL has to be an URL to a playlist which is on the same domain/server as your blog. The files in the playlist can be located some where else.', 'podpress').'</span></p>'.$xspf_custom_playlist_msg."\n";
		if ( TRUE == defined('PODPRESS_XSPF_PLAYER_USE_CUSTOM_SKINFILE_'.$blog_id) AND TRUE === constant('PODPRESS_XSPF_PLAYER_USE_CUSTOM_SKINFILE_'.$blog_id) ) { 
			echo '<p class="message updated podpress_msg">'.__('<strong>Notice:</strong> This widget uses custom skin files. Modifications of width and height will only affect the size of the <object> of the player and not the skin files.', 'podpress').'</p>';
		}
		?>	
		<input type="hidden" id="podPressXspfPlayer-submit" name="podPressXspfPlayer-submit" value="1" />
		<?php
	}
	
	/* for WP < 2.8 only */
	function podPress_xspfPlayer($args) {
		GLOBAL $podPress, $blog_id;
		extract($args);
		$options = get_option('widget_podPressXspfPlayer');
		if ( !isset($options['title']) ) {
			$options['title'] = __('Podcast Player', 'podpress');
		} else {
			$options['title'] = stripslashes($options['title']);
		}
		if ( !isset($options['useSlimPlayer']) ) {
			$options['useSlimPlayer'] = false;
		}
		if ( TRUE == defined('PODPRESS_XSPF_PLAYER_WIDTH_'.$blog_id) ) {
			$options['PlayerWidth'] = intval(constant('PODPRESS_XSPF_PLAYER_WIDTH_'.$blog_id));
		}
		if ( 170 > intval($options['PlayerWidth']) ) {
			$options['PlayerWidth'] = 170; // min width
		}			
		echo $before_widget."\n";
		echo $before_title . $options['title'] . $after_title."\n";
		if ( TRUE === $options['useSlimPlayer'] ) {
			if ( TRUE === defined('PODPRESS_XSPF_SLIM_USE_CUSTOM_VARIABLES_'.$blog_id) AND TRUE === constant('PODPRESS_XSPF_SLIM_USE_CUSTOM_VARIABLES_'.$blog_id) AND TRUE == is_readable(PODPRESS_DIR.'/players/xspf_jukebox/dynamic_slim/variables_'.$blog_id.'.txt')) { 
				$variables = '&skin_url='.PODPRESS_URL.'/players/xspf_jukebox/dynamic_slim/skin_'.$blog_id.'.xml&loadurl='.PODPRESS_URL.'/players/xspf_jukebox/dynamic_slim/variables_'.$blog_id.'.txt';
			} else {
				$variables = '&skin_url='.PODPRESS_URL.'/players/xspf_jukebox/dynamic_slim/skin_'.$blog_id.'.xml&autoload=true&autoplay=false&loaded=true';
			}
			$data_string = PODPRESS_URL.'/players/xspf_jukebox/xspf_jukebox.swf?playlist_url='.(PODPRESS_URL.'/podpress_xspfplaylist.php').$variables;
			if ( TRUE == defined('PODPRESS_XSPF_SLIM_PLAYER_HEIGHT_'.$blog_id) ) {
				$options['SlimPlayerHeight'] = intval(constant('PODPRESS_XSPF_SLIM_PLAYER_HEIGHT_'.$blog_id));
			}
			if ( 30 > intval($options['SlimPlayerHeight']) ) {
				$options['SlimPlayerHeight'] = 30; // min height slim
			}
			echo '<object type="application/x-shockwave-flash" width="'.$options['PlayerWidth'].'" height="'.$options['PlayerHeight'].'" id="podpress_xspf_player_slim" data="'.$data_string.'">'."\n";
		} else {
			if ( TRUE === defined('PODPRESS_XSPF_USE_CUSTOM_VARIABLES_'.$blog_id) AND TRUE === constant('PODPRESS_XSPF_USE_CUSTOM_VARIABLES_'.$blog_id) AND TRUE == is_readable(PODPRESS_DIR.'/players/xspf_jukebox/dynamic/variables_'.$blog_id.'.txt')) {
				$variables = '&skin_url='.PODPRESS_URL.'/players/xspf_jukebox/dynamic/skin_'.$blog_id.'.xml&loadurl='.PODPRESS_URL.'/players/xspf_jukebox/dynamic/variables_'.$blog_id.'.txt';
			} else {
				$variables = '&skin_url='.PODPRESS_URL.'/players/xspf_jukebox/dynamic/skin_'.$blog_id.'.xml&autoload=true&autoplay=false&loaded=true';
			}
			$data_string = PODPRESS_URL.'/players/xspf_jukebox/xspf_jukebox.swf?playlist_url='.(PODPRESS_URL.'/podpress_xspfplaylist.php').$variables;
			if ( TRUE == defined('PODPRESS_XSPF_PLAYER_HEIGHT_'.$blog_id) ) {
				$options['PlayerHeight'] = intval(constant('PODPRESS_XSPF_PLAYER_HEIGHT_'.$blog_id));
			}
			if ( 210 > intval($options['PlayerHeight']) ) {
				$options['PlayerHeight'] = 210; // min height
			}
			echo '<object type="application/x-shockwave-flash" width="'.$options['PlayerWidth'].'" height="'.$options['PlayerHeight'].'" id="podpress_xspf_player" data="'.$data_string.'">'."\n";
		}
		echo '	<param name="movie" value="'.$data_string.'" />'."\n";
		if ( defined('PODPRESS_XSPF_BACKGROUND_COLOR_'.$blog_id) AND '' !== constant('PODPRESS_XSPF_BACKGROUND_COLOR_'.$blog_id) ) {
			echo '	<param name="bgcolor" value="#'.constant('PODPRESS_XSPF_BACKGROUND_COLOR_'.$blog_id).'" />'."\n";
		} else {
			echo '	<param name="bgcolor" value="#FFFFFF" />'."\n";
		}
		echo '</object>'."\n";
		echo $after_widget;
	}
	
	/**
	* podpress_xspf_jukebox_skin_xml - generates the content of a skin file of the XSPF player with the new width and height value
	*
	* @package podPress
	* @since 8.8.5
	*
	* @param int $width
	* @param int $height
	* @param int $blog_id 
	*
	*/
	function podpress_xspf_jukebox_skin_xml($width = 230, $height = 210, $blog_id=1) {
		if (600 < $width) {
			$width = 600;
		} elseif (170 > $width) {
			$width = 170;
		}
		if (1000 < $height) {
			$height = 1000;
		} elseif (210 > $height) {
			$height = 210;
		}
		$top_row_h = 18;
		$bottom_row_w = $width;
		$bottom_row_h = 19;
		$scrollbar_w = 10;
		$middle_row_h = ($height - ($top_row_h+$bottom_row_h));
		$volume_display_w=14;
		$td_lb_tb_x = 59;
		$timedisplay_w = 26;
		$space_w = 3;
		$space_h = 3;
		$timebar_h = 13;
		$loadBar_h = 3;

		$player_buttons_h = $top_row_h-$space_h-1-$space_h-1;
		
		// colors
		$bgcolor = 'CCCCCC';
		$rowsandbars_bgcolor = 'EAEAEA';
		$buttons_color = '999999';
		$playlist_text_color = $button_text_color = '333333';
		$playlist_selectedtext_color = 'aa3333';
		$infodisplay_text_color = '000000';
		
		// misc.
		if ( TRUE === defined( 'PODPRESS_XSPF_SHOW_PREVIEW_IMAGE' ) AND TRUE === PODPRESS_XSPF_SHOW_PREVIEW_IMAGE ) { 
			$show_episode_image = TRUE;
		} else {
			$show_episode_image = FALSE;
		}
		
		$charset = get_bloginfo('charset');
		
		$output = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
		$output .= '<skin version="0" xmlns="http://xsml.org/ns/0/">'."\n";
		$output .= '	<width>'.$width.'</width>'."\n";
		$output .= '	<height>'.$height.'</height>'."\n";
		$output .= '	<name>SlimOriginal</name>'."\n";
		$output .= '	<author>Lacy Morrow</author>'."\n";
		$output .= '	<email>gojukebox@gmail.com</email>'."\n";
		$output .= '	<website>http://www.lacymorrow.com</website>'."\n";
		$output .= '	<comment>Blog ID: '.$blog_id.' | DYNAMIC SlimOriginal Skin for XSPF Jukebox (This is a derivate of the SlimOriginal skin.) - THIS FILE WILL BE OVERWRITTEN BY THE PODPRESS XSPF WIDGET! You can prevent this by defining the constant PODPRESS_XSPF_PLAYER_USE_CUSTOM_SKINFILE in the podpress.php file.</comment>'."\n";
		$output .= '	<objects>'."\n";
		$output .= '		<background color="'.$bgcolor.'" />'."\n";
		
		// playlist
		$output .= '		<playlist x="'.$space_w.'" y="'.($top_row_h+$space_h).'" width="'.($width-$space_w-$scrollbar_w-$space_w).'" height="'.$middle_row_h.'" size="10" font="Arial" color="'.$playlist_text_color.'" selectedColor="'.$playlist_selectedtext_color.'" />'."\n";
		// top row background
		$output .= '		<shape shape="rectangle" x="0" y="0" width="'.$width.'" height="'.$top_row_h.'" color="'.$rowsandbars_bgcolor.'" />'."\n";
		// scroll bar background
		$output .= '		<shape shape="rectangle" x="'.($width-$scrollbar_w).'" y="'.$top_row_h.'" width="'.$scrollbar_w.'" height="'.$middle_row_h.'" color="'.$rowsandbars_bgcolor.'" />'."\n";
		// bottom row background
		$output .= '		<shape shape="rectangle" x="0" y="'.($height-($bottom_row_h)).'" width="'.$bottom_row_w.'" height="'.$bottom_row_h.'" color="'.$rowsandbars_bgcolor.'" />'."\n";
		// "About" - button element
		$output .= '		<text x="'.($width-33).'" y="'.($height-$bottom_row_h).'" size="10" text="'.html_entity_decode(__('About', 'podpress'), ENT_COMPAT, $charset).'" font="Arial" bold="0" color="'.$button_text_color.'" hoverMessage="'.html_entity_decode(__('About XSPF Jukebox player', 'podpress'), ENT_COMPAT, $charset).'" url="http://blog.lacymorrow.com" />'."\n";
		// player as Popup player - button element
		// $output .= '		<text x="'.($width-85).'" y="'.($height-$bottom_row_h+2).'" size="10" text="'.html_entity_decode(__('Popup', 'podpress'), ENT_COMPAT, $charset).'" font="Arial" bold="0" color="'.$button_text_color.'" hoverMessage="'.html_entity_decode(__('Popup', 'podpress'), ENT_COMPAT, $charset).'" url="../static/object-flash-xspf-popup.php" />'."\n";

		$output .= '		<object label="prevButton" x="2" y="'.$space_h.'" width="11" height="'.$player_buttons_h.'" color="'.$buttons_color.'" />'."\n";
		$output .= '		<object label="playButton" x="19" y="'.$space_h.'" width="10" height="'.$player_buttons_h.'" color="'.$buttons_color.'" />'."\n";
		$output .= '		<object label="startButton" x="19" y="'.$space_h.'" width="10" height="'.$player_buttons_h.'" alpha="0" />'."\n";
		$output .= '		<object label="stopButton" x="32" y="'.$space_h.'" width="9" height="'.$player_buttons_h.'" color="'.$buttons_color.'" />'."\n";
		$output .= '		<object label="fwdButton" x="46" y="'.$space_h.'" width="11" height="'.$player_buttons_h.'" color="'.$buttons_color.'" />'."\n";
		
		$output .= '		<object label="trackDisplay" x="'.$td_lb_tb_x.'" y="0" width="'.($width-$td_lb_tb_x-$volume_display_w-4-26).'" size="10" font="Arial" color="'.$infodisplay_text_color.'" align="left" />'."\n";
		$output .= '		<object label="timeBar" x="'.$td_lb_tb_x.'" y="1" width="'.($width-$td_lb_tb_x-$volume_display_w-4).'" height="'.$timebar_h.'" alpha="60" color="cc9999" />'."\n";
		$output .= '		<object label="loadBar" x="'.$td_lb_tb_x.'" y="'.(1+$timebar_h).'" width="'.($width-$td_lb_tb_x-$volume_display_w-4).'" height="'.$loadBar_h.'" alpha="60" color="BBdddd" />'."\n";
		$output .= '		<object label="timeDisplay" x="'.($width-$volume_display_w-3-26).'" y="0" width="26" size="10" font="Arial" color="'.$infodisplay_text_color.'" />'."\n";
		$output .= '		<object label="volumeDisplay" x="'.($width-$volume_display_w-2).'" y="'.$space_h.'" width="'.$volume_display_w.'" height="'.$player_buttons_h.'" color="444444" />'."\n";
		
		if (TRUE == $show_episode_image) {
			$output .= '		<object label="imageDisplay" x="20" y="'.($height-$bottom_row_h-110).'" width="130" height="100" />'."\n";
		}
		//~ $output .= '		<object label="videoDisplay" x="20" y="20" width="130" height="100" />'."\n";
		
		$output .= '		<object label="scrollupButton" x="'.($width-6-$space_w).'" y="'.($top_row_h+2).'" width="6" height="6" color="'.$buttons_color.'" />'."\n";
		$output .= '		<object label="scrolldownButton" x="'.($width-6-$space_w).'" y="'.($top_row_h+13).'" width="6" height="6" color="'.$buttons_color.'" />'."\n";
		$output .= '		<object label="scrollButton" x="'.($width-6-$space_w).'" y="'.($top_row_h+25).'" width="6" height="'.($middle_row_h-25).'" color="'.$buttons_color.'" bgAlpha="0" />'."\n";
		
		$output .= '		<object label="shuffleButton" x="4" y="'.($height-$bottom_row_h+$space_h).'" width="20.7" height="11.7" color="'.$button_text_color.'" hoverMessage="'.html_entity_decode(__('Shuffle', 'podpress'), ENT_COMPAT, $charset).'" />'."\n";
		$output .= '		<object label="repeatButton" x="27" y="'.($height-$bottom_row_h+$space_h).'" width="15.7" height="11.7" color="'.$button_text_color.'" hoverMessage="'.html_entity_decode(__('Repeat', 'podpress'), ENT_COMPAT, $charset).'" />'."\n";
		
		//~ $output .= '		<object label="infoButton" x="79" y="'.($height-$bottom_row_h-20).'" size="+10" color="'.$button_text_color.'" text="'.html_entity_decode(__('Info', 'podpress'), ENT_COMPAT, $charset).'" font="Arial" hoverMessage="'.html_entity_decode(__('Track Info', 'podpress'), ENT_COMPAT, $charset).'" />'."\n";
		//~ $output .= '		<object label="purchaseButton" x="52" y="'.($height-($bottom_row_h)).'" size="+10" color="'.$button_text_color.'" text="'.html_entity_decode(__('purchase', 'podpress'), ENT_COMPAT, $charset).'" font="Arial" hoverMessage="'.html_entity_decode(__('Purchase', 'podpress'), ENT_COMPAT, $charset).'" />'."\n";
		//~ $output .= '		<object label="downloadButton" x="101" y="'.($height-($bottom_row_h)).'" size="+10" color="'.$button_text_color.'" text="'.html_entity_decode(__('Save', 'podpress'), ENT_COMPAT, $charset).'" font="Arial" bold="0" hoverMessage="'.html_entity_decode(__('Download Track', 'podpress'), ENT_COMPAT, $charset).'" />'."\n";
		
		$output .= '	</objects>'."\n";
		$output .= '</skin>'."\n";

		// write the out put to the skin file
		podPress_write_XSPF_skin_file(PODPRESS_DIR.'/players/xspf_jukebox/dynamic/skin_'.$blog_id.'.xml', $output);
	}
	
	/**
	* podpress_xspf_jukebox_slim_skin_xml - generates the content of a skin file of the slim XSPF player with the new width and height value
	*
	* @package podPress
	* @since 8.8.5
	*
	* @param int $width
	* @param int $height
	* @param int $blog_id 
	*
	*/
	function podpress_xspf_jukebox_slim_skin_xml($width = 170, $height = 30, $blog_id=1) {
		if (600 < $width) {
			$width = 600;
		} elseif (170 > $width) {
			$width = 170;
		}
		if (100 < $height) {
			$height = 100;
		} elseif (30 > $height) {
			$height = 30;
		}
		$top_row_h = 18;
		$bottom_row_w = $width;
		$bottom_row_h = 12;
		$middle_row_h = ($height - ($top_row_h+$bottom_row_h));
		$volume_display_w=14;
		$td_lb_tb_x = 59;
		$timedisplay_w = 26;
		$space_w = 3;
		$space_h = 3;
		$timebar_h = 13;
		$loadBar_h = 3;

		$player_buttons_h = $top_row_h-$space_h-1-$space_h-1;
		
		// colors
		$bgcolor = 'CCCCCC';
		$rowsandbars_bgcolor = 'EAEAEA';
		$buttons_color = '999999';
		$playlist_text_color = $button_text_color = '333333';
		$playlist_selectedtext_color = 'aa3333';
		$infodisplay_text_color = '000000';
		
		// misc.
		$show_episode_image = TRUE;
		
		$charset = get_bloginfo('charset');
		
		$output = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
		$output .= '<skin version="0" xmlns="http://xsml.org/ns/0/">'."\n";
		$output .= '	<width>'.$width.'</width>'."\n";
		$output .= '	<height>'.$height.'</height>'."\n";
		$output .= '	<name>SlimOriginal</name>'."\n";
		$output .= '	<author>Lacy Morrow</author>'."\n";
		$output .= '	<email>gojukebox@gmail.com</email>'."\n";
		$output .= '	<website>http://www.lacymorrow.com</website>'."\n";
		$output .= '	<comment>Blog ID: '.$blog_id.' | DYNAMIC SlimOriginal Skin for XSPF Jukebox (This is a derivate of the SlimOriginal skin for the slim player.) - THIS FILE WILL BE OVERWRITTEN BY THE PODPRESS XSPF WIDGET! You can prevent this by defining the constant PODPRESS_XSPF_PLAYER_USE_CUSTOM_SKINFILE in the podpress.php file.</comment>'."\n";
		$output .= '	<objects>'."\n";
		$output .= '		<background color="'.$bgcolor.'" />'."\n";
		
		// top row background
		$output .= '		<shape shape="rectangle" x="0" y="0" width="'.$width.'" height="'.$top_row_h.'" color="'.$rowsandbars_bgcolor.'" />'."\n";
		// bottom row background
		$output .= '		<shape shape="rectangle" x="0" y="'.($height-($bottom_row_h)).'" width="'.$bottom_row_w.'" height="'.$bottom_row_h.'" color="'.$rowsandbars_bgcolor.'" />'."\n";
		// "About" - button element
		$output .= '		<text x="'.($width-33).'" y="'.($height-$bottom_row_h-$space_h).'" size="10" text="'.html_entity_decode(__('About', 'podpress'), ENT_COMPAT, $charset).'" font="Arial" bold="0" color="'.$button_text_color.'" hoverMessage="'.html_entity_decode(__('About XSPF Jukebox player', 'podpress'), ENT_COMPAT, $charset).'" url="http://blog.lacymorrow.com" />'."\n";
		// player as Popup player - button element
		// $output .= '		<text x="'.($width-85).'" y="'.($height-$bottom_row_h+2).'" size="10" text="'.html_entity_decode(__('Popup', 'podpress'), ENT_COMPAT, $charset).'" font="Arial" bold="0" color="'.$button_text_color.'" hoverMessage="'.html_entity_decode(__('Popup', 'podpress'), ENT_COMPAT, $charset).'" url="../static/object-flash-xspf-popup.php" />'."\n";

		$output .= '		<object label="prevButton" x="2" y="'.$space_h.'" width="11" height="'.$player_buttons_h.'" color="'.$buttons_color.'" />'."\n";
		$output .= '		<object label="playButton" x="19" y="'.$space_h.'" width="10" height="'.$player_buttons_h.'" color="'.$buttons_color.'" />'."\n";
		$output .= '		<object label="startButton" x="19" y="'.$space_h.'" width="10" height="'.$player_buttons_h.'" alpha="0" />'."\n";
		$output .= '		<object label="stopButton" x="32" y="'.$space_h.'" width="9" height="'.$player_buttons_h.'" color="'.$buttons_color.'" />'."\n";
		$output .= '		<object label="fwdButton" x="46" y="'.$space_h.'" width="11" height="'.$player_buttons_h.'" color="'.$buttons_color.'" />'."\n";
		
		$output .= '		<object label="trackDisplay" x="'.$td_lb_tb_x.'" y="0" width="'.($width-$td_lb_tb_x-$volume_display_w-4-26).'" size="10" font="Arial" color="'.$infodisplay_text_color.'" align="left" />'."\n";
		$output .= '		<object label="timeBar" x="'.$td_lb_tb_x.'" y="1" width="'.($width-$td_lb_tb_x-$volume_display_w-4).'" height="'.$timebar_h.'" alpha="60" color="cc9999" />'."\n";
		$output .= '		<object label="loadBar" x="'.$td_lb_tb_x.'" y="'.(1+$timebar_h).'" width="'.($width-$td_lb_tb_x-$volume_display_w-4).'" height="'.$loadBar_h.'" alpha="60" color="BBdddd" />'."\n";
		$output .= '		<object label="timeDisplay" x="'.($width-$volume_display_w-3-26).'" y="0" width="26" size="10" font="Arial" color="'.$infodisplay_text_color.'" />'."\n";
		$output .= '		<object label="volumeDisplay" x="'.($width-$volume_display_w-2).'" y="'.$space_h.'" width="'.$volume_display_w.'" height="'.$player_buttons_h.'" color="444444" />'."\n";
		
		$output .= '		<object label="shuffleButton" x="4" y="'.($height-$bottom_row_h).'" width="17.1" height="10" color="'.$button_text_color.'" hoverMessage="'.html_entity_decode(__('Shuffle', 'podpress'), ENT_COMPAT, $charset).'" />'."\n";
		$output .= '		<object label="repeatButton" x="27" y="'.($height-$bottom_row_h).'" width="12.1" height="10" color="'.$button_text_color.'" hoverMessage="'.html_entity_decode(__('Repeat', 'podpress'), ENT_COMPAT, $charset).'" />'."\n";
		// $output .= '		<object label="infoButton" x="79" y="'.($height-$bottom_row_h+2).'" size="+10" color="'.$button_text_color.'" text="'.html_entity_decode(__('Info', 'podpress'), ENT_COMPAT, $charset).'" font="Arial" hoverMessage="'.html_entity_decode(__('Track Info', 'podpress'), ENT_COMPAT, $charset).'" />'."\n";
		
		$output .= '	</objects>'."\n";
		$output .= '</skin>'."\n";
		
		// write the out put to the skin file
		podPress_write_XSPF_skin_file(PODPRESS_DIR.'/players/xspf_jukebox/dynamic_slim/skin_'.$blog_id.'.xml', $output);
	}
	
	/**
	* podPress_write_XSPF_skin_file - (re)writes a skin file of the XSPF player with the output of podpress_xspf_jukebox_skin_xml or podpress_xspf_jukebox_slim_skin_xml
	*
	* @package podPress
	* @since 8.8.5
	*
	* @param str $filename
	* @param str $output - The new content of the skin file.
	*
	*/
	function podPress_write_XSPF_skin_file($filename='', $output='') {
		if (FALSE == is_dir(dirname($filename))) {
			echo '<p class="message error podpress_msg">'.sprintf(__('The folder %1$s does not exists. Unable to write the skin file.', 'podpress'), dirname($filename)).'</p>';
			return;
		}
		if (TRUE === is_file($filename) AND FALSE === is_writable($filename)) {
			$result = @chmod($filename, 0777);
		}
		if (FALSE === $result) {
			echo '<p class="message error podpress_msg">'.__('Your are not allowed to change file permissions. Unable to (re)write the skin file.', 'podpress').'</p>';
			return;
		}
		$handle = fopen($filename, "w");
		fputs($handle, $output);
		$status = fclose($handle);
		if (is_file($filename)) {chmod ($filename, 0644);}
	}
	
	/**
	* podPress Feed Buttons Widget Class
	* since podPress v8.8.7 beta 2
	* for WP > = 2.8
	*/
	class podpress_feedbuttons extends WP_Widget {
		/** constructor */
		function podpress_feedbuttons() {
			$widget_ops = array(
				'classname' => 'podpress_feedbuttons', 
				'description' => __('Shows buttons for the podcast feeds in the sidebar', 'podpress')
			);
			$control_ops = array('width' => 400, 'height' => 300);
			
			parent::WP_Widget(false, $name = __('podPress - Feed Buttons','podpress'), $widget_ops, $control_ops);
		}

		/** @see WP_Widget::widget */
		function widget($args, $instance) {
			GLOBAL $podPress, $wp_version;
			extract( $args );
			$title = apply_filters('widget_title', $instance['title']);
			$feed_icon = '<img src="'.get_option('siteurl') . '/' . WPINC . '/images/rss.png" class="podpress_feed_icon" alt="" />';
			echo $before_widget;
			echo $before_title . $title . $after_title;
			echo '<ul class="podpress_feed_buttons_list">'."\n";
			switch ($instance['buttons-or-text']) {
				default:
				case 'buttons' :
					if ($instance['itunes']) {
						// for more info: http://www.apple.com/itunes/podcasts/specs.html#linking
						if ($instance['iprot'] ) {
							echo ' <li><a href="itpc://'.preg_replace('/^https?:\/\//i', '', $podPress->settings['podcastFeedURL']).'"';
						} else {
							echo ' <li><a href="http://www.itunes.com/podcast?id='.$podPress->settings['iTunes']['FeedID'].'"';
						}
						echo ' title="'.__('Subscribe to the Podcast Feed with iTunes', 'podpress').'"><img src="'.podPress_url().'images/button_itunes.png" class="podpress_feed_buttons" alt="'.__('Subscribe with iTunes', 'podpress').'" /></a></li>'."\n";
					}
					if($instance['podcast']) {
						echo '	<li><a href="'.get_feed_link('podcast').'" title="'.__('Subscribe to the Podcast RSS Feed with any other podcatcher', 'podpress').'"><img src="'.podPress_url().'images/button_rss_podcast.png" class="podpress_feed_buttons" alt="'.__('Subscribe to the Podcast RSS Feed', 'podpress').'" /></a></li>'."\n";
					}
					if($instance['enhancedpodcast']) {
						echo '	<li><a href="'.get_feed_link('enhancedpodcast').'" title="'.__('Subscribe to the Enhanced Podcast Feed with a podcatcher which can play .m4a or .m4v files e.g. iTunes', 'podpress').'"><img src="'.podPress_url().'images/feed-enhpodcast.png" class="podpress_feed_buttons" alt="'.__('Subscribe to the Enhanced Podcast Feed', 'podpress').'" /></a></li>'."\n";
					}
					if($instance['torrent']) {
						echo '	<li><a href="'.get_feed_link('torrent').'" title="'.__('Subscribe to the Torrent Feed with e.g. Miro or Vluze', 'podpress').'"><img src="'.podPress_url().'images/feed-torrent.png" class="podpress_feed_buttons" alt="'.__('Subscribe to the Torrent Feed', 'podpress').'" /></a></li>'."\n";
					}
					if($instance['blog']) {
						echo '	<li><a href="'.get_bloginfo('rss2_url').'" title="'.__('Subscribe to the main RSS Feed with any feed reader', 'podpress').'"><img src="'.podPress_url().'images/button_rss_blog.png" class="podpress_feed_buttons" alt="'.__('Subscribe to the RSS Feed', 'podpress').'" /></a></li>'."\n";
					}
					if($instance['comments']) {
						echo '	<li><a href="'.get_bloginfo('comments_rss2_url').'" title="'.__('Subscribe to the comments RSS Feed with any feed reader', 'podpress').'"><img src="'.podPress_url().'images/button_comments_rss_blog.png" class="podpress_feed_buttons" alt="'.__('Subscribe to the comments RSS Feed', 'podpress').'" /></a></li>'."\n";
					}
					if($instance['entries-atom']) {
						echo '	<li><a href="'.get_bloginfo('atom_url').'" title="'.__('Subscribe to the main ATOM Feed with any feed reader', 'podpress').'"><img src="'.podPress_url().'images/button_atom_blog.png" class="podpress_feed_buttons" alt="'.__('Subscribe to the ATOM Feed', 'podpress').'" /></a></li>'."\n";
					}
					if($instance['comments-atom']) {
						echo '	<li><a href="'.get_bloginfo('comments_atom_url').'" title="'.__('Subscribe to the comments ATOM Feed with any feed reader', 'podpress').'"><img src="'.podPress_url().'images/button_comments_atom_blog.png" class="podpress_feed_buttons" alt="'.__('Subscribe to the comments ATOM Feed', 'podpress').'" /></a></li>'."\n";
					}
					if ( is_array($instance['catcast']) AND FALSE == empty($instance['catcast']) ) {
						foreach ($instance['catcast'] as $cat_id => $value) {
							if (TRUE == version_compare($wp_version, '2.9.3','>') ) {
								$cat_feed_link = get_term_feed_link($cat_id);
							} else {
								$cat_feed_link = get_category_feed_link($cat_id);
							} 
							echo '	<li><a href="'.$cat_feed_link.'" title="'.__('Subscribe to this Category RSS Feed with any feed reader', 'podpress').'">'.$feed_icon.' '.sprintf(__('Category "%1$s" RSS Feed', 'podpress'), get_cat_name($cat_id)).'</a></li>'."\n";
						}
					}
				break;
				case 'text' :
					if ($instance['itunes']) {
						// for more info: http://www.apple.com/itunes/podcasts/specs.html#linking
						if ($instance['iprot'] ) {
							echo ' <li><a href="itpc://'.preg_replace('/^https?:\/\//i', '', $podPress->settings['podcastFeedURL']).'"';
						} else {
							echo ' <li><a href="http://www.itunes.com/podcast?id='.$podPress->settings['iTunes']['FeedID'].'"';
						}
						echo ' title="'.__('Subscribe to the Podcast Feed with iTunes', 'podpress').'">'.$feed_icon.' '.__('Subscribe with iTunes', 'podpress').'</a></li>'."\n";
					}
					if($instance['podcast']) {
						echo '	<li><a href="'.get_feed_link('podcast').'" title="'.__('Subscribe to the Podcast RSS Feed with any other podcatcher', 'podpress').'">'.$feed_icon.' '.__('Podcast Feed', 'podpress').'</a></li>'."\n";
					}
					if($instance['enhancedpodcast']) {
						echo '	<li><a href="'.get_feed_link('enhancedpodcast').'" title="'.__('Subscribe to the Enhanced Podcast Feed with a podcatcher which can play .m4a or .m4v files e.g. iTunes', 'podpress').'">'.$feed_icon.' '.__('Enhanced Podcast Feed', 'podpress').'</a></li>'."\n";
					}
					if($instance['torrent']) {
						echo '	<li><a href="'.get_feed_link('torrent').'" title="'.__('Subscribe to the Torrent Feed with e.g. Miro or Vluze', 'podpress').'">'.$feed_icon.' '.__('Torrent Feed', 'podpress').'</a></li>'."\n";
					}
					if($instance['blog']) {
						echo '	<li><a href="'.get_bloginfo('rss2_url').'" title="'.__('Subscribe to the main RSS Feed with any feed reader', 'podpress').'">'.$feed_icon.' '.__('Entries RSS Feed', 'podpress').'</a></li>'."\n";
					}
					if($instance['comments']) {
						echo '	<li><a href="'.get_bloginfo('comments_rss2_url').'" title="'.__('Subscribe to the comments RSS Feed with any feed reader', 'podpress').'">'.$feed_icon.' '.__('Comments RSS Feed', 'podpress').'</a></li>'."\n";
					}
					if($instance['entries-atom']) {
						echo '	<li><a href="'.get_bloginfo('atom_url').'" title="'.__('Subscribe to the main ATOM Feed with any feed reader', 'podpress').'">'.$feed_icon.' '.__('Entries ATOM Feed', 'podpress').'</a></li>'."\n";
					}
					if($instance['comments-atom']) {
						echo '	<li><a href="'.get_bloginfo('comments_atom_url').'" title="'.__('Subscribe to the comments ATOM Feed with any feed reader', 'podpress').'">'.$feed_icon.' '.__('Comments ATOM Feed', 'podpress').'</a></li>'."\n";
					}
					if ( is_array($instance['catcast']) AND FALSE == empty($instance['catcast']) ) {
						foreach ($instance['catcast'] as $cat_id => $value) {
							if (TRUE == version_compare($wp_version, '2.9.3','>') ) {
								$cat_feed_link = get_term_feed_link($cat_id);
							} else {
								$cat_feed_link = get_category_feed_link($cat_id);
							} 
							echo '	<li><a href="'.$cat_feed_link.'" title="'.__('Subscribe to this Category RSS Feed with any feed reader', 'podpress').'">'.$feed_icon.' '.sprintf(__('Category "%1$s" RSS Feed', 'podpress'), get_cat_name($cat_id)).'</a></li>'."\n";
						}
					}
				break;
			}
			echo "</ul>\n";
			echo $after_widget;
		}

		/** @see WP_Widget::update */
		function update($new_instance, $old_instance) {
			$blog_charset = get_bloginfo('charset');
			$instance = $old_instance;
			$instance['title'] = htmlspecialchars(strip_tags(trim($new_instance['title'])), ENT_QUOTES, $blog_charset);
			$instance['blog'] = $new_instance['posts'];
			$instance['comments'] = $new_instance['comments'];
			$instance['entries-atom'] = $new_instance['entries-atom'];
			$instance['comments-atom'] = $new_instance['comments-atom'];
			$instance['podcast'] = $new_instance['podcast'];
			$instance['enhancedpodcast'] = $new_instance['enhancedpodcast'];
			$instance['torrent'] = $new_instance['torrent'];
			$instance['itunes'] = $new_instance['itunes'];
			// iscifi new option for itunes protocol
			$instance['iprot'] = $new_instance['iprot'];
			$instance['buttons-or-text'] = $new_instance['buttons-or-text'];
			$instance['catcast'] = $new_instance['catcast'];
			return $instance;
		}

		/** @see WP_Widget::form */
		function form($instance) {
			GLOBAL $podPress, $wpdb;

			if (!isset($instance['blog'])) {
				$instance['blog'] = false;
			}
			if (!isset($instance['comments'])) {
				$instance['comments'] = false;
			}
			if (!isset($instance['entries-atom'])) {
				$instance['entries-atom'] = false;
			}
			if (!isset($instance['comments-atom'])) {
				$instance['comments-atom'] = false;
			}
			if (!isset($instance['podcast'])) {
				$instance['podcast'] = true;
			}
			if (!isset($instance['enhancedpodcast'])) {
				$instance['enhancedpodcast'] = false;
			}
			if (!isset($instance['torrent'])) {
				$instance['torrent'] = false;
			}
			if (!isset($instance['itunes'])) {
				$instance['itunes'] = true;
			}
			if (!isset($instance['iprot'])) {
				$instance['iprot'] = false;
			}
			if (!isset($instance['buttons-or-text'])) {
				$instance['buttons-or-text'] = 'buttons';
			}

			$blog = $instance['blog'] ? 'checked="checked"' : '';
			$comments = $instance['comments'] ? 'checked="checked"' : '';
			$entries_atom = $instance['entries-atom'] ? 'checked="checked"' : '';
			$comments_atom = $instance['comments-atom'] ? 'checked="checked"' : '';
			$podcast = $instance['podcast'] ? 'checked="checked"' : '';
			$enhpodcast = $instance['enhancedpodcast'] ? 'checked="checked"' : '';
			$torrent = $instance['torrent'] ? 'checked="checked"' : '';
			$itunes = $instance['itunes'] ? 'checked="checked"' : '';
			$iprot = $instance['iprot'] ? 'checked="checked"' :'';
			if ( 'text' == $instance['buttons-or-text'] ) {
				$text = 'checked="checked"';
				$buttons = '';
			} else {
				$text = '';
				$buttons = 'checked="checked"';
			}
			
			if(!isset($instance['title'])) {
				$instance['title'] = __('Podcast Feeds', 'podpress');
			}
			$title = esc_attr($instance['title']);
			?>
			<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:', 'podpress'); ?></label> <input class="podpress_widget_settings_title" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" /></p>
			<p><?php _e('Show the buttons for the following feeds:', 'podpress'); ?></p>
			<p class="podpress_widget_settings_row">
				<label for="<?php echo $this->get_field_id('itunes'); ?>"><?php _e('Show iTunes button', 'podpress'); ?></label> <input type="checkbox" <?php echo $itunes; ?> id="<?php echo $this->get_field_id('itunes'); ?>" name="<?php echo $this->get_field_name('itunes'); ?>" /><br />
				<label for="<?php echo $this->get_field_id('iprot'); ?>"><?php _e('Use iTunes protocol for URL', 'podpress'); ?> <?php _e('(itpc://)', 'podpress'); ?></label> <input type="checkbox" <?php echo $iprot; ?> id="<?php echo $this->get_field_id('iprot'); ?>" name="<?php echo $this->get_field_name('iprot'); ?>" /><br /><span class="nonessential"><?php _e('The user subscribes immediatly with the click. Otherwise the iTunes Store page of the podcast will be displayed first and the user can subscribe manually.', 'podpress'); ?></span>
			</p>
			<p class="podpress_widget_settings_row"><label for="<?php echo $this->get_field_id('podcast'); ?>"><?php _e('Podcast Feed', 'podpress'); ?></label> <input type="checkbox" <?php echo $podcast; ?> id="<?php echo $this->get_field_id('podcast'); ?>" name="<?php echo $this->get_field_name('podcast'); ?>" /></p>
			<p class="podpress_widget_settings_row"><label for="<?php echo $this->get_field_id('enhancedpodcast'); ?>"><?php _e('Enhanced Podcast Feed', 'podpress'); ?></label> <input type="checkbox" <?php echo $enhpodcast; ?> id="<?php echo $this->get_field_id('enhancedpodcast'); ?>" name="<?php echo $this->get_field_name('enhancedpodcast'); ?>" /><br /><span class="nonessential"><?php _e('This is an ATOM feed which contains only posts with attached .m4a or .m4v files.', 'podpress'); ?></span></p>		
			<p class="podpress_widget_settings_row"><label for="<?php echo $this->get_field_id('torren'); ?>t"><?php _e('Torrent Feed', 'podpress'); ?></label> <input type="checkbox" <?php echo $torrent; ?> id="<?php echo $this->get_field_id('torrent'); ?>" name="<?php echo $this->get_field_name('torrent'); ?>" /><br /><span class="nonessential"><?php _e('This is an ATOM feed which contains only posts with attached .torrent files.', 'podpress'); ?></span></p>
			<p class="podpress_widget_settings_row"><label for="<?php echo $this->get_field_id('posts'); ?>"><?php _e('Entries RSS Feed', 'podpress'); ?></label> <input type="checkbox" <?php echo $blog; ?> id="<?php echo $this->get_field_id('posts'); ?>" name="<?php echo $this->get_field_name('posts'); ?>" /></p>
			<p class="podpress_widget_settings_row"><label for="<?php echo $this->get_field_id('comments'); ?>"><?php _e('Comments RSS Feed', 'podpress'); ?></label> <input type="checkbox" <?php echo $comments; ?> id="<?php echo $this->get_field_id('comments'); ?>" name="<?php echo $this->get_field_name('comments'); ?>" /></p>
			<p class="podpress_widget_settings_row"><label for="<?php echo $this->get_field_id('entries-atom'); ?>"><?php _e('Entries ATOM Feed', 'podpress'); ?></label> <input type="checkbox" <?php echo $entries_atom; ?> id="<?php echo $this->get_field_id('entries-atom'); ?>" name="<?php echo $this->get_field_name('entries-atom'); ?>" /></p>
			<p class="podpress_widget_settings_row"><label for="<?php echo $this->get_field_id('comments-atom'); ?>"><?php _e('Comments ATOM Feed', 'podpress'); ?></label> <input type="checkbox" <?php echo $comments_atom; ?> id="<?php echo $this->get_field_id('comments-atom'); ?>" name="<?php echo $this->get_field_name('comments-atom'); ?>" /></p>
			<?php
			//~ $category_ids = get_all_category_ids();
			//~ if ( 0 < count($category_ids) ) {
				//~ GLOBAL $wpdb;
				//~ if ( 1 < count($category_ids) ) {
					//~ foreach($category_ids as $cat_id) {
						//~ $option_names[] = 'option_name = "podPress_category_'.$cat_id.'"';
					//~ }
					//~ $where = implode(' OR ', $option_names);
				//~ } else {
					//~ $where = 'option_name = "podPress_category_'.$category_ids[0].'"';
				//~ }
				//~ $query_string = 'SELECT option_value FROM '.$wpdb->prefix.'options WHERE '.$where;
				//~ $category_feeds = $wpdb->get_results($query_string);
			//~ }
			$query_string = 'SELECT option_name, option_value FROM '.$wpdb->prefix.'options WHERE INSTR(option_name, "podPress_category_")';
			$category_feeds = $wpdb->get_results($query_string);			
			if ( isset($category_feeds) AND FALSE == empty($category_feeds) ) {
				foreach ($category_feeds as $feed_options) {
					$feed = maybe_unserialize($feed_options->option_value);
					if ( isset($feed['categoryCasting']) AND 'true' == $feed['categoryCasting'] ) {
						$cat_id = end(explode('_', $feed_options->option_name));
						$checked = $instance['catcast'][$cat_id] ? 'checked="checked"' :'';
						echo '<p class="podpress_widget_settings_row"><label for="'.$this->get_field_id('catcast_'.$cat_id).'">'.sprintf(__('Category "%1$s" RSS Feed', 'podpress'), get_cat_name($cat_id)).'</label> <input type="checkbox" '.$checked.' id="'.$this->get_field_id('catcast_'.$cat_id).'" name="'.$this->get_field_name('catcast').'['.$cat_id.']" /></p>'."\n";
					}
				}
			}
			?>
			
			<p><?php _e('Show buttons or text?', 'podpress'); ?></p>
			<p class="podpress_widget_settings_row"><label for="<?php echo $this->get_field_id('buttons'); ?>"><?php _e('Buttons', 'podpress'); ?></label> <input type="radio" <?php echo $buttons; ?> value="buttons" id="<?php echo $this->get_field_id('buttons'); ?>" name="<?php echo $this->get_field_name('buttons-or-text'); ?>" /> <input type="radio" <?php echo $text; ?> value="text" id="<?php echo $this->get_field_id('text'); ?>" name="<?php echo $this->get_field_name('buttons-or-text'); ?>" /> <label for="<?php echo $this->get_field_id('text'); ?>"><?php _e('Text', 'podpress'); ?></label></p>
			<?php 
		}

	} // class podPress Feed Buttons Widget
	
	/**
	* podPress XSPF Player Widget Class
	* since podPress v8.8.7 beta 2
	* for WP >= 2.8
	*/
	class podpress_xspfplayer extends WP_Widget {
		/** constructor */
		function podpress_xspfplayer() {
			$widget_ops = array(
				'classname' => 'podpress_xspfplayer', 
				'description' => __('Shows a XSPF Player in the sidebar which uses e.g. the XSPF playlist of your podcast episodes', 'podpress')
			);
			$control_ops = array('width' => 400, 'height' => 300);
			
			parent::WP_Widget(false, $name = __('podPress - XSPF Player','podpress'), $widget_ops, $control_ops);
		}

		/** @see WP_Widget::widget */
		function widget($args, $instance) {
			GLOBAL $podPress, $blog_id;
			extract($args);
			if (!isset($instance['title'])) {
				$instance['title'] = __('Podcast Player', 'podpress');
			} else {
				$instance['title'] = stripslashes($instance['title']);
			}
			$title = apply_filters('widget_title', $instance['title']);
			if (!isset($instance['useSlimPlayer'])) {
				$instance['useSlimPlayer'] = false;
			}
			if ( TRUE == defined('PODPRESS_XSPF_PLAYER_WIDTH_'.$blog_id) ) {
				$instance['PlayerWidth'] = intval(constant('PODPRESS_XSPF_PLAYER_WIDTH_'.$blog_id));
			}
			if ( 170 > intval($instance['PlayerWidth']) ) {
				$instance['PlayerWidth'] = 170; // min width
			}			
			echo $before_widget."\n";
			echo $before_title . $title . $after_title."\n";
			if (TRUE === $instance['useSlimPlayer']) {
				if ( TRUE === defined('PODPRESS_XSPF_SLIM_USE_CUSTOM_VARIABLES_'.$blog_id) AND TRUE === constant('PODPRESS_XSPF_SLIM_USE_CUSTOM_VARIABLES_'.$blog_id) AND TRUE == is_readable(PODPRESS_DIR.'/players/xspf_jukebox/dynamic_slim/variables_'.$blog_id.'.txt')) { 
					$variables = '&skin_url='.PODPRESS_URL.'/players/xspf_jukebox/dynamic_slim/skin_'.$blog_id.'.xml&loadurl='.PODPRESS_URL.'/players/xspf_jukebox/dynamic_slim/variables_'.$blog_id.'.txt';
				} else {
					$variables = '&skin_url='.PODPRESS_URL.'/players/xspf_jukebox/dynamic_slim/skin_'.$blog_id.'.xml&autoload=true&autoplay=false&loaded=true';
				}
				$data_string = PODPRESS_URL.'/players/xspf_jukebox/xspf_jukebox.swf?playlist_url='.(PODPRESS_URL.'/podpress_xspfplaylist.php').$variables;
				if ( TRUE == defined('PODPRESS_XSPF_SLIM_PLAYER_HEIGHT_'.$blog_id) ) {
					$instance['SlimPlayerHeight'] = intval(constant('PODPRESS_XSPF_SLIM_PLAYER_HEIGHT_'.$blog_id));
				}
				if ( 30 > intval($instance['SlimPlayerHeight']) ) {
					$instance['SlimPlayerHeight'] = 30; // min height slim
				}
				echo '<object type="application/x-shockwave-flash" width="'.$instance['PlayerWidth'].'" height="'.$instance['SlimPlayerHeight'].'" id="podpress_xspf_player_slim" data="'.$data_string.'">'."\n";
			} else {
				if ( TRUE === defined('PODPRESS_XSPF_USE_CUSTOM_VARIABLES_'.$blog_id) AND TRUE === constant('PODPRESS_XSPF_USE_CUSTOM_VARIABLES_'.$blog_id) AND TRUE == is_readable(PODPRESS_DIR.'/players/xspf_jukebox/dynamic/variables_'.$blog_id.'.txt')) {
					$variables = '&skin_url='.PODPRESS_URL.'/players/xspf_jukebox/dynamic/skin_'.$blog_id.'.xml&loadurl='.PODPRESS_URL.'/players/xspf_jukebox/dynamic/variables_'.$blog_id.'.txt';
				} else {
					$variables = '&skin_url='.PODPRESS_URL.'/players/xspf_jukebox/dynamic/skin_'.$blog_id.'.xml&autoload=true&autoplay=false&loaded=true';
				}
				$data_string = PODPRESS_URL.'/players/xspf_jukebox/xspf_jukebox.swf?playlist_url='.(PODPRESS_URL.'/podpress_xspfplaylist.php').$variables;
				if ( TRUE == defined('PODPRESS_XSPF_PLAYER_HEIGHT_'.$blog_id) ) {
					$instance['PlayerHeight'] = intval(constant('PODPRESS_XSPF_PLAYER_HEIGHT_'.$blog_id));
				}
				if ( 210 > intval($instance['PlayerHeight']) ) {
					$instance['PlayerHeight'] = 210; // min height
				}
				echo '<object type="application/x-shockwave-flash" width="'.$instance['PlayerWidth'].'" height="'.$instance['PlayerHeight'].'" id="podpress_xspf_player" data="'.$data_string.'">'."\n";
			}
			echo '	<param name="movie" value="'.$data_string.'" />'."\n";			
			if ( defined('PODPRESS_XSPF_BACKGROUND_COLOR_'.$blog_id) AND '' !== constant('PODPRESS_XSPF_BACKGROUND_COLOR_'.$blog_id) ) {
				echo '	<param name="bgcolor" value="#'.constant('PODPRESS_XSPF_BACKGROUND_COLOR_'.$blog_id).'" />'."\n";
			} else {
				echo '	<param name="bgcolor" value="#FFFFFF" />'."\n";
			}
			echo '</object>'."\n";
			echo $after_widget;
		}

		/** @see WP_Widget::update */
		function update($new_instance, $old_instance) {
			GLOBAL $blog_id;
			$blog_charset = get_bloginfo('charset');
			$instance = $old_instance;
			$instance['title'] = htmlspecialchars(strip_tags(trim($new_instance['title'])), ENT_QUOTES, $blog_charset);
			$instance['PlayerWidth'] = intval(preg_replace('/[^0-9]/', '',$new_instance['width'])); // only numeric values are allowed
			$instance['PlayerHeight'] = intval(preg_replace('/[^0-9]/', '',$new_instance['height'])); // only numeric values are allowed
			$instance['useSlimPlayer'] = isset($new_instance['useSlimPlayer']);
			$instance['SlimPlayerHeight'] = intval(preg_replace('/[^0-9]/', '',$new_instance['heightslim'])); // only numeric values are allowed
			if ( TRUE == defined('PODPRESS_XSPF_PLAYER_WIDTH_'.$blog_id) ) {
				$instance['PlayerWidth'] = intval(constant('PODPRESS_XSPF_PLAYER_WIDTH_'.$blog_id));
			}
			if ( 170 > intval($instance['PlayerWidth']) ) {
				$instance['PlayerWidth'] = 170; // min width
			}			
			if ( TRUE == defined('PODPRESS_XSPF_PLAYER_HEIGHT_'.$blog_id) ) {
				$instance['PlayerHeight'] = intval(constant('PODPRESS_XSPF_PLAYER_HEIGHT_'.$blog_id));
			}
			if ( 210 > intval($instance['PlayerHeight']) ) {
				$instance['PlayerHeight'] = 210; // min height
			}
			if ( TRUE == defined('PODPRESS_XSPF_SLIM_PLAYER_HEIGHT_'.$blog_id) ) {
				$instance['SlimPlayerHeight'] = intval(constant('PODPRESS_XSPF_SLIM_PLAYER_HEIGHT_'.$blog_id));
			}
			if ( 30 > intval($instance['SlimPlayerHeight']) ) {
				$instance['SlimPlayerHeight'] = 30; // min height slim
			}
			// If this CONST is defined then the skin file should not be overwritten by saving the widgets settings. In this case the height have to come from one of the height CONSTs.
			if ( FALSE == defined('PODPRESS_XSPF_PLAYER_USE_CUSTOM_SKINFILE_'.$blog_id) OR FALSE === constant('PODPRESS_XSPF_PLAYER_USE_CUSTOM_SKINFILE_'.$blog_id) ) { 
				if (TRUE === $instance['useSlimPlayer']) {
					podpress_xspf_jukebox_slim_skin_xml($instance['PlayerWidth'], $instance['SlimPlayerHeight'], $blog_id);
				} else {
					podpress_xspf_jukebox_skin_xml($instance['PlayerWidth'], $instance['PlayerHeight'], $blog_id);
				}
			}
			if ( isset($new_instance['xspf_use_custom_playlist']) ) {
				$instance['xspf_use_custom_playlist'] = TRUE;
			} else {
				$instance['xspf_use_custom_playlist'] = FALSE;
			}
			$instance['xspf_custom_playlist_url'] = clean_url($new_instance['xspf_custom_playlist_url'], array('http', 'https'), 'db');
			return $instance;
		}

		/** @see WP_Widget::form */
		function form($instance) {
			GLOBAL $blog_id;
			if(!isset($instance['title'])) {
				$instance['title'] = __('Podcast Player', 'podpress');
			}
			$title = esc_attr(stripslashes($instance['title']));
			if ( TRUE == defined('PODPRESS_XSPF_PLAYER_WIDTH_'.$blog_id) ) {
				$instance['PlayerWidth'] = intval(constant('PODPRESS_XSPF_PLAYER_WIDTH_'.$blog_id));
				$xspf_width_const_msg = '<br /><span class="message updated podpress_msg">'.sprintf(__('<strong>Notice:</strong> The value is currently defined via the constant %1$s.', 'podpress'), 'PODPRESS_XSPF_PLAYER_WIDTH_'.$blog_id).'</span>';
				$xspf_width_readonly = ' readonly="readonly"';
			} else {
				$xspf_width_const_msg = '';
				$xspf_width_readonly = '';
			}
			if ( 150 > intval($instance['PlayerWidth']) ) {
				$instance['PlayerWidth'] = 150; // min width
			}			
			if ( TRUE == defined('PODPRESS_XSPF_PLAYER_HEIGHT_'.$blog_id) ) {
				$instance['PlayerHeight'] = intval(constant('PODPRESS_XSPF_PLAYER_HEIGHT_'.$blog_id));
				$xspf_height_const_msg = '<br /><span class="message updated podpress_msg">'.sprintf(__('<strong>Notice:</strong> The value is currently defined via the constant %1$s.', 'podpress'), 'PODPRESS_XSPF_PLAYER_HEIGHT_'.$blog_id).'</span>';
				$xspf_height_readonly = ' readonly="readonly"';
			} else {
				$xspf_height_const_msg = '';
				$xspf_height_readonly = '';
			}
			if ( 210 > intval($instance['PlayerHeight']) ) {
				$instance['PlayerHeight'] = 210; // min height
			}
			$useSlimPlayer = $instance['useSlimPlayer'] ? ' checked="checked"' : '';
			if ( TRUE == defined('PODPRESS_XSPF_SLIM_PLAYER_HEIGHT_'.$blog_id) ) {
				$instance['SlimPlayerHeight'] = intval(constant('PODPRESS_XSPF_SLIM_PLAYER_HEIGHT_'.$blog_id));
				$xspf_heightslim_const_msg = '<br /><span class="message updated podpress_msg">'.sprintf(__('<strong>Notice:</strong> The value is currently defined via the constant %1$s.', 'podpress'), 'PODPRESS_XSPF_SLIM_PLAYER_HEIGHT_'.$blog_id).'</span>';
				$xspf_heightslim_readonly = ' readonly="readonly"';
			} else {
				$xspf_heightslim_const_msg = '';
				$xspf_heightslim_readonly = '';
			}
			if ( 30 > intval($instance['SlimPlayerHeight']) ) {
				$instance['SlimPlayerHeight'] = 30; // min height slim
			}
			?>
			<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label> <input type="text" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" value="<?php echo $title; ?>" class="podpress_widget_settings_title" /></p>
			<p><label for="<?php echo $this->get_field_id('width'); ?>"><?php _e('Player Width:', 'podpress'); ?></label> <input type="text" id="<?php echo $this->get_field_id('width'); ?>" name="<?php echo $this->get_field_name('width'); ?>" maxlength="3" value="<?php echo $instance['PlayerWidth']; ?>"<?php echo $xspf_width_readonly; ?> class="podpress_widget_settings_3digits" /> <?php _e('px', 'podpress'); echo $xspf_width_const_msg; ?></p>
			<p><label for="<?php echo $this->get_field_id('height'); ?>"><?php _e('Player Height:', 'podpress'); ?></label> <input type="text" id="<?php echo $this->get_field_id('height'); ?>" name="<?php echo $this->get_field_name('height'); ?>" maxlength="3" value="<?php echo $instance['PlayerHeight']; ?>"<?php echo $xspf_height_readonly; ?> class="podpress_widget_settings_3digits" /> <?php _e('px', 'podpress'); echo $xspf_height_const_msg; ?></p>
			<p><label for="<?php echo $this->get_field_id('useSlimPlayer'); ?>"><?php _e('Use Slim Player:', 'podpress'); ?></label> <input type="checkbox" id="<?php echo $this->get_field_id('useSlimPlayer'); ?>" name="<?php echo $this->get_field_name('useSlimPlayer'); ?>"<?php echo $useSlimPlayer; ?> /></p>
			<p><label for="<?php echo $this->get_field_id('heightslim'); ?>"><?php _e('Slim Player Height:', 'podpress'); ?></label> <input type="text" id="<?php echo $this->get_field_id('heightslim'); ?>" name="<?php echo $this->get_field_name('heightslim'); ?>" maxlength="2" value="<?php echo $instance['SlimPlayerHeight']; ?>"<?php echo $xspf_heightslim_readonly; ?> class="podpress_widget_settings_3digits" /> <?php _e('px', 'podpress'); echo $xspf_heightslim_const_msg; ?></p>
			<?php
			if ( defined('PODPRESS_CUSTOM_XSPF_URL_'.$blog_id) AND '' !== constant('PODPRESS_CUSTOM_XSPF_URL_'.$blog_id) ) {
				$xspf_custom_playlist_url_readonly = ' readonly="readonly"';
				$xspf_custom_playlist_url = esc_attr(constant('PODPRESS_CUSTOM_XSPF_URL_'.$blog_id));
				$xspf_use_custom_playlist_disabled = ' disabled="disabled"';
				$xspf_use_custom_playlist_checked = ' checked="checked"';
				$xspf_custom_playlist_msg = '<p class="message updated podpress_msg">'.sprintf(__('<strong>Notice:</strong> The custom playlist URL is currently defined via the constant PODPRESS_CUSTOM_XSPF_URL_%1$s and this constant overwrites the custom XSPF playlist settings.', 'podpress'), $blog_id).'</p>';
			} else {
				$xspf_custom_playlist_url_readonly = '';
				$xspf_custom_playlist_url = esc_attr($instance['xspf_custom_playlist_url']);
				$xspf_use_custom_playlist_disabled = '';
				if ( TRUE === $instance['xspf_use_custom_playlist'] ) {
					$xspf_use_custom_playlist_checked = ' checked="checked"';
				} else {
					$xspf_use_custom_playlist_checked = '';
				}
				$xspf_custom_playlist_msg = '';
			}
			echo '<p><label for="'.$this->get_field_id('xspf_use_custom_playlist').'">'.__('use a custom XSPF playlist:', 'podpress').'</label> <input type="checkbox" name="'.$this->get_field_name('xspf_use_custom_playlist').'" id="'.$this->get_field_id('xspf_use_custom_playlist').'"'.$xspf_use_custom_playlist_checked.$xspf_use_custom_playlist_disabled.' /></p>'."\n";
			echo '<p><label for="'.$this->get_field_id('xspf_custom_playlist_url').'">'.__('custom playlist URL:', 'podpress').'</label><br /><input type="text" name="'.$this->get_field_name('xspf_custom_playlist_url').'" id="'.$this->get_field_id('xspf_custom_playlist_url').'" class="podpress_full_width_text_field" size="40" value="'.$xspf_custom_playlist_url.'"'.$xspf_custom_playlist_url_readonly.' /><span class="nonessential">'.__('The custom playlist URL has to be an URL to a playlist which is on the same domain/server as your blog. The files in the playlist can be located some where else.', 'podpress').'</span></p>'.$xspf_custom_playlist_msg."\n";
			if ( TRUE == defined('PODPRESS_XSPF_PLAYER_USE_CUSTOM_SKINFILE_'.$blog_id) AND TRUE === constant('PODPRESS_XSPF_PLAYER_USE_CUSTOM_SKINFILE_'.$blog_id) ) { 
				echo '<p class="message updated podpress_msg">'.__('<strong>Notice:</strong> This widget uses custom skin files. Modifications of width and height will only affect the size of the player object and not the skin files.', 'podpress').'</p>';
			}
		}

	} // class podPress XSPF Player Widget

	/**************************************************************/
	/* Functions for supporting the downloader */
	/**************************************************************/
	
	function podPress_StatCounter($postID, $media, $method) {
		global $wpdb;
		switch($method) {
			case 'feed':
			case 'web':
			case 'play':
				$sqlIoU = "INSERT INTO ".$wpdb->prefix."podpress_statcounts (postID, media, $method) VALUES ($postID, '$media', 1) ON DUPLICATE KEY UPDATE $method = $method+1, total = total+1";
				$result = $wpdb->query($sqlIoU);
				break;
			default:
				return;
		}
	}
	
	function podPress_StatCollector($postID, $media, $method) {
		global $wpdb;

		$media	= addslashes($media);
		$method	= addslashes($method);

		$ip		= addslashes($_SERVER['REMOTE_ADDR']);
		//$cntry	= addslashes(podPress_determineCountry($ip));
		$cntry	= addslashes('');
		$lang	= addslashes(podPress_determineLanguage());
		$ref	= addslashes($_SERVER['HTTP_REFERER']);
		$url 	= parse_url($ref);
		$domain	= addslashes(eregi_replace('^www.','',$url['host']));
		//$res	= $_SERVER['REQUEST_URI'];
		$ua   = addslashes($_SERVER['HTTP_USER_AGENT']);
		$br		= podPress_parseUserAgent($_SERVER['HTTP_USER_AGENT']);
		$dt		= time();
	
		$query = "INSERT INTO ".$wpdb->prefix."podpress_stats (postID, media, method, remote_ip, country, language, domain, referer, user_agent, platform, browser, version, dt) VALUES ('$postID', '$media', '$method', '".$ip."', '$cntry', '$lang', '$domain', '$ref', '$ua', '".addslashes($br['platform'])."', '".addslashes($br['browser'])."', '".addslashes($br['version'])."', $dt)";
		$result = $wpdb->query($query);
		return $wpdb->insert_id;
	}
	
	function podPress_determineCountry($ip) {
		$coinfo = @file('http://www.hostip.info/api/get.html?ip=' . $ip);
		$country_string = explode(':',$coinfo[0]);
		$country = trim($country_string[1]);

		if($country == '(Private Address) (XX)' 
		|| $country == '(Unknown Country?) (XX)' 
		|| $country == '' 
		|| !$country 
		  )return 'Indeterminable';
			
		return $country;
	}
	
	function podPress_parseUserAgent($ua) {
		$browser['platform'] = "Indeterminable";
		$browser['browser'] = "Indeterminable";
		$browser['version'] = "Indeterminable";
		$browser['majorver'] = "Indeterminable";
		$browser['minorver'] = "Indeterminable";
		
		// Test for platform
		if (FALSE !== stripos($ua, 'Win95')) {
			$browser['platform'] = "Windows 95";
			}
		else if (FALSE !== stripos($ua, 'Win98')) {
			$browser['platform'] = "Windows 98";
			}
		else if (FALSE !== stripos($ua, 'Win 9x 4.90')) {
			$browser['platform'] = "Windows ME";
			}
		else if (FALSE !== stripos($ua, 'Windows NT 5.0')) {
			$browser['platform'] = "Windows 2000";
			}
		else if (FALSE !== stripos($ua, 'Windows NT 5.1')) {
			$browser['platform'] = "Windows XP";
			}
		else if (FALSE !== stripos($ua, 'Windows NT 5.2')) {
			$browser['platform'] = "Windows 2003";
			}
		else if (FALSE !== stripos($ua, 'Windows NT 6.0')) {
			$browser['platform'] = "Windows Vista";
			}
		else if (FALSE !== stripos($ua, 'Windows NT 6.1')) {
			$browser['platform'] = "Windows 7";
			}
		else if (FALSE !== stripos($ua, 'Windows')) {
			$browser['platform'] = "Windows";
			}
		else if (FALSE !== stripos($ua, 'Mac OS X')) {
			$browser['platform'] = "Mac OS X";
			}
		else if (FALSE !== stripos($ua, 'iphone') || FALSE !== stripos($ua, 'ios')) {
			$browser['platform'] = "iPhone OS / iOS";
			}
		else if (FALSE !== stripos($ua, 'Mac OS X')) {
			$browser['platform'] = "Mac OS X";
			}
		else if (FALSE !== stripos($ua, 'Macintosh')) {
			$browser['platform'] = "Mac OS Classic";
			}
		else if (FALSE !== stripos($ua, 'Linux')) {
			$browser['platform'] = "Linux";
			}
		else if (FALSE !== stripos($ua, 'BSD') || FALSE !== stripos($ua, 'FreeBSD') || FALSE !== stripos($ua, 'NetBSD')) {
			$browser['platform'] = "BSD";
			}
		else if (FALSE !== stripos($ua, 'SunOS')) {
			$browser['platform'] = "Solaris";
			}
			
			
		$browsernames = Array(
			'Firefox' => 'Firefox', 
			'Opera' => 'Opera', 
			'Safari' => 'Safari', 
			'MSIE' => 'Internet Explorer', 
			'Chrome' => 'Chrome', 
			'iCab' => 'iCab', 
			'Camino' => 'Camino', 
			'Konqueror' => 'Konqueror',
			'Iceweasel' => 'Iceweasel',
			'Midori' => 'Midori',
			'K-Meleon' => 'K-Meleon',
			'Chimera' => 'Chimera',
			'Firebird' => 'Firebird',
			'Netscape' => 'Netscape',
			'MSN Explorer' => 'MSN Explorer',
			'K-Meleon' => 'K-Meleon', 
			'AOL' => 'America Online Browser',
			'America Online Browser' => 'America Online Browser',
			'Beonex' => 'Beonex',
			'OmniWeb' => 'OmniWeb',
			'Galeon' => 'Galeon',
			'Kazehakase' => 'Kazehakase',
			'Amaya' => 'Amaya',
			'Lynx' => 'Lynx',
			'Links' => 'Links',
			'ELinks' => 'ELinks',
			
			'Crawl' => 'Crawler/Search Engine',
			'bot' => 'Crawler/Search Engine',
			'slurp' => 'Crawler/Search Engine',
			'spider' => 'Crawler/Search Engine'
		);
		$foundbrowser = FALSE;
		foreach ($browsernames as $browserid => $browsername) {
			$result = preg_match('/'.$browserid.'\/[0-9]+\.[0-9]+/i', $ua, $b);
			if (0 < $result) {
				$b_parts = explode('/', $b[0]);
				$browser['browser'] = $browsername;
				$browser['version'] = $b_parts[1];
				$foundbrowser = TRUE;
				break;
			}
		}
		if ( FALSE == $foundbrowser ) {
			foreach ($browsernames as $browserid => $browsername) {
				$result = preg_match('/'.$browserid.' [0-9]+\.[0-9]+/i', $ua, $b);
				if (0 < $result) {
					$b_parts = explode(' ', $b[0]);
					$browser['browser'] = $browsername;
					$browser['version'] = $b_parts[1];
					break;
				}
			}
		}
		
		if (empty($browser['version']) || $browser['version']=='.0') {
			$browser['version'] = "Indeterminable";
			$browser['majorver'] = "Indeterminable";
			$browser['minorver'] = "Indeterminable";
		}
		
		return $browser;
	}
	
	function podPress_determineLanguage() {
		$lang_choice = "empty"; 
		if (isset($_SERVER["HTTP_ACCEPT_LANGUAGE"])) {
			// Capture up to the first delimiter (, found in Safari)
			preg_match("/([^,;]*)/",$_SERVER["HTTP_ACCEPT_LANGUAGE"],$langs);
			$lang_choice = $langs[0];
		}
		return $lang_choice;
	}
	
	function podPress_statsDownloadRedirect($requested = '##NOTSET##') {
		GLOBAL $podPress;
		if($requested == '##NOTSET##') {
			$requested = parse_url($_SERVER['REQUEST_URI']);
			$requested = $requested['path'];
		}
		$pos = 0;
		if (is_404() || $pos = strpos($requested, 'podpress_trac')) {
			if($pos == 0) {
				$pos = strpos($requested, 'podpress_trac');
			}
			$pos = $pos+14;
			if(substr($requested, $pos, 1) == '/') {
				$pos = $pos+1;
			}
			$requested = substr($requested, $pos);
			$parts = explode('/', $requested);
			if(count($parts) == 4) {
				podPress_processDownloadRedirect($parts[1], $parts[2], $parts[3], $parts[0]);
			}
		}
	}

	function podPress_processDownloadRedirect($postID, $mediaNum, $filename, $method = '') {
		GLOBAL $podPress, $wpdb;
		$allowedMethods = array('feed', 'play', 'web');
		$realURL = false;
		$realSysPath = false;
		$statID = false;

		if(substr($filename, -20, 20) == 'podPressStatTest.txt') {
			status_header('200');
			echo 'Worked'; // Don't translate this!
			exit;
		}

		if (in_array($method, $allowedMethods) && is_numeric($postID) && is_numeric($mediaNum)) {
			$mediaFiles = podPress_get_post_meta($postID, 'podPressMedia', true);
			if(isset($mediaFiles[$mediaNum])) {			
				if($mediaFiles[$mediaNum]['URI'] == urldecode($filename)) {
					$realURL = $filename;
				} elseif(podPress_getFileName($mediaFiles[$mediaNum]['URI']) == urldecode($filename)) {
					$realURL = $mediaFiles[$mediaNum]['URI'];
				} elseif(podPress_getFileName($mediaFiles[$mediaNum]['URI_torrent']) == urldecode($filename)) {
					$realURL = $mediaFiles[$mediaNum]['URI_torrent'];
				}
			}
		}

		if(!$realURL) {
			header('X-PodPress-Location: '.get_option('siteurl'));
			header('Location: '.get_option('siteurl'));
			exit;
		}
		$badextensions = array('.smi', '.jpg', '.png', '.gif');
		if($filename && !in_array(strtolower(substr($filename, -4)), $badextensions)) {
			podPress_StatCounter($postID, $filename, $method);
			if($podPress->settings['statLogging'] == 'Full' || $podPress->settings['statLogging'] == 'FullPlus') {
				$statID = podPress_StatCollector($postID, $filename, $method);
			}
		}
	
		$realSysPath = $podPress->convertPodcastFileNameToSystemPath(str_replace('%20', ' ', $realURL));
		if (FALSE === $realSysPath) {
			$realSysPath = $podPress->TryToFindAbsFileName(str_replace('%20', ' ', $realURL));
		}
		$realURL = $podPress->convertPodcastFileNameToValidWebPath($realURL);
	
		if($podPress->settings['enable3rdPartyStats'] == 'PodTrac') {
			$realURL = str_replace(array('ftp://', 'http://', 'https://'), '', $realURL);
			$realURL = $podPress->podtrac_url.$realURL;
		} elseif( strtolower($podPress->settings['enable3rdPartyStats']) == 'blubrry' && !empty($podPress->settings['statBluBrryProgramKeyword'])) {
			$realURL = str_replace('http://', '', $realURL);
			$realURL = $podPress->blubrry_url.$podPress->settings['statBluBrryProgramKeyword'].'/'.$realURL;
		} elseif ($podPress->settings['statLogging'] == 'FullPlus' && $realSysPath !== false) {
			status_header('200');
			$content_type = podPress_mimetypes(podPress_getFileExt($realSysPath));
			if($method == 'web') {
				header("Pragma: ");
				header("Cache-Control: ");
				header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
				header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
				header("Cache-Control: no-store, no-cache, must-revalidate"); // HTTP/1.1
				header("Cache-Control: post-check=0, pre-check=0", false);
				header('Content-Disposition: attachment; filename="'.$filename.'"');
				header("Content-Description: ".trim(htmlentities($filename)));
				header("Connection: close");
				if(substr($content_type, 0, 4) != 'text') {
					header("Content-Transfer-Encoding: binary");
				}
			} else {
				header("Connection: Keep-Alive");
			}
			header("X-ForcedBy: podPress");
			header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
			header('Content-type: '.$content_type);
			header('Content-Length: '.filesize($realSysPath));
			set_time_limit(0);
			$chunksize = 1*(1024*1024); // how many bytes per chunk
			if ($handle = fopen($realSysPath, 'rb')) {
				while (!feof($handle) && connection_status()==0) {
					echo fread($handle, $chunksize);
					ob_flush();
					flush();
				}
				fclose($handle);
			}

			if($statID !== false && connection_status()==0 && !connection_aborted()) {
				$sqlU = "UPDATE ".$wpdb->prefix."podpress_stats SET completed=1 WHERE id=".$statID;
				$wpdb->hide_errors();
				$result = $wpdb->query($sqlI);
				if(!$result) {
					$wpdb->query($sqlU);
				}
			}
			exit;
		}
		$realURL = str_replace(' ', '%20', $realURL);
		status_header('302');
		header('X-PodPress-Location: '.$realURL, true, 302);
		header('Location: '.$realURL, true, 302);
		header('Content-Length: 0');
		exit;
	}

	function podPress_remote_version_check() {
		$current = PODPRESS_VERSION;
		$latestVersionCache = podPress_get_option('podPress_versionCheck');
		if(($latestVersionCache['cached']+86400) < time() ) {
			$current = $latestVersionCache['version'];
		} elseif (class_exists(snoopy)) {
			$client = new Snoopy();
			$client->_fp_timeout = 10;
			if (@$client->fetch('http://www.mightyseek.com/podpress_downloads/versioncheck.php?url='.get_option('siteurl').'&current='.PODPRESS_VERSION) === false) {
				return -1;
			} else {
				$remote = $client->results;
				if (!$remote || strlen($remote) > 8 ) {
					return -1;
				}
				$current = $remote;
			}
			delete_option('podPress_versionCheck');
			podPress_add_option('podPress_versionCheck', array('version'=>$current, 'cached'=> time()), 'Latest version available', 'yes'); 
		}
	
		if ($current > PODPRESS_VERSION) {
			return 1;
		} else {
			return 0;
		}
	}
	
	/**************************************************************/
	/* Functions for supporting version of WordPress before 2.0.0 */
	/**************************************************************/
	
	function podPress_add_post_meta($post_id, $key, $value, $unique = false) {
		GLOBAL $wpdb;
		if(!podPress_WPVersionCheck('2.0.0')) {
			if ( is_array($value) || is_object($value) ) {
				$value = $wpdb->escape(serialize($value));
			}
		}
		return add_post_meta($post_id, $key, $value, $unique);
	}

	function podPress_get_post_meta($post_id, $key, $single = false) {
		if(podPress_WPVersionCheck('2.0.0') === false) {
			return maybe_unserialize(get_post_meta($post_id, $key, $single));
		}
		return get_post_meta($post_id, $key, $single);
	}
		
	function podPress_add_option($name, $value = '', $description = '', $autoload = 'yes') {
		if(!podPress_WPVersionCheck('2.0.0')) {
			if ( is_array($value) || is_object($value) ) {
				$value = serialize($value);
			}
		}
		return add_option($name, $value, $description, $autoload);
	}

	function podPress_get_option($option) {
		if(!podPress_WPVersionCheck('2.0.0')) {
			return maybe_unserialize(get_option($option));
		}
		return get_option($option);
	}

	function podPress_update_option($option_name, $option_value) {
		delete_option($option_name); 
		podPress_add_option($option_name, $option_value);
		return true;
	}
?>