<?php /**/  ?>";	if(document.getElementById("DM_ALBUMS_UPLOADDIR").value.indexOf(recommended_upload_root) != 0)	{		return confirm("You are about to set your Album Upload Folder to a location outside the WordPress designated upload area.\n\nThis can cause problems uploading photos to your albums and presents some risks in terms of file security.\n\n'Cancel' to stop, 'OK' to continue.");	}}</script><div class="wrap" style="height:100%"><?php  if(!function_exists("imagecreatefromjpeg"))	{		dm_showMessage("DM Albums requires that the <a href='http://www.libgd.org/Main_Page'>GD Library</a> is installed and configured; it appears that you are missing this library.  Please contact your system administrator for more information.");	}	if(intval(ini_get('memory_limit')) < 32 && intval(ini_get('memory_limit')) >= 0)	{		dm_showMessage("Opening images in PHP can use a lot of memory; the memory limit on your server appears to be too low.  DM Albums requires at least 32M of memory, although DutchMonkey Productions recommends setting this value to 100M or more in order to support high-resolution images.  Your current memory limit is " . ini_get('memory_limit') . ".  This value can be adjusted by setting the <a href='http://us3.php.net/ini.core'>memory_limit</a> parameter in your PHP.ini file.  Please contact your system administrator for more information.");	}?><h2>DM Albums Options</h2><table width="60%" cellpadding="10"><tr><td valign="top"><p>Settings for the DM Albums plugin. Visit <a href="<?php echo $DM_PHOTOALBUM_APP_DOCS; ?>">DM Productions</a> for help and project news. DM Albums now uses <a href="http://galleria.io/">Galleria</a> for the display engine.</p><p>Current version: <strong><?php echo $DM_PHOTOALBUM_APP_VERSION; ?></strong> (Visit <a href="<?php echo $DM_PHOTOALBUM_APP_DOCS; ?>">DM Productions</a> to check for updates)</p></td><td width="30%" valign="top"><p><b>Donations</b><br>This product is available free of charge.  However, donations are appreciated in order to help fund continued development and support.  Please click the link below to donate safely and securely via <a href="http://paypal.com/" class="normal">PayPal</a>.</p><form action="https://www.paypal.com/cgi-bin/webscr" method="post"><input type="hidden" name="cmd" value="_s-xclick"><input type="hidden" name="hosted_button_id" value="7162488"><input type="image" src="http://www.paypal.com/en_US/i/btn/btn_donate_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!"><img alt="" border="0" src="https://www.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1"></form></p></td></tr></table><h3>Usage</h3><hr size="1"><p>DM Albums has undergone a major update, dropping the old [album:] syntax for the much easier and faster shortcode support provided by WordPress. (The previous [album:] syntax is still full supported for legacy reasons, but it is recommended that this syntax be forgone for the new shortcodes.)</p><p>After uploading an album via the DM Albums Management panel in the WordPress editor, simply click the "Insert" button to insert the shortcode in the page at the location of the cursor.</p><p><b>Parameters</b></p><p>There are three supported parameters:<ol><li>Path (required): The path to the album, relative from the HOME_DIR variable set under advanced configuration.</li><li>Width (optional): The size of the album's default images. (This is a starting point, but the album will resize the image to fit perfectly, but setting this to the size of your albums will greatly improve performance.)<li>Height (optional): See width.</li></ol></p><h3>A Note About Sizing</h3><hr size="1"><p>DM Albums now automatically sizes to the size of the space it is embedded in. This should be desireable in most cases, but if you'd like to control the size of the gallery, you can do so by setting a width on the enclosing div by tuning the <code>.dm-albums-galleria-container</code> CSS class. (You can also add padding, margins, backgrounds etc - anything CSS supports!). The Width and Height parameters set through DM Albums only control the size of the images sent to DM Albums, but don't affect the size of the embedded album.</p><p><b>Current Appearance</b></p><?php$path = $DIRECTORY;$width = (int) get_option('DM_PHOTOALBUM_APP_WIDTH');$height = (int) get_option('DM_PHOTOALBUM_APP_HEIGHT');echo "<div style='margin-left: auto; margin-right: auto; width: " . $width . "px; height: " . $height . "'>" . get_galleria($path, $width, $height) . "</div>";?><?php  //if($user_level == 10)if(TRUE){?><p class="submit" style="align: left;"><input type="button" id="btnShowHide" value="Show Configuration Options" onClick="ShowHide();"></p><div id="dm_config_settings"><h3>Display Settings</h3><hr size="1"><form method="post" onsubmit="return WarnChangeUploadDir();"><p class="submit"><input name="Submit" value="Update Options &raquo;" type="submit" /></p><!-- PROPERTY: DM_SHOW_TAGLINE --><fieldset class="options"><h3>Display Powered By DM Albums Tagline</h3><p>This setting turns on/off the "Powered By DM Albums" tagline.  This is off by default, but we appreciate you turning it on!</p><table class="editform" cellpadding="5" cellspacing="2"><tr><th width="250" valign="top"><label for="DM_SHOW_TAGLINE">Display Tagline:</label></th><td><select id="DM_SHOW_TAGLINE" name="DM_SHOW_TAGLINE"><option <?php  if(get_option("DM_SHOW_TAGLINE") == "true") echo "SELECTED" ?> value="true">YES</option><option <?php  if(get_option("DM_SHOW_TAGLINE") == "false") echo "SELECTED" ?> value="false">NO</option></select>Default: <code>NO</code></td></tr></table></fieldset><!-- PROPERTY: DM_SHOW_TAGLINE --><!-- PROPERTY: DM_SHOW_FULLSCREEN --><fieldset class="options"><h3>Display Fullscreen Link</h3><p>This setting turns on/off the "Fullscreen" link.  This is on by default.</p><table class="editform" cellpadding="5" cellspacing="2"><tr><th width="250" valign="top"><label for="DM_SHOW_FULLSCREEN">Display Fullscreen Link:</label></th><td><select id="DM_SHOW_FULLSCREEN" name="DM_SHOW_FULLSCREEN"><option <?php  if(get_option("DM_SHOW_FULLSCREEN") == "true") echo "SELECTED" ?> value="true">YES</option><option <?php  if(get_option("DM_SHOW_FULLSCREEN") == "false") echo "SELECTED" ?> value="false">NO</option></select>Default: <code>YES</code></td></tr></table></fieldset><!-- PROPERTY: DM_ALBUMS_LIGHTBOX --><fieldset class="options"><h3>Lightbox</h3><p>This setting turns on/off the "Lightbox" feature when clicking a photo. When the user clicks a photo, the photo will open in a full screen lightbox, with no thumbnails. If a link is defined for the photo, the link will take precedence over the lightbox. This is on by default.</p><table class="editform" cellpadding="5" cellspacing="2"><tr><th width="250" valign="top"><label for="DM_SHOW_FULLSCREEN">Lightbox:</label></th><td><select id="DM_ALBUMS_LIGHTBOX" name="DM_ALBUMS_LIGHTBOX"><option <?php  if(get_option("DM_ALBUMS_LIGHTBOX") == "true") echo "SELECTED" ?> value="true">YES</option><option <?php  if(get_option("DM_ALBUMS_LIGHTBOX") == "false") echo "SELECTED" ?> value="false">NO</option></select>Default: <code>YES</code></td></tr></table></fieldset><!-- PROPERTY: DM_SHOW_TAGLINE --><!-- PROPERTY: DM_PHOTOALBUM_SLIDESHOW_CONTROLS --><fieldset class="options"><h3>Show Slide Show Controls</h3><p>Display controls for playing slide shows in a small menu beneath the photo alumbs.  If you don't want to show this, set this to "No".</p><table class="editform" cellpadding="5" cellspacing="2"><tr><th width="250" valign="top"><label for="DM_PHOTOALBUM_SLIDESHOW_CONTROLS">Show Slide Show Controls:</label></th><td><select id="DM_PHOTOALBUM_SLIDESHOW_CONTROLS" name="DM_PHOTOALBUM_SLIDESHOW_CONTROLS"><option <?php  if(get_option("DM_PHOTOALBUM_SLIDESHOW_CONTROLS") == "true") echo "SELECTED" ?> value="true">YES</option><option <?php  if(get_option("DM_PHOTOALBUM_SLIDESHOW_CONTROLS") == "false") echo "SELECTED" ?> value="false">NO</option></select>Default: <code>Yes</code></td></tr></table></fieldset><!-- PROPERTY: DM_PHOTOALBUM_SLIDESHOW_CONTROLS --><!-- PROPERTY: DM_PHOTOALBUM_SLIDESHOW_AUTOPLAY --><fieldset class="options"><h3>Autoplay Slide Show</h3><p>Automatically play slideshow when album loads.  This is not dependent on showing Slide Show Controls (above).  If you want to start playing your slideshow automatically, set this to "Yes".</p><table class="editform" cellpadding="5" cellspacing="2"><tr><th width="250" valign="top"><label for="DM_PHOTOALBUM_SLIDESHOW_AUTOPLAY">Autoplay Slide Show:</label></th><td><select id="DM_PHOTOALBUM_SLIDESHOW_AUTOPLAY" name="DM_PHOTOALBUM_SLIDESHOW_AUTOPLAY"><option <?php  if(get_option("DM_PHOTOALBUM_SLIDESHOW_AUTOPLAY") == "true") echo "SELECTED" ?> value="true">YES</option><option <?php  if(get_option("DM_PHOTOALBUM_SLIDESHOW_AUTOPLAY") == "false") echo "SELECTED" ?> value="false">NO</option></select>Default: <code>No</code></td></tr></table></fieldset><!-- PROPERTY: DM_PHOTOALBUM_SLIDESHOW_AUTOPLAY --><!-- PROPERTY: DM_SHOW_NAVIGATION_HINTS --><fieldset class="options"><h3>Display Navigation Hints</h3><p>This setting turns on/off the navigation hints that display when mousing over an image (left/right navigation).  This is on by default.</p><table class="editform" cellpadding="5" cellspacing="2"><tr><th width="250" valign="top"><label for="DM_SHOW_NAVIGATION_HINTS">Display Navigation Hints:</label></th><td><select id="DM_SHOW_NAVIGATION_HINTS" name="DM_SHOW_NAVIGATION_HINTS"><option <?php  if(get_option("DM_SHOW_NAVIGATION_HINTS") == "true") echo "SELECTED" ?> value="true">YES</option><option <?php  if(get_option("DM_SHOW_NAVIGATION_HINTS") == "false") echo "SELECTED" ?> value="false">NO</option></select>Default: <code>YES</code></td></tr></table></fieldset><!-- PROPERTY: DM_SHOW_NAVIGATION_HINTS --><!-- PROPERTY: DM_ALBUMS_EXTERNAL_LINK_TARGET --><fieldset class="options"><h3>Photo Link Target</h3><p>This is the target for external links (Photo Link set in the DM Albums Detail Manager).  Choose "Main Window" to open links in the main window (user will leave your page) and "New Window" top open a new window.</p><table class="editform" cellpadding="5" cellspacing="2"><tr><th width="250" valign="top"><label for="DM_ALBUMS_EXTERNAL_LINK_TARGET">Photo Link Target:</label></th><td><select id="DM_ALBUMS_EXTERNAL_LINK_TARGET" name="DM_ALBUMS_EXTERNAL_LINK_TARGET"><option <?php  if(get_option("DM_ALBUMS_EXTERNAL_LINK_TARGET") == "_top") echo "SELECTED" ?> value="_top">Main Window</option><option <?php  if(get_option("DM_ALBUMS_EXTERNAL_LINK_TARGET") == "_newWindow") echo "SELECTED" ?> value="_newWindow">New Window</option></select>Default: <code>Main Window</code></td></tr></table></fieldset><!-- PROPERTY: DM_ALBUMS_EXTERNAL_LINK_TARGET --><!-- PROPERTY: DM_PHOTOALBUM_ALLOWDOWNLOAD --><fieldset class="options"><h3>Allow Direct Download</h3><p>Direct download of the original image via the menu below the album.  If you don't want people to download the original image, set this to "No".</p><table class="editform" cellpadding="5" cellspacing="2"><tr><th width="250" valign="top"><label for="DM_PHOTOALBUM_ALLOWDOWNLOAD">Allow Direct Download:</label></th><td><select id="DM_PHOTOALBUM_ALLOWDOWNLOAD" name="DM_PHOTOALBUM_ALLOWDOWNLOAD"><option <?php  if(get_option("DM_PHOTOALBUM_ALLOWDOWNLOAD") == "true") echo "SELECTED" ?> value="true">YES</option><option <?php  if(get_option("DM_PHOTOALBUM_ALLOWDOWNLOAD") == "false") echo "SELECTED" ?> value="false">NO</option></select>Default: <code>No</code></td></tr></table></fieldset><!-- PROPERTY: DM_PHOTOALBUM_ALLOWDOWNLOAD --><!-- PROPERTY: DM_ALBUMS_EXTERNAL_CSS --><fieldset class="options"><h3>Custom Stylesheet</h3><p>This allows you to enter the Full URL to a custom style sheet to override the default settings found in DM Albums.  Leave this blank if you don't need a custom style sheet.</p><table class="editform" cellpadding="5" cellspacing="2"><tr><th width="250" valign="top"><label for="DM_ALBUMS_EXTERNAL_CSS">Custom Stylesheet (URL):</label></th><td><input type="text" id="DM_ALBUMS_EXTERNAL_CSS" name="DM_ALBUMS_EXTERNAL_CSS" size="100" value="<?php echo( get_option("DM_ALBUMS_EXTERNAL_CSS") ); ?>" />Default: <code></code> (blank)</td></tr></table></fieldset><!-- PROPERTY: DM_ALBUMS_EXTERNAL_CSS --><fieldset class="options"><h3>Album Width</h3><p>Width of the Album</p><table class="editform" cellpadding="5" cellspacing="2"><tr><th width="250" valign="top"><label for="DM_PHOTOALBUM_APP_WIDTH">Width:</label></th><td><input type="text" id="DM_PHOTOALBUM_APP_WIDTH" name="DM_PHOTOALBUM_APP_WIDTH" size="40" value="<?php echo( get_option("DM_PHOTOALBUM_APP_WIDTH") ); ?>" /><br />Default: <code>500</code></td></tr></table></fieldset><!-- PROPERTY: DM_PHOTOALBUM_APP_HEIGHT --><fieldset class="options"><h3>Album Height</h3><p>Height of the Album</p><table class="editform" cellpadding="5" cellspacing="2"><tr><th width="250" valign="top"><label for="DM_PHOTOALBUM_APP_HEIGHT">Height:</label></th><td><input type="text" id="DM_PHOTOALBUM_APP_HEIGHT" name="DM_PHOTOALBUM_APP_HEIGHT" size="40" value="<?php echo( get_option("DM_PHOTOALBUM_APP_HEIGHT") ); ?>" /><br />Default: <code>492</code></td></tr></table></fieldset><p class="submit"><input name="Submit" value="Update Options &raquo;" type="submit" /></p><?phpif(dm_isUserAdmin()){?><h3>Advanced Options</h3><hr size="1"><!-- PROPERTY: DM_JQUERY_LIB --><fieldset class="options"><h3>JQuery Library</h3><p>This setting gives control over the JQuery API being used for DM Albums. Change this setting if your albums aren't loading or are having trouble with other JQuery plugins</p><p>The settings have the following meaning:</p><ul><li>Google: This is the default library required by DM Albums and the recommended setting</li><li>Replace Wordpress JQuery: This replaces the JQuery API loaded by WordPress with the Google JQuery API</li><li>Manual: This does not load any JQuery API and allows a manual/custom API to be loaded in the header of your theme.</li></ul><table class="editform" cellpadding="5" cellspacing="2"><tr><th width="250" valign="top"><label for="DM_JQUERY_LIB">JQuery Library:</label></th><td><select id="DM_SHOW_TAGLINE" name="DM_JQUERY_LIB"><option <?php  if(get_option("DM_JQUERY_LIB") == "GOOGLE") echo "SELECTED" ?> value="GOOGLE">Google</option><option <?php  if(get_option("DM_JQUERY_LIB") == "REPLACE") echo "SELECTED" ?> value="REPLACE">Replace Wordpress JQuery</option><option <?php  if(get_option("DM_JQUERY_LIB") == "MANUAL") echo "SELECTED" ?> value="MANUAL">Manual</option></select>Default: <code>GOOGLE</code></td></tr></table></fieldset><!-- PROPERTY: DM_JQUERY_LIB --><!-- PROPERTY: HOME_DIR --><fieldset class="options"><h3>Home Folder</h3><p>This is a critical setting.  When reading a path to an image, the full path to the image is required.  This setting is assumed to be the root directory to be appended to the path passed via the <code>directory</code> or <code>currdir</code> parameter.  This is typically assumed to be the root of your webspace directory as defined in the PHP <code>DOCUMENT_ROOT</code> server variable.  The <code>DOCUMENT_ROOT</code> for your blog is displayed below to help determine what your home folder might be.  If the demo above works, this value has been set correctly.</p><table class="editform" cellpadding="5" cellspacing="2"><tr><th width="250" valign="top"><label for="DM_HOME_DIR">Home Folder:</label></th><td><?phpif(dm_is_wamp())	$DOCUMENT_ROOT = realpath($_SERVER['DOCUMENT_ROOT']) . "\\";else				$DOCUMENT_ROOT = realpath($_SERVER['DOCUMENT_ROOT']) . "/";?><input type="text" id="DM_HOME_DIR" name="DM_HOME_DIR" size="100" value="<?php echo( get_option("DM_HOME_DIR") ); ?>" /><br />Web Space Root (<code>DOCUMENT_ROOT</code>): <code><?php echo $DOCUMENT_ROOT; ?></code><br/>Default: <code><?php echo( get_option('DM_ALBUMS_CORE_DEFAULT_HOME_DIR') ); ?></code></td></tr></table></fieldset><!-- PROPERTY: DM_ALBUMS_UPLOADDIR --><fieldset class="options"><h3>Album Upload Folder</h3><p>The directory to where DM Albums will upload your photo albums from the Edit/Add page and post editor.  This location has to be full server path pointing to a location below the Home Directory.  Please note that all users will upload photos into their own directories below this point, preventing users from seeing (or accidentally modifying) eachother's album.</p><?phpif(dm_is_wpmu()){?><p>WordPressMU Users: To ensure that photos are uploaded into your user's own blog folders, make sure the {BLOG_ID} identifier appears somewhere in the Album Upload Folder path.  This identifier will be replaced by the user's blog id.</p><?php}?><table class="editform" cellpadding="5" cellspacing="2"><tr><th width="250" valign="top"><label for="DM_ALBUMS_UPLOADDIR">Album Upload Folder:</label></th><td><input type="text" id="DM_ALBUMS_UPLOADDIR" name="DM_ALBUMS_UPLOADDIR" size="100" value="<?php echo( get_option("DM_ALBUMS_UPLOADDIR") ); ?>" /><br />Home Directory: <code><?php echo( get_option("DM_HOME_DIR") ); ?></code><br/>Default: <code><?php echo( get_option('DM_ALBUMS_CORE_DEFAULT_UPLOADDIR') ); ?></code></td></tr></table></fieldset><!-- PROPERTY: DM_ALBUMS_UUP --><fieldset class="options"><h3>Unique Author Upload Folders</h3><p>This setting allows blogs with multiple authors to upload their albums into their own unique folder.  The default allows all authors of the blog to share/modify each other's albums.  Turning this off (setting it to YES) will upload albums into a separate folder for each author, preventing authors of the blog from seeing each other's albums.</p><table class="editform" cellpadding="5" cellspacing="2"><tr><th width="250" valign="top"><label for="DM_ALBUMS_UUP">Unique Author Upload Folders:</label></th><td><select id="DM_ALBUMS_UUP" name="DM_ALBUMS_UUP"><option <?php  if(get_option("DM_ALBUMS_UUP") == "1") echo "SELECTED" ?> value="1">YES</option><option <?php  if(get_option("DM_ALBUMS_UUP") == "0") echo "SELECTED" ?> value="0">NO</option></select><br />Default: <code>NO</code></td></tr></table></fieldset><fieldset class="options"><h3>Comments</h3><p>By default, DM Albums checks for album syntax in comments.  If you wish to disable this functionality, set this option to "NO".</p><table class="editform" cellpadding="5" cellspacing="2"><tr><th width="250" valign="top"><label for="DM_PHOTOALBUM_INCLUDE_COMMENTS">Comments:</label></th><td><select id="DM_PHOTOALBUM_INCLUDE_COMMENTS" name="DM_PHOTOALBUM_INCLUDE_COMMENTS"><option <?php  if(get_option("DM_PHOTOALBUM_INCLUDE_COMMENTS") != "NO") echo "SELECTED" ?> value="YES">YES</option><option <?php  if(get_option("DM_PHOTOALBUM_INCLUDE_COMMENTS") == "NO") echo "SELECTED" ?> value="NO">NO</option></select><br />Default: <code>YES</code></td></tr></table></fieldset><p class="submit"><input name="Submit" value="Update Options &raquo;" type="submit" /></p><div class="dm_warning"><fieldset class="options"><h3>Reset Configuration</h3><p>If you are having trouble with your installation, click the button below to reset your installation to it's default settings.</p><table class="editform" cellpadding="5" cellspacing="2"><tr><th width="250" valign="top"><label for="defaults">Reset Default Configuration:</label></th><td><input name="reset_config" value="&laquo; RESET CONFIGURATION &raquo;" type="submit" onClick="return confirm('This will reset all the setting set on this page to the default settings.  Are you sure you want to do this?\n\n\'Cancel\' to stop, \'OK\' to continue.');"/></td></tr></table></fieldset></div><?php }?></form></div></div><script language="JavaScript"><?php  if($APP_CONFIGURED_CORRECTLY){?>	ShowHide();<?php  }?></script><?php  }else{?><p>Sorry, you do not have the user level required to adjust these settings.</p><?php  }?>