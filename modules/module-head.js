function head_page(){
	select_menu('reckoning-menu', 2);
	show_my_bid_menu();
}

function show_my_bid_menu(){
	var str = 'func=show_my_bid_menu';
	$.ajax({
		type: 'POST',
		data: str,
		url: 'mysql.php',
		success: function(html){
			$('#body').html(html);
			$('.my-bid-page li:first').trigger('click');
		}
	});
}

function get_my_reckoning(page){
	var li = page+'-bid-page';
	if(page == undefined){
		page = '';
		li = $('.my-bid-page li:first').attr('class');
	}
	show_loader_element('.my-bid-block');
	$('.my-bid-page li').removeClass('active');
	$('.my-bid-page li.'+li).addClass('active');
	var str = 'func=get_my_reckoning&page=' + page;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			$('.my-bid-block').html(html);
			remove_all_windows();
			$(".my-bid-table").tablesorter({
				headers:{
					/*8:{
						sorter: false
					}*/
				}
			});
		}
	});
}

function return_query_report_manager(){
	$('.my-bid-page li').removeClass('active');
	$('.my-bid-page .return-bid-page').addClass('active');
	var str = 'func=return_query_report_manager';
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			$('.my-bid-block').html(html);
		}
	});
}

function show_bron(id){
	var str = 'func=show_bron&id=' + id;
	$.ajax({
		type: 'POST',
		data: str,
		url: 'mysql.php',
		success: function(){
			remove_all_windows();
			view_schet(id);
			var cook = Get_Cookie('writing');
			var img = Get_Cookie('img');
			if(cook == '1')
				window.open('document.php?func=review_bron&id=' + id + '&img=' + img, 'Лист бронирования PDF');
		}
	});
}

function show_bron_forma(id){
	var img = Get_Cookie('img');
	window.open('document.php?func=review_bron&id=' + id + '&img=' + img, 'Лист бронирования PDF');
}

function show_bill(id){
	var str = 'func=show_bill&id=' + id;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			show_modal(html);
		}
	});
}

function form_bill(id, type){
	var payer = $('.payers input:checked').val();
	var date = $('.date_checked input:checked').val();
	var str = 'func=save_bill&id=' + id + '&payer=' + payer;
	var pay_days = parseInt($('#pay_days').val());
	if($('#pay_on_place').prop('checked') == true)
		str+= '&status_san=' + $('#type_pay input:checked').val();
	$.ajax({
		type: 'POST',
		data: str,
		url: 'mysql.php',
		success: function(){
			if(type == "send_mail")
				show_send_mail(id, "schet");
			remove_all_windows();
			view_schet(id);
			var img = Get_Cookie('img');
			if(Get_Cookie('writing') == '1')
				window.open('document.php?func=review_schet&id=' + id + '&img=' + img + '&date=' + date+'&pay_days='+pay_days, 'Счет PDF');
		}
	});
}

function confirm_schet(id){
	$.ajax({
		type: 'POST',
		data: 'func=confirm_schet&id=' + id,
		url: 'pdf.php',
		success: function(){
			remove_all_windows();
			view_schet(id);
		}
	});
}

function show_obmen(id){
	$.ajax({
		type: 'POST',
		data: 'func=show_obmen&id=' + id,
		url: 'mysql.php',
		success: function(html){
			show_modal(html);
		}
	});
}

function show_obmen_blank(id){
	var dubl = $('#dubl').val();
	var show_pay = '', reduced = '';
	var turist_mode = 0;
	if($('#show_pay').prop("checked"))
		show_pay = 1;
	if($('#show-reduced').prop("checked"))
		reduced = 1;

	if($('#turist-mode').prop("checked"))
		turist_mode = 1;

	var check = select_checkbox('services');
	window.open('document.php?func=review_obmen&id=' + id + '&dubl=' + dubl + '&show_pay=' + show_pay + '&reduced=' + reduced + '&service=' + check+'&turist_mode='+turist_mode, 'Обменная путевка PDF');
	remove_all_windows();
}

function pay_schet(id){
	var str = 'func=pay_schet&id=' + id;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			show_modal(html);
		}
	});
}

function save_pay_schet(id){
	var type = $('#type_pay').val();
	var pay_sum = $('#pay_sum').val();
	var office = $('#office-pay').val();
	var date = $('#date-pay').val();
	if(!type)
		show_warning('.pay-schet', 'Выберите способ оплаты');
	else if(!pay_sum)
		show_warning('.pay-schet', 'Укажите сумму оплаты');
	else if(!office)
		show_warning('.pay-schet', 'Укажите офис');
	else if(!date)
		show_warning('.pay-schet', 'Укажите дату оплаты');
	else{
		var str = 'func=save_pay_schet&id=' + id + '&type=' + type + '&pay_sum=' + pay_sum + '&office=' + office + '&date=' + date;
		$.ajax({
			url: 'mysql.php',
			type: 'POST',
			data: str,
			success: function(answer){
				remove_all_windows();
				show_alert('Заявка оплачена...');
				view_schet(id);
				all_bonus_klient();
				if(answer == 'mail')
					agency_document(id, 'putevka');
				else if(answer == 'cabinet')
					show_send_mail(id, 'obmen');
			}
		});
	}
}

function agency_document(id, doc){
	var str = 'func=agency_document&id=' + id;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			show_modal(html);
		}
	});
}

function confirm_agency_document(id, doc){
	var str = 'func=confirm_agency_document&id=' + id + '&doc=' + doc;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(){
			remove_all_windows();
			show_send_mail(id, 'obmen');
		}
	});
}

function request_pay_schet(id){
	var str = 'func=request_pay_schet&id=' + id;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(){
			remove_all_windows();
			view_schet(id);
		}
	});
}

function pay_schet_san(id){
	var str = 'func=pay_schet_san&id=' + id;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			show_modal(html);
		}
	});
}

function save_pay_schet_san(id){
	var date = $('#date-opl').attr('date');
	var pay_number = $('.pay-number').val();
	var sum_san = $('.sum-san').val();
	if(!date)
		show_warning('.pay-schet-san', 'Введите дату оплаты', false);
	else if(!pay_number)
		show_warning('.pay-schet-san', 'Введите номер платежного поручения', false);
	else if(!sum_san)
		show_warning('.pay-schet-san', 'Введите сумму платежа', false);
	else{
		var str = 'func=save_pay_schet_san&id=' + id + '&date=' + date + '&pay_number=' + pay_number + '&sum_san=' + sum_san;
		$.ajax({
			url: 'mysql.php',
			type: 'POST',
			data: str,
			success: function(){
				remove_all_windows();
				show_alert('Оплата в санаторий...');
				view_schet(id);
			}
		});
	}
}

function prepay_schet_san(id){
	var str = 'func=prepay_schet_san&id=' + id;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			show_modal(html);
		}
	});
}

function save_prepay_schet_san(id){
	var date = $('#date-opl').attr('date');
	var pay_number = $('.pay-number').val();
	var sum_san = $('.sum-san').val();
	if(!date)
		show_warning('.prepay-schet-san', 'Введите дату оплаты');
	else if(!pay_number)
		show_warning('.prepay-schet-san', 'Введите номер платежного поручения');
	else if(!sum_san)
		show_warning('.prepay-schet-san', 'Введите сумму предоплаты');
	else{
		var str = 'func=save_prepay_schet_san&id=' + id + '&date=' + date + '&pay_number=' + pay_number + '&sum_san=' + sum_san;
		$.ajax({
			url: 'mysql.php',
			type: 'POST',
			data: str,
			success: function(){
				remove_all_windows();
				show_alert('Предоплата в санаторий...');
				view_schet(id);
			}
		});
	}
}

function permit_pay_schet_san(id){
	var str = 'func=permit_pay_schet_san&id=' + id;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(){
			remove_all_windows();
			show_alert('Разрешена оплата в санаторий...');
			view_schet(id);
		}
	});
}

function permit_prepay_schet_san(id){
	var str = 'func=permit_prepay_schet_san&id=' + id;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(){
			remove_all_windows();
			show_alert('Разрешена предоплата в санаторий...');
			view_schet(id);
		}
	});
}

function return_schet_san(id){
	var str = 'func=return_schet_san&id=' + id;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(){
			remove_all_windows();
			view_schet(id);
		}
	});
}

function remove_payment(id){
	if(confirm('Снять оплату?')){
		var str = 'func=remove_payment&id=' + id;
		$.ajax({
			url: 'mysql.php',
			type: 'POST',
			data: str,
			success: function(){
				all_bonus_klient();
				remove_all_windows();
				view_schet(id);
				show_alert('Оплата снята...');
			}
		});
	}
}

function return_schet(id, old){
	var str = 'func=return_schet&id=' + id + '&old=' + old;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(){
			remove_all_windows();
			view_schet(id);
		}
	});
}

function prepay_schet(id){
	var str = 'func=prepay_schet&id=' + id;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			show_modal(html);
		}
	});
}

function save_prepay(id){
	var sum = $('#prepay').val();
	var type = $('#type_pay').val();
	var office = $('#office-pay').val();
	var date = $('#date-pay').val();
	if(sum == '')
		show_warning('.prepay-schet', 'Введите сумму предоплаты');
	else if(!type)
		show_warning('.prepay-schet', 'Выберите способ предоплаты');
	else if(!office)
		show_warning('.prepay-schet', 'Укажите офис');
	else if(!date)
		show_warning('.prepay-schet', 'Укажите дату оплаты');
	else{
		var str = 'func=save_prepay&sum=' + sum + '&id=' + id + '&type=' + type + '&office=' + office + '&date=' + date;
		$.ajax({
			url: 'mysql.php',
			type: 'POST',
			data: str,
			success: function(a){
				if(a == '1'){
					remove_all_windows();
					show_alert('Заявка предоплачена...');
					view_schet(id);
				}else
					show_warning('.prepay-schet', 'У вас нет прав или предоплата больше всей суммы.');
			}
		});
	}
}

function show_cancellation(id){
	remove_all_windows();
	window.open('document.php?func=review_cancel&id=' + id, 'Аннуляция PDF');
}

function review_cancel(id, status){
	var cook = 1;
	if($('#writing_check').length)
		cook = parseInt(Get_Cookie('writing'));
	if(cook == 1){
		var str = 'func=show_form_review_cancel&id=' + id + '&status=' + status;
		$.ajax({
			type: 'POST',
			data: str,
			url: 'mysql.php',
			success: function(html){
				show_modal(html);
			}
		});
	}else{
		var str = 'func=save_cancelation_not_writing&id=' + id + '&status=' + status;
		$.ajax({
			type: 'POST',
			data: str,
			url: 'mysql.php',
			success: function(){
				remove_all_windows();
				view_schet(id);
				all_bonus_klient();
			}
		});
	}
}

function show_cancel(id){
	window.open('document.php?func=review_cancel&id=' + id, 'Аннуляция PDF');
}

function save_cancelation(id, status){
	var cause = $('.cancel-form .cause').val();
	var note = $('.cancel-form .note').val();
	var reason = $('.cancel-form #reason-delete').val();
	if(!reason)
		show_warning('.cancel-form', 'Укажите причину аннуляции', false);
	else{
		var str = 'func=save_cancelation&cause=' + cause + '&id=' + id + '&note=' + note + '&reason=' + reason + '&status=' + status;
		$.ajax({
			type: 'POST',
			data: str,
			url: 'mysql.php',
			success: function(answer){
				remove_all_windows();
				view_schet(id);
				all_bonus_klient();
				window.open('document.php?func=review_cancel&id=' + id, 'Аннуляция PDF');
				if(answer == 'cabinet')
					show_send_mail(id, 'cancel');
			}
		});
	}
}

document.onclick = function(){
	$(document).bind('click.myEvent', function(e){
		if($(e.target).closest('#div_buttons').length == 0) {
			$('#div_buttons').remove();
			$(document).unbind('click.myEvent');
		}
	});
}

function show_turist(turist, schet, type){
	Set_Cookie('reck', schet);
	select_klient(turist, type);
}

function show_menu_document(id){
	var str = 'func=show_menu_document&id=' + id;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			show_div_buttons('btn-menu-document', html);
		}
	});
}

function show_but_div(id){
	var str = 'func=show_menu_bid&id=' + id;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			show_div_buttons('other'+id, html)
			if($('#pechat_check').length){
				var cook = Get_Cookie('img');
				if(cook == '1')
					document.getElementById('pechat_check').checked = true;
				else
					document.getElementById('pechat_check').checked = false;
			}
			if($('#writing_check').length){
				var cook = Get_Cookie('writing');
				if(cook == '1')
					document.getElementById('writing_check').checked = true;
				else
					document.getElementById('writing_check').checked = false;
			}
		}
	});
}

function show_but_div_admin(id){
	var str = 'func=show_admin_button_reckoning&id=' + id;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			show_div_buttons('admin-'+id, html);
		}
	});
}

function show_contract(id, ver){
	var str = 'func=show_contract&id=' + id + '&ver=' + ver;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			show_modal(html);
		}
	});
}

function save_show_contract(id, ver){
	var prepay = '';
	var check = select_checkbox('services');
	var payer = $('.payers input:checked').val();
	var date = $('.dates input:checked').val();
	if($('#prepay_sum').val()){
		var prepay_sum = $('#prepay_sum').val();
		var date_to = $('#date_to').attr('date');
		prepay = '&prepay=' + prepay_sum + '&date_to=' + date_to;
	}
	var str = 'func=save_contract&id=' + id + '&payer=' + payer + prepay + '&check=' + check;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(){
			remove_all_windows();
			window.open('document.php?func=review_contract&id=' + id + prepay + '&dates=' + date + '&ver=' + ver, 'Договор PDF');
		}
	});
}

function check_pechat(){
	if(document.getElementById('pechat_check').checked){
		document.getElementById('pechat_check').checked = false;
		Set_Cookie('img', '0');
		show_alert('Печать без штампа...');
	}else{
		Set_Cookie('img', '1');
		document.getElementById('pechat_check').checked = true;
		show_alert('Печать со штампом...');
	}
}

function check_writing(){
	if(document.getElementById('writing_check').checked){
		document.getElementById('writing_check').checked = false;
		Set_Cookie('writing', '0');
		show_alert('Не показывать документ...');
	}else{
		Set_Cookie('writing', '1');
		document.getElementById('writing_check').checked = true;
		show_alert('Показывать документ...');
	}
}

function show_dover(id, turist){
	window.open('document.php?func=review_dover&id=' + id + '&turist=' + turist, 'Лист бронирования PDF');
}

function show_confirm(id){
	remove_all_windows();
	window.open('document.php?func=review_confirm&id=' + id, 'Подтверждение бронирования PDF');
}

function show_napravlenie(id){
	remove_all_windows();
	window.open('document.php?func=review_napravlenie&id=' + id, 'Направление PDF');
}

function return_oplata(id){
	var str = 'func=show_return_oplata&id=' + id;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			show_modal(html);
		}
	});
}

function save_return_oplata(id){
	var sum = $('.sum-return').val();
	var date = $('#date-return').attr('date');
	var type = $('.type-pay').val();
	var number = $('.number-return').val();
	if(!sum)
		show_warning('.return-oplata', 'Введите сумму возврата');
	else if(!date)
		show_warning('.return-oplata', 'Введите дату возврата');
	else if(!type)
		show_warning('.return-oplata', 'Укажите тип оплаты');
	else{
		var str = 'func=save_return_oplata&id=' + id + '&sum=' + sum + '&type=' + type + '&date=' + date + '&number=' + number;
		$.ajax({
			type: 'POST',
			data: str,
			url: 'mysql.php',
			success: function(answer){
				if(!answer)
					show_warning('.return-oplata', 'Ошибка');
				else{
					remove_all_windows();
					show_alert('Сохранено...');
					view_schet(id);
					all_bonus_klient();
				}
			}
		});
	}
}

function return_oplata_query(id){
	var str = 'func=show_return_oplata_query&id=' + id;
	$.ajax({
		type: 'POST',
		data: str,
		url: 'mysql.php',
		success: function(html){
			show_modal(html);
		}
	});
}

function save_return_oplata_query(id){
	var sum = $('.sum-return').val();
	var date = $('.date-return').attr('date');
	var type = $('.type-pay').val();
	if(!sum)
		show_warning('.return-oplata', 'Введите сумму возврата', false);
	else if(!type)
		show_warning('.return-oplata', 'Выберите способ оплаты', false);
	else{
		var str = 'func=save_return_oplata_query&id=' + id + '&sum=' + sum + '&date=' + date + '&type=' + type;
		$.ajax({
			type: 'POST',
			url: 'mysql.php',
			data: str,
			success: function(){
				remove_all_windows();
				show_alert('Заявка на возврат сохранена...');
				view_schet(id);
			}
		});
	}
}

function edit_return_query(id){
	var str = 'func=edit_return_query&id=' + id;
	$.ajax({
		type: 'POST',
		data: str,
		url: 'mysql.php',
		success: function(html){
			show_modal(html);
		}
	});
}

function update_return_query(id){
	var sum = $('#sum-return').val();
	var date = $('#date-return').attr('date');
	var type = $('#type-pay').val();
	if(!sum)
		show_warning('.edit-query', 'Введите сумму возврата');
	else{
		var str = 'func=update_oplata_query&id=' + id + '&sum=' + sum + '&date=' + date + '&type=' + type;
		$.ajax({
			type: 'POST',
			data: str,
			url: 'mysql.php',
			success: function(){
				remove_all_windows();
				show_alert('Заявка на возврат изменена...');
				return_query_report();
			}
		});
	}
}

function delete_return_query(id){
	if(confirm('Удалить запрос на возврат?')){
		var str = 'func=delete_oplata_query&id=' + id;
		$.ajax({
			type: 'POST',
			data: str,
			url: 'mysql.php',
			success: function(){
				remove_all_windows();
				show_alert('Заявка на возврат удалена...');
				return_query_report();
			}
		});
	}
}

function check_return_query(id){
	var str = 'func=check_return_query&id=' + id;
	$.ajax({
		type: 'POST',
		data: str,
		url: 'mysql.php',
		success: function(){
			return_query_report();
		}
	});
}

function show_report_agency(id){
	window.open('document.php?func=report_agent&id=' + id, 'Отчет агента');
	remove_all_windows();
}

function sent_report_agent(id){
	var str = 'func=sent_report_agent&id=' + id;
	$.ajax({
		type: 'POST',
		data: str,
		url: 'mysql.php',
		success: function(){
			remove_all_windows();
			view_schet(id);
			show_alert('Отчет агента выслан...');
		}
	});
}

function received_report_agent(id){
	var str = 'func=received_report_agent&id=' + id;
	$.ajax({
		type: 'POST',
		data: str,
		url: 'mysql.php',
		success: function(){
			remove_all_windows();
			view_schet(id);
			show_alert('Отчет агента получен...');
		}
	});
}

function return_cancel(id){
	var str = 'func=return_cancel&id=' + id;
	$.ajax({
		type: 'POST',
		data: str,
		url: 'mysql.php',
		success: function(){
			remove_all_windows();
			view_schet(id);
			show_alert('Заявка возвращена в работу...');
		}
	});
}

function reckoning_put_aside(id){
	var str = 'func=reckoning_put_aside&id=' + id;
	$.ajax({
		type: 'POST',
		data: str,
		url: 'mysql.php',
		success: function(){
			remove_all_windows();
			view_schet(id);
			show_alert('Заявка отложена...');
		}
	});
}

function reckoning_from_aside(id){
	var str = 'func=reckoning_from_aside&id=' + id;
	$.ajax({
		type: 'POST',
		data: str,
		url: 'mysql.php',
		success: function(){
			remove_all_windows();
			view_schet(id);
			show_alert('Заявка возвращена в работу...');
		}
	});
}

function delete_changes_reckoning(id){
	var str = 'func=delete_changes_reckoning&id=' + id;
	$.ajax({
		type: 'POST',
		data: str,
		url: 'mysql.php',
		success: function(){
			remove_all_windows();
			view_schet(id);
			show_alert('Изменения приняты...');
		}
	});
}

function show_set_reward(id){
	var str = 'func=show_set_reward&id=' + id;
	$.ajax({
		type: 'POST',
		data: str,
		url: 'mysql.php',
		success: function(html){
			show_modal(html);
		}
	});
}

function set_reward(id){
	var reward = $('#reward').val();
	var str = 'func=set_reward&id=' + id + '&reward=' + reward;
	$.ajax({
		type: 'POST',
		data: str,
		url: 'mysql.php',
		success: function(){
			remove_all_windows();
			view_schet(id);
		}
	});
}

function set_time_payment(id){
	var str = 'func=set_time_payment&id=' + id;
	$.ajax({
		type: 'POST',
		data: str,
		url: 'mysql.php',
		success: function(html){
			show_modal(html);
		}
	});
}

function save_time_payment(id){
	var pay_date = $('#pay-date').attr('date');
	var prepay_date = $('#prepay-date').attr('date');
	var sum = $('#prepay-sum').val();
	var str = 'func=save_time_payment&id=' + id + '&pay_date=' + pay_date + '&prepay_date=' + prepay_date + '&sum=' + sum;
	$.ajax({
		type: 'POST',
		data: str,
		url: 'mysql.php',
		success: function(){
			remove_all_windows();
			view_schet(id);
			show_alert('Готово...');
		}
	});
}




function delete_bonus_form_reckoning(id){
	var str = 'func=delete_bonus_form_reckoning&id=' + id;
	$.ajax({
		type: 'POST',
		data: str,
		url: 'mysql.php',
		success: function(){
			remove_all_windows();
			view_schet(id);
		}
	});
}

function show_form_outweigh_reckoning(id){
	var str = 'func=show_form_outweigh_reckoning&id=' + id;
	$.ajax({
		type: 'POST',
		data: str,
		url: 'mysql.php',
		success: function(html){
			show_modal(html);
		}
	});
}

function outweigh_reckoning_to_agency(id){
	var agency = $('.agency-id').val();
	if(!agency)
		show_warning('.outweight-reckoning', 'Введите id агентства');
	else{
		var str = 'func=outweigh_reckoning_to_agency&id=' + id + '&agency=' + agency;
		$.ajax({
			type: 'POST',
			data: str,
			url: 'mysql.php',
			success: function(answer){
				remove_all_windows();
				if(answer == 1)
					select_agency(agency);
			}
		});
	}
}

function show_form_correction_reckoning(id){
	var str = 'func=show_form_correction_reckoning&id=' + id;
	$.ajax({
		type: 'POST',
		data: str,
		url: 'mysql.php',
		success: function(html){
			show_modal(html);
		}
	});
}

function set_correction_reckoning(id){
	var correction = $('.correction-reckoning').val();
	if(!agency)
		show_warning('.correction', 'Укажите поправку', false);
	else{
		var str = 'func=set_correction_reckoning&id=' + id + '&correction=' + correction;
		$.ajax({
			type: 'POST',
			data: str,
			url: 'mysql.php',
			success: function(){
				remove_all_windows();
				view_schet(id);
			}
		});
	}
}

function show_form_commission_reckoning(id){
	var str = 'func=show_form_commission_reckoning&id=' + id;
	$.ajax({
		type: 'POST',
		data: str,
		url: 'mysql.php',
		success: function(html){
			show_modal(html);
		}
	});
}

function set_commission_reckoning(id){
	var commission = $('.commission-reckoning').val();
	var str = 'func=set_commission_reckoning&id=' + id + '&commission=' + commission;
	$.ajax({
		type: 'POST',
		data: str,
		url: 'mysql.php',
		success: function(){
			remove_all_windows();
			view_schet(id);
		}
	});
}


function show_call_back_menu(){
	select_menu('call-back-menu', 2);
	var html = '<ul class="nav nav-tabs call-back-menu nav-justified"><li class="new-call" onclick="show_call_back(\'new\')"><a>Новые</a></li><li class="work-call" onclick="show_call_back(\'work\')"><a>В работе</a></li><li class="process-call" onclick="show_call_back(\'process\')"><a>Обработанные</a></li><li class="archive-call" onclick="show_call_back(\'archive\')"><a>Удаленные</a></li></ul><div class="view-order-call-back" style="margin-top: 20px"></div>';
	$('#body').html(html);
	$('.call-back-menu li:first').trigger('click');
}

function show_call_back(type){
	$('.call-back-menu li').removeClass('active');
	$('.call-back-menu .'+type+'-call').addClass('active');
	var str = 'func=show_call_back&type=' + type;
	$.ajax({
		type: 'POST',
		data: str,
		url: 'mysql.php',
		success: function(html){
			$('.view-order-call-back').html(html);
		}
	});
}

function edit_note_order_call_back(id){
	var str = 'func=edit_note_order_call_back&id=' + id;
	$.ajax({
		type: 'POST',
		data: str,
		url: 'mysql.php',
		success: function(html){
			show_modal(html);
		}
	});
}

function update_note_order_call_back(id){
	var note = $('.edit-call-back .note-call-back').val();
	var str = 'func=update_note_order_call_back&id=' + id + '&note=' + note;
	$.ajax({
		type: 'POST',
		data: str,
		url: 'mysql.php',
		success: function(){
			remove_all_windows();
			$('.order-call-back-'+id+' .note-call-back-block').removeClass('hidden').find('.note-text').html(note);
		}
	});
}

function change_status_order_call_back(id, status){
	var str = 'func=change_status_order_call_back&id=' + id + '&status=' + status;
	$.ajax({
		type: 'POST',
		data: str,
		url: 'mysql.php',
		success: function(){
			$('.order-call-back-'+id).hide('slow');
		}
	});
}

function show_form_create_order_call_back(id){
	var str = 'func=show_form_create_order_call_back&id=' + id;
	$.ajax({
		type: 'POST',
		data: str,
		url: 'mysql.php',
		success: function(html){
			show_modal(html);
			var id_obj = $('.id-object').attr('name');
			if(id_obj){
				select_room(id_obj);
			}
		}
	});
}

function create_order_call_back(id){
	var id_obj = $('.id-object').attr('name');
	var room = $('.create-order-call-back .select_room').val();
	var date = $('.create-order-call-back #date-z').attr('date');
	var days = $('.create-order-call-back .days').val();
	var surname = $('.create-order-call-back .surname').val();
	var name = $('.create-order-call-back .name').val();
	var otch = $('.create-order-call-back .otch').val();
	var price = $('.create-order-call-back .price').val();
	var type = $('.create-order-call-back .type').val();
	var number = $('.create-order-call-back .number').val();
	if(!id_obj)
		show_warning('.create-order-call-back', 'Выберите объект', false);
	else if(!surname)
		show_warning('.create-order-call-back', 'Укажите фамилию туриста', false);
	else if(!name)
		show_warning('.create-order-call-back', 'Укажите имя туриста', false);
	else if(!date)
		show_warning('.create-order-call-back', 'Укажите дату заезда', false);
	else if(!days)
		show_warning('.create-order-call-back', 'Укажите количество дней', false);
	else if(!price)
		show_warning('.create-order-call-back', 'Укажите цену', false);
	else if(!number)
		show_warning('.create-order-call-back', 'Укажите количество', false);
	else{
		$('.btn-update').button('loading');
		var str = 'func=create_order_call_back&id=' + id + '&date=' + date + '&days=' + days + '&surname=' + surname + '&name=' + name + '&otch=' + otch + '&price=' + price + '&type=' + type + '&number=' + number + '&room=' + room;
		$.ajax({
			url: 'mysql.php',
			type: 'POST',
			data: str,
			success: function(client){
				remove_all_windows();
				select_klient(client);
			}
		});
	}
}
function last_manager_assign_call_back(){
	var str = 'func=last_manager_assign_call_back';
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			show_div_buttons('last-manager', html);
		}
	});
}
