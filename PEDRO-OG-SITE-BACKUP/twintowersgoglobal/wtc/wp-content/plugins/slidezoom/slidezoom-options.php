<?php /**/ ?><?php
//error_reporting(E_ALL);
//ini_set("display_errors",1);
require_once(ABSPATH . '/wp-admin/admin-functions.php');
require_once($relativepath.'function/CommonClass.php');
?>

<?php
if ($_POST["action"] == "option")
{ sz_FuncUpdateOptions(); }
?>

<?php
//Get Options
$AryOptions = sz_FuncGetOptions();
$upload_path = attribute_escape(str_replace(ABSPATH, '', get_option('upload_path')));
$upload_path = ($upload_path == '' ? 'wp-content/uploads' : $upload_path);
//ReDefine Options Values if the Options not exist in Database
if (false === $AryOptions)
{
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

foreach ($AryOptions as $name => $option) { $$name = $option; }
	
  //Define HTML CheckBox Attribute
  $AttIsDebug  = ($IsDebug) ? ' checked="checked"' : "";
  $AttIsResize = ($IsResize) ? ' checked="checked"' : "";
  $AttIsThumbMode1 = ($ThumbMode == "1" ? ' checked="checked"' : "");
  $AttIsThumbMode2 = ($ThumbMode == "2" ? ' checked="checked"' : "");
  $AttIsLinkMode1 = ($LinkMode == "relative" ? ' checked="checked"' : "");
  $AttIsLinkMode2 = ($LinkMode == "absolute" ? ' checked="checked"' : "");
  $AttIshsAlign1 = ($hsAlign == "auto" ? ' checked="checked"' : "");
  $AttIshsAlign2 = ($hsAlign == "center" ? ' checked="checked"' : "");
  
  $AttIseaseInQuad_o = ($easeOpen == "easeInQuad" ? ' selected="selected"' : "");
  $AttIslinearTween_o = ($easeOpen == "linearTween" ? ' selected="selected"' : "");
  $AttIseaseInCirc_o = ($easeOpen == "easeInCirc" ? ' selected="selected"' : "");
  $AttIseaseInBack_o = ($easeOpen == "easeInBack" ? ' selected="selected"' : "");
  $AttIseaseOutBack_o = ($easeOpen == "easeOutBack" ? ' selected="selected"' : "");
  $AttIseaseInQuad_c = ($easeClose == "easeInQuad" ? ' selected="selected"' : "");
  $AttIslinearTween_c = ($easeClose == "linearTween" ? ' selected="selected"' : "");
  $AttIseaseInCirc_c = ($easeClose == "easeInCirc" ? ' selected="selected"' : "");
  $AttIseaseInBack_c = ($easeClose == "easeInBack" ? ' selected="selected"' : "");
  $AttIseaseOutBack_c = ($easeClose == "easeOutBack" ? ' selected="selected"' : "");
  
  $AttIsFade1 = ($Fade == 'true' ? ' selected="selected"' : "");
  $AttIsFade2 = ($Fade == 'true' ? "" : ' selected="selected"');
   
	//Print HTML
print <<<OUT
  <div class="wrap">
  <div id="icon-options-general" class="icon32"><br /></div>
  <h2>SlideZoom Settings</h2>
OUT;
  	if ($_POST["action"] == "option")
	 { echo "<div style=\"background-color: rgb(255, 251, 204);\" id=\"message\" class=\"updated fade\"><p>Setting updated.</p></div>"; }
print <<<OUT
  </div>
  <form method="POST">
        <h3>General Options</h3>
		 
<table class="form-table">
<tr valign="top" style="display:none;">
<th scope="row" style="width:260px;">How many images uploader on start?</th>
<td>
<input name="txtUploadCount" id="txtUploadCount" value="$Number_of_uploads" type="text" size="4" />
<span class="setting-description">Default is<code>10</code>
</span>
</td>
</tr>

<tr valign="top">
<th scope="row">Store uploads in this folder :</th>
<td><input name="upload_path" type="text" id="upload_path" value="$upload_path" size="30" ReadOnly Disabled /><br />
<span class="setting-description">Due to SlideZoom using same upload path with WordPress, <br />if you would upload to other directory, please change on <a href="options-media.php"><code>Miscellaneous Settings</code></a>
</span>
</td>
</tr>

</table>

<hr/>

<h3>Image Options</h3>
<table class="form-table">

<tr valign="top">
<th scope="row" style="width:260px;"><input name="chkIsResize" id="chkIsResize" value="1" type="checkbox" $AttIsResize />&nbsp;Resize uploaded images when :</th>
<td>
		<b>Width</b> over <input name="txtMaxWidth" id="txtMaxWidth" value="$MaxWidth" type="text" size="4" />px and
		<b>Height</b> over <input name="txtMaxHeight" id="txtMaxHeight" value="$MaxHeight" type="text" size="4" />px
		<span class="setting-description">&nbsp;Apply to the images that uploaded by files or zip.</span>
</td>
</tr>

<tr valign="top">
<th scope="row">Thumbnail size : </th>
<td>
	<b>Width : </b><input name="txtThumbWidth" id="txtThumbWidth" value="$ThumbWidth" type="text" size="4" />px 
	<b>Height : </b><input name="txtThumbHeight" id="txtThumbHeight" value="$ThumbHeight" type="text" size="4" />px
</td>
</tr>


<tr valign="top">
<th scope="row">Thumbnail Mode : </th>
<td>
    <input id="radThumbMode1" name="radThumbMode" type="radio" value="1" $AttIsThumbMode1 />Original mode<br />
		<span class="setting-description">
				&nbsp;Only apply HTML attribute : use <a href="http://www.w3schools.com/tags/att_img_width.asp"><code>Width</code></a>
					and <a href="http://www.w3schools.com/tags/att_img_height.asp"><code>Height</code></a>
					to change the display size.&nbsp;The uploaded images will not be touch.
		</span><br />
    <input id="radThumbMode2" name="radThumbMode" type="radio" value="2" $AttIsThumbMode2 />Resize mode<br />
		<span class="setting-description">
				Resize images to thumbnails with size above. 
		</span>
</td>
</tr>

<tr valign="top">
<th scope="row">Link Mode : </th>
<td>
    <input id="radLinkMode1" name="radLinkMode" type="radio" value="relative" $AttIsLinkMode1 />Relative<br />
		<span class="setting-description">
				&nbsp;e.g. &lt;a href="[WordPress-Upload Path]/your_image.jpg"&gt;&lt;img src="[WordPress-Upload Path]/your_image.jpg" /&gt;&lt;/a&gt;
		</span><br />
    <input id="radLinkMode2" name="radLinkMode" type="radio" value="absolute" $AttIsLinkMode2 />Absolute<br />
		<span class="setting-description">
				&nbsp;e.g. &lt;a href="http://www.domain.com/[WordPress-Upload Path]/your_image.jpg"&gt;&lt;img src="http://www.domain.com/[WordPress-Upload Path]/your_image.jpg" /&gt;&lt;/a&gt;<br />If you want the images show on your RSS , or images use on external site, select this one.
		</span>
		<br />
		<a href="http://www.mediacollege.com/internet/html/hyperlinks.html" target="_blank"><code>More about Link mode</code></a>
</td>
</tr>

</table>

<hr/>

<h3>Effect Options</h3>
<table class="form-table">
<tr valign="top">
<th scope="row">Easing : </th>
<td>
Opening : 
<select id="ddlhseo" name="ddlhseo">
  <option value="easeInQuad" $AttIseaseInQuad_o>easeInQuad&nbsp;&nbsp;&nbsp;&nbsp;</option>
  <option value="linearTween" $AttIslinearTween_o>linearTween</option>
  <option value="easeInCirc" $AttIseaseInCirc_o>easeInCirc</option>
  <option value="easeInBack" $AttIseaseInBack_o>easeInBack</option>
  <option value="easeOutBack" $AttIseaseOutBack_o>easeOutBack</option>
</select>
Closeing : 
<select id="ddlhsec" name="ddlhsec">
  <option value="easeInQuad" $AttIseaseInQuad_c>easeInQuad&nbsp;&nbsp;&nbsp;&nbsp;</option>
  <option value="linearTween" $AttIslinearTween_c>linearTween</option>
  <option value="easeInCirc" $AttIseaseInCirc_c>easeInCirc</option>
  <option value="easeInBack" $AttIseaseInBack_c>easeInBack</option>
  <option value="easeOutBack" $AttIseaseOutBack_c>easeOutBack</option>
</select>
<br />
		<span class="setting-description">
		The easing effects is supplemented by Robert Penner's Easing Equations, offering a variety of different effects. See the 
		<a href="http://highslide.com/ref/hs.easing" target="_blank"><code>demonstration</code></a>
		</span>
</td>
</tr>

<tr valign="top">
<th scope="row">Fade-in/out : </th>
<td>
<select id="ddlhsfade" name="ddlhsfade">
  <option value="true" $AttIsFade1>Yes&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</option>
  <option value="false" $AttIsFade2>No</option>
</select>
<span class="setting-description">
	Add a fading effect to the regular expand/contract effect.
</span>
</td>
</tr>

<tr valign="top">
<th scope="row">Duration : </th>
<td>
	<b>Expand : </b><input name="txtexpandDuration" id="txtexpandDuration" value="$expandDuration" type="text" size="4" /> 
	<b>Restore : </b><input name="txtrestoreDuration" id="txtrestoreDuration" value="$restoreDuration" type="text" size="4" />
	<span class="setting-description">
		Higher value to make the effect more pronounced.
	</span>
</td>
</tr>

</table>

<hr/>

<h3>Expander Options</h3>
<table class="form-table">

<tr valign="top">
<th scope="row">Expander alignment : </th>
<td>
	<input id="radhsAlign1" name="radhsAlign" type="radio" value="auto" $AttIshsAlign1 />Auto &nbsp; <input id="radhsAlign2" name="radhsAlign" type="radio" value="center" $AttIshsAlign2 />Center
	<br />
		<span class="setting-description">
				Position of the full image, set auto to rely on thumb image.
		</span>
</td>
</tr>

<tr valign="top">
<th scope="row">Expander Margin : </th>
<td>
	Left : <input name="txthsmarginLeft" id="txthsmarginLeft" value="$hsmarginLeft" type="text" size="4" />&nbsp; Top : <input name="txthsmarginTop" id="txthsmarginTop" value="$hsmarginTop" type="text" size="4" /> &nbsp; Right : <input name="txthsmarginRight" id="txthsmarginRight" value="$hsmarginRight" type="text" size="4" /> &nbsp; Bottom : <input name="txthsmarginBottom" id="txthsmarginBottom" value="$hsmarginBottom" type="text" size="4" />
	<br />
		<span class="setting-description">
				Say you have a 200px wide left menu on your blog and you don't want the popups to cover it. Setting the marginLeft to 200 or more for prevents that.
		</span>
</td>
</tr>

</table>


		<!--Show debug info<input name="chkIsDebug" id="chkIsDebug" value="1" type="checkbox" $AttIsDebug />-->
		  <input type="hidden" name="action" value="option"/>
		  <input type="hidden" name="sz_option_form" value="1"/>
<p class="submit">
		  <input type="submit" value="Save Changes" class="button-primary" />		
</p>
  </form>
OUT;
?>