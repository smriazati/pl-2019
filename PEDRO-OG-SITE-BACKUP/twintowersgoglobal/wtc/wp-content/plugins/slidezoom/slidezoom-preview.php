<?php /**/ ?><?php
 if (!isset($_SESSION)) { session_start(); }
if ( isset($_SESSION["outputhtml"]) )
{
$outputhtml = $_SESSION["outputhtml"];
$lastgroupid = $_SESSION["lastgroupid"];
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" >
<head>
	<title>SlideZoom Preview</title>
	<?php
	$html = "<script type=\"text/javascript\" src=\"highslide/highslide-with-gallery.packed.js\"></script>\r\n";
	$html .= "<link rel=\"stylesheet\" type=\"text/css\" href=\"highslide/highslide.css\" />\r\n";
	$html .= "<script type=\"text/javascript\">\r\n";
	$html .= "hs.graphicsDir=\"highslide/graphics/\";\r\n";
	$html .= "hs.showCredits = false;\r\n";
	$html .= "hs.wrapperClassName=\"wide-border\";\r\n";
	$html .= "hs.outlineType = \"rounded-white\";\r\n";
	$html .= "hs.outlineWhileAnimating = false;\r\n";
	$html .= "</script>\r\n";
	echo $html;
	?>
</head>
<body>
<?php  echo html_entity_decode($outputhtml); ?>
</body>
</html>
