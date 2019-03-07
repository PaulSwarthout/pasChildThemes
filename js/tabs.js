function eList(elementObjectList) {
	this.stdArray = elementObjectList;

	this.toArray = function () {
		var arr = [];
		for (var ndx = 0; ndx < this.stdArray.length; ndx++) {
			arr[arr.length] = this.stdArray[ndx];
		}
		return arr;
	}
}
function openCTHTab(element) {
	var tab = element.getAttribute("data-tab");
	// Declare all variables
	var ndx, tabcontent, tablinks;

	// Mark all content inactive
	tabcontent = new eList(document.getElementsByClassName("tabcontent")).toArray();
	for (ndx = 0; ndx < tabcontent.length; ndx++) {
		tabcontent[ndx].classList.toggle("tab_active", false);
		tabcontent[ndx].classList.toggle("tab_inactive", true);
	}
	
	// Mark all buttons inactive
	tablinks = new eList(document.getElementsByClassName("tablinks")).toArray();
	for (i = 0; i < tablinks.length; i++) {
		tablinks[i].classList.toggle("active", false);
	}

	// Find the button to be active
	activeButtonSlug = element.getAttribute("data-tab");
	activeIndex = tablinks.findIndex(function (cell) {
		return cell.getAttribute("data-tab") == activeButtonSlug;
	});

	// Find the tabcontent to be active
	contentIndex = tabcontent.findIndex(function (cell) {
		return cell.getAttribute("data-tab") == activeButtonSlug;
	});

	tablinks[activeIndex].classList.toggle("active", true);
	tabcontent[contentIndex].classList.toggle("tab_inactive", false);
	tabcontent[contentIndex].classList.toggle("tab_active", true);
}
var defaultOpen = document.getElementById("defaultOpen");
if (defaultOpen != null) {
	defaultOpen.click();
}
var cthPage = document.getElementById("child-themes-helper-page");
if (cthPage != null) {
	var body = document.getElementsByTagName("body")[0];
	var html = document.getElementsByTagName("html")[0];

	body.style.overflow = "hidden";
	html.style.overflow = "hidden";
}
