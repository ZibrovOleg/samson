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

	/*
	 * remove goods from the form
	 */ 
	function removeGoodsForm()
	{
		$('.form-multiple .modal-body .remove-goods').off().on('click', function() {
			$(this).parents('tr').remove();
		});
	}

	/*
	 * add goods in form
	 */ 
	function addGoodsForm()
	{
		$('.form-multiple .modal-body .add-goods').off().on('click', function() {
			let itemGoods = $(this).parents('tr'),
				firstField = itemGoods.find('td')[0],
				xml_id = $(firstField).find('input').val();

			if (!xml_id) {
				alert("Ошибка!\n\r"
				+ "Вы не заполнено обязательное поле XML_ID.")
				return;
			}

			if ($('.form-multiple .modal-body input[value=' + xml_id + ']').length > 0)
			{
				alert("Ошибка!\n\r"
					+ "Товара с таким внешним кодом " + xml_id + " уже есть");
				return;
			}

			$.ajax({
				type: 'POST',
				url: '/local/ajax/add_goods_by_form.php',
				data: {
					'sessid': BX.bitrix_sessid(),
					'xml_id': xml_id,
				},
				success: function(data) {
					if (data.length > 1)
					{
						itemGoods.html(data);

						let addItem = ''
						+ '<tr>'
							+ '<td scope="row">'
								+ '<input type="text" name="xml_id[]">'
							+ '</td>'
							+ '<td></td>'
							+ '<td></td>'
							+ '<td></td>'
							+ '<td><button type="button" class="btn btn-success btn-sm add-goods">добавить</button></td>'
						+ '</tr>';

						itemGoods.parent().append(addItem);
					}
					else
						alert("Ошибка!\n\r"
							+ "Нет товара с внешним кодом " + xml_id)

					addGoodsForm();
					removeGoodsForm();
				},
				error: function (jqXHR, exception) {}
			});
		});
	}

	addGoodsForm();
	removeGoodsForm();

	/*
	 * add goods in basket by xml_id
	 */ 
	$('.form-multiple').on('submit', function(e) {
		e.preventDefault();

		let formSerialize = $(this).serializeArray();
			formData = {
				'sessid': BX.bitrix_sessid(),
				'xml_id': []
			};

		$(formSerialize).each(function(index, data) {
			if (data.value)
				formData.xml_id.push(data.value);
		});

		$.ajax({
			type: 'POST',
			url: '/local/ajax/add_goods_by_basket.php',
			data: formData,
			success: function(data) {
				document.location.href = location;
			},
			error: function (jqXHR, exception) {}
		});
	});
	
});
