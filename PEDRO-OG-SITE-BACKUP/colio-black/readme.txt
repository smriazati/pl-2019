Colio - Black Theme
---------------------------------------------


To install Colio theme please follow the steps below


STEP #1

Copy colio-black/ folder into your site root and then in head section of 
your index.html file link following files

<link rel="stylesheet" href="colio-black/style.css" />
<link rel="stylesheet" href="colio-black/fancybox/jquery.fancybox.css" />
<script type="text/javascript" src="colio-black/fancybox/jquery.fancybox.js"></script>


STEP #2

Use following markup for content that will be linked with portfolio items
using "data-content" attribute


<div id="content_1" class="colio-content">
					
	<div class="main">
		
		<h3>Title</h3>
		<p>Description here</p>
		
		<ul class="social">
			<li><a class="facebook" href="http://fadebook.com">Facebook</a></li>
			<li><a class="twitter" href="http://twitter.com">Twitter</a></li>
			<li><a class="google" href="http://google.com">Google</a></li>
		</ul>
		
		<a class="visit-link" href="http://codecanyon.net">Visit Site</a>
		
	</div><!-- main -->
	
	<div class="side">
		<ul class="feed">
			<li>
				<a class="fancybox" rel="group1" href="pictures/pic1.jpg">
					<img src="pictures/pic1.jpg" alt="">
				</a>
			</li>
			<li>
				<a class="fancybox" rel="group1" href="pictures/pic2.jpg">
					<img src="pictures/pic2.jpg" alt="">
				</a>
			</li>
			<li>
				<a class="fancybox" rel="group1" href="pictures/pic3.jpg">
					<img src="pictures/pic3.jpg" alt="">
				</a>
			</li>
		</ul>
	</div><!-- side -->
	
</div><!-- colio-content # content_1 -->


STEP #3

Finally add code to init Fancybox on loaded content using Colio "onContent" callback

$('#my_portfolio > ul').colio({
	id: 'colio_1',
	onContent: function(content){
		$('a.fancybox', content).fancybox();
	}
});