Number.prototype.px = function () {
	return this + "px";
}
function elementInfo(element) {
	this.element= element;

	this.rect = element.getBoundingClientRect(element);

	this.width	= this.rect.width;
	this.height	= this.rect.height;

	this.UL = 
		{
			'X'	:	this.rect.left,
			'Y' :	this.rect.top
		};
	this.UR = 
		{
			'X' :	this.rect.right,
			'Y' :	this.rect.top
		};
	this.LL = 
		{
			'X' :	this.rect.left,
			'Y' :	this.rect.bottom
		};
	this.LR = 
		{
			'X' :	this.rect.right,
			'Y' :	this.rect.bottom
		};
	this.isMouseOver = function (point = mousePosition) {
		var obj = this;
		if ((point.x >= obj.UL.X && point.x <= obj.UR.X) &&
			(point.y >= obj.UL.Y && point.y <= obj.LL.Y)) {
			return true;
		} else {
			return false;
		}
	}
}
var pas_cth_menu =
	[
		{'title'	:	'Copy File to Child Theme',		'function'	:	'pas_cth_js_selectFile', 'themeType'	: 'parent'},
		{'title'	:	'Remove File from Child Theme', 'function'	:	'pas_cth_js_selectFile', 'themeType'	: 'child' },
		{'title'	:	'Edit Child Theme File',		'function'	:	'pas_cth_js_editFile',	 'themeType'	: 'child' },
		{'title'	:	'View Parent Theme File',		'function'	:	'pas_cth_js_editFile',	 'themeType'	: 'parent'}
	];
window.addEventListener("click", function (e) {
	if (document.getElementById("popupMenu") == null) { return; }
	var popupMenu = document.getElementById("popupMenu");
	var position = { 'x'	:	e.clientX,
					 'y'	:	e.clientY };
	var obj = new elementInfo(popupMenu);
	if (obj.isMouseOver(position)) {
		return;
	} else {
		if (popupMenu.parentNode != null) {
			popupMenu.parentNode.removeChild(popupMenu);
		}
		popupMenu.remove();
	}
	return;
});
function copyDataAttributes(targetElement, sourceElement) {
	var ndx, attribute;
	for (ndx = 0; ndx < sourceElement.attributes.length; ndx++) {
		attribute = sourceElement.attributes[ndx];
		if (attribute.name.left(5).toLowerCase() == "data-") {
			targetElement.setAttribute(attribute.nodeName, attribute.nodeValue);
		}
	}
}
function pas_cth_js_openMenu(element, event) {
	if (event.shiftKey) { return }
	var pos = new elementInfo(element)
	var position = { 'x'	:	event.clientX, 'y'	:	event.clientY };
	var jsdata = JSON.parse(element.getAttribute("data-jsdata"));
	var elementID = Date.now();
	var childGrid = document.getElementById("childGrid");
	var parentGrid = document.getElementById("parentGrid");
	var themeType = "";

	var obj = new elementInfo(childGrid);
	if (obj.isMouseOver(position)) {
		themeType = "child";
	} else {
		obj = new elementInfo(parentGrid);
		if (obj.isMouseOver(position)) {
			themeType = "parent";
		}
	}
	if (! themeType.length) {
		return;
	}
	var menuElements = [];
	pas_cth_menu.forEach(function (cell) {
		if (cell.themeType == themeType) {
			menuElements[menuElements.length] = cell;
		}
	});
	element.setAttribute("id", "file_" + elementID);

	var box = document.getElementById("popupMenu");
	if (box != null) {
		box.parentNode.removeChild(box);
		box.remove();
	}
	box = document.createElement("DIV");
	var p = document.createElement("P");
	p.id = "menuFileName";
	var txt = document.createTextNode("File: " + jsdata.file);
	p.appendChild(txt);

	
	
	var rect = p.getBoundingClientRect();
	var pHeight = rect.height;
	rect = p.getBoundingClientRect();

	box.appendChild(p);

	var anchor = [];

	for (var ndx = 0; ndx < menuElements.length; ndx++) {
		p = document.createElement("P");
		p.className = "menuP";
		anchor[anchor.length] = document.createElement("A");
		anchor[anchor.length-1].text = menuElements[ndx]['title'];
		anchor[anchor.length-1].href = "javascript:void(0);";
		anchor[anchor.length-1].setAttribute("data-elementid", "file_" + elementID);
		anchor[anchor.length-1].classList.add("popupLinks");
		anchor[anchor.length-1].onclick = window[menuElements[ndx]['function']];
		copyDataAttributes(anchor[anchor.length-1], element);
		p.appendChild(anchor[ndx]);
		box.appendChild(p);
	}
	box.id = "popupMenu";

	var lft = position.x + 25;
	var tp = position.y;
	box.style.left = lft.px();
	box.style.top = tp.px();
	document.getElementsByTagName("body")[0].appendChild(box);

	box.style.width = box.scrollWidth;

	var pRect = p.getBoundingClientRect();
	var boxRect = box.getBoundingClientRect();

	event.preventDefault();
}
function findCorners(objPos) {
	this.adjustCorners = function (adjSize) {
		this.UL.X -= adjSize;
		this.UL.Y -= adjSize;

		this.UR.X += adjSize;
		this.UR.Y -= adjSize;

		this.LL.X -= adjSize;
		this.LL.Y += adjSize;

		this.LR.X += adjSize;
		this.LR.Y += adjSize;
	}
}
