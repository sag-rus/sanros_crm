function touroperator(){
	select_menu('touroperator_menu');
	var str = 'func=show_head_page_touroperator';
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

function show_but_tour(id){
	var str = 'func=show_menu_touroperator&id=' + id;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			show_div_buttons('tour_active', html);
		}
	});
}

function add_tour_operator(){
	var html = '<div class="form-horizontal panel panel-default new-touroperator"><div class="panel-heading"><i class="fa fa-plus-circle"></i> Новый туроператор</div><div class="panel-body"><div class="form-group"><label class="col-sm-3 control-label">Сокращенное название</label><div class="col-sm-9"><input type="text" id="short_name" class="form-control" /></div></div><div class="form-group"><label class="col-sm-3 control-label">Полное название</label><div class="col-sm-9"><input type="text" id="name" class="form-control" /></div></div><div class="form-group"><label class="col-sm-3 control-label">Email</label><div class="col-sm-9"><input type="text" id="email" class="form-control" /></div></div><div class="form-group"><label class="col-sm-3 control-label">Телефон</label><div class="col-sm-9"><input type="text" id="telephone" class="form-control" /></div></div><div class="form-group"><label class="col-sm-3 control-label">Адрес</label><div class="col-sm-9"><input type="text" id="address" class="form-control" /></div></div><div class="form-group"><label class="col-sm-3 control-label">Юридический адрес</label><div class="col-sm-9"><input type="text" id="legal_address" class="form-control" /></div></div><div class="form-group"><label class="col-sm-3 control-label">Факс</label><div class="col-sm-9"><input type="text" id="fax" class="form-control" /></div></div><div class="form-group"><label class="col-sm-3 control-label">ICQ</label><div class="col-sm-9"><input type="text" id="icq" class="form-control" /></div></div><div class="form-group"><label class="col-sm-3 control-label">Skype</label><div class="col-sm-9"><input type="text" id="skype" class="form-control" /></div></div><div class="form-group form-group-margin"><label class="col-sm-3 control-label">Сайт</label><div class="col-sm-9"><input type="text" id="website" class="form-control" /></div></div></div><div class="panel-footer text-right"><button class="btn btn-success btn-sm" onclick="save_tour_operator()"><i class="fa fa-check-circle"></i> Сохранить</button></div></div>';
	$('#body').html(html);
}

function save_tour_operator(){
	var name = $('#name').val();
	var short_name = $('#short_name').val();
	var telephone = $('#telephone').val();
	var email = $('#email').val();
	var fax = $('#fax').val();
	var icq = $('#icq').val();
	var skype = $('#skype').val();
	var address = $('#address').val();
	var legal_address = $('#legal_address').val();
	var website = $('#website').val();
	clear_mistake('.new-touroperator');
	if(!short_name)
		show_mistake('#short_name');
	else if(!name)
		show_mistake('#name');
	else if(!telephone)
		show_mistake('#telephone');
	else if(!address)
		show_mistake('#address');
	else if(!check_email(email))
		show_mistake('#email');
	else{
		var str = 'func=save_new_tour_operator&name=' + name + '&short_name=' + short_name + '&telephone=' + telephone + '&email=' + email + '&fax=' + fax + '&icq=' + icq + '&skype=' + skype + '&address=' + address + '&website=' + website + '&legal_address=' + legal_address;
		$.ajax({
			url: 'mysql.php',
			type: 'POST',
			data: str,
			dataType: 'JSON',
			success: function(id){
				select_tour_operator(id);
				show_alert('Данные успешно сохранены...');
			}
		});
	}
}

function select_tour_operator(id){
	remove_all_windows();
	var str = 'func=select_tour_operator&id=' + id;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			$('#body').html(html);
			select_object_of_tour_operator(id);
		}
	});
}

function update_tour_operator(id){
	var name = $('#name').val();
	var short_name = $('#short_name').val();
	var telephone = $('#telephone').val();
	var email = $('#email').val();
	var fax = $('#fax').val();
	var icq = $('#icq').val();
	var skype = $('#skype').val();
	var address = $('#address').val();
	var legal_address = $('#legal_address').val();
	var website = $('#website').val();
	var inn = $('#inn').val();
	var kpp = $('#kpp').val();
	var bik = $('#bik').val();
	var ks = $('#ks').val();
	var rs = $('#rs').val();
	var bank = $('#bank').val();
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
		var str = 'func=update_tour_operator&name=' + name + '&short_name=' + short_name + '&telephone=' + telephone + '&email=' + email + '&fax=' + fax + '&icq=' + icq + '&skype=' + skype + '&address=' + address + '&website=' + website + '&id=' + id + '&legal_address=' + legal_address + '&inn=' + inn + '&kpp=' + kpp + '&bik=' + bik + '&ks=' + ks + '&rs=' + rs + '&bank=' + bank;
		$.ajax({
			url: 'mysql.php',
			type: 'POST',
			data: str,
			success: function(){
				select_tour_operator(id);
				show_alert('Данные успешно сохранены...');
			}
		});
	}
}

function edit_tour_operator(id){
	remove_all_windows();
	var str = 'func=edit_tour_operator&id=' + id;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			$('#body').html(html);
		}
	});
}

function find_tour_operator(stroka){
	var str = 'func=find_tour_operator&stroka=' + stroka;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			$('#div_result').html(html);
		}
	});
}

function add_object_to_tour_operator(id){
	var str = 'func=add_object_to_tour_operator&id=' + id;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			show_modal(html);
		}
	});
}

function save_object_to_tour_operator(id){
	var object = $('.id-object').attr('name');
	if(!object)
		show_warning('.add-object', 'Выберите объект', false);
	else{
		var str = 'func=save_object_to_tour_operator&id=' + id + '&object=' + object;
		$.ajax({
			url: 'mysql.php',
			type: 'POST',
			data: str,
			success: function(){
				remove_all_windows();
				select_object_of_tour_operator(id);
				show_alert('Объект добавлен...');
			}
		});
	}
}

function select_object_of_tour_operator(id){
	var str = 'func=select_objects_of_tour_operator&id=' + id;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			$('#info_turist').html(html);
		}
	});
}

function set_default_tour_operator(id_obj, id_tour){
	var str = 'func=set_default_tour_operator&id_obj=' + id_obj + '&id_tour=' + id_tour;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(){
			select_object_of_tour_operator(id_tour);
		}
	});
}

function delete_object_tour_operator(id_obj, id_tour){
	var str = 'func=delete_object_tour_operator&id_obj=' + id_obj + '&id_tour=' + id_tour;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(){
			select_object_of_tour_operator(id_tour);
		}
	});
}

function add_tour_operator_to_reck(id_obj, id_tour){
	var str = 'func=add_tour_operator_to_reck&id_obj=' + id_obj + '&id_tour=' + id_tour;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			select_object_of_tour_operator(id_tour);
		}
	});
}

function edit_tour_operator_sync_info(id){
	var str = 'func=edit_tour_operator_sync_info&id=' + id;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			show_modal(html);
		}
	});
}

function update_tour_operator_sync_info(id){
	var full_name = $('.edit-code .full-name-tour-operator').val();
	var code = $('.edit-code .code-tour-operator').val();
	if(isNaN(code) || !code)
		show_warning('.edit-code', 'Укажите правильно поля кода', false);
	else{
		var str = 'func=update_tour_operator_sync_info&id=' + id + '&code=' + code + '&full_name=' + full_name;
		$.ajax({
			url: 'mysql.php',
			type: 'POST',
			data: str,
			success: function(){
				remove_all_windows();
			}
		});
	}
}

function add_new_contract_tour_operator(id){
	var html = '<div class="modal fade"><div class="modal-dialog"><div class="modal-content"><div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-label="Close"><i class="fa fa-times"></i></button><h4 class="modal-title">Добавить новый договор</h4></div><div class="modal-body form-horizontal new-contract-touroperator"><div class="form-group"><label class="col-sm-4 control-label">Действует до</label><div class="col-sm-8"><input type="text" class="form-control datepicker" id="date-contract" /></div></div><div class="form-group form-group-margin"><label class="col-sm-4 control-label">Номер договора</label><div class="col-sm-8"><input type="text" class="form-control number-contract" /></div></div></div><div class="modal-footer"><button class="btn btn-success btn-sm" onclick="save_new_contract_tour_operator(' +id+ ')"><i class="fa fa-check-circle"></i> Сохранить</button></div></div></div></div>';
	show_modal(html);
	show_datepicker();
}

function save_new_contract_tour_operator(id){
	var date = $('.new-contract-touroperator #date-contract').attr('date');
	var number = $('.new-contract-touroperator .number-contract').val();
	if(!date)
		show_warning('.new-contract-touroperator', 'Укажите дату', false);
	else{
		remove_all_windows();
		var str = 'func=save_new_contract_tour_operator&id=' + id + '&date=' + date + '&number=' + number;
		$.ajax({
			url: 'mysql.php',
			type: 'POST',
			data: str,
			dataType: 'JSON',
			success: function(data){
				var html = '<div class="contract-touroperator-' +data['id']+ '">' +data['html']+ '</div>';
				$('.contracts-touroperator').append(html);
			}
		});
	}
}

function edit_contract_tour_operator(id){
	var str = 'func=edit_contract_tour_operator&id=' + id;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		dataType: 'JSON',
		success: function(data){
			var select = new Array();
			select[1] = 'SELECTED';
			select[2] = '';
			if(data['type'] == 'sanata'){
				select[1] = '';
				select[2] = 'SELECTED';
			}
			var html = '<div class="modal fade"><div class="modal-dialog"><div class="modal-content"><div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-label="Close"><i class="fa fa-times"></i></button><h4 class="modal-title">Изменить договор</h4></div><div class="modal-body form-horizontal edit-contract"><div class="form-group"><label class="col-sm-4 control-label">Действует до</label><div class="col-sm-8"><input type="text" class="form-control datepicker" id="date-contract" value="' +data['date']+ '" date="' +data['date']+ '" /></div></div><div class="form-group form-group-margin"><label class="col-sm-4 control-label">Номер договора</label><div class="col-sm-8"><input type="text" class="form-control number-contract" value="' +data['number']+ '" /></div></div></div><div class="modal-footer"><button class="btn btn-success btn-sm" onclick="update_contract_tour_operator(' +id+ ')"><i class="fa fa-check-circle"></i> Сохранить</button></div></div></div></div>';
			show_modal(html);
		}
	});
}

function update_contract_tour_operator(id){
	var date = $('.edit-contract #date-contract').attr('date');
	var number = $('.edit-contract .number-contract').val();
	if(!date)
		show_warning('.edit-contract', 'Укажите дату', false);
	else{
		remove_all_windows();
		var str = 'func=update_contract_tour_operator&id=' + id + '&date=' + date + '&number=' + number;
		$.ajax({
			url: 'mysql.php',
			type: 'POST',
			data: str,
			dataType: 'JSON',
			success: function(html){
				$('.contract-touroperator-'+id).html(html);
			}
		});
	}
}

function update_status_contract_tour_operator(id, status){
	var str = 'func=update_status_contract_tour_operator&id=' + id + '&status=' + status;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		dataType: 'JSON',
		success: function(html){
			$('.contract-touroperator-'+id).html(html);
		}
	});
}
