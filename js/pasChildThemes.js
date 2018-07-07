// pasChildThemes.js

function copyFile(element) {
	debugger;
}
function highlight(element) {
	if (element.data-bgc == "") {
		element.data-bgc = element.style.backgroundColor
		element.data-c = element.style.color
		element.style.backgroundColor = "RGB(250, 250, 0)";
		element.style.color = "RGB(255, 0, 0)";
	} else {
		element.style.backgroundColor = element.data-bgc
		element.style.color = element.data-c
	}

	var box = document.getElementById("actionBox")
	var style = "position:fixed;top:50%;left:50%;margin-top:-250px;margin-left:-400px;width:800px;height:500px;background-color:rgba(255,255,240,1);border:solid 1pt black;font-size:12pt;color:black;display:inline;visibility:visible;"
	if (box == null || box == undefined) {
		var box = document.createElement("div")
		var theBody = document.getElementsByTagName("body")[0];
		box.setAttribute("id", "actionBox");
		theBody.appendChild(box);
		box.style.cssText = style
		box.onclick=function () { box.parentNode.removeChild(box) }
	} else {
		box.style.cssText = style
	}
}