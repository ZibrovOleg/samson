/*
 * custom.js
 */

"use strict";

$(function() {
	/*
	 * event copy protection 
	 */ 
	$('body').bind('cut copy', function (e) {
		e.preventDefault();
		alert("Ошибка при копировании!\n\r"
			+ "Вы должны помнить, что нельзя копировать текст без разрешения.");
	});
});
