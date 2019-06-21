// Global states
var es = false;

$(function() {


	// On page load



  // BOOTSTRAP


  // collapse

  // Open BOOTSTRAP COLLAPSE based on URL
  var $workSection = $('#all-series-expanded');
  var anchor = window.location.hash;

  $(".collapse").collapse('hide');
  $(anchor).collapse('show');

  // Close other collapse group when new one is opened
  $workSection.on('show.bs.collapse','.collapse', function() {
    $workSection.find('.collapse.show').collapse('hide');
  });

  // Get ID of currently open collapse, to add to HashURL
  $workSection.on('shown.bs.collapse','.collapse', function() {
    var workId = $workSection.find('.collapse.show').attr('id');
    console.log("myID is:", workId)
  });



	// Get hash value from URL
	var hashLangValue = window.location.href.split('#')[1];
	// Turn hash value into string
	var hashLangValueString = String(hashLangValue);

	// If hash value string is undefined or null, default to english
	if (hashLangValueString === 'undefined' || hashLangValueString === null){
		console.log("No hashtag found");
		// Set state
		es = false;
		console.log("es", es);
		// Clear hashLangValueString
		hashLangValueString = 'en';
		// Toggle content
		toggleContent();
		// Update language menu
		updateLanguageMenu();
		// Add hash to URL
		addLangHashUrl();
	} else {
		// If hash value string is found and valid
		console.log("hashLangValueString", hashLangValueString);
		// If hashLangValueString is "es"
		if (hashLangValueString == "es"){
			// Set state
			es = true;
			console.log("es", es);
			// Toggle content
			toggleContent();
			// Update language menu
			updateLanguageMenu();
		} else {
			// Set state
			es = false;
			console.log("es", es);
			// Toggle content
			toggleContent();
			// Update language menu
			updateLanguageMenu();
			// Add hash to URL
			addLangHashUrl();
		}

	}



	// FUNctions

	// Toggle content
	function toggleContent(){
		if (es){
			$(".es").show();
			$(".en").hide();
		} else {
			$(".es").hide();
			$(".en").show();
		}
	}

	// Update language menu
	function updateLanguageMenu(){
		if (es){
			$( "#translation-toggle #english" ).removeClass('active-lang');
			$( "#translation-toggle #espanol" ).addClass('active-lang');
		} else {
			$( "#translation-toggle #espanol" ).removeClass('active-lang');
			$( "#translation-toggle #english" ).addClass('active-lang');
		}
	}

	// Add hash to URL
	function addLangHashUrl(){
		if (es){
      window.location.hash = "es";
			hashLangValueString = "es";
			console.log("hashLangValueString", hashLangValueString);
		} else {
			window.location.hash = "en";
			hashLangValueString = "en";
			console.log("hashLangValueString", hashLangValueString);
		}
	}

	// Add hash to active link
	function appendLangString(activeLink){
		var elmHrefAttr = activeLink.attr("href");
		console.log("elmHrefAttr", elmHrefAttr);
		activeLink.attr("href", elmHrefAttr + "#" + hashLangValueString);
	}

	// Activate english
	function activateEnglish(){
		// Set state
		es = false;
		console.log("es", es);
		// Toggle content
		toggleContent();
		// Update language menu
		updateLanguageMenu();
		// Add hash to URL
		addLangHashUrl();
	}

	// Activate spanish
	function activateSpanish(){
		// Set state
		es = true;
		console.log("es", es);
		// Toggle content
		toggleContent();
		// Update language menu
		updateLanguageMenu();
		// Add hash to URL
		addLangHashUrl();
	}




	// Bind events
	document.getElementById("english").addEventListener("click", activateEnglish);
	document.getElementById("espanol").addEventListener("click", activateSpanish);

	// Grab all links with an HREF that ends in .html, get attribute ahref, append hashLangValueString to .html
	$( 'a[href$=".html"]' ).click(function() {
		appendLangString($(this));
	});






});
