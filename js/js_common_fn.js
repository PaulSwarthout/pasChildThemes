if(typeof String.prototype.ltrim == "undefined")
	String.prototype.ltrim = function(){return this.replace(/^\s+/,"");}
if(typeof String.prototype.rtrim == "undefined")
	String.prototype.rtrim = function(){return this.replace(/\s+$/,"");}
if(typeof String.prototype.trim == "undefined")
	String.prototype.trim = function(){var str = this.ltrim();return str.rtrim();}
if(typeof String.prototype.right == "undefined")
	String.prototype.right = function(n){return this.substring(this.length - n, this.length)}
if(typeof String.prototype.left == "undefined")
	String.prototype.left = function(n) { return this.substring(0, n); }

// KillMe kills the error message boxes.
// After the last box has been destroyed, kill the actionBox div too.
function pas_cth_js_killMe(element) {
	var elements;
	if (element.parentNode != null) {
		element.parentNode.removeChild(element);
	}
	element.remove();

	elements = document.getElementsByName("errorMessageBox");
	if (0 == elements.length) {
		var actionBox = document.getElementById("actionBox");
		if (actionBox.parentNode != null) {
			actionBox.parentNode.removeChild(actionBox);
		}
		actionBox.remove();
	}
}
function pas_cth_js_processResponse(response) {
  // For a MENU:{} output, first, strip the MENU:{ and the final }.
	// Then dump the responseText to a menu box.
	if ("MENU:{" == response.left("MENU:{".length).toUpperCase()) {
		menuResponse = response.right(response.length - "menu:{".length);
		menuResponse = menuResponse.left(menuResponse.length - 1);
		box = pas_cth_js_showBox();
		box.setAttribute("id", "themeMenu");
		box.innerHTML = menuResponse;

	// DEBUG:{} only occurs when WP_DEBUG = true AND we're dealing with the pasDebug class.
	} else if ("DEBUG:{" == response.left("DEBUG:{".length).toUpperCase()) {
		actionBox = document.getElementById("actionBox");
		if (actionBox != null && actionBox != undefined) {
			if (actionBox.parentNode != null) {
				actionBox.parentNode.removeChild(actionBox);
			}
			actionBox.remove();
		}
		debugResponse = response.right(response.length - "debug:{".length);
		debugResponse = debugResponse.left(debugResponse.length - 1);
		box = pas_cth_js_createBox('debugBox', 'debug');
		box.innerHTML = debugResponse;
	// Nothing special. We've got output from the PHP function, dump it to the screen.
	} else {
		pas_cth_js_showBox().innerHTML = response;
	}
}
function pas_cth_js_showBox() {
	var box = document.getElementById("actionBox");
	var e;
	if (null == box || undefined == box) {
		box = document.createElement("div");
		var theBody = document.getElementsByTagName("body")[0];
		box.setAttribute("id", "actionBox");
		theBody.appendChild(box);
		box.onclick=function () {
			if (this.parentNode != null) {
				this.parentNode.removeChild(this);
			}
			this.remove();
		}
	}
	return box;

}
function pas_cth_js_createBox(id,className) {
	var box = document.getElementById(id);
	if (box != null && box != undefined) {
		if (box.parentNode != null) {
			box.parentNode.removeChild(box);
		}
		box.remove();
	}
	box = document.createElement("div");
	box.setAttribute("id", id);
	box.className = className;
	theBody = document.getElementsByTagName("body")[0];
	theBody.appendChild(box);
	box.onclick= function () {
		if (this.parentNode != null) {
			this.parentNode.removeChild(this);
		}
		this.remove();
	}
	return box;
}

function pas_cth_js_resetForm(frm) {
	frm.reset();
}
function pas_cth_js_showData(element) {
	var normalClass = "debugger";
	var pauseClass = "debuggerHide";

	var jsdata = element.getAttribute("data-jsdata");
	var d = document.getElementById("debugger");
	if (null == d || undefined == d) {
		d = document.getElementById("debuggerHide");
		if (null == d || undefined == d) {
			d = document.createElement("div");
			var theBody = document.getElementsByTagName("body")[0];
			d.setAttribute("id", "debugger");
			theBody.appendChild(d);
		}
	}
	// Set the "id" to normalClass for normal display, or pauseClass for mostly hidden, but left as
	//   reminder that this code needs to be removed.
	d.setAttribute("id", pauseClass);
	d.onmouseover = function () {
		if ("debugger" == d.id) {
			d.style.fontSize = "14pt";
		} else {
			d.setAttribute("id", "debugger");
		}
	}
	d.onmouseout = function () {
		d.style.fontSize = "8pt";
	}
	d.onclick = function () {
		if ("debugger" == d.id) {
			d.setAttribute("id", "debuggerHide");
		} else {
			if (d.parentNode != null) {
				d.parentNode.removeChild(d);
			}
			d.remove();
		}
	}
	d.innerHTML = jsdata;
}