<?php /**/ ?><?php
// If you want to use custom skins for the XSPF players then edit this file and rename it to podpress_xspf_config.php .
// ATTENTION: the podpress_xspf_config.php and the podpress_xspf_config-sample.php files are going to be replaced during the next automatic plugin upgrade! Please, save a back up file some place else.

// Begin - XSPF Jukebox player configuration:
// It is possible to define these constants for each blog in a multi site blog installation. All of these constants are ending with an underscore and a number. This number is the blog ID. 1 is the ID of the first resp. main blog. In a single blog installation the blog as the ID 1.

// Before you use these options please read the player documentation http://lacymorrow.com/projects/jukebox/xspfdoc.html and the skin documentation http://lacymorrow.com/projects/jukebox/skindoc.html
// podPress uses a derivate of the SlimOriginal skin.
// If you want to use a custom skin file for the XSPF player then uncomment this line and replace or edit the skin files in the folders /podpress/players/xspf_jukebox/dynamic/ or /podpress/players/xspf_jukebox/dynamic_slim/
// If PODPRESS_XSPF_PLAYER_USE_CUSTOM_SKINFILE is defined then the skin_X.xml files (the X stands for the ID of the blog | if you are using a one blog installation or for the main blog it is 1 ) will not be overwritten by changes in the widgets settings. If this is defined as TRUE then saving the widgets settings will only affect the size of the <object> of the XSPF players.
//if ( ! defined('PODPRESS_XSPF_PLAYER_USE_CUSTOM_SKINFILE_1') ) { define('PODPRESS_XSPF_PLAYER_USE_CUSTOM_SKINFILE_1', TRUE); }

// The width of the XSPF player (min: 170) in pixels. If you are using a custom skin file then this value affects only the width of the <object> of the XSPF player (which should be the same as in the skin file).
if ( ! defined( 'PODPRESS_XSPF_PLAYER_WIDTH_1' ) ) { define( 'PODPRESS_XSPF_PLAYER_WIDTH_1', 170 ); }

// The height of the XSPF player (min: 210) in pixels. If you are using a custom skin file then this value affects only the height of the <object> of the XSPF player (which should be the same as in the skin file).
if ( ! defined( 'PODPRESS_XSPF_PLAYER_HEIGHT_1' ) ) { define( 'PODPRESS_XSPF_PLAYER_HEIGHT_1', 211 ); }

// The height of the slim XSPF player (min: 30) in pixels. If you are using a custom skin file then this value affects only the height of the <object> of the slim XSPF player (which should be the same as in the skin file).
if ( ! defined( 'PODPRESS_XSPF_SLIM_PLAYER_HEIGHT_1' ) ) { define( 'PODPRESS_XSPF_SLIM_PLAYER_HEIGHT_1', 30 ); }

// Background-color of the player <object> (if this constant is not defined then the color is FFFFFF by default.)
if ( ! defined( 'PODPRESS_XSPF_BACKGROUND_COLOR_1' ) ) { define( 'PODPRESS_XSPF_BACKGROUND_COLOR_1', 'FFFFFF' ); }

// If you want to let the player show the episode preview images then uncomment the following line (This has only an effect if you are using the default player skins of podPress):
if ( ! defined('PODPRESS_XSPF_SHOW_PREVIEW_IMAGE_1') ) { define('PODPRESS_XSPF_SHOW_PREVIEW_IMAGE_1', TRUE); }

// podPress uses the parameters: &autoload=true&autoplay=false&loaded=true to load the XSPF player
// If you want to use custom parameters then uncomment the following lines and edit or replace the variables.txt files in the folders /podpress/players/xspf_jukebox/dynamic/ and /podpress/players/xspf_jukebox/dynamic_slim/.
if ( ! defined('PODPRESS_XSPF_USE_CUSTOM_VARIABLES_1') ) { define('PODPRESS_XSPF_USE_CUSTOM_VARIABLES_1', TRUE); }
if ( ! defined('PODPRESS_XSPF_SLIM_USE_CUSTOM_VARIABLES_1') ) { define('PODPRESS_XSPF_SLIM_USE_CUSTOM_VARIABLES_1', TRUE); }

// Remove the comment characters of the following line to define a custom URL for the XSPF player. The URL has to be an URL to a playlist which is on the same domain/server as your blog! 
// This constant overwrites the playlist URLs of all XSPF player widget of one blog! (But you can define via the widgets settings an individual URL for each XSPF widget.)
// if ( ! defined( 'PODPRESS_CUSTOM_XSPF_URL_1' ) ) { define( 'PODPRESS_CUSTOM_XSPF_URL_1', 'http://www.example.com/?feed=playlist.xspf' ); }
// End - XSPF Jukebox player configuration
?>