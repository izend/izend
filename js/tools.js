/**
 *
 * @copyright  2010-2013 izend.org
 * @version    6
 * @link       http://www.izend.org
 */

function trim(s) {
	return s.replace(/^\s\s*/, '').replace(/\s\s*$/, '');
}

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

function getcaretposition(e) {
	var pos = 0;

	if (document.selection && document.selection.createRange) {		// IE
		e.focus();
		var sel = document.selection.createRange();
		sel.moveStart('character', -e.value.length);
		pos = sel.text.length;
	}
	else if (e.selectionStart || e.selectionStart == '0') {			// Mozilla
		pos = e.selectionStart;
	}

	return pos;
}

function setcaretposition(e, pos) {
	if (e.createTextRange) {
		var range = e.createTextRange();
		range.collapse(true);
		range.moveEnd('character', pos);
		range.moveStart('character', pos);
		range.select();
	}
	else if (e.setSelectionRange) {
		e.setSelectionRange(pos, pos);
	}
}

function getwordat(s, pos) {
	if ((s[pos] == ' ' || pos == s.length) && (pos == 0 || s[pos-1] == ' ')) {
		return false;
	}

	for (i = pos-1; i >= 0 && s[i] != ' '; i--)
		;
	var beg = i < 0 ? 0 : i+1;

	for (i = pos; i < s.length && s[i] != ' '; i++)
		;
	var end = i >= s.length ? s.length : i;

	return s.substring(beg, end);
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

	label.innerHTML=remaining;
}

function addtag(id, open, close) {
	var e=document.getElementById(id);
	if (e) {
		if (document.selection && document.selection.createRange) {		// IE
			e.focus();
			var sel = document.selection.createRange();
			sel.text = open + sel.text + close;
		}
		else if (e.selectionStart || e.selectionStart == '0') {			// Mozilla
			var scrollTop = e.scrollTop;
			var startPos = e.selectionStart;
			var endPos = e.selectionEnd;
			e.value = e.value.substring(0, startPos) + open + e.value.substring(startPos, endPos) + close + e.value.substring(endPos, e.value.length);
			e.selectionStart = startPos;
			e.selectionEnd = endPos + open.length + close.length;
			e.scrollTop = scrollTop;
		}
		else {
			e.value = open + e.value + close;
		}

		e.focus();
	}
}

function striptags(id) {
	var e=document.getElementById(id);
	if (e) {
		var regexp=/<[^>]*>/gm;

		if (document.selection && document.selection.createRange) {		// IE
			e.focus();
			var sel = document.selection.createRange();
			sel.text = sel.text.replace(regexp, '');
		}

		else if (e.selectionStart || e.selectionStart == '0') {			// Mozilla
			var scrollTop = e.scrollTop;
			var startPos = e.selectionStart;
			var endPos = e.selectionEnd;
			var value = e.value.substring(startPos, endPos).replace(regexp, '');
			e.value = e.value.substring(0, startPos) + value + e.value.substring(endPos, e.value.length);
			e.selectionStart = startPos;
			e.selectionEnd = startPos + value.length;
			e.scrollTop = scrollTop;
		}

		else {
			e.value = e.value.replace(regexp, '');
		}

		e.focus();
	}
}

function entquote(id) {
	var e=document.getElementById(id);
	if (e) {
		if (document.selection && document.selection.createRange) {		// IE
			e.focus();
			var sel = document.selection.createRange();
			sel.text = entreplace(sel.text);
		}

		else if (e.selectionStart || e.selectionStart == '0') {			// Mozilla
			var scrollTop = e.scrollTop;
			var startPos = e.selectionStart;
			var endPos = e.selectionEnd;
			var value = entreplace(e.value.substring(startPos, endPos));
			e.value = e.value.substring(0, startPos) + value + e.value.substring(endPos, e.value.length);
			e.selectionStart = startPos;
			e.selectionEnd = startPos + value.length;
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

