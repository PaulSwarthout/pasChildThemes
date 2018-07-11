// pasChildThemes.js

function copyFile(element) {
	debugger;
}
function clearHighlights() {
	liElements = document.getElementsByTagName("li")
debugger
	for (ndx = 0; ndx < liElements.length; ndx++) {
		if (liElements[ndx].getAttribute("data-bgc")) {
			liElements[ndx].style.backgroundColor = liElements[ndx].getAttribute("data-bgc")
			liElements[ndx].style.color = liElements[ndx].getAttribute("data-c")
			liElements[ndx].removeAttribute("data-bgc")
			liElements[ndx].removeAttribute("data-c")
		}
	}
}
function highlight(element) {
	var databgc = element.getAttribute("data-bgc")
	var datac = element.getAttribute("data-c")
//debugger
	if (databgc != null) {
		clearHighlights();
	} else {
		element.setAttribute("data-bgc", element.style.backgroundColor)
		element.setAttribute("data-c", element.style.color)
		element.style.backgroundColor = "RGB(250, 250, 0)"
		element.style.color = "RGB(255, 0, 0)";
		showBox(element);
		dir = element.getAttribute("data-dir")
		fil = element.getAttribute("data-file")
		typ = element.getAttribute("data-type")

//		alert("Dir = " + dir + "\nFile = " + fil + "\nType = " + typ);
	}

	var xmlhttp = new XMLHttpRequest()
	var data = new FormData()

	xmlhttp.open("POST", ajaxurl,true)
	data.append("action", "fileSelect")
	data.append("directory", element.getAttribute("data-dir"))
	data.append("file", element.getAttribute("data-file"))
	data.append("type", element.getAttribute("data-type"))
	data.append("delimiter", element.getAttribute("data-delimiter"))

	xmlhttp.onreadystatechange = function () {
		if (xmlhttp.readyState == 4) {
			if (xmlhttp.status == 400) {
				msg = "400 Error:<br>" + xmlhttp.statusText
				document.getElementById("actionBox").innerHTML = msg
			} else if (xmlhttp.status == 200) {
				msg = xmlhttp.responseText + "<br>Done."
				document.getElementById("actionBox").innerHTML = msg
			}
		}
	}
	xmlhttp.send(data);
}
function showBox(element) {
	var box = document.getElementById("actionBox")
	var e;
	if (box == null || box == undefined) {
		var box = document.createElement("div")
		var theBody = document.getElementsByTagName("body")[0];
		box.setAttribute("id", "actionBox");
		theBody.appendChild(box);
		box.onclick=function () {
			box.parentNode.removeChild(box)

		}
	}

}