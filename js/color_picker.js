// /js/color_picker.js

if(typeof String.prototype.digits == "undefined") {
	String.prototype.digits = function(n) {
		var str = this;
		while (str.length < n) {
			str = "0" + str;
		}
		return str;
	}
}

function setColor(c = null) {
	var redSlider	= document.getElementById("redSlider")
	var greenSlider = document.getElementById("greenSlider")
	var blueSlider	= document.getElementById("blueSlider")

	var redSliderValue		= document.getElementById("redSliderValue")
	var greenSliderValue	= document.getElementById("greenSliderValue")
	var blueSliderValue		= document.getElementById("blueSliderValue")

	var redDIV = document.getElementById("redDIV")
	var greenDIV = document.getElementById("greenDIV")
	var blueDIV = document.getElementById("blueDIV")
	var exampleDIV = document.getElementById("exampleDIV")
	var exampleColor = document.getElementById("exampleColor")

	var color;

	if (c != null) {
		if (c.left(1) == "#") {
			c = c.right(c.length - 1)
		}
		redSlider.value = parseInt(c.left(2), 16);
		c = c.right(c.length - 2)

		greenSlider.value = parseInt(c.left(2), 16);
		c = c.right(c.length - 2)

		blueSlider.value = parseInt(c.left(2), 16);
		c = null;
	}

	redSliderValue.value = redSlider.value;
	color = "#" + parseInt(redSlider.value).toString(16).digits(2) + "0000";
	redDIV.style.background = color;

	greenSliderValue.value = greenSlider.value;
	color = "#00" + parseInt(greenSlider.value).toString(16).digits(2) + "00";
	greenDIV.style.background = color

	blueSliderValue.value = blueSlider.value;
	color = "#0000" + parseInt(blueSlider.value).toString(16).digits(2);
	blueDIV.style.background = color

	color = "#" + parseInt(redSlider.value).toString(16) + parseInt(greenSlider.value).toString(16) + parseInt(blueSlider.value).toString(16);
	exampleDIV.style.backgroundColor = "rgb(" + parseInt(redSlider.value,10) + ", " +
												parseInt(greenSlider.value,10) + ", " +
												parseInt(blueSlider.value, 10) + ")";
	exampleColor.value = ("#" + parseInt(redSlider.value, 10).toString(16).digits(2) +
	                           parseInt(greenSlider.value, 10).toString(16).digits(2) +
							   parseInt(blueSlider.value, 10).toString(16).digits(2)).toUpperCase();
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

function saveColor(button) {
	var frm = button.form
	var callingFieldName = frm.callingFieldName.value
	var callingField = document.getElementsByName(callingFieldName)[0];
	callingField.value = frm.exampleColor.value;

	var colorPickerWindow = document.getElementById("colorPickerWindow");
	var parent = colorPickerWindow.parentNode;
	if (parent != null) {
		parent.removeChild(colorPickerWindow)
	}
	colorPickerWindow.remove();

}