function returnonenter(e) {
	var keycode;

	if (window.event)
		keycode = window.event.keyCode;
	else if (e)
		keycode = e.which;
	else
		return true;

	return (keycode != 13);
}

function submitonenter(e, id) {
	var keycode;

	if (window.event)
		keycode = window.event.keyCode;
	else if (e)
		keycode = e.which;
	else
		return true;

	if (keycode == 13) {
		var button = document.getElementById(id);
		if (button)
			button.click();
		return false;
	}
	else
		return true;
}

function focusonenter(e, id) {
	var keycode;
	
	if (window.event)
		keycode = window.event.keyCode;
	else if (e)
		keycode = e.which;
	else
		return true;

	if (keycode == 13) {
		var field = document.getElementById(id);
		if (field)
			field.focus();
		return false;
	}
	else
		return true;
}

function focuson(id) {
	var e=document.getElementById(id);
	if (e)
		e.focus();
	return true;
}

function inputlimit(inputid, labelid, maxlength) {
	var input = document.getElementById(inputid);
	if (input == null)
		return;
	var label = document.getElementById(labelid);
	if (label == null)
		return;

	var s = input.value.replace("#\r\n#g","\n");
	var length = s.length;
	var remaining = maxlength - length;
	var v = document.createElement('span');
	v.id = labelid;
	v.innerHTML = Math.abs(remaining);
	v.className = remaining < 0 ? 'alert' : 'normal';
	label.parentNode.replaceChild(v, label);
}

function addtag(id, open, close) {
	var e=document.getElementById(id);
	if (e) {
		// IE
		if (document.selection && document.selection.createRange) {
			e.focus();
			sel = document.selection.createRange();
			sel.text = open + sel.text + close;
		}

		// Mozilla
		else if (e.selectionStart || e.selectionStart == '0') {
			var startPos = e.selectionStart;
			var endPos = e.selectionEnd;
			var scrollTop = e.scrollTop;
			e.value = e.value.substring(0, startPos) + open + e.value.substring(startPos, endPos) + close + e.value.substring(endPos, e.value.length);
			e.selectionStart = e.selectionEnd = endPos + open.length + close.length;
			e.scrollTop = scrollTop;
		}

		else {
			e.value = open + e.value + close;
		}

		e.focus();
	}
}

function entquote(id) {
	var e=document.getElementById(id);
	if (e) {
		// IE
		if (document.selection && document.selection.createRange) {
			e.focus();
			sel = document.selection.createRange();
			sel.text = entreplace(sel.text);
		}

		// Mozilla
		else if (e.selectionStart || e.selectionStart == '0') {
			var startPos = e.selectionStart;
			var endPos = e.selectionEnd;
			var scrollTop = e.scrollTop;
			e.value = e.value.substring(0, startPos) + entreplace(e.value.substring(startPos, endPos)) + e.value.substring(endPos, e.value.length);
			e.scrollTop = scrollTop;
		}

		else {
			e.value = entreplace(e.value);
		}

		e.focus();
	}
}

function entreplace(s) {
	return s.replace(/&/gm, '&amp;').replace(/</gm, '&lt;').replace(/>/gm, '&gt;').replace(/"/gm, '&quot;');
}

