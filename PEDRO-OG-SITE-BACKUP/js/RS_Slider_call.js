// You can also use "$(window).load(function() {"
$(function () {

  // IntroSlider
  $("#IntroSlider").responsiveSlides({
	auto: true,
	pager: true,
	nav: true,
	pause: false,
	speed: 500,
	maxwidth: 800,
	timeout: 5000,  
	namespace: "centered-btns"
  });

});