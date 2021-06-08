"use strict;"
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
function colorPickerElements(abbr) {
	this.initialColor = document.getElementById(abbr + "_initial_color");

	// <div class='grid-item 1, 2, 3
	this.rvalcell	= document.getElementById(abbr + "_rval_cell")
	this.gvalcell	= document.getElementById(abbr + "_gval_cell")
	this.bvalcell	= document.getElementById(abbr + "_bval_cell")
	this.hexvalcell	= document.getElementById(abbr + "_hexval_cell")

	this.rval		= document.getElementById(abbr + "_rval")
	this.gval		= document.getElementById(abbr + "_gval")
	this.bval		= document.getElementById(abbr + "_bval")
	this.hexval		= document.getElementById(abbr + "_hexval")

	this.redSliderCell		= document.getElementById(abbr + "_redSlider_cell")
	this.greenSliderCell	= document.getElementById(abbr + "_greenSlider_cell")
	this.blueSliderCell		= document.getElementById(abbr + "_blueSlider_cell")

	this.redSlider			= document.getElementById(abbr + "_redSlider")
	this.greenSlider		= document.getElementById(abbr + "_greenSlider")
	this.blueSlider			= document.getElementById(abbr + "_blueSlider")

	this.redName			= document.getElementById(abbr + "_redName")
	this.greenName			= document.getElementById(abbr + "_greenName")
	this.blueName			= document.getElementById(abbr + "_blueName")
	this.hexName			= document.getElementById(abbr + "_hexName")

	this.saveButtonCell		= document.getElementById(abbr + "_saveButton_cell")
	this.saveButton			= document.getElementById(abbr + "_saveButton")
	this.resetButtonCell	= this.saveButtonCell;
	this.resetButton		= document.getElementById(abbr + "_resetButton")

	this.cpOuter			= document.getElementById(abbr + "_cpOuter")

	this.hexColor2Decimal	= function (hexvalue) {
		hexvalue = (hexvalue.left(1) == "#" ? hexvalue.right(hexvalue.length - 1).digits(6) : hexvalue.digits(6));
		var r = parseInt(hexvalue.left(2), 16)
		hexvalue = hexvalue.right(hexvalue.length - 2)
		var g = parseInt(hexvalue.left(2), 16);
		hexvalue = hexvalue.right(hexvalue.length - 2)
		var b = parseInt(hexvalue.left(2), 16);

		return {red:r, green:g, blue:b};
	}
}
function colorValues(cpElements = null) {
	this.redValue	= parseInt(cpElements.redSlider.value, 10)
	this.greenValue = parseInt(cpElements.greenSlider.value, 10)
	this.blueValue	= parseInt(cpElements.blueSlider.value, 10)

	this.redHex		= parseInt(cpElements.rval.value, 10).toString(16).digits(2)
	this.greenHex	= parseInt(cpElements.gval.value, 10).toString(16).digits(2)
	this.blueHex	= parseInt(cpElements.bval.value, 10).toString(16).digits(2)

	this.redColor	= ("#" + this.redHex + "0000").toUpperCase();
	this.greenColor = ("#" + "00" + this.greenHex + "00").toUpperCase();
	this.blueColor	= ("#" + "0000" + this.blueHex).toUpperCase();

	this.color	= ("#" + this.redHex + this.greenHex + this.blueHex).toUpperCase();
//	this.colorParts = new colorParts(this.color);
}
function setColor(color, abbr, element) {
	var cp = new colorPickerElements(abbr);
	switch (color.toLowerCase()) {
		case "red":
			cp.redSlider.value = element.value;
			break;
		case "green":
			cp.greenSlider.value = element.value;
			break;
		case "blue":
			cp.blueSlider.value = element.value
			break;
	}
	updateColorPicker(abbr);
}
function setRed(element) {
	var abbr = element.id.left(element.id.length - "_.val".length);
	setColor("red", abbr, element);
}
function setGreen(element) {
	var abbr = element.id.left(element.id.length - "_.val".length);
	setColor("green", abbr, element);
}
function setBlue(element) {
	var abbr = element.id.left(element.id.length - "_.val".length);
	setColor("blue", abbr, element);
}
function setHex(element) {
	var abbr = element.id.left(element.id.length - "_hexval".length);
	var cp = new colorPickerElements(abbr);
	var decvalues = cp.hexColor2Decimal(element.value);

	cp.rval.value = decvalues.red;
	cp.redSlider.value = decvalues.red;

	cp.gval.value = decvalues.green;
	cp.greenSlider.value = decvalues.green;

	cp.bval.value = decvalues.blue;
	cp.blueSlider.value = decvalues.blue;

	updateColorPicker(abbr);
}
function updateColorPicker(abbrev = "") {
	var cp = new colorPickerElements(abbrev);
	var cv;

	var abbrevList = [];

	if (abbrev == "") {
		var divList = document.getElementsByTagName("div")
		var ndx;
		var div;
		var divID;

		for (ndx = 0; ndx < divList.length; ndx++) {
			div = divList[ndx];
			divID = div.id

			if (divID.toLowerCase().right("_redSlider_cell".length) == "_redslider_cell") {
				abbrevList.push(divID.left(divID.length - "_redslider_cell".length));
			}
		}
		for (ndx = 0; ndx < abbrevList.length; ndx++) {
			updateColorPicker(abbrevList[ndx]);
		}
		return;
	} else {
		// update the named color picker
	}

	cp.rval.value = parseInt(cp.redSlider.value, 10)
	cp.gval.value = parseInt(cp.greenSlider.value, 10)
	cp.bval.value = parseInt(cp.blueSlider.value, 10)

	cv = new colorValues(cp)
	cp.rvalcell.style.backgroundColor = cv.redColor
	cp.gvalcell.style.backgroundColor = cv.greenColor
	cp.bvalcell.style.backgroundColor = cv.blueColor
	cp.hexvalcell.style.backgroundColor = cv.color
	cp.saveButtonCell.style.backgroundColor = cv.color

	cp.redName.style.color = invertColor(cv.redColor)
	cp.greenName.style.color = invertColor(cv.greenColor)
	cp.blueName.style.color = invertColor(cv.blueColor)
	var invertedColor = invertColor(cv.color);
	cp.hexName.style.color = invertedColor;

	cp.rvalcell.style.borderColor = invertColor(cv.redColor)
	cp.gvalcell.style.borderColor = invertColor(cv.greenColor)
	cp.bvalcell.style.borderColor = invertColor(cv.blueColor)

	cp.hexval.value = cv.color

	cp.saveButton.style.borderColor = invertedColor;
	cp.resetButton.style.borderColor = invertedColor;

	cp.saveButton.disabled = false;
	cp.resetButton.disabled = false;
}
function getMaxColor(r, g, b) {
	var a = [r, g, b].sort();
	return a[a.length - 1];
}

function getMinColor(r, g, b) {
	var a = [r, g, b].sort();
	return a[0];
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
function pas_cth_js_showColorPicker(clr) {
	var currentColor = clr.value;
	var fieldName = clr.name;
	var dataBlock = {};
	dataBlock.initialColor = currentColor;
	dataBlock.callingFieldName = fieldName;
	var successCallback = function (response) {
		if (response.length) {
			pas_cth_js_createColorWindow(response);
		} else {
			location.reload();
		}
	}
	pas_cth_js_AJAXCall('displayColorPicker', dataBlock, successCallback, pas_cth_js_failureCallback);
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
function makeElement(id, name, type, value) {
	var element = document.createElement("input");
	element.setAttribute("id", id);
	element.setAttribute("name", name);
	element.setAttribute("type", type);
	element.setAttribute("value", value);
	return element;
}
function saveColor(button) {
	var abbr = button.getAttribute("data-abbr");
	var cp = new colorPickerElements(abbr);
	var dataBlock = {};
	cp.initialColor.value = cp.hexval.value;

	var jsInput;

	dataBlock.hexColorCode = cp.hexval.value;
	dataBlock.abbreviation = abbr;
	cp.saveButton.disabled = true;
	cp.resetButton.disabled = true;
	pas_cth_js_AJAXCall('saveOptions', dataBlock, pas_cth_js_successCallback, pas_cth_js_failureCallback);
}
function resetColor(element) {
	var abbr = element.id.left(element.id.length - "_resetButton".length)
	var cp = new colorPickerElements(abbr);
	cp.hexval.value = cp.initialColor.value;
	var hexcolors = cp.hexColor2Decimal(cp.initialColor.value);
	cp.rval.value = hexcolors.red;
	cp.gval.value = hexcolors.green;
	cp.bval.value = hexcolors.blue;

	cp.redSlider.value = hexcolors.red;
	cp.greenSlider.value = hexcolors.green;
	cp.blueSlider.value = hexcolors.blue;

	updateColorPicker(abbr);
	cp.saveButton.disabled = true;
	cp.resetButton.disabled = true;

}
// Get Complementary color
function invertColor(hex, bw = true) {
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
;
	var selectedFontSampleElement = document.getElementById("selectedFontSample")
;
	
	var dataBlock = {};
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

	dataBlock.fontName = fontName;
	dataBlock['fontFile-base'] = fontBase;
	var successCallback = function (response) {
		if (response.length) {
			pas_cth_js_processResponse(response);
			pas_cth_js_hideWait();
		}
	}
	pas_cth_js_AJAXCall('saveDefaultFont', dataBlock, successCallback, pas_cth_js_failureCallback);
}
function test(abbr) {
	var r = document.getElementById(abbr + "_rval_cell")
	r.style.backgroundColor = "#00FF00";

	var rslidercell = document.getElementById(abbr + "_redSlider_cell")
	var gslidercell = document.getElementById(abbr + "_greenSlider_cell")
	var bslidercell = document.getElementById(abbr + "_blueSlider_cell")
}

function makeItDarker(element) {
	var abbr = element.id.left(element.id.length - "_darkerBTN".length);
	var cp = new colorPickerElements(abbr);

	var cv = new colorValues(cp);

	var minColor = getMinColor(cv.redValue, cv.greenValue, cv.blueValue);
	var adjustValue = 255 * 0.05; // 5%

	cp.rval.value = (parseInt(cp.rval.value, 10) - adjustValue < 0 ? 0 : parseInt(cp.rval.value, 10) - adjustValue);
	cp.redSlider.value = cp.rval.value;

	cp.gval.value = (parseInt(cp.gval.value, 10) - adjustValue < 0 ? 0 : parseInt(cp.gval.value, 10) - adjustValue);
	cp.greenSlider.value = cp.gval.value;

	cp.bval.value = (parseInt(cp.bval.value, 10) - adjustValue < 0 ? 0 : parseInt(cp.bval.value, 10) - adjustValue);
	cp.blueSlider.value = cp.bval.value;
	updateColorPicker(abbr);
}
function makeItLighter(element) {
	var abbr = element.id.left(element.id.length - "_lighterBTN".length);
	var cp = new colorPickerElements(abbr);

	var cv = new colorValues(cp);

	var maxColor = getMaxColor(cv.redValue, cv.greenValue, cv.blueValue);
	var adjustValue = 255 * 0.05; // 5%

	cp.rval.value = (parseInt(cp.rval.value, 10) + adjustValue > 255 ? 255 : parseInt(cp.rval.value, 10) + adjustValue);
	cp.redSlider.value = cp.rval.value;

	cp.gval.value = (parseInt(cp.gval.value, 10) + adjustValue > 255 ? 255 : parseInt(cp.gval.value, 10) + adjustValue);
	cp.greenSlider.value = cp.gval.value;

	cp.bval.value = (parseInt(cp.bval.value, 10) + adjustValue > 255 ? 255 : parseInt(cp.bval.value, 10) + adjustValue);
	cp.blueSlider.value = cp.bval.value;
	updateColorPicker(abbr);
}
function setWebColor(element, color) {
	var abbr = element.getAttribute("data-abbr");
	var cp = new colorPickerElements(abbr);
	var decvalues = cp.hexColor2Decimal(color);

	cp.rval.value = decvalues.red;
	cp.gval.value = decvalues.green;
	cp.bval.value = decvalues.blue;

	cp.redSlider.value = decvalues.red;
	cp.greenSlider.value = decvalues.green;
	cp.blueSlider.value = decvalues.blue;
	cp.hexval.value = color;

	updateColorPicker(abbr);
}
function generateScreenShot(abbr) {
	var saveBTNs = new eList(document.getElementsByClassName("saveButton")).toArray();
	var unsavedCount = 0;

	saveBTNs.map(function (cell) {
		if (! cell.disabled ) {
			unsavedCount++;
		}
	});

	if (unsavedCount > 0) {
		var box = pas_cth_library.displayError("Please save or reset your changes before attempting to generate a new screenshot for your child theme.");
		var p = document.createElement("P");
		p.innerHTML = "close [X]";
		box.appendChild(p);
		return;
	}
	var displayScreenshotFile = function (xmlResponse) {
		var parser = new DOMParser();
		var xmlDoc = parser.parseFromString(xmlResponse,"text/xml");
		var records = xmlDoc.getElementsByTagName("record");
		var site_url	= records[0].getElementsByTagName("siteURL")[0].textContent;
		var stylesheet	= records[0].getElementsByTagName("stylesheet")[0].textContent;
		var filename	= records[0].getElementsByTagName("filename")[0].textContent;

		var box = pas_cth_library.createBox("screenshotBox")
		box.onclick = function () {
			if (this.parentNode != null) {
				this.parentNode.removeChild(this);
			}
			this.remove();
		}
		var theBody = document.getElementsByTagName("body")[0];
		var p = document.createElement("P");
		p.appendChild(document.createTextNode("[X] close"));

		var img = document.createElement("IMG");
		img.src = site_url + "/wp-content/themes/" + stylesheet + "/" + filename + "?cacheBuster=" + Date.now();
		box.appendChild(img);
		box.appendChild(p);
	}

	pas_cth_js_AJAXCall("generateScreenShot", null, displayScreenshotFile);
}
