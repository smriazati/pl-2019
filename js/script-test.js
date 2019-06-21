// Global states
var es = false;

$(function() {


	// On page load

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
			$( "#translation-toggle #en" ).removeClass('active-lang');
			$( "#translation-toggle #es" ).addClass('active-lang');
		} else {
			$( "#translation-toggle #es" ).removeClass('active-lang');
			$( "#translation-toggle #en" ).addClass('active-lang');
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
	document.getElementById("en").addEventListener("click", activateEnglish);
	document.getElementById("es").addEventListener("click", activateSpanish);

	// Grab all links, get attribute ahref, append hashLangValueString to .html
	$( "a" ).click(function() {
		appendLangString($(this));
	});





// ADD series IDs to url for better permalink
// if a dv has a data-toggle="collapse"
// save the data-target="#" into a variable
// add this value to the variable


	// Bootstrap collapse fix
	var $myGroup = $('#all-series-expanded');
  $myGroup.on('show.bs.collapse','.collapse', function() {
    $myGroup.find('.collapse.show').collapse('hide');
  });


});
