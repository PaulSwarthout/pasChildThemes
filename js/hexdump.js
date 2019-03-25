if (typeof Array.prototype.highlightSpecial == "undefined") {
	Array.prototype.highlightSpecial = function () {
		return this
			.map(
				function (element, index) {
					var str = element.split("")
						.map(
							function (ch) {
								var charCode = ch.charCodeAt(0);
								switch (charCode) {
									case 1:
										return "[~font class='newline'~].[~/font~]";
										break;
									case 2:
										return "[~font class='tab'~].[~/font~]";
										break;
									default:
										return ch;
										break;
								}
							} ).join("");
					return str;
				} );
	}
}
if (typeof String.prototype.toASCII == "undefined") {
	String.prototype.toASCII = function () {
		var str = this.split("")
			.map(
				function (item) {
					return (item.charCodeAt(0) > 255 ? String.fromCharCode(255) : item);
				} )
			.join("");
		return str;
	}
}
if (typeof Array.prototype.toHex == "undefined") {
	Array.prototype.toHex = function () {
		var arr = this
			.map(
				function (char) {
					return char.charCodeAt(0).toString(16).padStart(2, '0').toUpperCase();
				} );
		return arr;
	}
}
if (typeof String.prototype.toHex == "undefined") {
	String.prototype.toHex = function () {
		return this.split("").toHex().join(" ");
	}
}
if (typeof String.prototype.toHTML == "undefined") {
	String.prototype.toHTML = function () {
		return this.replace(/</g, '&lt;').replace(/>/g, '&gt;');
	}
}
if (typeof Array.prototype.toHTML == "undefined") {
	Array.prototype.toHTML = function () {
		return this
			.map(
				function (item) {
					return item.replace(/</g, '&lt;').replace(/>/g, '&gt;');
				} );
	}
}
if (typeof String.prototype.filterNotVisible == "undefined") {
	String.prototype.filterNotVisible = function (replacementCharacter = "") {
		return this.replace(/[^ -~\x0A\x09]/g, replacementCharacter).replace(/\x0A/g, String.fromCharCode(1)).replace(/\x09/g, String.fromCharCode(2)).replace(/\x20/g, "_");
	}
}
if (typeof String.prototype.splice == "undefined") {
	String.prototype.splice = function (strt, n) {
		var sliced = this.slice(strt, n);
		var remaining = this.slice(n, this.length);
		return {slice : sliced, remainder : remaining};
	}
}
if (typeof Array.prototype.setIDs == "undefined") {
	Array.prototype.setIDs = function () {
		return this
			.map(
				function (element, index) {
					return "<font id='hx_" + index + "'>" + element + "</font>";
				} );
	}
}
/*
 * Array.prototype.mouseover uses [~ and ~] instead of < and > to prevent the String.prototype.toHTML()
 * function from rendering the mouseovers useless.
*/
if (typeof Array.prototype.mouseover == "undefined") {
	Array.prototype.mouseover = function () {
		return this
			.map(
				function (element, index) {
					return "[~span onmouseover='javascript:highlight(this, \"hx_" + index + "\");' onmouseout='javascript:dim(this, \"hx_" + index + "\");'~]" + element + "[~/span~]";
				} );
	}
}
function highlight(self, elementID) {
	var element = document.getElementById(elementID);
	element.style.color = "red";
	element.style.backgroundColor = "yellow";
	self.style.color = "red";
	self.style.backgroundColor = "yellow";
}
function dim(self, elementID) {
	var element = document.getElementById(elementID);
	element.style.color = "black";
	element.style.backgroundColor = "white";
	self.style.color = "black";
	self.style.backgroundColor = "white";
}

function pas_cth_js_hexdump() {
	var ee = new pas_cth_js_editElements();
	var str = ee.editBox.innerText.toASCII();

	var strArray	= str.split("");
	var hexArray	= strArray.toHex();
	var hexChunks	= hexArray.join("").match(/.{1,8}/g).setIDs();

	var dispString		= str.filterNotVisible(".")
	var outputChunks	= dispString.match(/.{1,4}/g).highlightSpecial().mouseover();

	var div = document.createElement("div");
	var closeBTN = document.createElement("span");
	div.setAttribute("id", "hexDump")
	document.getElementsByTagName("body")[0].appendChild(div);
	closeBTN.setAttribute("id", "hexCloseBTN");
	closeBTN.innerHTML = "X";
	div.appendChild(closeBTN);

	elementHexOutput = "";
	elementTxtOutput = "";

	while (hexChunks.length > 0) {
		outputHex = hexChunks.splice(0, 6);
		elementHexOutput += outputHex.join("&nbsp;&nbsp;") + "<br>";

		outputTxt = outputChunks.splice(0, 6);
		elementTxtOutput += outputTxt.toHTML().join(" ").replace(/\[~/g, "<").replace(/~\]/g, ">") + "<br>";
	}

	outputSetup = "<div id='hexGrid'><div class='hexItem1'>" + elementHexOutput + "</div><div class='hexItem2'>" + elementTxtOutput + "</div></div>";

	div.innerHTML += outputSetup;
	document.getElementById("hexCloseBTN").onclick =
		function () {
			var hexBox = document.getElementById("hexDump")
			if (hexBox.parentNode != null) {
				hexBox.parentNode.removeChild(hexBox);
			}
			hexBox.remove();
		}
}
