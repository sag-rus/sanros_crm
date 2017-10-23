function agency(){
	select_menu('agency_menu');
	var str = 'func=show_head_page_agency';
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			$('#body').html(html);
		}
	});
	show_loader('body');
}

function add_turist_agency(){
	var str = 'func=add_new_agency';
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			$('#body').html(html);
		}
	});
}

function save_all_agency(){
	var name = $('#name').val();
	var short_name = $('#short_name').val();
	var telephone = $('#telephone').val();
	var email = $('#email').val();
	var fax = $('#fax').val();
	var icq = $('#icq').val();
	var skype = $('#skype').val();
	var note_a = $('#note_a').val();
	var address = $('#address').val();
	var legal_address = $('#legal_address').val();
	var website = $('#website').val();
	if(!check_email(email))
		show_warning('.new-agency', 'Не введено введен Email');
	else if(name == '')
		show_warning('.new-agency', 'Не введено полное название');
	else if(short_name == '')
		show_warning('.new-agency', 'Не введено сокращенное название');
	else if(telephone == '')
		show_warning('.new-agency', 'Не введен телефон');
	else if(address == '')
		show_warning('.new-agency', 'Не введен адрес');
	else{
		name = name.replace('+', 'plus');
		var str = 'func=save_new_agency&name=' + name + '&short_name=' + short_name + '&telephone=' + telephone + '&email=' + email + '&fax=' + fax + '&icq=' + icq + '&skype=' + skype + '&note_a=' + note_a + '&address=' + address + '&website=' + website + '&legal_address=' + legal_address;
		$.ajax({
			url: 'mysql.php',
			type: 'POST',
			data: str,
			success: function(a){
				select_agency(a);
				show_alert('Агентство сохранено...');
			}
		});
	}
}

function select_agency(id, cache){
	if(cache != 'no-cache')
		select_menu('schet_menu');
	var str = 'func=select_agency&id=' + id;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			$('#body').html(html);
			var reck = Get_Cookie('reck');
			if(reck){
				view_schet(reck);
			}else
				klient_schet(id, 'agency');
		}
	});
}

function edit_agency(id){
	remove_all_windows();
	var str = 'func=edit_agency&id=' + id;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			$('#body').html(html);
		}
	});
}

function update_agency(id){
	var name = $('#name').val();
	var type_com = $('#type_com').val();
	var short_name = $('#short_name').val();
	var telephone = $('#telephone').val();
	var email = $('#email').val();
	var fax = $('#fax').val();
	var icq = $('#icq').val();
	var skype = $('#skype').val();
	var note_a = $('#note_a').val();
	var address = $('#address').val();
	var legal_address = $('#legal_address').val();
	var website = $('#website').val();
	var inn = $('#inn').val();
	var kpp = $('#kpp').val();
	var bik = $('#bik').val();
	var ks = $('#ks').val();
	var rs = $('#rs').val();
	var bank = $('#bank').val();
	var present = $('#present').val();
	var post = $('#post').val();
	var doc = $('#doc').val();
	if(!check_email(email))
		show_warning('.edit', 'Не введено поле Email');
	else if(name == '')
		show_warning('.edit', 'Не введено поле полн.название');
	else if(short_name == '')
		show_warning('.edit', 'Не введено поле сокр.название');
	else if(telephone == '')
		show_warning('.edit', 'Не введено поле Телефон');
	else if(address == '')
		show_warning('.edit', 'Не введено поле Адрес');
	else{
		name = name.replace("+", "plus");
		var str = 'func=update_agency&name=' + name + '&short_name=' + short_name + '&telephone=' + telephone + '&email=' + email + '&fax=' + fax + '&icq=' + icq + '&skype=' + skype + '&note=' + note_a + '&address=' + address + '&website=' + website + '&id=' + id + '&legal_address=' + legal_address + '&inn=' + inn + '&kpp=' + kpp + '&bik=' + bik + '&ks=' + ks + '&rs=' + rs + '&bank=' + bank + '&present=' + present + '&post=' + post + '&doc=' + doc + '&type_com=' + type_com;
		$.ajax({
			url: 'mysql.php',
			type: 'POST',
			data: str,
			success: function(){
				select_agency(id, 'no-cache');
				show_alert('Агентство изменено...');
			}
		});
	}
}

function find_agency(event,stroka){
	var number = $('.number-contract-agency').val();
	var $statusFilter = $('.agency-status-filter li');
	var login_status = 0;
	var page = 0;

	var event = event || window.event;
	if(event) {
		var $obj = $(event.currentTarget);
		if(typeof $obj.attr('data-login-status') !== 'undefined') {
			$statusFilter.removeClass('active');
			$obj.addClass('active');
			login_status = $obj.attr('data-login-status');
		}
		else {
			login_status = $('.agency-status-filter li.active').attr('data-login-status');
		}

		if(typeof $obj.attr('data-page-number') != 'undefined') {
			page = parseInt($obj.attr('data-page-number'));
		}
	}


	if(!stroka)
		stroka = $('#name_agency').val();
	
	var str = 'func=find_agency&stroka=' + stroka + '&number=' + number+'&login_status='+login_status+'&page='+page;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			$('#div_result').html(html);
		}
	});
}

function add_new_contract(id){
	var str = 'func=add_new_contract_agency&id=' + id;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			show_modal(html);
		}
	});
}

function save_new_contract(id){
	var date = $('.date-contract').attr('date');
	var number = $('.number-contract').val();
	if(!date)
		show_warning('.new-contract', 'Не указана дата', false);
	else if(!number)
		show_warning('.new-contract', 'Не указан номер договора', false);
	else{
		var str = 'func=save_new_contract_agency&id=' + id + '&date=' + date + '&number=' + number;
		$.ajax({
			url: 'mysql.php',
			type: 'POST',
			data: str,
			success: function(){
				remove_all_windows();
				select_agency(id);
				show_alert('Готово...');
			}
		});
	}
}

function edit_agency_contract(id){
	var str = 'func=edit_agency_contract&id=' + id;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			show_modal(html);
		}
	});
}

function update_agency_contract(id){
	var date = $('#date-contract').attr('date');
	var number = $('.number-contract').val();
	if(!date)
		show_warning('.edit-contract', 'Не указана дата');
	else if(!number)
		show_warning('.edit-contract', 'Не указан номер договора');
	else{
		var str = 'func=update_agency_contract&id=' + id + '&date=' + date + '&number=' + number;
		$.ajax({
			url: 'mysql.php',
			type: 'POST',
			data: str,
			success: function(id){
				remove_all_windows();
				select_agency(id);
				show_alert('Готово...');
			}
		});
	}
}

function agency_to_trash(id){
	var str = 'func=agency_to_trash&id=' + id;
	if(confirm('Отправить агентство в архив?'))
		$.ajax({
			url: 'mysql.php',
			type: 'POST',
			data: str,
			success: function(){
				select_agency(id);
				show_alert('Готово...');
			}
		});
}

function scan_received(agency, id){
	var str = 'func=scan_received&id=' + id;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(){
			select_agency(agency);
			show_alert('Готово...');
		}
	});
}

function original_received(agency, id){
	var str = 'func=original_received&id=' + id;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(){
			select_agency(agency);
			show_alert('Готово...');
		}
	});
}

function show_but_agency(id){
	var str = 'func=show_menu_agency&id=' + id;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			show_div_buttons('agency_active', html);
		}
	});
}

function throw_off_agency_contract(id){
	if(confirm('Агентский договор не получен?')){
		var str = 'func=throw_off_agency_contract&id=' + id;
		$.ajax({
			url: 'mysql.php',
			type: 'POST',
			data: str,
			success: function(id){
				select_agency(id);
				show_alert('Готово...');
			}
		});
	}
}

function show_agency_dogovor(id){
	window.open('pdf.php?func=agency_dogovor&id=' + id, 'Агентский договор');
}

function show_questionary_agency(id){
	save_old_html_for_back();
	var str = 'func=show_questionary_agency&id=' + id;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			$('#body').html(html);
		}
	});
}

function update_questionary_agency(id){
	var data = new Object();
	data['check-cabinet'] = $('.check-cabinet:checked').val();
	data['check-place'] = $('.check-place:checked').val();
	data['check-find'] = $('.check-find:checked').val();
	data['cabinet-note'] = $('.cabinet-note').val();
	data['place-note'] = $('.place-note').val();
	data['find-note'] = $('.find-note').val();
	var array = JSON.stringify(data);
	var str = 'func=update_questionary_agency&id=' + id + '&data=' + array;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(){
			agency();
		}
	});
}

function view_all_questionary(){
	var str = 'func=view_all_questionary';
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			$('#body').html(html);
		}
	});
}

function show_history_agency(id){
	var str = 'func=show_history_agency&id=' + id;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			$('#info_turist').html(html);
		}
	});
}

function edit_agency_sync_info(id){
	var str = 'func=edit_agency_sync_info&id=' + id;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			show_modal(html);
		}
	});
}

function update_agency_sync_info(id){
	var name = $('.edit-agency .name-agency').val();
	var code = $('.edit-agency .inn-agency').val();
	var inn = $('.edit-agency .code-1C').val();
	if(isNaN(code) || isNaN(inn) || !code || !inn)
		show_warning('.edit-agency', 'Укажите правильно поля кода и ИНН', false);
	else{
		var str = 'func=update_agency_sync_info&id=' + id + '&name=' + name + '&code=' + code + '&inn=' + inn;
		$.ajax({
			url: 'mysql.php',
			type: 'POST',
			data: str,
			success: function(){
				select_agency(id);
			}
		});
	}
}
