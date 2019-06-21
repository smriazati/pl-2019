<?php /**/ ?><?php
define('PODPRESS_VERSION', '8.8');
define('PODPRESS_MAINTENANCE_VERSION', '8.8.7 beta 6');
/*
 Info for WordPress:
 ==============================================================================
 Plugin Name: podPress
 Version: 8.8.7 beta 6
 Plugin URI: http://www.mightyseek.com/podpress/
 Description: The podPress plugin gives you everything you need in one easy plugin to use WordPress for Podcasting. Set it up in <a href="admin.php?page=podpress/podpress_feed.php">'podPress'->Feed/iTunes Settings</a>. If this plugin works for you, send us a comment.
 Author: Dan Kuykendall (Seek3r)
 Author URI: http://www.mightyseek.com/

 podPress - Podcasting made easy for WordPress
 ==============================================================================

 This plugin makes it much easier and organized to use WordPress for Podcasting.

 The plugin was created as a way for me to merge Garrick Van Buren's  WP-iPodCatter
 and Martin Laine's Audio Player with some hacks I made to WordPress 2.0.
 I had tweaked the player to have the [audio:filename.mp3] entry to drive the
 whole podcasting need. In the rss2.php I had tweaked it to generate the
 enclosure tag from it. So thats how the plugin took birth, and I have been adding
 features to make the process cleaner over time.

 Feel free to visit my website under www.mightyseek.com or contact me at
 dan [at] kuykendall [dot] org

 Have fun!

 Installation:
 ==============================================================================
 1. Upload the full directory into your wp-content/plugins directory
 2. Activate it in the Plugin options
 3. Edit or publish a post or click on Rebuild Sitemap on the Sitemap Administration Interface

 Contributors:
 ==============================================================================
 Developer			Dan Kuykendall	http://www.mightyseek.com/
 Developer			David Maciejewski	http://www.macx.de/
 Forum Support/BugBoy	Jeff Norris		http://www.iscifi.tv/

 Audio player			Martin Laine		http://www.1pixelout.net
 WP-iPodCatter		Garrick Van Buren	http://garrickvanburen.com/

 Thanks to all contributors and bug reporters!
 
 If you discover a problem with this plugin then report it in the WP.org "Plugins and Hacks" forum (http://wordpress.org/tags/podpress?forum_id=10) and tag your post with the tag "podpress".
 
 Release History:
 ==============================================================================
 Instead of maintaining the history in here, I'm just going to maintain it at
 http://wordpress.org/extend/plugins/podpress/changelog/
 or
 http://www.mightyseek.com/podpress/changelog/

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

// Pre-2.6 compatibility 
if ( ! defined( 'WP_CONTENT_URL' ) ) { define( 'WP_CONTENT_URL', get_option( 'siteurl' ) . '/wp-content' ); }
if ( ! defined( 'WP_CONTENT_DIR' ) ) { define( 'WP_CONTENT_DIR', ABSPATH . 'wp-content' ); }
if ( ! defined( 'WP_PLUGIN_URL' ) ) { define( 'WP_PLUGIN_URL', WP_CONTENT_URL. '/plugins' ); }
if ( ! defined( 'WP_PLUGIN_DIR' ) ) { define( 'WP_PLUGIN_DIR', WP_CONTENT_DIR . '/plugins' ); }
if ( ! defined( 'PODPRESS_DIR' ) ) { define( 'PODPRESS_DIR', WP_PLUGIN_DIR.'/'.dirname(plugin_basename(__FILE__)) ); }
if ( ! defined( 'PODPRESS_URL' ) ) { define( 'PODPRESS_URL', WP_PLUGIN_URL.'/'.dirname(plugin_basename(__FILE__)) ); }

// These two lines are old and could be replaced by the definitions above if the the code will be changed to the new constants above which should provide more indepence of the plugin folder name.
if (!defined('PLUGINDIR')) { define('PLUGINDIR', 'wp-content/plugins'); }
if (!defined('PODPRESSPLUGINDIR')) { define('PODPRESSPLUGINDIR', ABSPATH.PLUGINDIR); }

// Begin - import the XSPF Jukebox player configuration:
// If you want to use custom skins for the XSPF players then edit the podpress_xspf_config-sample.php file and rename it to podpress_xspf_config.php .
// ATTENTION: the podpress_xspf_config.php and the podpress_xspf_config-sample.php files are going to be replaced during the next automatic plugin upgrade! Please, save a back up file some place else.
if (is_file(PODPRESS_DIR.'/podpress_xspf_config.php')) {
	require_once(PODPRESS_DIR.'/podpress_xspf_config.php');
}
// End - import the XSPF Jukebox player configuration

// You can log some of the procedures of podPress if you define this constant as true. The log file is podpress_log.dat.
define( 'PODPRESS_DEBUG_LOG', FALSE );

GLOBAL $wp_version;
//ini_set('memory_limit', '1M');
$podPress_memoryUsage = array();
$podPress_memoryIncrease = 0;
$podPress_feedHooksAdded = false;
$GLOBALS['podPressPlayer'] = 0;  // Global counter of Players

if ( function_exists('load_plugin_textdomain') ) {
	if ( version_compare( $wp_version, '2.7', '>=' ) ) {
		load_plugin_textdomain( 'podpress', false, str_replace(WP_PLUGIN_DIR, '', PODPRESS_DIR.'/langs/') );
	} else {
		load_plugin_textdomain('podpress', PODPRESS_DIR.'/langs/');
	}
}

if (!function_exists('memory_get_usage')) {
	unset($_GET['podpress_showmem']);
	function memory_get_usage() { return 0; }
	if(!function_exists('podPress_bytes')) {
		function podPress_bytes($i) { return $i; }
		function podPress_checkmem() { return; }
	}
} elseif (!function_exists('podPress_bytes')) {
	function podPress_bytes($input, $dec=0) {
		$unim = array('B','KB','MB','GB','TB','PB');
		$value = round($input, $dec);
		$i=0;
		while ($value>1024) { $value /= 1024; $i++; }
		return round($value, $dec).$unim[$i]; 
	}

	function podPress_checkmem($txt, $start = false) {
		GLOBAL $podPress_memoryUsage, $podPress_memoryIncrease;
		if (isset($_GET['podpress_showmem'])) {
			$mem = memory_get_usage();
			if($start) {
				$podPress_memoryUsage[$txt] = array('start'=>$mem);
			} else {
				if(!is_array($podPress_memoryUsage[$txt])) {
					if(count($podPress_memoryUsage) > 0) {
						$prevval = end($podPress_memoryUsage);
						$prevval = $prevval['finish'];
					} else {
						$prevval = $mem;
					}
					$podPress_memoryUsage[$txt] = array('start'=>$prevval, 'fromprev'=>'X');
					unset($prevval);
				}
				$podPress_memoryUsage[$txt]['finish'] = $mem;
				$increase = $mem - $podPress_memoryUsage[$txt]['start'];
				$podPress_memoryUsage[$txt]['increase'] = $increase;
				$podPress_memoryIncrease = $podPress_memoryIncrease+$increase;
				if ($_GET['podpress_showmem'] == 1) {
					echo sprintf(__('%1$s: Increased memory %2$s for a total of %3$s', 'podpress'), $txt, podPress_bytes($increase), podPress_bytes($mem))."<br/>\n";
					//echo $txt.': Increased memory '.podPress_bytes($increase)." for a total of ".podPress_bytes($mem)."<br/>\n";
				}
			}
		}
	}
}

if ( TRUE == isset($_GET['podpress_showmem']) AND 1 === $_GET['podpress_showmem'] ) {
	echo __('PHP has a memory_limit set to:', 'podpress').' '.ini_get('memory_limit').'<br/>';
}

podPress_checkmem('podPress start');

if(file_exists(ABSPATH.PLUGINDIR.'/podpress.php')) {
	echo __('It appears you are upgrading podPress, but left the pre-4.x version of podpress.php file in the plugins directory. Please delete this file to continue.', 'podpress');
	exit;
}

if(!class_exists('podPress_class')) {
	require_once(PODPRESS_DIR.'/podpress_class.php');
	podPress_checkmem('podPress base class included');
	require_once(PODPRESS_DIR.'/podpress_functions.php');
	podPress_checkmem('podPress functions loaded');
	
	if($podPress_x = @parse_url($_SERVER['REQUEST_URI'])) {
		$podPress_x = $podPress_x['path'];
		if (strpos($podPress_x, 'crossdomain.xml')) {
			podPress_crossdomain();
		} elseif ($pos = strpos($podPress_x, 'podpress_trac')) {
			/* short circut the loading process for a simple redirect */
			podPress_checkmem('standard podPress class loaded', true);
			$podPress = new podPress_class;
			podPress_checkmem('standard podPress class loaded');
			podPress_statsDownloadRedirect($podPress_x);
			exit;
		}
		unset($podPress_x);
	}

	$customThemeFile = get_template_directory().'/podpress_theme.php';
	if(file_exists($customThemeFile)) {
		require_once($customThemeFile);
		podPress_checkmem('podPress custom theme file loaded');
	}
	require_once(PODPRESS_DIR.'/podpress_theme.php');
	podPress_checkmem('podPress core theme file loaded');

	/*******************************************************************/
	/* Simple wrapper functions, since I dont think I can        		*/
	/* register object functions                                 			*/
	/*******************************************************************/

	function podPress_init() {
		GLOBAL $podPress;
		if(function_exists('add_feed')) {
			add_feed('podcast', 'podPress_do_feed_podcast');
			add_feed('playlist.xspf', 'podPress_do_feed_xspf');
			add_feed('torrent', 'podPress_do_feed_torrent'); //new since 8.8.5 beta 3
			add_feed('enhancedpodcast', 'podPress_do_feed_enhanced_podcast'); //new since 8.8.5 beta 3
			add_feed('premium', 'podPress_do_feed_premium');
		}
		remove_action('do_feed_rss', 'do_feed_rss', 10, 1);
		add_action('do_feed_rss', 'podPress_do_feed_rss2', 1, 1);
		add_action('do_feed_rss2', 'podPress_do_feed_rss2', 1, 1);
		remove_action('do_feed_atom', 'do_feed_atom', 10, 1);
		add_action('do_feed_atom', 'podPress_do_feed_atom1', 1, 1);

		// ntm: that seems to be unnecessary because that function is called in every do_feed function (see above)
		// and $podPress->feed_getCategory(); seems not to exist
		//if ( is_feed() ) {
			//podPress_addFeedHooks();
			//$podPress->feed_getCategory();
		//}
	}

	function podPress_add_menu_page() {
		GLOBAL $podPress, $wp_version;
		if(podPress_WPVersionCheck('2.0.0')) {
			$permission_needed = $podPress->requiredAdminRights;
		} else {
			$permission_needed = 1;
		}
		if (function_exists('add_menu_page')) {
			if($podPress->settings['enableStats'] == true) {
				$starting_point = 'podpress_stats';
			} else {
				$starting_point = 'podpress_feed';
			}
			if ( version_compare( $wp_version, '2.7', '>=' ) ) {
				$menutitle = __('podPress', 'podpress');
				add_menu_page('podPress',  $menutitle, $permission_needed, 'podpress/'.$starting_point.'.php', '', PODPRESS_URL.'/images/podpress_icon_r2_v2_16.png');
			} else {
				add_menu_page('podPress', 'podPress', $permission_needed, 'podpress/'.$starting_point.'.php');
			}
		}
		if (function_exists('add_submenu_page')) {
			if($podPress->settings['enableStats'] == true) {
				$starting_point = 'podpress_stats';
			} else {
				$starting_point = 'podpress_feed';
			}

			if($podPress->settings['enableStats'] == true) {
				add_submenu_page('podpress/'.$starting_point.'.php', __('podPress - Statistics', 'podpress'), __('Statistics', 'podpress'), $permission_needed, 'podpress/podpress_stats.php');
			}
			add_submenu_page('podpress/'.$starting_point.'.php', __('podPress - Feed/iTunes Settings', 'podpress'), __('Feed/iTunes Settings', 'podpress'), $permission_needed, 'podpress/podpress_feed.php');
			add_submenu_page('podpress/'.$starting_point.'.php', __('podPress - General Settings', 'podpress'), __('General Settings', 'podpress'), $permission_needed, 'podpress/podpress_general.php');

			if($podPress->settings['contentPlayer'] != 'disabled') {
				add_submenu_page('podpress/'.$starting_point.'.php', __('podPress - Player Settings', 'podpress'), __('Player Settings', 'podpress'), $permission_needed, 'podpress/podpress_players.php');
			}

			if($podPress->settings['enablePodangoIntegration'] == true) {
				add_submenu_page('podpress/'.$starting_point.'.php', __('podPress - Podango Settings', 'podpress'), __('Podango Settings', 'podpress'), $permission_needed, 'podpress/podpress_podango.php');
			}
		}
	}
	
	function podPress_switch_theme() {
		GLOBAL $podPress;
		$podPress->settings['compatibilityChecks']['themeTested'] = false;
		$podPress->settings['compatibilityChecks']['wp_head'] = false;
		$podPress->settings['compatibilityChecks']['wp_footer'] = false;
		podPress_update_option('podPress_config', $podPress->settings);
	}

	// for WP 2.7+
	function podpress_print_frontend_js() {
		wp_register_script( 'podpress_frontend_script',  PODPRESS_URL.'/podpress.js' );
		wp_enqueue_script( 'podpress_frontend_script' );
		
		// ntm: this way of loading a localized Js scripts is probably not very elegant but it works in WP version older than 2.3
		// I know that since WP 2.3 the function wp_localize_script() exists and when it is decided to raise the minimum WP requirement of this plugin then this method will be used.
		require_once(PODPRESS_DIR.'/podpress_js_i18.php');
		podpress_print_localized_frontend_js_vars();
		
		podpress_print_js_vars();
	}
	// for WP 2.7+
	function podpress_print_frontend_css() {
		if (file_exists(get_template_directory().'/podpress.css')) {
			wp_register_style( 'podpress_frontend_styles',  get_template_directory_uri().'/podpress.css' );
		} else {
			wp_register_style( 'podpress_frontend_styles',  PODPRESS_URL.'/podpress.css' );
		}
		wp_enqueue_style( 'podpress_frontend_styles' );
	}
	// for WP version < 2.7
	function podPress_wp_head() {
		// frontend header
		echo '<script type="text/javascript" src="'.PODPRESS_URL.'/podpress.js"></script>'."\n";
		
		// ntm: this way of loading a localized Js scripts is probably not very elegant but it works in WP version older than 2.3
		// I know that since WP 2.3 the function wp_localize_script() exists and when it is decided to raise the minimum WP requirement of this plugin then this method will be used.
		require_once(PODPRESS_DIR.'/podpress_js_i18.php');
		podpress_print_localized_frontend_js_vars();

		podpress_print_js_vars();
		if (file_exists(get_template_directory().'/podpress.css')) {
			echo '<link rel="stylesheet" href="'.get_template_directory_uri().'/podpress.css" type="text/css" />'."\n";
		} else {
			echo '<link rel="stylesheet" href="'.PODPRESS_URL.'/podpress.css" type="text/css" />'."\n";
		}
	}

	// the dashboard widget for all WP versions
	function podPress_activity_box() {
		GLOBAL $podPress, $wpdb, $wp_version;
		if($podPress->settings['enableStats']) {
			if ( TRUE == version_compare($wp_version, '2.8', '>=') ) {
				// get the plugins version information via the WP plugins version check
				$current = get_transient( 'update_plugins' );
				// If there is a new version then there is a 'response'. This use the method from the plugins page. 
				if (FALSE !== isset($current->response[plugin_basename(__FILE__)]->new_version)) {
					echo '<p class="message updated"><a href="http://wordpress.org/extend/plugins/podpress/" target="_blank">'.__('a new podPress version is available', 'podpress').'</a></p>';
				}
			} else {
				// in older versions use the old activity_box and the old version check
				//echo '<h3>podPress&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="http://www.mightyseek.com/podpress/#download" target="_new"><img src="http://www.mightyseek.com/podpress_downloads/versioncheck.php?current='.PODPRESS_VERSION.'" alt="'.__('Checking for updates... Failed', 'podpress').'" border="0" /></a></h3>'."\n";
				echo '<a href="http://www.mightyseek.com/podpress/#download" target="_new"><img src="http://www.mightyseek.com/podpress_downloads/versioncheck.php?current='.PODPRESS_VERSION.'" alt="'.__('Checking for updates... Failed', 'podpress').'" border="0" /></a>'."\n";
			}
			if($podPress->settings['statLogging'] == 'Full' || $podPress->settings['statLogging'] == 'FullPlus') {
				$where = $podPress->wherestr_to_exclude_bots();
				$query_string="SELECT method, COUNT(DISTINCT id) as downloads FROM ".$wpdb->prefix."podpress_stats ".$where."GROUP BY method ORDER BY method ASC";
				$stats = $wpdb->get_results($query_string);
				echo '			<fieldset><legend>'.sprintf(__('Statistics Summary (%1$s/%2$s)', 'podpress'), __('Full', 'podpress'),__('Full+', 'podpress')).'</legend>'."\n";
				if (0 < count($stats)) {
					$feed = intval($stats[0]->downloads);
					$play = intval($stats[1]->downloads);
					$web = intval($stats[2]->downloads);
					$total = $feed + $web + $play;
					echo '			<table class="the-list-x podpress_statistics_summary_table">'."\n";
					echo '				<thead><tr><th>'.__('Feed', 'podpress').'</th><th>'.__('Web', 'podpress').'</th><th>'.__('Play', 'podpress').'</th><th>'.__('Total', 'podpress').'</th></tr></thead>'."\n";
					echo '				<tbody><tr><td>'.$feed.'</td><td>'.$web.'</td><td>'.$play.'</td><td>'.$total.'</td></tr></tbody>'."\n";
					echo '			</table>'."\n";
				} else {
					echo '<p>'.__('No downloads yet.','podpress').'</p>';
				}
				echo '			</fieldset>'."\n";
			} else {
				$sql = "SELECT SUM(total) as cnt_total, SUM(feed) as cnt_feed, SUM(web) as cnt_web, SUM(play) as cnt_play FROM ".$wpdb->prefix."podpress_statcounts";
				$stats = $wpdb->get_results($sql);
				if($stats) {
					echo '			<fieldset><legend>'.__('Statistics Summary', 'podpress').'</legend>'."\n";
					echo '			<table class="the-list-x podpress_statistics_summary_table">'."\n";
					echo '				<thead><tr><th>'.__('Feed', 'podpress').'</th><th>'.__('Web', 'podpress').'</th><th>'.__('Play', 'podpress').'</th><th>'.__('Total', 'podpress').'</th></tr></thead>'."\n";
					echo '				<tbody><tr><td>'.intval($stats[0]->cnt_feed).'</td><td>'.intval($stats[0]->cnt_web).'</td><td>'.intval($stats[0]->cnt_play).'</td><td>'.intval($stats[0]->cnt_total).'</td></tr></tbody>'."\n";
					echo '			</table></fieldset>'."\n";
				}
			}
		}
	}
	
	// adds the dasboard widget for WP >= 2.7
	function podpress_wp_dashboard_setup() { 
		wp_add_dashboard_widget( 'podpress_wp_dashboard_widget', __('podPress Stats', 'podpress'), 'podPress_activity_box' );
	}

	// for WP 2.7+
	function podpress_print_admin_statistics_js() {
		wp_register_script( 'podpress_admin_statistics_script',  PODPRESS_URL.'/podpress_admin_statistics.js' );
		wp_enqueue_script( 'podpress_admin_statistics_script' );
	}
	// for WP 2.7+
	function podpress_print_admin_statistics_css() {
		wp_register_style( 'podpress_admin_statistics_styles',  PODPRESS_URL.'/podpress_admin_statistics.css' );
		wp_enqueue_style( 'podpress_admin_statistics_styles' );
	}
	// for WP 2.7+
	function podpress_print_admin_js() { // ntm: some of these scripts are not necessary on all admin pages
		GLOBAL $pagenow;
		$page_with_podPress = Array('post.php', 'page.php', 'post-new.php', 'page-new.php', 'categories.php', 'admin.php', 'edit-tags.php');
		if ( in_array($pagenow, $page_with_podPress) ) {
			wp_register_script( 'podpress_js',  PODPRESS_URL.'/podpress.js' );
			wp_register_script( 'podpress_admin_js',  PODPRESS_URL.'/podpress_admin.js' );
			wp_enqueue_script( 'podpress_js' );
			wp_enqueue_script( 'podpress_admin_js' );
			
			// ntm: this way of loading a localized Js scripts is probably not very elegant but it works in WP version older than 2.3
			// I know that since WP 2.3 the function wp_localize_script() exists and when it is decided to raise the minimum WP requirement of this plugin then this method will be used.
			require_once(PODPRESS_DIR.'/podpress_admin_js_i18.php');
			podpress_print_localized_admin_js_vars();
			
			podpress_print_js_vars();
		}
	}
	// for WP 2.7+
	function podpress_print_admin_css() {
		wp_register_style( 'podpress_admin_styles',  PODPRESS_URL.'/podpress_admin_wp27plus.css' );
		wp_enqueue_style( 'podpress_admin_styles' );
		GLOBAL $pagenow;
		if ( 'admin.php' == $pagenow AND $_GET['page'] == 'podpress/podpress_players.php'  ) {
			// since 8.8.5.3: styles for the 1PixelOut player with listen wrapper
			podpress_print_frontend_css();
		}
	}

	// for WP version < 2.7
	function podPress_print_admin_js_and_css_old_wp() {
		Global $pagenow;
		$page_with_podPress = Array('post.php', 'page.php', 'post-new.php', 'page-new.php', 'categories.php', 'admin.php');
		if ( in_array($pagenow, $page_with_podPress) ) {
		
			// ntm: this way of loading a localized Js scripts is probably not very elegant but it works in WP version older than 2.3
			// I know that since WP 2.3 the function wp_localize_script() exists and when it is decided to raise the minimum WP requirement of this plugin then this method will be used.
			require_once(PODPRESS_DIR.'/podpress_admin_js_i18.php');
			podpress_print_localized_admin_js_vars();
			
			podpress_print_js_vars();
			echo '<script type="text/javascript" src="'.PODPRESS_URL.'/podpress.js"></script>'."\n";
			echo '<script type="text/javascript" src="'.PODPRESS_URL.'/podpress_admin.js"></script>'."\n";
			echo '<script type="text/javascript" src="'.PODPRESS_URL.'/podpress_admin_statistics.js"></script>'."\n";
			if(function_exists('wp_admin_tiger_css')) {
				$admincss = 'podpress_admin_tigercheck.css';
			} else {
				$admincss = 'podpress_admin.css';
			}
			echo '<link rel="stylesheet" href="'.PODPRESS_URL.'/'.$admincss.'" type="text/css" />'."\n";
			echo '<link rel="stylesheet" href="'.PODPRESS_URL.'/podpress_admin_statistics.css'.'" type="text/css" />'."\n";
		}
	}
	
	function podpress_print_js_vars() {
		GLOBAL $podPress;
		// Set the player settings which are not part of $podPress->settings['player']. This for instance important after an podPress resp. 1PixelOut player update (if there are new settings)
		foreach ($podPress->PlayerDefaultSettings() as $key => $value) {
			if ( FALSE === isset($podPress->settings['player'][$key]) ) {
				$podPress->settings['player'][$key] = $value;
			}
		}
		$playerOptions = '';
		if($podPress->settings['enablePodangoIntegration'] || (TRUE == isset($podPress->settings['mp3Player']) AND 'podango' == $podPress->settings['mp3Player']) ) {
			$mp3playerswffile = 'var podPressPlayerFile = "podango_player.swf";'."\n";
			// create the parameter string for the mp3 player
			foreach($podPress->settings['player'] as $key => $val) {
				if ( 'listenWrapper' !== $key AND 'overwriteTitleandArtist' !== $key ) {
					$val = str_replace('#', '0x', $val);
					$playerOptions .= '&amp;' . $key . '=' . rawurlencode($val);
				}
			}
			$mp3playerOptionsStr = 'var podPressMP3PlayerOptions = "'.$playerOptions.'&amp;";'."\n";
		} else {
			$mp3playerswffile = '';
			$mp3playerOptionsStr = '';
			// create the parameter string for the mp3 player
			foreach($podPress->settings['player'] as $key => $val) {
				if ( 'listenWrapper' !== $key AND 'overwriteTitleandArtist' !== $key ) {
					$val = str_replace('#', '', $val);
					$playerOptions .= $key . ':"' . rawurlencode($val).'", ';
					$podpupplayerOptions .= '	podPressPopupPlayerOpt["' . $key . '"] = "' . rawurlencode($val).'";'."\n";
				}
			}
			echo '<script type="text/javascript" src="'.PODPRESS_URL.'/players/1pixelout_audio-player.js"></script>'."\n";
			echo '<script type="text/javascript">//<![CDATA['."\n";
			echo '	var podPressPlayerFile = "1pixelout_player.swf";'."\n"; // this is for the Play in Popup function, too!
			echo '	var podPressPopupPlayerOpt = new Object();'."\n";
			echo $podpupplayerOptions;
			echo '	podpressAudioPlayer.setup("'.PODPRESS_URL.'/players/" + podPressPlayerFile, {'.$playerOptions.' pagebg:"FFFFFF", transparentpagebg:"yes", encode: "no"} );'."\n";
			echo '//]]></script>'."\n";
		}
		echo '<script type="text/javascript">//<![CDATA['."\n";
		echo 'var podPressBlogURL = "'.get_option('siteurl').'/";'."\n";
		echo 'var podPressBackendURL = "'.PODPRESS_URL.'/";'."\n";
		echo 'var podPressDefaultPreviewImage = podPressBackendURL+"images/vpreview_center.png";'."\n";
		echo $mp3playerswffile;
		echo $mp3playerOptionsStr;
		if (TRUE == isset($podPress->settings['player']['listenWrapper']) AND TRUE == $podPress->settings['player']['listenWrapper']) {
			echo 'var podPressMP3PlayerWrapper = true;'."\n";
		} else {
			echo 'var podPressMP3PlayerWrapper = false;'."\n";
		}
		if (TRUE == isset($podPress->settings['cortado_version']) AND 'cortado_signed' == $podPress->settings['cortado_version']) {
			echo 'var podPress_cortado_signed = true;'."\n";
		} else {
			echo 'var podPress_cortado_signed = false;'."\n";
		}
		if ('yes' == $podPress->settings['player']['overwriteTitleandArtist']) { // should the 1Pixelout player try to show the ID3 data or the custom values
			echo 'var podPressOverwriteTitleandArtist = true;'."\n";
		} else {
			echo 'var podPressOverwriteTitleandArtist = false;'."\n";
		}
		echo 'var podPressText_PlayNow = "'.__('Play Now', 'podpress').'";'."\n";
		echo 'var podPressText_HidePlayer = "'.__('Hide Player', 'podpress').'";'."\n";
		echo '//]]></script>'."\n";
	}
	
	function podPress_admin_head() {
		GLOBAL $podPress, $action;
		if(!$podPress->settings['compatibilityChecks']['themeTested']) {
			$podPress->settings['compatibilityChecks']['themeTested'] = true;
			podPress_update_option('podPress_config', $podPress->settings);
		}
		if(!$podPress->settings['compatibilityChecks']['wp_head']) {
			$podPress->settings['compatibilityChecks']['wp_head'] = true;
			podPress_update_option('podPress_config', $podPress->settings);
		} else {
			$podPress->settings['compatibilityChecks']['wp_head'] = true;
		}

		// ntm: old podPress version check. It checks only at myghtyseek.com and not at wordpress.org for new versions !!! and only on the plugins.php page
		if ((strpos($_SERVER['REQUEST_URI'], 'plugins.php') !== false) && (podPress_remote_version_check() == 1)) {
			$atmp = parse_url(podPress_url());
			$xyz = $atmp['path'];

			echo "<script type='text/javascript' src='" . $xyz . "/prototype-1.4.0.js'></script>\n";
			$alert = "\n";
			$alert .= "\n<script type='text/javascript'>";
			$alert .= "\n//<![CDATA[";
			$alert .= "\nfunction alertNewPodPressVersion() {";
			$alert .= "\n	pluginname = 'podPress';";
			$alert .= "\n	allNodes = document.getElementsByClassName('name');";
			$alert .= "\n	for(i = 0; i < allNodes.length; i++) {";
			$alert .= "\n			var regExp=/<\S[^>]*>/g;";
			$alert .= "\n	    temp = allNodes[i].innerHTML;";
			$alert .= "\n	    if (temp.replace(regExp,'') == pluginname) {";
			$alert .= "\n		    Element.setStyle(allNodes[i].getElementsByTagName('a')[0], {color: '#f00'});";
			$alert .= "\n		    new Insertion.After(allNodes[i].getElementsByTagName('strong')[0],'<br/><small>" .  __('new version available', 'podpress') . "</small>');";
			$alert .= "\n	  	}";
			$alert .= "\n	}";
			$alert .= "\n}";

			$alert .= "\naddLoadEvent(alertNewPodPressVersion);";

			$alert .= "\n//]]>";
			$alert .= "\n</script>";
			$alert .= "\n";
			echo $alert;
		}
	}

	function podPress_admin_footer() {
		GLOBAL $podPress, $action;
		//~ if ((strpos($_SERVER['REQUEST_URI'], 'categories.php') !== false) && $action == 'edit') {
			//~ echo "<script type=\"text/javascript\">var x=1; var y = document.getElementByName('editcat').innerHTML; alert('y: '+y);</script>";
		//~ }
		if(!$podPress->settings['compatibilityChecks']['themeTested']) {
			$podPress->settings['compatibilityChecks']['themeTested'] = true;
			podPress_update_option('podPress_config', $podPress->settings);
		}
		if(!$podPress->settings['compatibilityChecks']['wp_footer']) {
			$podPress->settings['compatibilityChecks']['wp_footer'] = true;
			podPress_update_option('podPress_config', $podPress->settings);
		} else {
			$podPress->settings['compatibilityChecks']['wp_footer'] = true;
		}
	}

	function podPress_wp_footer() {
		GLOBAL $podPress;
		if($podPress->settings['enableFooter']) {
			echo '<div id="podPress_footer" style="text-align: center;"><cite>'.__('Podcast powered by ', 'podpress').'<a href="http://www.mightyseek.com/podpress/" title="podPress, '.__('a plugin for podcasting with WordPress', 'podpress').'"><strong>podPress (v'.PODPRESS_VERSION.' / v'.PODPRESS_MAINTENANCE_VERSION.')</strong></a></cite></div>';
		}
	}

	function podPress_get_the_guid($guid) {
		GLOBAL $post, $wpdb;
		if ( empty($guid) ) {
			$guid = get_permalink( $post->ID );
			if ( is_object($post) && !empty($post->ID) ) {
				$wpdb->query("UPDATE ".$wpdb->posts." SET guid = '". $guid ."' WHERE ID=".$post->ID);
			}
		}
		return $guid;
	}

	function podPress_get_attached_file($file, $id = '') {
		if ( is_feed() ) { return ''; }
		return $file;
	}
	
	function podPress_wp_get_attachment_metadata($data, $id = '') {
		if ( is_feed() ) { return ''; }
		return $data;
	}

	function podPress_crossdomain() {
		// ntm: Which purpose has this function? Shouldn't the crossdomain file on the server/domain with the mp3 files? And isn't it to sloppy and dangerous to allow acces from all domains?
		// http://www.adobe.com/devnet/articles/crossdomain_policy_file_spec.html
		header("HTTP/1.0 200 OK");
		header('Content-type: text/xml; charset=' . get_bloginfo('charset'), true);
		echo '<?xml version="1.0"?>'."\n";
		echo '<!DOCTYPE cross-domain-policy SYSTEM "http://www.macromedia.com/xml/dtds/cross-domain-policy.dtd">'."\n";
		echo '<cross-domain-policy>'."\n";
		echo '  <allow-access-from domain="*" />'."\n";
		echo '</cross-domain-policy>'."\n";
		exit;
	}

	function podPress_do_feed_rss2($withcomments) {
		GLOBAL $wp_query, $podpress_allowed_ext;
		podPress_addFeedHooks();
		$cat_id = get_query_var('cat');
		$categorysettings = get_option('podPress_category_'.$cat_id);
		if ( FALSE !== $categorysettings AND 'true' == $categorysettings['categoryCasting'] ) {
			// get the list of file types
			$podpress_allowed_ext = podpress_get_exts_from_filetypes($categorysettings['FileTypes']);
			if (is_array($podpress_allowed_ext) AND FALSE === empty($podpress_allowed_ext)) {
				// get only posts with podPress attachments
				define('PODPRESS_PODCASTSONLY', true);
				// get only posts with torrent files
				$wp_query = podpress_only_posts_with_certain_files($wp_query, $podpress_allowed_ext);
			}
		}
		if (!function_exists('do_feed_rss2')) {
			load_template(ABSPATH.'wp-rss2.php');
		} else {
			do_feed_rss2($withcomments);
		}
	}

	function podPress_do_feed_premium($withcomments) {
		GLOBAL $cache_lastpostmodified;
		unset($_SERVER['HTTP_IF_MODIFIED_SINCE']);
		$cache_lastpostmodified = date('Y-m-d h:i:s', time()+36000);
		podPress_addFeedHooks();
		define('PREMIUMCAST', true);
		podPress_validateLogin();
		if(!function_exists('do_feed_rss2')) {
			load_template(ABSPATH.'wp-rss2.php');
		} else {
			do_feed_rss2($withcomments);
		}
	}

	function podPress_do_feed_podcast($withcomments) {
		GLOBAL $wp_query;
		podPress_addFeedHooks();
		define('PODPRESS_PODCASTSONLY', true);
		$wp_query->get_posts();
		if (!function_exists('do_feed_rss2')) {
			load_template(ABSPATH.'wp-rss2.php');
		} else {
			do_feed_rss2($withcomments);
		}
	}

	function podPress_do_feed_atom1($withcomments) {
		podPress_addFeedHooks();
		global $wp_version;
		// do_feed_atom() exists since WP 2.1 and since WP 2.3 there is a good ATOM feed support build in WP (http://codex.wordpress.org/Version_2.3#Developer_.2F_Power_User_Stuff)
		// for older WP versions use the podPress ATOM feed template
		if (!function_exists('do_feed_atom') OR TRUE == version_compare('2.3', $wp_version,'>')) { 
			load_template(ABSPATH.PLUGINDIR.'/podpress/wp-atom1.php');
		} else {
			do_feed_atom($withcomments);
		}
	}

	function podPress_do_feed_torrent($withcomments) {
		// this function creates a ATOM feed which contains only posts which have an torrent file attached with podPress
		GLOBAL $wp_query, $podpress_allowed_ext;
		podPress_addFeedHooks();
		// get only posts with podPress attachments
		define('PODPRESS_PODCASTSONLY', true);
		define('PODPRESS_TORRENTCAST', true);
		// make sure that only the podPress attachment which is a torrent file will be an enclosure in the ATOM item
		$podpress_allowed_ext =  Array('torrent');
		// get only posts with torrent files
		podpress_only_posts_with_certain_files($wp_query, $podpress_allowed_ext);
		add_filter('wp_title_rss', 'podpress_extend_torrent_feed_title');
		if (!function_exists('do_feed_atom') OR TRUE == version_compare('2.3', $wp_version,'>')) { 
			load_template(ABSPATH.PLUGINDIR.'/podpress/wp-atom1.php');
		} else {
			do_feed_atom($withcomments);
		}
	}
	
	function podPress_do_feed_enhanced_podcast($withcomments) {
		// this function creates a ATOM feed which contains only posts which have m4a or m4v files attached with podPress
		GLOBAL $wp_query, $podpress_allowed_ext;
		podPress_addFeedHooks();
		// get only posts with podPress attachments
		define('PODPRESS_PODCASTSONLY', true);
		// make sure that only the podPress attachment which is a torrent file will be an enclosure in the ATOM item
		$podpress_allowed_ext = Array('m4a', 'm4v');		
		// get only posts with m4a/m4v files
		podpress_only_posts_with_certain_files($wp_query, $podpress_allowed_ext);
		add_filter('wp_title_rss', 'podpress_extend_m4x_feed_title');
		if (!function_exists('do_feed_atom') OR TRUE == version_compare('2.3', $wp_version,'>')) { 
			load_template(ABSPATH.PLUGINDIR.'/podpress/wp-atom1.php');
		} else {
			do_feed_atom($withcomments);
		}
	}
	
	function podPress_do_feed_xspf() {
		GLOBAL $wp_query;
		podPress_addFeedHooks();
		define('PODPRESS_PODCASTSONLY', true);
		$wp_query->get_posts();
		podPress_xspf_playlist();
	}

	function podPress_addFeedHooks() {
		GLOBAL $podPress, $podPress_feedHooksAdded;		
		if(!$podPress_feedHooksAdded) {
			require_once(ABSPATH.PLUGINDIR.'/podpress/podpress_feed_functions.php');
			podPress_checkmem('podPress feed functions loaded');
			add_filter('option_blogname', 'podPress_feedblogname');
			add_filter('option_blogdescription', 'podPress_feedblogdescription');
			add_filter('option_rss_language', 'podPress_feedblogrsslanguage');
			add_filter('option_rss_image', 'podPress_feedblogrssimage');

			/* stuff that goes in the rss feed */
			add_action('the_content_rss', array(&$podPress, 'insert_content'));
			add_action('rss2_ns', 'podPress_rss2_ns');
			add_action('rss2_head', 'podPress_rss2_head');
			// Remove all enclosures which were not added with podPress. They will be added again at the end of the action rss2_item.
			add_filter('rss_enclosure', 'podPress_dont_print_nonpodpress_enclosures');
			add_action('rss2_item', 'podPress_rss2_item');

			/* stuff that goes in the atom feed */
			add_action('atom_head', 'podPress_atom_head');
			// Remove all enclosures which were not added with podPress. They will be added again at the end of the action atom_entry.
			add_filter('atom_enclosure', 'podPress_dont_print_nonpodpress_enclosures');
			add_action('atom_entry', 'podPress_atom_entry');
			$podPress_feedHooksAdded = true;
		}
	}
}

	/*************************************************************/
	/* !!! BEGINNING OF THE ACTION !!!                           */
	/*************************************************************/

	/*************************************************************/
	/* Create the podPress object                                */
	/*************************************************************/

	if ( FALSE == isset($podPress) OR FALSE == is_object($podPress) ) {
		if(get_option('podPress_version') < PODPRESS_VERSION) {
			$podPress_inUpgrade = true;
		} else {
			$podPress_inUpgrade = false;
		}
		$podPress_inAdmin = strpos($_SERVER['REQUEST_URI'], 'wp-admin');
		if($podPress_inUpgrade) {
			podPress_checkmem('podpress admin class loaded', true);
			require_once(ABSPATH.PLUGINDIR.'/podpress/podpress_admin_functions.php');
			require_once(ABSPATH.PLUGINDIR.'/podpress/podpress_upgrade_class.php');
			podPress_checkmem('podpress upgrade class loaded');
			require_once(ABSPATH.PLUGINDIR.'/podpress/podpress_admin_class.php');
			podPress_checkmem('podpress admin class loaded');
			$podPress = new podPressUpgrade_class(get_option('podPress_version'));
			header('Location: '.$_SERVER['REQUEST_URI']);
			exit;
		} elseif ($podPress_inAdmin) {
			podPress_checkmem('podpress admin functions loaded', true);
			require_once(ABSPATH.PLUGINDIR.'/podpress/podpress_admin_functions.php');
			podPress_checkmem('podpress admin functions loaded');
			if(isset($_GET['page'])) {
				$podPress_adminPage = $_GET['page'];
			} elseif(isset($_POST['podPress_submitted'])) {
				$podPress_adminPage = 'podpress/podpress_'.$_POST['podPress_submitted'].'.php';
			} else {
				$podPress_adminPage = 'usedefault';
			}
			switch($podPress_adminPage) {
				case 'podpress/podpress_general.php':
					require_once(ABSPATH.PLUGINDIR.'/podpress/podpress_admin_general_class.php');
					podPress_checkmem('admin general code loaded');
					break;
				case 'podpress/podpress_feed.php':
					require_once(ABSPATH.PLUGINDIR.'/podpress/podpress_admin_feed_class.php');
					podPress_checkmem('admin feed code loaded');
					break;
				case 'podpress/podpress_players.php':
					require_once(ABSPATH.PLUGINDIR.'/podpress/podpress_admin_player_class.php');
					podPress_checkmem('admin player code loaded');
					break;
				case 'podpress/podpress_stats.php':
					require_once(ABSPATH.PLUGINDIR.'/podpress/podpress_admin_stats_class.php');
					podPress_checkmem('admin stats code loaded');
					break;
				case 'podpress/podpress_podango.php':
					require_once(ABSPATH.PLUGINDIR.'/podpress/podpress_admin_podango_class.php');
					podPress_checkmem('admin podango code loaded');
					break;
				default:
					require_once(ABSPATH.PLUGINDIR.'/podpress/podpress_admin_class.php');
					podPress_checkmem('admin code loaded');
			}

			$podPress = new podPressAdmin_class();
			
			if($podPress->settings['enablePodangoIntegration']) {
				podPress_checkmem('PodangoAPI code loaded', true);
				require_once(ABSPATH.PLUGINDIR.'/podpress/podango-api.php');
				$podPress->podangoAPI = new PodangoAPI ($podPress->settings['podangoUserKey'], $podPress->settings['podangoPassKey']);
				if(!empty($podPress->settings['podangoDefaultPodcast'])) {
					$podPress->podangoAPI->defaultPodcast = $podPress->settings['podangoDefaultPodcast'];
				}
				if(!empty($podPress->settings['podangoDefaultTranscribe'])) {
					$podPress->podangoAPI->defaultTranscribe = (int)$podPress->settings['podangoDefaultTranscribe'];
				}
				podPress_checkmem('PodangoAPI code loaded');
			}
		} else {
			podPress_checkmem('standard podPress class loaded', true);
			$podPress = new podPress_class;
			podPress_checkmem('standard podPress class loaded');
		}
	}
	/*************************************************************/
	/* Register all the actions and filters                      */
	/*************************************************************/
	/* Add podpress data to each post */
	if(!podPress_WPVersionCheck()) {
		// WP 1.5 legacy vars support
		if(isset($table_prefix) && !isset($wpdb->prefix)) {
			$wpdb->prefix = $table_prefix;
		}
		if(isset($tablecomments) && !isset($wpdb->comments)) {
			$wpdb->comments = $tablecomments;
		}
	}

	add_action('init', 'podPress_init');

	/* Add podpress data to each post */
	if(podPress_WPVersionCheck()) {
		add_action('the_posts', array(&$podPress, 'the_posts'));
	} else {
		add_filter('the_posts', array(&$podPress, 'the_posts'));
	}

	// ntm: where is do_action('xmlrpc-mw_ ? 
	add_action('xmlrpc-mw_newPost', array(&$podPress, 'xmlrpc_post_addMedia'));
	add_action('xmlrpc-mw_editPost', array(&$podPress, 'xmlrpc_post_addMedia'));

	add_filter('posts_join', array(&$podPress, 'posts_join'));
	add_filter('posts_where', array(&$podPress, 'posts_where'));

	/* stuff that goes in the display of the Post */
	add_filter('the_content', array(&$podPress, 'insert_content'));
	add_filter('get_the_excerpt', array(&$podPress, 'insert_the_excerpt'), 1);
	add_filter('the_excerpt', array(&$podPress, 'insert_the_excerptplayer'));
	
	add_filter('get_attached_file', 'podPress_get_attached_file');
	add_filter('wp_get_attachment_metadata', 'podPress_wp_get_attachment_metadata');
	
	/* stuff that goes in the HTML header */
	if ( TRUE == version_compare($wp_version, '2.7', '>=') ) {
		if (FALSE === is_admin()) {
		add_action('wp_print_scripts', 'podpress_print_frontend_js');
		add_action('wp_print_styles', 'podpress_print_frontend_css');
		}
	} else {
		add_action('wp_head', 'podPress_wp_head');
	}
	add_action('wp_footer', 'podPress_wp_footer');
	add_action('switch_theme', 'podPress_switch_theme');

	/* misc stuff */
	// the dashboard widget:
	if ($podPress->settings['enableStats'] == true AND ((isset($podPress->settings['disabledashboardwidget']) AND TRUE === $podPress->settings['disabledashboardwidget']) or (!isset($podPress->settings['disabledashboardwidget']))) ) {
		if ( TRUE == version_compare($wp_version, '2.7', '>=') ) { // for WP >= 2.7 add the stats overview as a dashboard widget
			add_action('wp_dashboard_setup', 'podpress_wp_dashboard_setup');
		} else { // for older versions via the activity_box_end hook
			add_action('activity_box_end', 'podPress_activity_box');
		}
		add_action('template_redirect', 'podPress_statsDownloadRedirect');
	}
	
	add_filter('get_the_guid', 'podPress_get_the_guid');

	/* stuff that goes into all feeds */
	// ntm: that seems to be unnecessary because that function is called in every do_feed function (see above)
	//~ if(is_feed()) {
		//~ podPress_addFeedHooks();
	//~ }

	/* Widgets */
	if ( TRUE == version_compare($wp_version, '2.8', '>=') ) {
		// ntm: using the "new" Widget API 
		add_action('widgets_init', create_function('', 'return register_widget("podpress_feedbuttons");'));
		add_action('widgets_init', create_function('', 'return register_widget("podpress_xspfplayer");'));
	} else {
		add_action('widgets_init', 'podPress_loadWidgets');
	}
	
	/* stuff for premium podcasts */
	if($podPress->settings['enablePremiumContent']) {
		require_once(ABSPATH.PLUGINDIR.'/podpress/podpress_premium_functions.php');
		podPress_checkmem('premium functions included');
		add_action('wp_login', 'podpress_adddigestauth');
	}

	/* stuff that goes into setting up the site for podpress */
	if($podPress_inAdmin) {
		add_action('activate_podpress/podpress.php', array(&$podPress, 'activate'));
		add_action('deactivate_podpress/podpress.php', array(&$podPress, 'deactivate'));

		/* if this is an admin page, run the function to add podpress tab to options menu */
		if ( TRUE == version_compare($wp_version, '2.7', '>=') ) {
			add_action('admin_print_scripts', 'podpress_print_admin_js');
			add_action('admin_print_scripts-podpress/podpress_stats.php', 'podpress_print_admin_statistics_js');
			add_action('admin_print_styles', 'podpress_print_admin_css');
			add_action('admin_print_styles-podpress/podpress_stats.php', 'podpress_print_admin_statistics_css');
			add_action('admin_print_styles-index.php', 'podpress_print_admin_statistics_css');
		} else {
			add_action('admin_head', 'podPress_print_admin_js_and_css_old_wp');
		}		
		add_action('admin_head', 'podPress_admin_head');
		add_action('admin_menu', 'podPress_add_menu_page');
		add_action('admin_footer', 'podPress_admin_footer');

		/* Adds a custom section to the "advanced" Post and Page edit screens */
		if ( TRUE == version_compare($wp_version, '2.5', '>=') ) {
			add_action('admin_menu', 'add_podpress_form_box_for_modern_wp');
		} else {
			add_action('simple_edit_form', array(&$podPress, 'post_form'));
			add_action('edit_form_advanced', array(&$podPress, 'post_form'));
			add_action('edit_page_form', array(&$podPress, 'page_form'));
		}
		add_action('save_post', array(&$podPress, 'post_edit'));

		/* stuff that goes in the category */
		add_action('create_category', array(&$podPress, 'edit_category'));
		add_action('edit_category_form', array(&$podPress, 'edit_category_form'));
		add_action('edit_category', array(&$podPress, 'edit_category'));
		//add_action('delete_category', array(&$podPress, 'delete_category'));

		/* stuff for editing settings */
		if(isset($_POST['podPress_submitted']) && method_exists($podPress,'settings_'.$_POST['podPress_submitted'].'_save')) {
			$funcnametouse = 'settings_'.$_POST['podPress_submitted'].'_save';
			$podPress->$funcnametouse();
		}

		/* stuff for editing settings */
		$wp_importers['podcast'] = array ('Podcast RSS2', 'podPress import of posts from a Podcast RSS2 feed.', array(&$podPress, 'import_dispatch'));
		//if(function_exists('register_importer')) {
		//	register_importer('podcast', __('Podcast RSS2'), __('Import posts from an RSS2 Podcast feed'), array (&$podPress, 'import_dispatch'));
		//}

	} else {
	}

	//podPress_checkmem('podPress end');
	if(!function_exists('podPress_shutdown')) {
		function podPress_shutdown() {
			GLOBAL $podPress_memoryUsage, $podPress_memoryIncrease;
			if ( TRUE == isset($_GET['podpress_showmem']) AND 1 === $_GET['podpress_showmem'] ) {
				echo "Total podpress mem: ".podPress_bytes($podPress_memoryIncrease)." out of a total ".podPress_bytes(memory_get_usage())."<br/>\n";
			}

			if ( TRUE == isset($_GET['podpress_showmem']) AND 2 === $_GET['podpress_showmem'] ) {
				html_print_r($podPress_memoryUsage);
			} elseif ( TRUE == isset($_GET['podpress_showmem']) AND 3 === $_GET['podpress_showmem'] ) {
				comment_print_r($podPress_memoryUsage);
			}
		}
		add_action( 'shutdown', 'podPress_shutdown', 1);
	}


// adding the podPress box to the post / page editor pages
function add_podpress_form_box_for_modern_wp() {
	add_meta_box( 'podPressstuff', __('podPress - podcasting settings of this post', 'podpress'), 'podpress_box_content_post', 'post', 'advanced' );
	add_meta_box( 'podPressstuff', __('podPress - podcasting settings of this page', 'podpress'), 'podpress_box_content_page', 'page', 'advanced' );
}
function podpress_box_content_post() {
	global $podPress;
	echo "\n<!-- podPress dbx for modern WP versions - post -->\n";
	$podPress->post_form_wp25plus('post');
	echo "\n<!-- podPress dbx for modern WP versions - post -->\n";
}
function podpress_box_content_page() {
	global $podPress;
	echo "\n<!-- podPress dbx for modern WP versions - page -->\n";
	$podPress->post_form_wp25plus('page');
	echo "\n<!-- podPress dbx for modern WP versions - page -->\n";
}
?>