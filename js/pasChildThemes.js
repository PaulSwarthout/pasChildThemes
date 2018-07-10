// pasChildThemes.js

function copyFile(element) {
	debugger;
}
function highlight(element) {
	var databgc = element.getAttribute("data-bgc")
	var datac = element.getAttribute("data-c")
//debugger
	if (databgc != null) {
		element.style.backgroundColor = databgc
		element.style.color = datac
		element.removeAttribute("data-bgc")
		element.removeAttribute("data-c")
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

	xmlhttp.open("POST", ajaxObject['ajax_url'],true)
	data.append("action", "pasChildThemes_selectFile")
	data.append("dir", element.getAttribute("data-dir"))
	data.append("fil", element.getAttribute("data-file"))
	data.append("typ", element.getAttribute("data-type"))

	xmlhttp.onreadystatechange = function () {
		if (xmlhttp.status == 200) {
		debugger
			document.getElementById("actionBox").innerHTML = xmlhttp.responseText;
		}
	}
	xmlhttp.send(data);
}
function showBox(element) {
	var box = document.getElementById("actionBox")
	var e;
	var style = "position:fixed;top:50%;left:50%;margin-top:-250px;margin-left:-400px;width:800px;height:500px;background-color:rgba(255,255,240,1);border:solid 1pt black;font-size:12pt;color:black;display:inline;visibility:visible;"
	if (box == null || box == undefined) {
		var box = document.createElement("div")
		var theBody = document.getElementsByTagName("body")[0];
		box.setAttribute("id", "actionBox");
		theBody.appendChild(box);
		box.style.cssText = style
		box.onclick=function () { box.parentNode.removeChild(box) }

		e = document.createElement("span")
		e.setAttribute("id", "boxHeader")
		fil = element.getAttribute("data-file")
debugger
		e.innerHTML = element.getAttribute("data-file")
		e.className = "pasChildTheme_boxHeader"
		e.style.width = "400px";
		e.style.height = "100px"
		box.appendChild(e)
	} else {
		box.style.cssText = style
	}
}