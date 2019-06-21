<?php /**/ ?><?php
/*
Plugin Name: SlideZoom
Plugin URI: http://www.netatlantis.com/?page_id=1317
Description: SlideZoom is a lightweight plugin allow bulk upload images or zip upload to generate a HighSlide JS gallery. <br />
Output as HTML code / BBCode etc...  And offering embed the gallery by simple copy and paste to your WordPress's posts or pages.
Author: Cheung Tat Ming
Version: 1.4.1
Author URI: http://www.netatlantis.com/

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
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

Please note  :
HighSlide JS is not part of this license and is available under a Creative Commons Attribution-NonCommercial 2.5 License.
HighSlide JS is Copyright by Vevstein Web (highslide.com) , for commercial use please look at the HighSlide JS homepage : http://highslide.com/ .
*/

//error_reporting(E_ALL);
//ini_set("display_errors",1);

if (!isset($_SESSION)) { session_start(); }
$relativepath = plugin_dir_path( __FILE__ );

require_once(ABSPATH . '/wp-admin/admin-functions.php');
require_once($relativepath.'function/CommonClass.php');
require_once($relativepath.'function/ImageClass.php');
require_once($relativepath.'function/ZipClass.php');

register_activation_hook(__FILE__,'sz_FirstStart');
add_action('admin_head' , 'sz_PrintMainHead');
add_action('admin_menu' , 'sz_AddMediaMenu'); 
add_action('admin_menu' , 'sz_AddOptionMenu'); 

add_action('wp_head' , 'sz_PrintScriptStyle');
add_action('init' , 'sz_LoadjQuery');
add_action('init' , 'sz_ProcessForm');

function sz_FirstStart()
{
	if(!get_option('slidezoom_options'))
	{
		add_option('slidezoom_options', 
      				  array('IsDebug' => 0, 'IsResize' => 0, 'MaxWidth' => '9999', 
              				'MaxHeight' => '9999', 'ThumbMode' => '2', 
              				'ThumbWidth' => '160','ThumbHeight' => '160','Number_of_uploads' => '16',
							'LinkMode' => 'absolute','hsAlign' => 'auto',
							'hsmarginLeft' => '15', 'hsmarginTop' => '15','hsmarginRight' => '15','hsmarginBottom' => '15', 'easeOpen' => 'easeOutBack',
							'easeClose' => 'easeInBack','expandDuration' => '500','restoreDuration' => '500', 'Fade' => 0)
					);
	} 
}

function sz_ProcessForm() {
  if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_POST['sz_main_form']) { 
  /*Declare Section*/
        $AryOptions = sz_FuncGetOptions();
        $AryFiles = array();
        $AryThumbs = array();
        $AryErrors = array();
	$wud = wp_upload_dir();
	$upload_mode = $_POST["sz_mode"];

    /* Router */
	switch ($upload_mode)
	{
		//==========Zip Upload=============//
		case "zip":
 			$zip = new clsZip();
			$ziparray = $zip -> unzip($_FILES['ZipUpload']['tmp_name'],  $relativepath.'tmp/' );
			$FileCount = sizeof($ziparray);
			//echo $FileCount."<br>"; 
			//print_r($ziparray);
			//die();
		   if ($FileCount > 0) {
			for ($i=0; $i < $FileCount; $i++) {
				sz_Log("[Zip] File -> (" . $ziparray[$i]['name'] . " found).");
				//echo $ziparray[$i]['name']."<br>";

				if (($imginfo = @getimagesize($ziparray[$i]['tmp_name'])) === false ) {
						sz_Log("Image format not correct.");
						continue;
				}
			
				$file = array(
                                                       'name' => $ziparray[$i]['name'],
                                                       'type' => image_type_to_mime_type($imginfo[2]),
      		  				       'tmp_name' => $ziparray[$i]['tmp_name'],
                                                       'error' => $ziparray[$i]['error'],
                                                       'size' => $ziparray[$i]['size']
						 );
															 
				// ====Resize image (if need)====
				list($w_orig,$h_orig,$type,$html) = $imginfo;
				if ($AryOptions['IsResize'] && ($w_orig > $AryOptions['MaxWidth'] && $h_orig > $AryOptions['MaxHeight']) ) {
					sz_Log("Starting Resize ...");
					$image = new clsImage();
					$image -> load($file['tmp_name']);
					$image -> resizeToWidth($AryOptions['MaxWidth']);
					$image -> save($file['tmp_name']);
					$result = true;
				} else { 
					//No need resize
					$result = true;  sz_Log("Skipped resize.");		
			    }
      
				if ($result) { // success
					$AryFiles[] = sz_my_handle_upload($file['tmp_name'],$file['name'],$wud['path']);  sz_Log("Upload images success.");
				} else {
					$AryErrors[] = $file['name'];   sz_Log("Upload Image Error.");
				}

				// ==== Break Times ====
				$result = false;

				// ====Create Thumbs (if need) ====
				if ( @intval($AryOptions['ThumbMode']) === 2 ) {
					sz_Log("Creating Thumbs ...");
					$uploaded_filename = sz_getFilename(end(explode("/" , $AryFiles[$i]['url']))).'.'.sz_getFileExt(end(explode("/" , $AryFiles[$i]['url'])));
					$thumbname = sz_getFilename(end(explode("/" , $AryFiles[$i]['url']))).'-t.'.sz_getFileExt(end(explode("/" , $AryFiles[$i]['url'])));
					$wud = wp_upload_dir();
					$image = new clsImage();
					$image -> load($wud['path']."/".sanitize_file_name($uploaded_filename));
					$image -> resizeToWidth($AryOptions['ThumbWidth']);
					$image -> save($wud['path']."/".$thumbname);
					$result = true;
				} else { 
			     	$thumbname = end(explode("/" , $AryFiles[$i]['url']));
					$result = true;  sz_Log("Skipped create thumbs.");
				}

				if ($result) { // create thumb success
					$AryThumbs[] = $wud['url']."/".$thumbname;  sz_Log("Create thumb success.");
				} else {
					$AryErrors[] = end(explode("/" , $AryFiles[$i]['url']));  sz_Log("Create thumb Error.");
				}
				$GLOBALS['AryFiles'] = $AryFiles;
				$GLOBALS['AryThumbs'] = $AryThumbs;
				$GLOBALS['AryErrors'] = $AryErrors;

			}
		   }
		break;
		//==========Files Upload=============//
		case "file":
			$FileCount = count($_FILES['FileUpload']['name']);
		   if ($FileCount > 0) {
			for ($i=0; $i < $FileCount; $i++) {
				sz_Log("[File] File -> (" . $_FILES['FileUpload']['name'][$i] . ").");

				$file = array(
      		  						   'name' => &$_FILES['FileUpload']['name'][$i],
      		  						   'type' => &$_FILES['FileUpload']['type'][$i],
      		  						   'tmp_name' => &$_FILES['FileUpload']['tmp_name'][$i],
      		  						   'error' => &$_FILES['FileUpload']['error'][$i],
      		  						   'size' => &$_FILES['FileUpload']['size'][$i]
      		  						 );
				
				if (($imginfo = @getimagesize($file['tmp_name'])) === false ) {
						sz_Log("Image format not correct.");
						continue;
				}
				
				// ====Resize image (if need)====
				list($w_orig,$h_orig,$type,$html) = $imginfo;
				if ($AryOptions['IsResize'] && ($w_orig > $AryOptions['MaxWidth'] && $h_orig > $AryOptions['MaxHeight']) ) {
					sz_Log("Starting Resize ...");
					$image = new clsImage();
					$image -> load($file['tmp_name']);
					$image -> resizeToWidth($AryOptions['MaxWidth']);
					$image -> save($file['tmp_name']);
					$result = true;
				} else { 
					//No need resize
					$thumbname = end(explode("/" , $AryFiles[$i]['url']));
					$result = true;  sz_Log("Skipped resize.");		
			    }
      
				if ($result) { // success
					$AryFiles[] = wp_handle_upload($file , array('action'=>'save') );  sz_Log("Upload images success.");
				} else {
					$AryErrors[] = $file['name'];   sz_Log("Upload Image Error.");
				}
	  
				// ==== Break Times ====
				$result = false;

				// ====Create Thumbs (if need) ====
				if ( @intval($AryOptions['ThumbMode']) === 2 ) {
					sz_Log("Creating Thumbs ...");
					$uploaded_filename = sz_getFilename(end(explode("/" , $AryFiles[$i]['url']))).'.'.sz_getFileExt(end(explode("/" , $AryFiles[$i]['url'])));
					$thumbname = sz_getFilename(end(explode("/" , $AryFiles[$i]['url']))).'-t.'.sz_getFileExt(end(explode("/" , $AryFiles[$i]['url'])));
					$wud = wp_upload_dir();
					$image = new clsImage();
					$image -> load($wud['path']."/".sanitize_file_name($uploaded_filename));
					$image -> resizeToWidth($AryOptions['ThumbWidth']);
					$image -> save($wud['path']."/".$thumbname);
					$result = true;
				} else { 
					$thumbname = end(explode("/" , $AryFiles[$i]['url']));
					$result = true;  sz_Log("Skipped create thumbs.");
				}

				if ($result) { // create thumb success
					$AryThumbs[] = $wud['url']."/".$thumbname;  sz_Log("Create thumb success.");
				} else {
					$AryErrors[] = end(explode("/" , $AryFiles[$i]['url']));  sz_Log("Create thumb Error.");
				}
				$GLOBALS['AryFiles'] = $AryFiles;
				$GLOBALS['AryThumbs'] = $AryThumbs;
				$GLOBALS['AryErrors'] = $AryErrors;
				
				//echo $_POST["imagecaption"][$i]."<br>";
				$AryImageCaptions[] = $_POST["imagecaption"][$i];
				$GLOBALS['AryImageCaptions'] = $AryImageCaptions;
				
			}
		   }
		break;
	  } //End switch

	} //End if

} //End function

function FuncShowMain() {
  global $title;
  global $home;
  global $sz_Log;
  global $AryFiles;
  global $AryThumbs;
  global $AryErrors;
  global $AryImageCaptions;
  
 //Get Options
$AryOptions = sz_FuncGetOptions();

//ReDefine Options Values if the Options not exist in Database
	$wud = wp_upload_dir();
  if (false === $AryOptions) {
    $AryOptions = array(
	  'IsDebug' => 0,
      'IsResize' => 0,
      'MaxWidth' => 1000,
      'MaxHeight' => 1000,
	  'ThumbMode' => 1,
	  'ThumbWidth' => 160,
	  'ThumbHeight' => 160,
      'Number_of_uploads' => 10,
      'LinkMode' => 'absolute',
	  'hsAlign' => 'auto',
	  'hsmarginLeft' => 15,
	  'hsmarginTop' => 15,
	  'hsmarginRight' => 15,
	  'hsmarginBottom' => 15,
	  'easeOpen' => 'easeOutBack',
	  'easeClose' => 'easeInBack',
	  'expandDuration' => '500',
	  'restoreDuration' => '500',
	  'Fade' => 'true'
    );
  }
    
  foreach ($AryOptions as $name => $option) 
  				  { $$name = $option; }
  
  // Debugging info
  $good_results = (@is_array($AryFiles) && $AryFiles !== array());
  $bad_results = (@is_array($AryErrors) && $AryErrors !== array());  
  $display_results = $good_results || $bad_results;
  
  if ($display_results) {
    //$debug = ($do_debug) ? "<div class=\"sz_box\"><h3>Debugging info:</h3>\n" . $sz_Log . "<pre>".@print_r($Aryfiles,true)."</pre></div>" : "";
  }
  
  // Print result for uploaded images and errors
  $raw = "";
  $img_html = "";
  $outputhtml = "";
  $bbcode = "";
  $i = 0;
if ($display_results) {
	//===
	  if ($good_results) {
	    				$img_html .= "<h3>Images were uploaded successfully.</h3>
	    											<p>To use them in a post, copy and paste the code below.</p>\n<div>";
	  												//echo $_POST["chkGallery"];
	  												if ($_POST["chkGallery"] != "") {
	  													$_SESSION["lastgroupid"] = strtotime("now");
														$outputhtml = "<script type=\"text/javascript\">\r\n";
														$outputhtml .= "hs.addSlideshow({ \r\n";
														$outputhtml .= "slideshowGroup: '".$_SESSION["lastgroupid"]."', interval: 5000, repeat: true, useControls: true, fixedControls: true, \r\n";
														$outputhtml .= " overlayOptions: { opacity: 0.75, position: 'bottom center',	hideOnMouseOut: true } });  \r\n";
														$outputhtml .= "</script>\r\n";
														$outputhtml .= "<div class=\"highslide-gallery\">";
	  												}
	  												  foreach ($AryFiles as $file)
													  {
	  										 					$img_html .= htmlentities(
	  										 					sprintf('<img src="%s" alt="%s"/>', 
	  										 					preg_replace('@^https?://.*?/@','/',$file['url']), 
	  										 					basename($file['url']) )
	  										 		 		  	) . "<br/>";
															
															switch ($AryOptions['ThumbMode'])
															{
																case "1":
	  										 					$outputhtml .= htmlentities(
	  										 					sprintf('<a href="%s" title="%s" class="highslide" onclick="return hs.expand(this %s)" target="_blank"><img src="%s" alt="%s" width="%s" height="%s" /></a>', 
	  										 					($LinkMode == 'relative' ? preg_replace('@^https?://.*?/@','/',$file['url']) : $file['url']), 
	  										 					basename($file['url']),
	  										 					($_POST["chkGallery"] != "" ? ",{slideshowGroup:".$_SESSION["lastgroupid"]."}": ""),
	  										 					($LinkMode == 'relative' ? preg_replace('@^https?://.*?/@','/',$file['url']) : $file['url']), 
	  										 					basename($file['url']), $ThumbWidth, $ThumbHeight )
	  										 		   			);
																break;
																case "2":
	  										 					$outputhtml .= htmlentities(
	  										 					sprintf('<a href="%s" title="%s" class="highslide" onclick="return hs.expand(this %s)" target="_blank"><img src="%s" alt="%s"  /></a>', 
	  										 					($LinkMode == 'relative' ? preg_replace('@^https?://.*?/@','/',$file['url']) : $file['url']), 
	  										 					basename($file['url']),
	  										 					($_POST["chkGallery"] != "" ? ",{slideshowGroup:'".$_SESSION["lastgroupid"]."'}": ""),
	  										 					($LinkMode == 'relative' ? preg_replace('@^https?://.*?/@','/',$AryThumbs[$i]) : $AryThumbs[$i]), 
	  										 					basename($AryThumbs[$i]) )
	  										 		   			);
																break;
															}
															if ($AryImageCaptions[$i] != "")
																$outputhtml .= "<div class=\"highslide-caption\">".$AryImageCaptions[$i]."</div>";
															
															$bbcode .= htmlentities(
	  										 					sprintf('[url=%s][img]%s[/img][/url]', 
																$file['url'], 
	  										 					$AryThumbs[$i] )
	  										 		 		  	);
																
															$raw .= $file['url'].chr(13);
															
															$i++;
													  }
													  if ($_POST["chkGallery"] != "")
															$outputhtml .= "</div>";
	  }
	//===
	
	//===
  if ($bad_results) {
	    					     $img_html .= "\n<h3>Errors occurred while resizing some images.</h3>\n<p>The following images had errors:</p>\n<ul>\n";
	  												  foreach ($AryErrors as $error) 
													  {
	  										 			$img_html .= "<li>$error</li>\n";
	  										 		  }
	    					     $img_html .= "</ul>\n";
	    					     if ($IsDebug == false)
	    					     { $img_html .= "<p>To get more information, please check debug options.</p>\n"; }  
	  						}
	  							$img_html = "<div class=\"sz_box\">\n$img_html\n</div>\n</div>\n";
  }
	//===
	
	  /* -----------*/
 	//$_SESSION["o"] = "This is Output HTML.";
 	//echo 'o='.$_SESSION["foo"].'<br>';
 	//echo 's='.$_SESSION["outputhtml"].'<br>';
 	//echo 'v='.$outputhtml.'<br>';
 	//echo 'result='.$good_results.'<br>';
 	//echo 'i='.$i.'<br>';
  	/* -----------*/
	
	$codebox = ($good_results ? "visible" : "hidden");
	$_SESSION["outputhtml"] = $outputhtml;

print <<<HEAD
<div class="wrap sz">
<div id="icon-upload" class="icon32"></div><h2>$title - Batch Images Upload!</h2>
HEAD;

echo "<code>System Info :</code>&nbsp;";
echo "PHP version : ".sz_CheckPHP().'&nbsp;&nbsp;';
echo "Zip : ".sz_LoadZip().'&nbsp;&nbsp;';
echo "GD : ".sz_LoadGD().'&nbsp;&nbsp;';
echo "Tmp folder : ".sz_CheckTmpFolder().'&nbsp;&nbsp;';
echo "Max upload size : ".sz_getMaxUploadSize().'<br />';
echo "<hr />"; 

print <<<FORM
  <form enctype="multipart/form-data" method="post">
    <div>
      <input type="hidden" id="sz_mode" name="sz_mode" value="file"/>
      <input type="hidden" name="action" value="save"/>
      <input type="hidden" name="sz_main_form" value="true"/>
      <input id="rad_file_mode" name="sz_toggle_mode" type="radio" value="file" onclick="toggle_mode(this.value)" checked="checked" style=\"cursor:pointer;\" />&nbsp;<label for="rad_file_mode" style=\"cursor:pointer;\">Files upload</label>
      <input id="rad_zip_mode" name="sz_toggle_mode" type="radio" value="zip" onclick="toggle_mode(this.value)" style=\"cursor:pointer;\" />&nbsp;<label for="rad_zip_mode" style=\"cursor:pointer;\">Zip upload</label>
      <input type="checkbox" id="chkGallery" name="chkGallery" style=\"cursor:pointer;\">&nbsp;<label for="chkGallery" style=\"cursor:pointer;\">Upload as Gallery</label>
FORM;

		
print <<<FILE_INPUT
            <div id="sz_ctlcontainer">
   		<p>Only support *.jpg , *.png , *.gif format.</p>
   		<div id="FileUploaderDiv">
    		   <input id="FileUploader" type="file" name="FileUpload[]" size="80" maxlength="50" accept="gif|jpg|png" />
                </div>
            </div>
            <div id="sz_file_button" style="padding-left:520px;">
                <input id="btnpost" type="submit" value="Upload" class="button-secondary action" style="width:80px" />
            </div>
FILE_INPUT;
	 
print <<<ZIP_DIV
      <div id="sz_zipcontainer" style="display:none;">
                <label id="lblZipUpload">Zip upload : <input type="file" name="ZipUpload" size="50" /></label>
                <input type="submit" value="Upload" class="button-secondary action"" />
      </div>
ZIP_DIV;
	 
print <<<FOOTER
    </div>
FOOTER;

print <<<MSG
	<!------Output------>
	<div id="output_html" Style="visibility: $codebox ;";>
		<h3>Upload Success ! </h3>
		<h5>Copy the HTML or BBCode below , and paste and your post or page.</h5>
MSG;
?>
	<input type="button" value="Preview" onclick="javascript:window.open('../wp-content/plugins/slidezoom/slidezoom-preview.php','SlideZoom Preview','height=480, width=680');" class="button-secondary action"/><br />
        <input id="radraw"  name="outputmode" type="radio" value="raw" onclick="ChangeOutput(this.value);" />Raw Url
         (Original images url) &nbsp; 
        <input id="radhtml" checked="checked" name="outputmode" type="radio" value="html" onclick="ChangeOutput(this.value);" />HTML
         (For WordPress, Facebook, Forum etc...) &nbsp; 
        <input id="radbbcode" name="outputmode" type="radio" value="bbcode" onclick="ChangeOutput(this.value);" />BBCode
         (For Forum) &nbsp; 
	<div id="rawbox" style="display:none"><textarea id="txtoutputraw"  cols="100" rows="14" onclick="this.select();"><?php echo $raw ?></textarea></div>
	<div id="htmlbox" style="display:display"><textarea id="txtoutputhtml"  cols="100" rows="14" onclick="this.select();"><?php echo $outputhtml ?></textarea></div>
	<div id="bbcodebox" style="display:none"><textarea id="txtoutputbbcode"  cols="100" rows="14" onclick="this.select();"><?php echo $bbcode ?></textarea></div>
		
<?php
$raw = "";
$outputhtml = "";
$bbcode = "";

print <<<MSG
	</div>
MSG;
?>

<?php
print <<<OUT
  </form>
  </div>
</div>
OUT;
}
?>
