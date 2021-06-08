class pas_cth_spinning_class {
	constructor() {
		this.self = this;
		this.wait_cursor_count = 0;
	}
	wait_cursor() {
		debugger
		var spinner;
		var self = this;
		if (this.wait_cursor_count == 0) {
			spinner = this.showSpinner();
			spinner.onclick = function () { toc_common.default_cursor(); self.wait_cursor_count = 0; }
		}
		this.wait_cursor_count++;
	}
	default_cursor() {
		this.wait_cursor_count--;
		if (this.wait_cursor_count <= 0) {
			this.hideSpinner();
			this.wait_cursor_count = 0;
		}
	}
	showSpinner() {
		var blanket = document.getElementById("wait_cursor_blanket");
		if (blanket == null || blanket == undefined) {
			blanket = document.createElement('div');
			blanket.id = 'wait_cursor_blanket';
			document.getElementsByTagName("body")[0].appendChild(blanket);
	
			var child = document.createElement('div');
			child.id = 'wait_cursor';
			child.classList.add('wait_cursor');
			blanket.appendChild(child);
		}
		blanket.style.visibility = "visible";
		blanket.style.zIndex = 9e10;
	
		return blanket;
	}
	hideSpinner() {
		var blanket = document.getElementById("wait_cursor_blanket");
		if (blanket != null && blanket != undefined) {
			blanket.style.visibility = "hidden";
		}
	}
}
var pas_cth_spinner = new pas_cth_spinning_class();
