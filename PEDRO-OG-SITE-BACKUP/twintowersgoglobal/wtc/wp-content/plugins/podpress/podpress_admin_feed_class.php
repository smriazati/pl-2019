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
	class podPressAdmin_class extends podPress_class
	{
		function podPressAdmin_class() {
			parent::cleanup_itunes_keywords();
			$this->podPress_class();
			return;
		}

		function settings_feed_edit() {
			GLOBAL $wp_version;
			podPress_isAuthorized();
			if (isset($_GET['updated']) && $_GET['updated'] == 'true') {
				echo '<div id="message" class="updated fade"><p>'. __('Settings Saved', 'podpress').'</p></div>';
			}
			$blog_charset = get_bloginfo('charset');
			echo '<div class="wrap">'."\n";
			if ( TRUE == version_compare($wp_version, '2.7', '>=') ) {
				echo '<div id="podpress-icon" class="icon32"><br /></div>';
			}
			echo '	<h2>'.__('Feed/iTunes Settings', 'podpress').'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="http://www.mightyseek.com/podpress/#download" target="_new"><img src="http://www.mightyseek.com/podpress_downloads/versioncheck.php?current='.PODPRESS_VERSION.'" alt="'.__('Checking for updates... Failed.', 'podpress').'" border="0" /></a></h2>'."\n";
			echo '	<form method="post">'."\n";
			if ( function_exists('wp_nonce_field') ) { // since WP 2.0.4
				wp_nonce_field('podPress_feed_settings_nonce');
			}
			podPress_DirectoriesPreview('feed_edit');

			echo '	<fieldset class="options">'."\n";
			echo '		<legend>'.__('Feed Settings:', 'podpress').'</legend>'."\n";
			/*
			echo '		<p class="submit"> '."\n";
			echo '		<input type="submit" name="Submit" value="'.__('Update Options', 'podpress').' &raquo;" /> '."\n";
			echo '		</p> '."\n";
			*/
			echo '		<table width="100%" cellspacing="2" cellpadding="5" class="editform" id="podpress_feed_options_table">'."\n";
			echo '			<tr>'."\n";
			echo '				<td width="50%"><h3>'.__('iTunes Settings', 'podpress').'</h3></td>'."\n";
			echo '				<td width="50%"><h3>'.__('General Feed Settings', 'podpress').'</h3></td>'."\n";
			echo '			</tr>'."\n";
			echo '			<tr>'."\n";
			echo '				<td width="100%" colspan="2"><a href="http://www.feedvalidator.org/check.cgi?url='.urlencode(get_option('siteurl').'?feed=podcast').'"></a></td>'."\n";
			echo '			</tr>'."\n";

			echo '			<tr valign="top"> '."\n";
			echo '				<td width="50%">';
			echo '					<label for="iTunesFeedID"><strong>'.__('iTunes:FeedID', 'podpress').'</strong></label>';
			echo '					<br/>';
			echo '					<input name="iTunes[FeedID]" id="iTunesFeedID" type="text" value="'.$this->settings['iTunes']['FeedID'].'" size="10" />';
			echo '					<input type="button" name="Ping_iTunes_update" value="Ping iTunes Update" onclick="javascript: if(document.getElementById(\'iTunesFeedID\').value != \'\') { window.open(\'https://phobos.apple.com/WebObjects/MZFinance.woa/wa/pingPodcast?id=\'+document.getElementById(\'iTunesFeedID\').value); }"/>'."\n";
			if(1==2 && !empty($this->settings['iTunes']['FeedID'])) {
				echo '				<font border="1">';
				echo '				http://phobos.apple.com/WebObjects/MZStore.woa/wa/viewPodcast?id='.$this->settings['iTunes']['FeedID'];
				echo '				</font>';
			}
			echo '				</td>'."\n";

			echo '				<td width="50%">';
			echo '					<label for="podcastFeedURL"><strong>'.__('Podcast Feed URL', 'podpress').'</strong></label>';
			echo '					<br/>';
			echo '					<input type="text" id="podcastFeedURL" class="podpress_wide_text_field" name="podcastFeedURL" size="40" value="'.attribute_escape($this->settings['podcastFeedURL']).'" onchange="podPress_updateFeedSettings();" /><br />'.__('The URL of your Podcast Feed. If you want to register your podcast at the iTunes Store or if your podcast is already listed there then this input field should contain the same URL as in the iTunes Store settings. If you want change the URL at the iTunes Store then please read first the help text of the iTunes:New-Feed-Url option.', 'podpress');
			echo '				</td>'."\n";
			echo '			</tr>'."\n";

			echo '			<tr valign="top"> '."\n";
			echo '				<td width="50%">';
			echo '					<label for="iTunesNewFeedURL"><strong>'.__('iTunes:New-Feed-Url', 'podpress').'</strong></label>';
			echo '					<br/>';
			echo '					<select name="iTunes[new-feed-url]" id="iTunesNewFeedURL">'."\n";
			echo '						<option value="Disable" '; if($this->settings['iTunes']['new-feed-url'] != 'Enable') { echo 'selected="selected"'; } echo '>'.__('Disable', 'podpress').'</option>'."\n";
			echo '						<option value="Enable" '; if($this->settings['iTunes']['new-feed-url'] == 'Enable') { echo 'selected="selected"'; } echo '>'.__('Enable', 'podpress').'</option>'."\n";
			echo '					</select>'."\n";
			echo '					<br/>'.__('If you want to change the URL of your podcast feed which you have used in the iTunes Store then change the "Podcast Feed URL" and set this option to "Enable" until the iTunes Store recognizes the new URL. This may take several days. "Enable" will add the <code>&lt;itunes:new-feed-url&gt;</code> tag to the RSS feeds and set the "Podcast Feed URL" as the new URL. For further information about "<a href="http://www.apple.com/itunes/podcasts/specs.html#changing" title="iTunes Podcasting Resources: Changing Your Feed URL" target="_blank">Changing Your Feed URL</a>" read on in the <a href="http://www.apple.com/itunes/podcasts/specs.html" target="_blank" title="iTunes Podcasting Resources: Making a Podcast">iTunes Podcasting Resources</a>.', 'podpress');
			echo '				</td>'."\n";
			echo '				<td width="50%">';
			echo '					<input type="button" value="'.__('Validate your Feed','podpress').'" onclick="javascript: if(document.getElementById(\'podcastFeedURL\').value != \'\') { window.open(\'http://www.feedvalidator.org/check.cgi?url=\'+document.getElementById(\'podcastFeedURL\').value); }"/>'."\n";
			echo '				</td>'."\n";
			echo '			</tr>'."\n";

			echo '			<tr valign="top">'."\n";
			echo '				<td width="50%">&nbsp;</td>'."\n";
			echo '				<td width="50%">';
			echo '					<label for="blogname"><strong>'.__('Blog/Podcast title', 'podpress').'</strong></label>';
			echo '					<br/>';
			echo '					<input type="text" id="blogname" name="blogname" class="podpress_wide_text_field" size="40" value="'.attribute_escape(get_option('blogname')).'" onchange="podPress_updateFeedSettings();" /><br />'.__('Used for both Blog and Podcast.', 'podpress').' <em class="message error">'.sprintf(__('Changes here will affect %1$s!', 'podpress'),__('the blog title', 'podpress')).'</em>';
			echo '				</td>'."\n";
			echo '			</tr>'."\n";

			echo '			<tr valign="top">'."\n";
			echo '				<td width="50%">';
			echo '					<label for="iTunesSummary"><strong>'.__('iTunes:Summary', 'podpress').'</strong></label>';
			echo '					<br/>';
			echo '					<textarea id="iTunesSummary" name="iTunes[summary]" class="podpress_wide_text_field" rows="4" cols="40" onchange="podPress_updateFeedSettings();">'.stripslashes($this->settings['iTunes']['summary']).'</textarea>';
			echo '					<br />'.__('Used as iTunes description.', 'podpress')."\n";
			echo '				</td>'."\n";
			echo '				<td width="50%">';
			echo '					<label for="blogdescription"><strong>'.__('Blog Tagline', 'podpress').'</strong></label>';
			echo '					<br/>';
			echo '					<textarea id="blogdescription" name="blogdescription" class="podpress_wide_text_field" rows="4" cols="40" onchange="podPress_updateFeedSettings();">'.stripslashes(get_option('blogdescription')).'</textarea>';
			echo '					<br/>'.__('In a few words, explain what this site is about.').' <em class="message error">'.sprintf(__('Changes here will affect %1$s!', 'podpress'),__('the tagline of this blog', 'podpress')).'</em>'."\n";
			echo '				</td>'."\n";
			echo '			</tr>'."\n";

			echo '			<tr valign="top">'."\n";
			echo '				<td width="50%">'."\n";
			echo '					<label for="iTunesImage"><strong>'.__('iTunes:Image', 'podpress').'</strong></label>';
			echo '					<br/>';
			echo '					'.__('The iTunes image should be a square image with <a href="http://www.apple.com/itunes/podcasts/specs.html#image" target="_blank">at least 600 x 600 pixels</a> as Apple writes (08/2010) in "<a href="http://www.apple.com/itunes/podcasts/specs.html" target="_blank">Making a Podcast</a>" of their own Podcasting Resources. In the "<a href="http://www.apple.com/itunes/podcasts/creatorfaq.html" target="_blank">FAQs: For Podcast Makers</a>" 300 x 300 pixels are recommended by Apple. iTunes supports JPEG and PNG images (the file name extensions should ".jpg" or ".png").', 'podpress')."\n";
			echo '					<br/>';
			echo '					<input type="text" id="iTunesImage" name="iTunes[image]" class="podpress_wide_text_field" value="'.attribute_escape($this->settings['iTunes']['image']).'" size="40" onchange="podPress_updateFeedSettings();"/>'."\n";
			echo '					<br />';
			echo '					<img id="iTunesImagePreview" style="width:300px; height:300px;" alt="Podcast Image - Big" src="" />'."<br />\n";
			echo '					<em>'.__('(This image is only a preview which is limited to 300 x 300 pixels.) ', 'podpress').'</em>';
			echo '				</td>'."\n";
			echo '				<td width="50%">'."\n";
			echo '					<label for="rss_image"><strong>'.__('Blog/RSS Image (144 x 144 pixels)', 'podpress').'</strong></label>'."\n";
			echo '					<br/>';
			echo '					<input type="text" id="rss_image" name="rss_image" class="podpress_wide_text_field" value="'.attribute_escape(get_option('rss_image')).'" size="40" onchange="podPress_updateFeedSettings();"/>'."\n";
			echo '					<br />';
			echo '					<img id="rss_imagePreview" style="width:144px; height:144px;" alt="Podcast Image - Small" src="" />'."\n";
			echo '				</td>'."\n";
			echo '			</tr>'."\n";

			echo '			<tr valign="top"> '."\n";
			echo '				<td width="50%">';
			echo '					<label for="iTunesAuthor"><strong>'.__('iTunes:Author/Owner', 'podpress').'</strong></label>';
			echo '					<br/>';
			echo '					<input type="text" name="iTunes[author]" id="iTunesAuthor" class="podpress_wide_text_field" value="'.attribute_escape($this->settings['iTunes']['author']).'" size="40" onchange="podPress_updateFeedSettings();"/>';
			echo '				</td>'."\n";
			echo '				<td width="50%">';
			echo '					<label for="admin_email"><strong>'.__('Owner E-mail address', 'podpress').'</strong></label>';
			echo '					<br/>';
			echo '					<input type="text" name="admin_email" id="admin_email" class="podpress_wide_text_field" value="'.attribute_escape(get_option('admin_email')).'" size="40" /><br /><em class="message error">'.sprintf(__('Changes here will affect %1$s!', 'podpress'),__('the email address of the blog admin', 'podpress')).'</em>';
			echo '				</td>'."\n";
			echo '			</tr>'."\n";

			echo '			<tr valign="top"> '."\n";
			echo '				<td width="50%">';
			echo '					<label for="iTunesSubtitle"><strong>'.__('iTunes:Subtitle', 'podpress').'</strong></label>';
			echo '					<br/>';
			echo '					<textarea name="iTunes[subtitle]" id="iTunesSubtitle" class="podpress_wide_text_field" rows="4" cols="40">'.stripslashes($this->settings['iTunes']['subtitle']).'</textarea>';
			echo '					<br/>'.__('Used as default Podcast Episode Title (255 characters)', 'podpress')."\n";
			echo '				</td>'."\n";
			echo '				<td width="50%">';
			echo '					<label for="rss_language"><strong>'.__('Language of the News Feed content', 'podpress').'</strong></label>';
			echo '					<br/>';
			echo '					<select id="rss_language" name="rss_language" onchange="podPress_updateFeedSettings();">'."\n";
			echo '						<optgroup label="'.__('Select Language', 'podpress').'">'."\n";
			podPress_itunesLanguageOptions(get_option('rss_language'));
			echo '						</optgroup>'."\n";
			echo '					</select>'."\n".'<br /><em class="message error">'.sprintf(__('Changes here will affect %1$s!', 'podpress'),__('the language of the main feeds of this blog', 'podpress')).'</em> <em>'.__('(This select box is not the only but probably the most comfortable way to change this option. So change it back if you do not want to use this plugin anymore.)', 'podpress').'</em>';
			echo '				</td>'."\n";
			echo '			</tr>'."\n";

			echo '			<tr valign="top"> '."\n";
			echo '				<td width="50%">';
			echo '					<label for="iTunesKeywords"><strong>'.__('iTunes:Keywords', 'podpress').'</strong></label>';
			echo '					<br/>';
			echo '					<textarea name="iTunes[keywords]" id="iTunesKeywords" class="podpress_wide_text_field" rows="4" cols="40">'.stripslashes($this->settings['iTunes']['keywords']).'</textarea>';
			echo '					<br/>('.__('Comma seperated list', 'podpress').', '.__('max 8', 'podpress').')';
			echo '				</td>'."\n";
			echo '				<td width="50%">';
			//~ echo '					<label for="rss_showlinks"><strong>'.__('Show Download Links in RSS Encoded Content', 'podpress').'</strong></label>';
			//~ echo '					<br/>';
			//~ echo '					<select name="rss_showlinks" id="rss_showlinks">'."\n";
			//~ echo '						<option value="yes" '; if($this->settings['rss_showlinks'] == 'yes') { echo 'selected="selected"'; } echo '>'.__('Yes', 'podpress').'</option>'."\n";
			//~ echo '						<option value="no" '; if($this->settings['rss_showlinks'] != 'yes') { echo 'selected="selected"'; }  echo '>'.__('No', 'podpress').'</option>'."\n";
			//~ echo '					</select>'."\n";
			//~ echo '					<br/>'.__('Yes will put download links in the RSS encoded content. That means users can download from any site displaying the link.', 'podpress')."\n";
			echo '				</td>'."\n";
			echo '			</tr>'."\n";

			echo '			<tr valign="top"> '."\n";
			echo '				<td>';
			echo '					<label for="iTunesCategory_0"><strong>'.__('iTunes:Categories', 'podpress').'</strong></label>';
			echo '					<br/>';
			echo '					<select id="iTunesCategory_0" name="iTunes[category][0]" onchange="podPress_updateFeedSettings();">'."\n";
			echo '						<optgroup label="'.__('Select Primary', 'podpress').'">'."\n";
			podPress_itunesCategoryOptions(stripslashes($this->settings['iTunes']['category'][0]));
			echo '						</optgroup>'."\n";
			echo '					</select><br />'."\n";
			echo '					<select name="iTunes[category][1]">'."\n";
			echo '						<optgroup label="'.__('Select Second', 'podpress').'">'."\n";
			podPress_itunesCategoryOptions(stripslashes($this->settings['iTunes']['category'][1]));
			echo '						</optgroup>'."\n";
			echo '					</select><br />'."\n";
			echo '					<select name="iTunes[category][2]">'."\n";
			echo '						<optgroup label="'.__('Select Third', 'podpress').'">'."\n";
			podPress_itunesCategoryOptions(stripslashes($this->settings['iTunes']['category'][2]));
			echo '						</optgroup>'."\n";
			echo '					</select>'."\n";
			echo '				</td>'."\n";
			echo '				<td width="50%">'."\n";
			echo '					<label for="rss_category"><strong>'.__('RSS Category', 'podpress').'</strong></label>';
			echo '					<br/>';
			echo '					<input type="text" name="rss_category" id="rss_category" class="podpress_wide_text_field" value="'.attribute_escape($this->settings['rss_category']).'" size="45" />'."\n";
			echo '					<br />'.__('A category for your RSS feeds. (This is for everyone except iTunes).', 'podpress')."\n";
			echo '				</td>'."\n";
			echo '			</tr>'."\n";

			echo '			<tr valign="top"> '."\n";
			echo '				<td width="50%">';
			echo '					<label for="iTunesExplicit"><strong>'.__('iTunes:Explicit', 'podpress').'</strong></label>';
			echo '					<br/>';
			echo '					<select name="iTunes[explicit]" id="iTunesExplicit">'."\n";
			echo '						<option value="No" '; if($this->settings['iTunes']['explicit'] == 'No') { echo 'selected="selected"'; } echo '>'.__('No', 'podpress').'</option>'."\n";
			echo '						<option value="Yes" '; if($this->settings['iTunes']['explicit'] == 'Yes') { echo 'selected="selected"'; } echo '>'.__('Yes', 'podpress').'</option>'."\n";
			echo '						<option value="Clean" '; if($this->settings['iTunes']['explicit'] == 'Clean') { echo 'selected="selected"'; } echo '>'.__('Clean', 'podpress').'</option>'."\n";
			echo '					</select>'."\n";
			echo '					<br/>'.__('Setting to indicate (in iTunes) whether or not your podcast contains explicit language or X-rated content', 'podpress')."\n";
			echo '					<br/>'.__('"No" (default) - no indicator will show up', 'podpress')."\n";
			echo '					<br/>'.__('"Yes" - an "EXPLICIT" parental advisory graphic will appear next to your podcast artwork or name in iTunes', 'podpress')."\n";
			echo '					<br/>'.__('"Clean" - means that you are sure that no explicit language or adult content is included any of the episodes, and a "CLEAN" graphic will appear', 'podpress')."\n";
			echo '					<p>'.__('You have also the possibility to adjust this option for each post or page with at least one podcast episode (in the post/page editor).', 'podpress').'</p>'."\n";
			echo '				</td>'."\n";
			echo '				<td width="50%">'."\n";
			echo '					<label for="rss_copyright"><strong>'.__('Feed Copyright / license name', 'podpress').'</strong></label>';
			echo '					<br/>';
			echo '					<input type="text" name="rss_copyright" id="rss_copyright" class="podpress_wide_text_field" value="'.attribute_escape($this->settings['rss_copyright']).'" size="65" />'."\n";
			echo '					<br />'.__('Enter the copyright string resp. the license name. For example: Copyright &#169 by Jon Doe, 2009 OR <a href="http://creativecommons.org/licenses/by-nc-sa/2.5/" target="_blank">CreativeCommons Attribution-Noncommercial-Share Alike 2.5</a>', 'podpress')."\n";
			
			echo '					<br /><br />'."\n";
			echo '					<label for="rss_license_url"><strong>'.__('URL to the full Copyright / license text', 'podpress').'</strong></label>';
			echo '					<br />';
			echo '					<input type="text" name="rss_license_url" id="rss_license_url" class="podpress_wide_text_field" class="podpress_wide_text_input_field" value="'.attribute_escape($this->settings['rss_license_url']).'" size="65" />'."\n";
			echo '					<br />'.__('If you use a special license like a <a href="http://creativecommons.org/licenses" target="_blank" title="Creative Commons">Creative Commons</a> License for your news feeds then enter the complete URL (e.g. <a href="http://creativecommons.org/licenses/by-nc-sa/2.5/" target="_blank">http://creativecommons.org/licenses/by-nc-sa/2.5/</a>) to the full text of this particular license here.', 'podpress')."<br />\n";
			echo '					<p>'.__('Notice: You can set post specific license URLs and names by defining two custom fields per post. One with the name <strong>podcast_episode_license_name</strong> and one custom field with then name <strong>podcast_episode_license_url</strong>. If you want to set post specific values the it is necessary to define at least the custom field with the URL. If the license name is not defined then the name will be the URL.', 'podpress').'</p>';
			echo '				</td>'."\n";
			echo '			</tr>'."\n";

			$data['rss_ttl'] = get_option('rss_ttl');
			if(!empty($data['rss_ttl']) && $data['rss_ttl'] < 1440) {
				$data['rss_ttl'] = 1440;
			}

			echo '			<tr valign="top"> '."\n";
			echo '				<td width="50%">';
			echo '					<label for="rss_ttl"><strong>'.__('TTL', 'podpress').' ('.__('time-to-live', 'podpress').')</strong></label>';
			echo '					<br/>';
			echo '					<input name="rss_ttl" id="rss_ttl" type="text" value="'; if($data['rss_ttl']) { echo $data['rss_ttl']; } else { echo '1440'; } echo '" size="4" />';
			echo '					'.__('min', 'podpress').'<br/>'.__('Minimum is 24hrs which is 1440 mins.', 'podpress').' <a href="http://cyber.law.harvard.edu/rss/rss.html#ltttlgtSubelementOfLtchannelgt" title="RSS 2.0 Specification - TTL">'.__('More about TTL ...', 'podpress').'</a>'."\n";
			echo '				</td>'."\n";

			echo '				<td width="50%">';
			echo '					<label for="posts_per_rss"><strong>'.__('Syndication feeds show the most recent', 'podpress').'</strong></label>';
			echo '					<br/>';
			echo '					<input type="text" id="posts_per_rss" name="posts_per_rss" size="3" value="'.get_option('posts_per_rss').'" /> '.__('posts', 'podpress').'<br />'.'<em class="message error">'.sprintf(__('Changes here will affect %1$s!', 'podpress'),sprintf(__('the same value on the %1$s page', 'podpress'), __('Reading Settings'))).'</em>';
			echo '				</td>'."\n";
			echo '			</tr>'."\n";

			echo '			<tr valign="top"> '."\n";
			echo '				<td width="50%">';
			echo '					<label for="iTunesBlock"><strong>'.__('iTunes:Block', 'podpress').'</strong></label>';
			echo '					<br/>';
			echo '					<select name="iTunes[block]" id="iTunesBlock">'."\n";
			echo '						<option value="No" '; if($this->settings['iTunes']['block'] != 'Yes') { echo 'selected="selected"'; } echo '>'.__('No', 'podpress').'</option>'."\n";
			echo '						<option value="Yes" '; if($this->settings['iTunes']['block'] == 'Yes') { echo 'selected="selected"'; } echo '>'.__('Yes', 'podpress').'</option>'."\n";
			echo '					</select>'."\n";
			echo '					<br/>'.__('Use this if you are no longer creating a podcast and you want it removed from iTunes.', 'podpress')."\n";
			echo '					<br/>'.__('"No" (default) - the podcast appears in the iTunes Podcast directory', 'podpress')."\n";
			echo '					<br/>'.__('"Yes" - prevent the entire podcast from appearing in the iTunes Podcast directory', 'podpress')."\n";
			echo '					<p>'.__('You can also use such an option for each of your podcast episodes (in the post/page editor).', 'podpress').'</p>'."\n";
			
			echo '				</td>'."\n";
			echo '				<td width="50%">';
			echo '					<label for="blog_charset"><strong>'.__('Encoding for pages and feeds').'</strong></label>';
			echo '					<br/>';
			echo '					<input type="text" id="blog_charset" name="blog_charset" size="20" value="'.attribute_escape($blog_charset).'" /><br />'.__('The <a href="http://codex.wordpress.org/Glossary#Character_set">character encoding</a> of your site  (UTF-8 is <a href="http://www.apple.com/itunes/podcasts/specs.html#encoding" target="_blank" title="iTunes Podcast Resources - Making a Podcast">recommended</a>)', 'podpress').' <em class="message error">'.sprintf(__('Changes here will affect %1$s!', 'podpress'),sprintf(__('the same value on the %1$s page', 'podpress'), __('Reading Settings'))).'</em>';
			echo '				</td>'."\n";
			echo '			</tr>'."\n";

			echo '			<tr valign="top"> '."\n";
			echo '				<td width="50%">&nbsp;</td>'."\n";
			echo '				<td width="50%">';
			echo '					<label for="protectFeed"><strong>'.__('Aggressively Protect the news feeds', 'podpress').'</strong></label>';
			echo '					<br/>';
			echo '					<select name="protectFeed" id="protectFeed">'."\n";
			echo '						<option value="No" '; if($this->settings['protectFeed'] != 'Yes') { echo 'selected="selected"'; } echo '>'.__('No', 'podpress').'</option>'."\n";
			echo '						<option value="Yes" '; if($this->settings['protectFeed'] == 'Yes') { echo 'selected="selected"'; } echo '>'.__('Yes', 'podpress').'</option>'."\n";
			echo '					</select>'."\n";
			echo '					<br/>'.__('"No" (default) will convert only ampersand, less-than, greater-than, apostrophe and quotation signs to their numeric character references.', 'podpress')."\n";
			echo '					<br/>'.__('"Yes" will convert any invalid characters to their numeric character references in the feeds.', 'podpress')."\n";
			echo '				</td>'."\n";
			echo '			</tr>'."\n";

			echo '		</table>'."\n";
			echo '		<p class="submit"> '."\n";
			echo '		<input type="submit" name="Submit" value="'.__('Update Options', 'podpress').' &raquo;" /> '."\n";
			echo '		</p> '."\n";
			echo '	</fieldset>'."\n";

			echo '<script type="text/javascript">'." podPress_updateFeedSettings();</script>";

			echo '	<input type="hidden" name="podPress_submitted" value="feed" />'."\n";
			echo '	</form> '."\n";
			echo '</div>'."\n";

			//end of settings_feed_edit function
		}
		
		function settings_feed_save() {
			if ( function_exists('check_admin_referer') ) {
				check_admin_referer('podPress_feed_settings_nonce');
			}
			$blog_charset = get_bloginfo('charset');
			if(function_exists('wp_cache_flush')) {
				wp_cache_flush();
			}
			if(isset($_POST['iTunes'])) {
				$iTunesSettings = $_POST['iTunes'];
				$iTunesSettings['summary'] = htmlspecialchars(strip_tags(trim($_POST['iTunes']['summary'])), ENT_QUOTES, $blog_charset);
				$iTunesSettings['image'] = clean_url($_POST['iTunes']['image'], array('http', 'https'), 'db');
				$iTunesSettings['author'] = htmlspecialchars(strip_tags(trim($_POST['iTunes']['author'])), ENT_QUOTES, $blog_charset);
				$iTunesSettings['subtitle'] = htmlspecialchars(strip_tags(trim($_POST['iTunes']['subtitle'])), ENT_QUOTES, $blog_charset);
				$iTunesSettings['keywords'] = $this->cleanup_itunes_keywords($_POST['iTunes']['keywords'], $blog_charset);
				$iTunesSettings['category'] = array();
				if(is_array($_POST['iTunes']['category'])) {
					foreach ($_POST['iTunes']['category'] as $value) {
						if('#' != $value AND '[ '.__('nothing', 'podpress').' ]' != $value) {
							$iTunesSettings['category'][] = $value;
						}
					}
				}
				$this->settings['iTunes'] = $iTunesSettings;
			}
			
			if(isset($_POST['blogname'])) { podPress_update_option('blogname', htmlspecialchars(strip_tags(trim($_POST['blogname'])), ENT_QUOTES, $blog_charset)); }
			if(isset($_POST['blogdescription'])) { podPress_update_option('blogdescription', htmlspecialchars(strip_tags(trim($_POST['blogdescription'])), ENT_QUOTES, $blog_charset)); }
			if(isset($_POST['admin_email'])) { podPress_update_option('admin_email', htmlspecialchars(strip_tags(trim($_POST['admin_email'])), ENT_QUOTES, $blog_charset)); }

			if(isset($_POST['blog_charset'])) { podPress_update_option('blog_charset', htmlspecialchars(strtoupper(strip_tags(trim($_POST['blog_charset']))), ENT_QUOTES, $blog_charset)); }
			if(isset($_POST['posts_per_rss'])) { podPress_update_option('posts_per_rss', intval(preg_replace('/[^0-9]/', '', $_POST['posts_per_rss']))); }

			if(isset($_POST['rss_language'])) { podPress_update_option('rss_language', htmlspecialchars(strip_tags(trim($_POST['rss_language'])), ENT_QUOTES, $blog_charset));	}
			if(isset($_POST['rss_ttl'])) { podPress_update_option('rss_ttl', intval(preg_replace('/[^0-9]/', '', $_POST['rss_ttl'])));	}
			if(isset($_POST['rss_image'])) { podPress_update_option('rss_image', htmlspecialchars(strip_tags(trim($_POST['rss_image'])), ENT_QUOTES, $blog_charset));	}

			if(isset($_POST['rss_category'])) {
				$this->settings['rss_category'] = htmlspecialchars(strip_tags(trim($_POST['rss_category'])), ENT_QUOTES, $blog_charset);
			}
			if(isset($_POST['rss_copyright'])) {
				$this->settings['rss_copyright'] = htmlspecialchars(strip_tags(trim($_POST['rss_copyright'])), ENT_QUOTES, $blog_charset);
			}
			if(isset($_POST['rss_license_url'])) {
				$this->settings['rss_license_url'] = clean_url($_POST['rss_license_url'], array('http', 'https'), 'db');
			}
			if(isset($_POST['rss_showlinks'])) {
				$this->settings['rss_showlinks'] = $_POST['rss_showlinks'];
			}
			if(isset($_POST['podcastFeedURL'])) {
				$this->settings['podcastFeedURL'] = clean_url($_POST['podcastFeedURL'], array('http', 'https'), 'db');
			}
			if( isset($_POST['protectFeed']) AND 'yes' == strtolower($_POST['protectFeed']) ) {
				$this->settings['protectFeed'] = 'Yes';
			} else {
				$this->settings['protectFeed'] = 'No';
			}

			podPress_update_option('podPress_config', $this->settings);

			$location = get_option('siteurl') . '/wp-admin/admin.php?page=podpress/podpress_feed.php&updated=true';
			header('Location: '.$location);
			exit;
		}
	}
?>