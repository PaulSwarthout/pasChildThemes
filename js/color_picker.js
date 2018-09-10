// /js/color_picker.js
if(typeof String.prototype.right == "undefined")
	String.prototype.right = function(n){return this.substring(this.length - n, this.length)}
if(typeof String.prototype.left == "undefined")
	String.prototype.left = function(n) { return this.substring(0, n); }

if(typeof String.prototype.digits == "undefined") {
	String.prototype.digits = function(n) {
		var str = this;
		while (str.length < n) {
			str = "0" + str;
		}
		return str;
	}
}
function colorPickerElements() {
	// <input type='range' id='{color}Slider' ... >
	this.redSlider	= document.getElementById("redSlider")
	this.greenSlider= document.getElementById("greenSlider")
	this.blueSlider = document.getElementById("blueSlider")

	// <input type='text' id='{color}SliderValue' ...>
	this.redInt		= document.getElementById("redInt")
	this.greenInt	= document.getElementById("greenInt")
	this.blueInt	= document.getElementById("blueInt")

	// <div id='{color}DIV'>
	this.redDIV		= document.getElementById("redDIV")
	this.greenDIV	= document.getElementById("greenDIV")
	this.blueDIV	= document.getElementById("blueDIV")
	this.exampleDIV = document.getElementById("exampleDIV")

	// <input type='text' id='exampleColor' ... >
	this.colorText	= document.getElementById("colorText")
}
function colorValues(cpElements = null) {
	if (cpElements == null) {
		cpElements = new colorPickerElements();
	}
	this.redValue	= parseInt(cpElements.redSlider.value, 10)
	this.greenValue = parseInt(cpElements.greenSlider.value, 10)
	this.blueValue	= parseInt(cpElements.blueSlider.value, 10)

	this.redHex		= parseInt(cpElements.redInt.value, 10).toString(16).digits(2)
	this.greenHex	= parseInt(cpElements.greenInt.value, 10).toString(16).digits(2)
	this.blueHex	= parseInt(cpElements.blueInt.value, 10).toString(16).digits(2)
	this.color	= ("#" + this.redHex + this.greenHex + this.blueHex).toUpperCase();
	var parts = new colorParts(this.color);
	this.redColor = parts.redColor;
	this.greenColor = parts.greenColor;
	this.blueColor = parts.blueColor;
}
function setRed(redInt) {
	var cp = new colorPickerElements();
	cp.redSlider.value = redInt
	updateColorPicker();
}
function setGreen(greenInt) {
	var cp = new colorPickerElements();
	cp.greenSlider.value = greenInt;
	updateColorPicker();
}
function setBlue(blueInt) {
	var cp = new colorPickerElements();
	cp.blueSlider.value = blueInt;
	updateColorPicker();
}
function updateColorPicker() {
	var cp = new colorPickerElements();
	var cv;

	cp.redInt.value = parseInt(cp.redSlider.value, 10)
	cp.greenInt.value = parseInt(cp.greenSlider.value, 10)
	cp.blueInt.value = parseInt(cp.blueSlider.value, 10)

	cv = new colorValues(cp)
	cp.redDIV.style.backgroundColor = cv.redColor
	cp.greenDIV.style.backgroundColor = cv.greenColor
	cp.blueDIV.style.backgroundColor = cv.blueColor

	cp.exampleDIV.style.backgroundColor = cv.color;
	cp.colorText.value = cv.color
}


function colorPickerLibrary() {
	var cv = new colorValues();

	this.rgb = "rgb(" + cv.redValue + ", " + cv.greenValue + ", " + cv.blueValue + ")";
	this.colorCode = "#" + cv.redHex + cv.greenHex + cv.blueHex;
	this.setColor = function (color) {
		var parts = new colorParts(color);
		var cp = new colorPickerElements();

		cp.redSlider.value = parts.red
		cp.greenSlider.value = parts.green
		cp.blueSlider.value = parts.blue

		cp.redInt.value = parts.red
		cp.greenInt.value = parts.green
		cp.blueInt.value = parts.blue

		cp.redDIV.style.background = parts.redColor
		cp.greenDIV.style.background = parts.greenColor
		cp.blueDIV.style.background = parts.blueColor

		cp.exampleDIV.style.background = color

		cp.colorText.value = color
	}
	this.getColor = function () {
		var cv = new colorValues();
		return (cv.getColor());
	}
}
function colorParts(color) {
	var str = (color.left(1) == "#" ? color.right(color.length - 1) : color);
	this.redHex = str.substr(0, 2)
	this.red = parseInt(this.redHex, 16)
	this.redColor = "#" + this.redHex + "0000";

	this.greenHex = str.substr(2, 2)
	this.green = parseInt(this.greenHex, 16)
	this.greenColor = "#00" + this.greenHex + "00";

	this.blueHex = str.substr(4, 2)
	this.blue = parseInt(this.blueHex, 16)
	this.blueColor = "#0000" + this.blueHex;
}

function setColor(color) {
	var cpElements = new colorPickerElements();
	var cpLibrary  = new colorPickerLibrary();

	cpLibrary.setColor(color)
	updateColorPicker();
}
function pas_cth_js_showColorPicker(clr) {
	var xmlhttp = new XMLHttpRequest();
	var data	= new FormData();
	var currentColor = clr.value;
	var fieldName = clr.name;

	data.append('action', 'displayColorPicker');
	data.append('initialColor', currentColor);
	data.append('callingFieldName', fieldName);

	xmlhttp.open("POST", ajaxurl,true);

	xmlhttp.onreadystatechange = function () {
		if (4 == xmlhttp.readyState) {
			// strip the AJAX zero from wp_die() WORDPRESS ONLY
			var response = (xmlhttp.responseText.length >= 1 ?
								xmlhttp.responseText.left(xmlhttp.responseText.length - 1) :
								xmlhttp.responseText);

			switch (xmlhttp.status) {
				case 200: // Everything is Okay
					/* If responseText is not empty, there might be a request to overwrite
					 * or a request to delete that needs to be displayed.
					 * else, reload the page.
					 * <= 1 accounts for the AJAX return of zero that sometimes shows up
					 * despite my best efforts to avoid that.
					 */
					if (response.length <= 0) {
						// refresh the current display
						location.reload();
					} else {
						pas_cth_js_createColorWindow(response);
						// display any output from the wp_ajax_* function.
//						pas_cth_js_processResponse(response);
					}
					break;

				case 400: // There was an error
					msg = "400 Error:<br>" + xmlhttp.statusText;
					pas_cth_js_showBox().innerHTML = msg;
					break;
			}
		}
	}
	xmlhttp.send(data);
}

function pas_cth_js_createColorWindow(response) {
	var colorPickerWindow = document.getElementById("colorPickerWindow");
	var theBody = document.getElementsByTagName("body")[0];
	if (colorPickerWindow == null || colorPickerWindow == undefined) {
		colorPickerWindow = document.createElement("div");
		colorPickerWindow.setAttribute("id", "colorPickerWindow");
		theBody.appendChild(colorPickerWindow);
	}
	colorPickerWindow.innerHTML = response;
}
function exitColorPickerDialog() {
	var colorPickerWindow = document.getElementById("colorPickerWindow");
	var parent = colorPickerWindow.parentNode;
	if (parent != null) {
		parent.removeChild(colorPickerWindow)
	}
	colorPickerWindow.remove();
}
function saveColor(button) {
	var frm = button.form
	var callingFieldName = frm.callingFieldName.value
	var callingField = document.getElementsByName(callingFieldName)[0];
	callingField.value = frm.colorText.value;
	pas_cth_js_SetOption(callingField);
	var fgColor = invertColor(frm.colorText.value, true)
	callingField.style.color = fgColor;
	callingField.style.backgroundColor = frm.colorText.value
	callingField.style.border = "inset";
	exitColorPickerDialog();
}
function cancelColorChange(element) {
	exitColorPickerDialog();
}
function invertColor(hex, bw) {
    if (hex.indexOf('#') === 0) {
        hex = hex.slice(1);
    }
    var r = parseInt(hex.slice(0, 2), 16),
        g = parseInt(hex.slice(2, 4), 16),
        b = parseInt(hex.slice(4, 6), 16);
    if (bw) {
        // http://stackoverflow.com/a/3943023/112731
        return (r * 0.299 + g * 0.587 + b * 0.114) > 186
            ? '#000000'
            : '#FFFFFF';
    }
    // invert color components
    r = (255 - r).toString(16).digits(2);
    g = (255 - g).toString(16).digits(2);
    b = (255 - b).toString(16).digits(2);
    // pad each with zeros and return
    return "#" + r + g + b;
}
function showDropDown(listBoxID) {
	var listBox = document.getElementById(listBoxID)
	if (listBox.style.display == "") {
		listBox.style.display = "inline-block";
	} else {
		listBox.style.display = "";
	}
}
function selectThisFont(fontDataElement) {
	var fontData = JSON.parse(fontDataElement.getAttribute("data-font"));
	var row				= fontData['data-row'];
	var fontName		= row['fontName']
	var fontBase		= row['fontFile-base'];
	var fontFile		= fontData['url'] + fontBase + ".ttf";
	var fontSampleImg	= fontData['url'] + fontBase + ".png";

	var textBox = document.getElementById(fontData['text-box']);
	var listBox = document.getElementById(fontData['list-box']);

	var selectedFontNameElement = document.getElementById("selectedFontName")
	var selectedFontSampleElement = document.getElementById("selectedFontSample")

	selectedFontNameElement.innerHTML = fontName

	var img = document.getElementById("sampleFontImage")
	if (img != null) {
		if (img.parentNode != null) {
			img.parentNode.removeChild(img);
		}
		img.remove();
	}
	img = document.createElement("img")
	img.setAttribute("id", "sampleFontImage")
	img.src = fontSampleImg
	img.style.cssText = "visibility:visible;display:inline;";
	selectedFontSampleElement.appendChild(img)

	textBox.style.display = "inline";
	listBox.style.display = "none";

	var xmlhttp = new XMLHttpRequest();
	var data = new FormData();
	data.append("action", "saveDefaultFont");
	data.append("fontName", fontName);
	data.append("fontFile-base", fontBase);

	xmlhttp.open("POST", ajaxurl,true);
	xmlhttp.onreadystatechange = function () {

		if (4 == xmlhttp.readyState) {
			// strip the AJAX zero from wp_die() WORDPRESS ONLY
			var response = (xmlhttp.responseText.length >= 1 ?
								xmlhttp.responseText.left(xmlhttp.responseText.length - 1) :
								xmlhttp.responseText);
			switch (xmlhttp.status) {
				case 200: // Everything is Okay
					/* If responseText is not empty, there might be a request to overwrite
					 * or a request to delete that needs to be displayed.
					 * else, reload the page.
					 * <= 1 accounts for the AJAX return of zero that sometimes shows up
					 * despite my best efforts to avoid that.
					 */
					if (response.length <= 0) {
						// do nothing. Just return.

					} else {
						// display any output from the wp_ajax_* function.
						pas_cth_js_processResponse(response);
					}
					break;

				case 400: // There was an error
					msg = "400 Error:<br>" + xmlhttp.statusText;
					pas_cth_js_showBox().innerHTML = msg;
					break;
			}
		}
	}
	xmlhttp.send(data);
}