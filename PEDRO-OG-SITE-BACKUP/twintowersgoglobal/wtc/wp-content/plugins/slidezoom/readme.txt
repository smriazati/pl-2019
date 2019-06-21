=== SlideZoom - Batch Upload & Gallery===
Contributors: Cheung Tat Ming
Donate link: http://www.netatlantis.com/?page_id=1317
Tags: Zip,Upload,Images,Highslide,Gallery,Slideshow,jQuery
Requires at least: 2.5
Tested up to: 3.30
stable tag: 1.4.1

SlideZoom is a lightweight plugin allow bulk upload images or zip upload to generate a HighSlide JS gallery.
Output as HTML code / BBCode etc...  And offering embed the gallery to everywhere (WordPress, Web ,Facebook, Forum, eBay etc...) by simple copy and paste to posts or pages.
To make the best use of your web space , to let all things serve as your own image hosting!

== Description ==

SlideZoom is a lightweight plugin allow bulk upload images or zip upload to generate a HighSlide JS gallery.
Output as HTML code / BBCode etc...  And offering embed the gallery to everywhere (WordPress, Web ,Facebook, Forum, eBay etc...) by simple copy and paste to posts or pages.
To make the best use of your web space , to let all things serve as your own image hosting!
<ul>
<li>Auto resize upload image, support jpg, png, gif format.</li>
<li>Auto rename when duplicate file name.</li>
<li>Auto create thumbnail images.</li>
</ul>
For demonstration, go to my blog and see how it work!
<a href="http://www.netatlantis.com/?page_id=1317" target="_blank">http://www.netatlantis.com/?page_id=1317</a>

== Installation ==

**New Installation :**

`1. Download & unzip to WordPress plugin directory.	
2. CHMOD the tmp folder to 777 (writable).
3. Login to WordPress admin panel and active the plugin.	
4. A new sub-menu [SlideZoom] will be appear under [setting] ,
   go to setting page and check your favourite setting.	
4. Under Media tab , click on sub-menu [SlideZoom] to upload images.`

**Upgrade  :**

`IMPORTANT : After upgrade SlideZoom, please go to SlideZoom setting page,
check all setting then save one time to complete the upgrade.

1. First deactivate SlideZoom.	
2. Download the latest SlideZoom , unzip and over write the old files.	
3. Activate SlideZoom.
----OR----
Simple using Wordpress automatic upgrade.`

== Frequently Asked Questions == 

= Do I need to download HighSlide JS or do somethings on WordPress's theme ? =

SlideZoom is Plug & Play plugin. 
You don't have to do anything at HighSlide JS Script , SlideZoom already hook up the script. And nothing to do about WordPress's theme. 
After you copy & paste the HTML Code in the textbox and publish the posts, HighSlide's script will load automatically.

= What happen if same filename is exist in my images folder? Will it be overwrite? =

No, if filename is same, newer file will be append a number as new filename.It apply on both classic file upload or zip upload.

= Any foot print ? =

The only foot print is SlideZoom need to write one line to WP database's options table , this line named : [slidezoom_options].
If you want keep your wp database clean , simple delete that line after deactivate.

= What happen after i deactivate SlideZoom, the images will lost on post/page ? =

SlideZoom is 100% flexible! After you deactivate the plugin , the thumbs/images you generated will keep in post/page. And the size will be retain.
What you lost is HighSlide effect.

= Your english look bad ? Why? =

Because i come from HongKong , I speak Chinese :)

== Screenshots ==

Please visit my blog page for demonstration : http://www.netatlantis.com/?page_id=1317
If you find any bugs or have any ideas, please leave a message on the page above.

1. The Options Screen
2. Output as HTML
3. Output as BBCode
4. Result - Highslide JS Gallery

== Changelog ==

`Version	Date
1.4.1	02/Jan/2012
Changed : Compatibility with WordPress 3.3.0
Changed : Update Highslide JS to version 4.1.13
Notes : Long times haven't update this. But i use this usually and i love it. 
        So this plugin still under maintenance and more function is WIP.`

`1.4.0	27/Feb/2011
Added : Gallery Mode - You can group all the uploaded images as a gallery.
        With gallery, there is a control show overlay on group of images to let 
        you play as slideshow or show next/prev images etc.   
Added : Images Caption - you can add some description of the images individually,
        the description will display when enlarge.
Changed : Minor bug fix and UI adjustment.
Changed : Compatibility with WordPress 3.1.0
Changed : Update Highslide JS to version 4.1.9
Fixed : Fix zip upload error caused by wrong function name  - getFilename.`

`1.3.3	03/Jul/2010
Changed : Compatibility with WordPress 3.0.0
Changed : Update jQuery Multiple File Upload Plugin to 1.4.7
Fixed : Auto insert default options value when first activation.`

`1.3.2	10/Jan/2010
Fixed : Remove unuse Javascript file.
Changed : Compatibility with WordPress 2.9.1
Changed : Update Highslide JS to version 4.1.8`

`1.3.1	05/Sep/2009
Fixed : Duplicate thumbnail images issue. Thanks alex.elite for bug report.
Changed : Images upload slot is replaced by auto-grow upload slot.`

`1.3.0	29/Aug/2009	
Fixed : It cause error on resize images , when filename contains uppercase character , 
        space , or special character. 
Added : Easing effect - see demo : http://highslide.com/ref/hs.easing . 
Added : Fade-in/out effect. 
Added : Ability to control the expander position using margin or alignment.
Added : Ability to tuning the duration of zoom-in and zoom-out.`

`1.2.1	05/Jun/2009	
Fixed : I make a mistake on the output html. It should be </a> , not <a/> .`

`1.2.0	01/Jun/2009	
Added : Zip upload , just like some image hosting, zip your images and upload. 
        SildeZoom will auto unzip, rename, resize.
Added : Generate relative url or absolute url for output html.
Added : Generate raw images url, for other purpose.
Added : Detect server setting and display info.
Changed : For better file management, thumbnail's filename ,
        now change from '-thumb' to '-t' and append to last of filename.	
Fixed : Possible missing thumbnail's url on some condition.`

`1.1.1	17/Apr/2009	
Changed : This version is maintenance release. 
        I suggest everyone should update to this version.
Fixed "Stack overflow in IE6 / IE7 / IE8" Javascript error alert.	
Changed : Update HighSlide JS to 4.1.4, 
        and reduce the size of highslide.js from 50KB to 25KB.	
Changed : Remove the "Powered by..." label. It is legal action. 
        See: http://highslide.com/forum/viewtopic.php?f=4&t=608`

`1.1.0	31/Mar/2009	
Added : Output result as BBCode.	
Added : Support resize images as thumbs, different from v1.0.0. 
        v1.0.0 only using html tag to reduce the size, but now, it is REAL resize. 
        Thumbnails image's file name will be insert "thumb_" as begin.	
Changed : Update HighSlide JS to 4.1.2.	
Changed : Shrink the HighSlide JS package from 90kB to 50kB now & remove unuse JS file.	
Changed : Refine the option and upload page layout.	`

`1.0.0	27/Mar/2009	
Initial Release.`

== Future Development ==

1. Support another PHP image process library.
2. More HighSlide styles & settings.
3. Custom upload directory.

**Any Suggestion? <br />Please go to my Blog : http://www.netatlantis.com/?page_id=1317 and leave a suggestion.**
**If you like this plugin , please promote to your friends! :) **