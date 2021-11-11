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
}
function pas_cth_js_expertMode(element) {
	var dataBlock = {};
	var optionsHelp = document.getElementById("optionsHelp");
	var createChildHelp = document.getElementById("createChildHelp");
	var copyThemeFilesHelp = document.getElementById("copyThemeFilesHelp");
	var screenshotHelp = document.getElementById("screenshotHelp");

	if (element.checked) {
		if (optionsHelp != null) { optionsHelp.classList.toggle("hideHelp", true); }
		if (createChildHelp != null) { createChildHelp.classList.toggle("hideHelp", true); }
		if (copyThemeFilesHelp != null) { copyThemeFilesHelp.classList.toggle("hideHelp", true); }
		if (screenshotHelp != null) { screenshotHelp.classList.toggle("hideHelp", true); }

		dataBlock.enabled = "TRUE";
	} else {
		if (optionsHelp != null) { optionsHelp.classList.toggle("hideHelp", false); }
		if (createChildHelp != null) { createChildHelp.classList.toggle("hideHelp", false); }
		if (copyThemeFilesHelp != null) { copyThemeFilesHelp.classList.toggle("hideHelp", false); }
		if (screenshotHelp != null) { screenshotHelp.classList.toggle("hideHelp", false); }
		dataBlock.enabled = "FALSE";
	}
	pas_cth_js_AJAXCall("setExpertMode", dataBlock);
}
function setDefaultChildTheme(element, childThemeName) {
	var dataBlock = { 'childTheme'	:	childThemeName };
	var reloadFN = function (response) {
		if (response.length == 0) {
			location.reload();
		} else {
			var box = document.createElement("div");
			box.id = "errorBox";
			document.getElementsByTagName("body")[0].appendChild(box);
			box.innerHTML = response;
			box.onclick = function (event) {
				if (this.parentNode != null) {
					this.parentNode.removeChild(this);
				}
				this.remove();
			}
			pas_cth_spinner.default_cursor();
		}
	}
	document.getElementsByTagName("body")[0].style.cursor = "wait";

	pas_cth_spinner.wait_cursor();
	
	pas_cth_js_AJAXCall("setDefaultChildTheme", dataBlock, reloadFN);
}
window.addEventListener("load", function (event) {
	var tabPage = document.getElementById("tabPage");
	if (tabPage == null) { return }
	tabPage.style.height = window.innerHeight.px();
});