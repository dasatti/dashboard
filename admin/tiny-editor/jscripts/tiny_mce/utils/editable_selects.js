/**
 * $Id: editable_selects.js 867 2008-06-09 20:33:40Z spocke $
 *
 * Makes select boxes editable.
 *
 * @author Moxiecode
 * @copyright Copyright © 2004-2008, Moxiecode Systems AB, All rights reserved.
 */

var TinyMCE_Editableselects = {
	editselectElm : null,

	init : function() {
		var nl = document.getElementsByTagName("select"), i, d = document, o;

		for (i=0; i<nl.length; i++) {
			if (nl[i].className.indexOf('mceEditableselect') != -1) {
				o = new Option('(value)', '__mce_add_custom__');

				o.className = 'mceAddselectValue';

				nl[i].options[nl[i].options.length] = o;
				nl[i].onchange = TinyMCE_Editableselects.onChangeEditableselect;
			}
		}
	},

	onChangeEditableselect : function(e) {
		var d = document, ne, se = window.event ? window.event.srcElement : e.target;

		if (se.options[se.selectedIndex].value == '__mce_add_custom__') {
			ne = d.createElement("input");
			ne.id = se.id + "_custom";
			ne.name = se.name + "_custom";
			ne.type = "text";

			ne.style.width = se.offsetWidth + 'px';
			se.parentNode.insertBefore(ne, se);
			se.style.display = 'none';
			ne.focus();
			ne.onblur = TinyMCE_Editableselects.onBlurEditableselectInput;
			ne.onkeydown = TinyMCE_Editableselects.onKeyDown;
			TinyMCE_Editableselects.editselectElm = se;
		}
	},

	onBlurEditableselectInput : function() {
		var se = TinyMCE_Editableselects.editselectElm;

		if (se) {
			if (se.previousSibling.value != '') {
				addselectValue(document.forms[0], se.id, se.previousSibling.value, se.previousSibling.value);
				selectByValue(document.forms[0], se.id, se.previousSibling.value);
			} else
				selectByValue(document.forms[0], se.id, '');

			se.style.display = 'inline';
			se.parentNode.removeChild(se.previousSibling);
			TinyMCE_Editableselects.editselectElm = null;
		}
	},

	onKeyDown : function(e) {
		e = e || window.event;

		if (e.keyCode == 13)
			TinyMCE_Editableselects.onBlurEditableselectInput();
	}
};
