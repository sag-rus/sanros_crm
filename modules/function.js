var BOSH_SERVICE = 'http://bosh.metajack.im:5280/xmpp-httpbind';
var connection = null;

var old_page, scroll_val;
var i = 1;
var izm = 1;
var kol = 1;
var max_bonus;

$(function(){
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: 'func=check_login',
		success: function(answer){
			if(!answer)
				write_authorization_form();
			else
				write_body();
		}
	});
});

var month = {
		1 : 'Январь',
		2 : 'Февраль',
		3 : 'Март',
		4 : 'Апрель',
		5 : 'Май',
		6 : 'Июнь',
		7 : 'Июль',
		8 : 'Август',
		9 : 'Сентябрь',
		10 : 'Октябрь',
		11 : 'Ноябрь',
		12 : 'Декабрь',
	}

var dataCRM = {
	'manager' : new Array(),
	'status-bid' : new Array(),
	'status-bid-object' : new Array(),
	'reward' : new Array(),
	'discount' : new Array(),
	'commission' : new Array(),
	'consultation' : new Array(),
	'source' : new Array()
	}

function login(){
	var login = $('#login').val();
	var password = $('#password').val();
	if(login == '')
		show_warning('.form-authorization', 'Не введен логин');
	else if(password == '')
		show_warning('.form-authorization', 'Не введен пароль');
	else{
		var str = 'func=check_login&login=' + login + '&password=' + password;
		$.ajax({
			url: 'mysql.php',
			type: 'POST',
			data: str,
			success: function(answer){
				if(answer == 0)
					show_warning('.form-authorization', 'Неправильный логин или пароль');
				else if(answer == 2)
					show_warning('.form-authorization', 'Доступ запрещен');
				else{
					$('.popup-substrate').remove();
					write_body();
				}
			}
		});
	}
}

function check_pass(id, _function) {
	var login = $('#login').val();
	var password = $('#password').val();

	if(login == '')
		login_exit();
		else if(password == '')
			show_warning('.form-check-pass', 'Не введен пароль');
		else{
			var str = 'func=' + _function + '&login=' + login + '&password=' + password + '&id=' + id;
			$.ajax({
				url: 'mysql.php',
				type: 'POST',
				data: str,
				success: function(answer){
					if(answer.indexOf('alert-danger') > 0) {
						$('.form-check-pass').find('.alert-danger').remove();
						$('.form-check-pass').append(answer);
					} else {
						show_modal(answer);
					}
				}
			});
		}
}


function write_authorization_form(){
	var html = '<div class="popup-substrate"><div class="form-horizontal form-authorization"><div class="form-group"><div class="col-sm-12"><div class="input-group"><span class="input-group-addon"><i class="fa fa-user"></i></span><input type="text" class="form-control" id="login" placeholder="Логин" onKeyPress="if(event.keyCode == 13) login()"></div></div></div><div class="form-group"><div class="col-sm-12"><div class="input-group"><span class="input-group-addon"><i class="fa fa-key"></i></span><input type="password" class="form-control" id="password" placeholder="Пароль" onKeyPress="if(event.keyCode == 13) login()"></div></div></div><div class="form-group"><div class="col-sm-12"><button type="button" style="width: 100%;" class="btn btn-success btn-sm" onClick="login()"><i class="fa fa-sign-in"></i> Вход</button></div></div></div></div>';
	$('body').append(html);
}

function login_exit(){
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: 'func=logout',
		success: function(){
			location.reload();
		}
	});
}



function show_popup(html, type, width){
	remove_all_windows();
	if(html){
		if(type == 'big')
			html = "<div id='popup' class='popup-substrate'><div class='down big_down' id='down'><i class='fa fa-times fa-2x icon_close' onclick='remove_all_windows()'></i><div id='popup_body'>" +html+ "</div></div></div>";
		else
			html = "<div id='popup' class='popup-substrate'><div class='down' id='down'><i class='fa fa-times fa-2x icon_close' onclick='remove_all_windows()'></i><div id='popup_body'>" +html+ "</div></div></div>";
		$('body').append(html);
		if(width)
			$('.down').css('width', width);
	}
}

function show_modal(html){
	remove_all_windows();
	$('body').append(html);
	$('.modal').modal();
	if($('.modal .datepicker').length)
		show_datepicker();
	if($('.modal .hide-button-modal').length){
		$('.modal .hide-button-modal').click(function(){
			var name = $(this).attr('name');
			var type = $(this).attr('type');
			$('.modal').hide();
			$('.modal-backdrop').hide();
			$('.main-header .navbar-nav .messages-menu-' +type).remove();
			var html = '<li class="messages-menu modal-menu messages-menu-' +type+ '"><a><i class="fa fa-calendar-check-o"></i> ' +name+ '</a></li>';
			$('.main-header .navbar-nav').prepend(html);
			$('body').removeClass('modal-open');
			$('.main-header .navbar-nav .messages-menu-' +type).click(function(){
				$('.modal').show();
				$('.modal-backdrop').show();
				$('body').addClass('modal-open');
			});
		});
	}
}

function show_window_popup(html){
	remove_all_windows();
	$('body').append(html);
}

function show_alert_popup(message){
	remove_all_windows();
	var html = "<div id='popup' class='popup-substrate'><div class='down'><i class='fa fa-times fa-2x icon_close' onclick='remove_all_windows()'></i><div id='popup_body'><div class='alert alert-warning'>" +message+ "</div></div></div></div>";
	$('body').append(html);
}

function show_loader(div){
	if(!div)
		div = 'body';
	show_loader_element('#'+div);
}

function show_loader_element(element){
//	var html = "<div class='loader'>Загрузка...<br /><br /><i class='fa fa-refresh fa-4x fa-spin'></i></div>";
	var html = "<div class='dots-loader'><i class='fa fa-refresh fa-4x fa-spin'></i></div>";
	$(element).html(html);
}

function clear_mistake(element){
	$(element).removeClass('has-error').removeClass('has-feedback');
	$(element).find('span').remove();
}

function show_mistake(element){
	$(element).addClass('has-error').addClass('has-feedback');
	$(element).find('span').remove();
	$(element).append('<span class="form-control-feedback" aria-hidden="true"><i class="fa fa-times"></i></span>');
}

function select_menu(li, p_menu){
	save_old_html_for_back();
	$('#body').html('');
	$('.menu-sidebar li').removeClass('active-menu');
	$('#'+li).addClass('active-menu');
	remove_all_windows();
}

function save_old_html_for_back(){
	old_page = $('#body').html();
	scroll_val = getBodyScrollTop();
}

function write_body(){
	var str = 'func=write_body';
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		dataType: 'JSON',
		success: function(data){
			$('body').html(data['html']);
			head_page();
			check_menu_count();
			setInterval("check_menu_count()", 240000);
			$('.main-header .btn-setting').popover({
				placement: 'bottom',
				content: show_chat_setting
			});
			$('.main-header .btn-notification').popover({
				placement: 'bottom',
				content: show_notification_user
			});
			/*$('.main-header .btn-consultation').popover({
				placement: 'bottom',
				content: show_consultation_chats
			});*/

			dataCRM['manager'] = data['manager'];
			dataCRM['reward'] = data['reward'];
			dataCRM['status-bid'] = data['status-bid'];
			dataCRM['source'] = data['source'];
		}
	});
	Set_Cookie('writing', '1');
}

function check_menu_count(){
	var str = 'func=check_menu_count&cache='+Math.random();
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		dataType: 'JSON',
		success: function(data){
			if(data['new-bid']['reckoning'] > 0){
				if(!$('#reckoning-menu .badge').length)
					$('#reckoning-menu a').append('<span class="badge count-red pull-right"></span>');
				$('#reckoning-menu .badge').html(data['new-bid']['reckoning']);
			}else
				$('#reckoning-menu .badge').remove();
			if(data['new-bid']['call'] > 0){
				if(!$('#call-back-menu .badge').length)
					$('#call-back-menu a').append('<span class="badge count-red pull-right"></span>');
				$('#call-back-menu .badge').html(data['new-bid']['call']);
			}else
				$('#call-back-menu .badge').remove();


			if(data['no-price'] > 0){
				if(!$('#obj_menu .badge').length){
					$('#obj_menu a').append("<span class='badge count-red pull-right'>" +data['no-price']+ "</span>");
				}else
					$('#obj_menu .badge').html(data['no-price']);
			}else
				$('#obj_menu .badge').remove();


			if(data['reminder'] > 0){
				if(!$('#reminder_menu .badge').length){
					$('#reminder_menu a').append("<span class='badge count-red pull-right'>" +data['reminder']+ "</span>");
				}else
					$('#reminder_menu .badge').html(data['reminder']);
			}else if($('#reminder_menu .badge').length)
				$('#reminder_menu .badge').remove();

			if(data['check-news'] > 0){
				if(!$('#news-menu .badge').length){
					$('#news-menu a').append("<span class='badge count-red pull-right'>" +data['check-news']+ "</span>");
				}else
					$('#news-menu .badge').html(data['check-menu']);
			}else
				$('#news-menu .badge').remove();

			if(data['question']['all'] > 0){
				if(!$('.question-menu .badge').length)
					$('.question-menu a').append("<span class='badge count-red pull-right'>" +data['question']['all']+ "</span>");
				else
					$('.question-menu .badge').html(data['question']['all']);
				if(data['question']['turist'] > 0)
					$('.question-turist .badge').html(data['question']['turist']);
				else
					$('.question-turist .badge').remove();
				if(data['question']['agency'] > 0)
					$('.question-agency .badge').html(data['question']['agency']);
				else
					$('.question-agency .badge').remove();
				if(data['question']['object'] > 0)
					$('.question-object .badge').html(data['question']['object']);
				else
					$('.question-object .badge').remove();
			}else
				$('.question-menu .badge').remove();

			check_menu_count_rating(data['rating']);
			check_menu_count_object(data['object']);
			check_update_system(data['check-update']);
			check_button_new_message(data['check-message']);
			check_new_notification(data['check-notification'])
		}
	});
}

function check_menu_count_rating(data){
	if(data && data['all'] > 0){
		if(!$('#all-admin-menu .badge').length)
			$('#all-admin-menu a:first').append("<span class='badge count-red pull-right'>" +data['all']+ "</span>");
		else
			$('#all-admin-menu .badge').html(data['all']);
		if(data['rating'] > 0 && !$('.show-rating a .badge').length)
			$('.show-rating a').append("<span class='badge count-red pull-right'>" +data['rating']+ "</span>");
		else if(data['rating'] > 0)
			$('.show-rating .badge').html(data['rating']);
		else
			$('.show-rating .badge').remove();
		if(data['comment'] > 0 && !$('.show-comment-rating a .badge').length)
			$('.show-comment-rating a').append("<span class='badge count-red pull-right'>" +data['comment']+ "</span>");
		else if(data['comment'] > 0)
			$('.show-comment-rating .badge').html(data['comment']);
		else
			$('.show-comment-rating .badge').remove();
	}else if($('#all-admin-menu .badge').length){
		$('#all-admin-menu .badge').remove();
		$('.show-rating .badge').remove();
		$('.show-comment-rating .badge').remove();
	}
}

function check_menu_count_object(data){
	if(data && data['all'] > 0){
		if(!$('.menu-object-cabinet .badge').length)
			$('.menu-object-cabinet a:first').append("<span class='badge count-red pull-right'>" +data['all']+ "</span>");
		else
			$('.menu-object-cabinet .badge').html(data['all']);

		if(data['check'] > 0 && !$('#check-object-menu a .badge').length)
			$('#check-object-menu a').append("<span class='badge count-red pull-right'>" +data['check']+ "</span>");
		else if(data['check'] > 0)
			$('#check-object-menu .badge').html(data['check']);
		else
			$('#check-object-menu .badge').remove();

		if(data['new'] > 0 && !$('#new-request-object a .badge').length)
			$('#new-request-object a').append("<span class='badge count-red pull-right'>" +data['new']+ "</span>");
		else if(data['new'] > 0)
			$('#new-request-object .badge').html(data['new']);
		else
			$('#new-request-object .badge').remove();
	}else
		$('.menu-object-cabinet .badge').remove();
	$('#account-object .badge').remove();
}

function check_new_notification(data){
	if(data['new'] > 0)
		$('.btn-notification').addClass('btn-danger');
	else
		$('.btn-notification').removeClass('btn-danger');
}

function check_update_system(data){
	if(data['update'] == 1 && !jQuery('.warning-update').length){
		jQuery('#body').prepend(data['html']);
	}
}

function validate_input(){
	if((event.keyCode < 48) || (event.keyCode > 57))
		event.returnValue = '';
}

function validate_sum(id){
	if(!id)
		id = 'sum';
	var sum = document.getElementById(id).value;
	var code = event.keyCode;
	var pos = sum.indexOf(".");
	if((code > 57) || (code < 48)){
		if((code > 43) && (code < 47)){
			if((pos == "-1") && (sum != "")){
				event.returnValue = ".";
			}else
				event.returnValue = "";
		}else
			event.returnValue = "";
	}
	sum = document.getElementById(id).value;
	if(sum.indexOf(",") > -1)
		document.getElementById(id).value = document.getElementById(id).value.replace(",", ".");
	if(sum.indexOf("-") > -1)
		document.getElementById(id).value = document.getElementById(id).value.replace("-", ".");
	if(sum.indexOf("..") > -1)
		document.getElementById(id).value = document.getElementById(id).value.replace("..", ".");
}

function check_email(email){
	if(email == '')
		return true;
	var pos1 = parseInt(email.indexOf("@"));
	var pos2 = parseInt(email.lastIndexOf("."));
	if((pos1 < 1) || (pos2 < (pos1 + 2)) || ((pos2 + 1 )>= email.length))
		return false;
	return true;
}

function save_all(){
	var t = 1, s, d, ss, type_dis = '1';

	var telephone = $('#telephone').val();
	var name = $('#name').val();
	var surname = $('#surname').val();
	var otch = $('#otch').val();
	var email = $('#email').val();
	var passport = $('#passport').val();
	var output = $('#output').val();
	var date_pas = $('#date-pass').attr('date');
	var date = $('#date').attr('date');
	var address = $('#address').val();
	var id_obj = $('.id-object').attr('name');
	var id_tour = $('.id-tour-operator').val();
	if(!id_tour)
		id_tour = '';
	var id_room = $('.select_room').val();
	var sum = $('#sum').val();
	var note = $('#note').val();
	var type = $('#type').val();
	var days = $('#days').val();
	var date_z = $('#arrival').attr('date');
	var number = $('#number').val();
	var number_turist = $('#number_turist').val();
	var discount = $('#id_dis').val();

	var commis = $('#commis').val();
	var add_one_day = $('#add_one_day input:checked').val();
	if((!surname))
		show_warning('.new-reckoning', 'Не правильно введена фамилия туриста');
	else if ((!name) || (name.length < 2))
		show_warning('.new-reckoning', 'Не введено имя туриста');
	else if(!check_email(email))
		show_warning('.new-reckoning', 'Не верно введен Email');
	else if(((telephone.length < 10)) && (telephone != ''))
		show_warning('.new-reckoning', 'Не верно введен телефон');
	else if(isNaN(days) || (days == ''))
		show_warning('.new-reckoning', 'Не верно введены количество дней');
	else if(date_z == '')
		show_warning('.new-reckoning', 'Не введена дата заезда');
	else if(id_obj == '')
		show_warning('.new-reckoning', 'Не выбран объект');
	else if(isNaN(number) || (number == ''))
		show_warning('.new-reckoning', 'Не верно введено количество');
	else if(!sum)
		show_warning('.new-reckoning', 'Не указана цена');
	else if(!check_true_dates(date_z, 'new'))
		show_warning('.new-reckoning', 'Дата заезда введена неправильно');
	else{
		var str = 'func=save_all&surmane=' + surname + '&name=' + name + '&otch=' + otch + '&telephone=' + telephone + '&email=' + email + '&passport=' + passport + '&date=' + date + '&id_obj=' + id_obj + '&id_tour=' + id_tour + '&id_room=' + id_room + '&sum=' + sum + '&note=' + note + '&days=' + days + '&date_z=' + date_z + '&address=' + address + '&output=' + output + '&date_pas=' + date_pas + '&number=' + number + '&number_turist=' + number_turist + '&type=' + type + '&discount=' + discount + '&commis=' + commis + '&add_one_day=' + add_one_day;
		$.ajax({
			url: 'mysql.php',
			type: 'POST',
			data: str,
			success: function(answer){
				if(answer == 0){
					show_warning('.new-reckoning', 'Неправильно указана цена');
				}else{
					select_klient(answer);
					show_alert('Данные сохранены...');
				}
			}
		});
	}
}

function passport_space(){
	if((event.keyCode < 48) || (event.keyCode > 57))  event.returnValue = '';
	else{
		var pass = document.getElementById('passport').value;
		var dlina = pass.length;

		if(dlina == 4){
			pass = pass + " ";
		}
		document.getElementById('passport').value = pass;
	}
}

function text_focus(id, text, action){
	if(action == 'focus'){
		if(document.getElementById(id).value == text)
			document.getElementById(id).value = '';
	}else if(action == 'blur'){
		if(document.getElementById(id).value == '')
			document.getElementById(id).value = text;
	}
}

function find_klient(event, id, table, func, reck){
	if(!func)
		func = '';
	if(!reck)
		reck = '';
	var poisk = $('#'+id).val();
	if(poisk == '')
		$('#find').remove();
	var str = 'func=help_search_by_name&poisk=' + poisk + '&table=' + table + '&function=' + func + '&reck=' + reck;
	if(event.keyCode == 40){
		if($('.help-window .current-position').length)
			$('.help-window .current-position').removeClass('current-position').next().addClass('current-position');
		else if($('.help-window').length)
			$('.help-window span:first').addClass('current-position');
	}else if(event.keyCode == 38){
		if($('.help-window .current-position').length)
			$('.help-window .current-position').removeClass('current-position').prev().addClass('current-position');
		else if($('.help-window').length)
			$('.help-window span:last').addClass('current-position');
	}else if(event.keyCode == 13){
		$('.help-window .current-position:first').trigger('click');
		$('#find').remove();
	}else if(event.keyCode == 27)
		$('#find').remove();
	else{
		if((poisk.length > 2 || ((func == 'use_object' || func == 'view_object') && poisk.length > 1))){
			$.ajax({
				url: 'mysql.php',
				type: 'POST',
				data: str,
				success: function(html){
					if(html){
						if(document.getElementById(id)){
							if(!$('#find').length){
								$('body').append("<div id='find' class='help-window'></div>");
								var width = $('#'+id).css('width');
								var elm = document.getElementById(id);
								var coords = getOffset(elm);
								var left = coords.left;
								var top = coords.top + 28;
								$('#find').css('top', top);
								$('#find').css('left', left);
								$('#find').css('width', width);
							}
							$('#find').html(html);
						}
					}else
						$('#find').remove();
				}
			});
		}else
			$('#find').remove();
	}
}

function find_klient_blur(){
	$('#find').remove();
}

function select_red(i){
	document.getElementById('tel_text'+i).disabled = false;
	$('#tel_text'+i).focus();
}

function select_blur(i){
	document.getElementById('tel_text'+i).disabled = true;
}

function use_object(id, div){
	$('#find').remove();
	if(id == 'new'){
		show_popup_new_object();
	}else{
		var str = 'func=select_name_object&id=' + id;
		$.ajax({
			url: 'mysql.php',
			type: 'POST',
			data: str,
			success: function(html){
				$('#object_name').html("<div class='well well-sm id-object' name='" +id+ "'><span>" + html + "</span>&nbsp;&nbsp;<i class='fa fa-pencil fa-lg pointer icon_edit' onclick='change_select_object()'></i></div>");
				var attr = $('#object_name').attr('name');
				if(attr == 'new-reck'){
					select_room(id);
					select_reward(id);
					select_add_one_day(id);
					select_tour_operator_object(id);
				}else if(attr == 'edit-reck')
					select_tour_operator_object(id);
			}
		});
	}
}

function select_tour_operator_object(id){
	var str = 'func=select_tour_operator_object&id=' + id;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			if(html){
				$('.tour-operator-html').show();
				$('.tour-operator-html .html').html(html);
			}else{
				$('.tour-operator-html').hide();
				$('.tour-operator-html .html').html('');
			}
		}
	});
}

function select_reward(id){
	if($('#commis').length){
		var date = $('#arrival').attr('date');
		var str = 'func=select_reward_object&id=' + id + '&date=' + date;
		$.ajax({
			url: 'mysql.php',
			type: 'POST',
			data: str,
			success: function(reward){
				$('#commis').val(reward);
				$('#commis').removeAttr('disabled');
				if(reward != 0)
					$('#commis').attr('disabled', 'disabled');
			}
		});
	}
}

function select_add_one_day(id){
	if($('#add_one_day').length){
		var str = 'func=select_add_one_day_object&id=' + id;
		$.ajax({
			url: 'mysql.php',
			type: 'POST',
			data: str,
			success: function(html){
				$('#add_one_day').html(html);
				view_date_out();
			}
		});
	}
}

function show_popup_new_object(){
	var str = 'func=show_modal_new_object';
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			show_modal(html);
		}
	});
}

function save_new_object(){
	var name = $('#new_object').val().replace(new RegExp('"', 'g'), '');
	if(name == '')
		show_warning('.modal .new-object', 'Введите название объекта', false);
	else{
		var str = 'func=save_new_object&name=' + name;
		$.ajax({
			url: 'mysql.php',
			type: 'POST',
			data: str,
			success: function(id){
				show_modal_new_room(id);
			}
		});
	}
}

function show_modal_new_room(id){
	var str = 'func=show_modal_new_room&id=' + id;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			show_modal(html);
		}
	});
}

function save_room_to_new_object(id){
	var name = $('.new-room-object').val();
	if(!name)
		show_warning('.modal .new-room', 'Введите название номера', false);
	else{
		var str = 'func=save_new_room_object&object=' + id + '&room=' + name;
		$.ajax({
			url: 'mysql.php',
			type: 'POST',
			data: str,
			success: function(){
				remove_all_windows();
				use_object(id);
			}
		});
	}
}

function change_select_object(){
	$('#object_name').html("<input type='text' onkeyup='find_klient(event, \"object\", \"object\", \"use_object\")' id='object' class='form-control id-object' placeholder='Объект' name='' />");
	$('#klient_room').html("");
}

function select_website(div){
	remove_all_windows();
	var edit = "&nbsp;&nbsp;<i class='fa fa-pencil fa-lg pointer icon_edit' onclick='change_website()'></i>";
	$('#url_website').html("<div class='well well-sm'><span id='url'>" + div + "</span>" + edit + "</div>");
}

function change_website(){
	$('#url_website').html("<input type='text' class='form-control' onkeyup='find_klient(event, \"website\", \"st_website\")' id='website' placeholder='Сайт' />");
}

function select_room(id_obj){
	var str = 'func=select_object_rooms&id_obj=' + id_obj;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			$('#klient_room').html(html);
		}
	});
}

function find_similar_turist(id, pole, required){
	var text = $('#'+id).val();
	var str = 'func=find_similar_turist&pole=' + pole + '&text=' + text;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(answer){
			var html = "";
			if(answer == "1")
				html = "<i class='fa fa-exclamation-triangle fa-2x pointer icon_warning' onclick='show_similar_turist(\"" +id+ "\", \"" +pole+ "\")'></i>";
			else if($('#'+id).val())
				html = "<i class='fa fa-check-circle fa-2x icon_download'></i>";
			else if(required == '1')
				html = "<i class='fa fa-times-circle fa-2x icon_delete'></i>";
			$('.mark-'+id).html(html);
		}
	});
}

function verification_input_data(id, required){
	var text = $('#'+id).val();
	var html = "";
	if(text)
		html = "<i class='fa fa-check-circle fa-2x icon_download'></i>";
	else if(!text && required == '1')
		html = "<i class='fa fa-times-circle fa-2x icon_delete'></i>";
	$('.mark-'+id).html(html);
}

function reckoning_type_checker() {
	var type = parseInt(jQuery('#reck_type').val()),
			$arrival = jQuery('#arrival'),
			$days = jQuery('#days'),
			$object = jQuery('#object'),
			$number_turist = jQuery('#number_turist'),
			$commis = jQuery('#commis'),
			$id_dis = jQuery('#id_dis'),
			$view_date_v = jQuery('#view_date_v'),
			$mark_days = jQuery('.mark-days'),
			$mark_object = jQuery('.mark-object'),
			$klient_room = jQuery('#klient_room'),
			$sum = jQuery('#sum'),
			$type_room = jQuery('#type'),
			$mark_sum = jQuery('.mark-sum'),
			$number = jQuery('#number'),
			$mark_number = jQuery('.mark-number');

	if(type === 0) {
		$arrival.prop('disabled',false);
    $view_date_v.prop('disabled',false);
    $days.prop('disabled',false);
    $object.prop('disabled',false);
    $number_turist.prop('disabled',false);
    $commis.prop('disabled',false);
    $id_dis.prop('disabled',false);
    $klient_room.prop('disabled',false);
    $sum.prop('disabled',false);
    $type_room.prop('disabled',false);
    $number.prop('disabled',false);
	}
	else {
    $arrival.prop('disabled',true);
    $arrival.val("");
    $view_date_v.prop('disabled',false);
    $view_date_v.val("");
    $mark_days.html("");
    $mark_object.html("");
    $days.prop('disabled',true);
    $object.prop('disabled',true);
    $number_turist.prop('disabled',true);
    $commis.prop('disabled',true);
    $id_dis.prop('disabled',true);
    $klient_room.prop('disabled',true);
    $sum.prop('disabled',true);
    $mark_sum.html("");
    $type_room.prop('disabled',true);
    $number.prop('disabled',true);
    $number.val("");
    $mark_number.html("");
	}
}

function show_similar_turist(id, pole){
	var text = $('#'+id).val();
	var str = 'func=show_similar_turist&pole=' + pole + '&text=' + text;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			show_modal(html);
		}
	});
}

function add_klient(){
	select_menu('new_klient_menu');
	var str = 'func=add_new_client';
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			$('#body').html(html);
			show_datepicker();
		}
	});
}

function change_label_number(){
	var type = $('#type').val();
	if(type == '1')
		$('#label_number').html('Кол-во<br /><strong>отдыхающих</strong>');
	else if(type == '2')
		$('#label_number').html('Кол-во<br /><strong>номеров (домов)</strong>');
	else if(type == '3')
		$('#label_number').html('Кол-во<br /><strong>заездов</strong>');
}

function remove_all_windows(){
	if(document.getElementById('cancelation_forma'))
		$('#cancelation_forma').remove();
	if(document.getElementById('new_pos_forma'))
		$('#new_pos_forma').remove();
	if(document.getElementById('popup'))
		$('#popup').remove();
	if(document.getElementById('div_buttons'))
		$('#div_buttons').remove();
	if(document.getElementById('find'))
		$('#find').remove();
	if(document.getElementById('div_alert'))
		$('#div_alert').remove();
	$('.modal .modal-header i').trigger('click');
	$('.modal-menu').remove();
}

function show_alert(message){
	if($('#div_alert').length)
		$('#div_alert').remove();
	var html = "<div id='div_alert'>" +message+ "</div>";
	$('body').append(html);
	setTimeout("$('#div_alert').remove();", 3000);
}

function show_prev_page(){
	if(old_page != ''){
		$('#body').html(old_page);
		window.scroll(0, scroll_val);
		$('.datepicker').removeClass('hasDatepicker');
		show_datepicker();
	}
}

function check_true_dates(date, type){
	var current = new Date();
	var current_year = current.getFullYear();
	var current_month = current.getMonth() + 1;
	var arr = date.split("-");
	var year = parseInt(arr[0]);
	var month = parseInt(arr[1]);
	if((year < current_year || (year == current_year && month < current_month)) && type == 'new')
		return false;
	if((year == 2017) || (year == 2018))
		return true;
	return false;
}

function check_date_interval(date, days){
	var now = Date.now() / 1000;
	var timestamp = Date.parse(date) / 1000;
	if((now + days * 86400) >= timestamp)
		return true;
	return false;
}

function select_checkbox(class_name, div){
	if(div)
		div = "#"+div+" ";
	else
		div = "";
	var answer = "";
	$(div+'input:checkbox:checked.'+class_name).each(function(){
		answer+= this.value + "_";
	});
	return answer;
}

function select_checkbox_json(element){
	var answer = new Array;
	$(element).find('input:checkbox:checked').each(function(){
		answer.push(this.value);
	});
	var data = JSON.stringify(answer);
	return data;
}

function select_checkbox_element(element){
	var answer = "";
	$(element+' input:checkbox:checked').each(function(){
		answer+= this.value + "_";
	});
	return answer;
}

function show_div_buttons(div, html){
	var elm = document.getElementById(div);
	var coords = getOffset(elm);
	var left = coords.left;
	var top = coords.top + 20;
	if(document.getElementById('div_buttons'))
		$('#div_buttons').remove();
	$('body').append("<div id='div_buttons'></div>");
	if(html)
		$('#div_buttons').html(html);
	$('#div_buttons').css('top', top);
	$('#div_buttons').css('left', left);
}

function show_floating_div(element, html){
	if($('#div_buttons').length)
		$('#div_buttons').remove();
	if(element.length && html){
		var offset = element.offset();
		$('body').append("<div id='div_buttons'></div>");
		$('#div_buttons').html(html);
		$('#div_buttons').css('top', offset.top + 15);
		$('#div_buttons').css('left', offset.left);
		$(document).bind('click.myEvent', function(e){
			if($(e.target).closest('#div_buttons').length == 0) {
				$('#div_buttons').remove();
				$(document).unbind('click.myEvent');
			}
		});
	}
}

function getBodyScrollTop(){
	return self.pageYOffset || (document.documentElement && document.documentElement.scrollTop) || (document.body && document.body.scrollTop);
}

function getOffset(elem){
	if (elem.getBoundingClientRect){
		return getOffsetRect(elem)
	}else{
		return getOffsetSum(elem)
	}
}

function getOffsetSum(elem){
	var top=0, left=0
 	while(elem){
		top = top + parseInt(elem.offsetTop)
		left = left + parseInt(elem.offsetLeft)
		elem = elem.offsetParent
	}
	return {top: top, left: left}
}

function getOffsetRect(elem) {
	var box = elem.getBoundingClientRect()
	var body = document.body
	var docElem = document.documentElement
	var scrollTop = window.pageYOffset || docElem.scrollTop || body.scrollTop
	var scrollLeft = window.pageXOffset || docElem.scrollLeft || body.scrollLeft
	var clientTop = docElem.clientTop || body.clientTop || 0
	var clientLeft = docElem.clientLeft || body.clientLeft || 0
	var top = box.top + scrollTop - clientTop
	var left = box.left + scrollLeft - clientLeft
	return { top: Math.round(top), left: Math.round(left) }
}

function Get_Cookie(check_name){
	var a_all_cookies = document.cookie.split(';');
	var a_temp_cookie = '';
	var cookie_name = '';
	var cookie_value = '';
	var b_cookie_found = false;
	for(i = 0; i < a_all_cookies.length; i++){
		a_temp_cookie = a_all_cookies[i].split('=');
		cookie_name = a_temp_cookie[0].replace(/^\s+|\s+$/g, '');
		if(cookie_name == check_name){
			b_cookie_found = true;
			if (a_temp_cookie.length > 1)
				cookie_value = unescape(a_temp_cookie[1].replace(/^\s+|\s+$/g, ''));
			return cookie_value;
			break;
		}
		a_temp_cookie = null;
		cookie_name = '';
	}
	if(!b_cookie_found)
		return null;
}

function Set_Cookie(name, value){
	document.cookie = name + "=" + escape(value);
}

function scroll_to_element(id){
	if($('#'+id).length){
		var dist = $('#'+id).offset().top;
		jQuery('body,html').animate({ scrollTop: dist }, 1000);
	}
}

function show_warning(div, message, scroll){
	if(!$(div+' .warning-alert').length)
		$(div).append("<div class='alert alert-danger warning-alert'></div>");
	$(div+' .warning-alert').html("<i class='fa fa-exclamation-triangle'></i>&nbsp;" + message);
	if(scroll != false){
		var dist = $(div+' .warning-alert').offset().top;
		// $('body,html').animate({ scrollTop: dist }, 1000);
	}
}

function clear_mistake(element){
	$(element).removeClass('has-error').removeClass('has-feedback');
}

function show_mistake(element){
	$(element).addClass('has-error').addClass('has-feedback').focus();
}

function get_rooms_object(id, div){
	var str = 'func=get_rooms_object&id=' + id;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			$(div).html(html);
		}
	});
}

function use_tour_operator(id){
	$('#find').remove();
	var str = 'func=get_name_tour_operator&id=' + id;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			$('#tour_operator_span').html("<div class='well well-sm id-tour-operator' name='" +id+ "'>" + html + "&nbsp;&nbsp;<i class='fa fa-pencil fa-lg pointer icon_edit' onclick='change_tour_operator()'></i></div>");
		}
	});
}

function check_size_limit(element, size, label){
	var text = $(element).val();
	var length = text.length;
	var raz = size - length;
	$(label).html(raz);
}

function change_tour_operator(){
	$('#tour_operator_span').html("<input type='text' class='form-control id-tour-operator' id='tour_operator' onkeyup='find_klient(event, \"tour_operator\", \"tour_operator\", \"use_tour_operator\")' name='' placeholder='Туроператор'>");
}

function cut_null_date(date){
	var new_date;
	var array = date.split("-");
	var new_date = array[0] + "-" + parseInt(array[1]) + "-" + parseInt(array[2]);
	return new_date;
}

function get_current_time(){
	var date = new Date();
	var time = date.getHours() + ":" + date.getMinutes();
	return time;
}

function show_datepicker(){
	$('.datepicker').attr('date', '');
	$('.datepicker').each(function(){
		var id = $(this).attr('id');
		var value = $(this).val();
		if(!$('.convers-date[label="'+id+'"]').length){
			$(this).after("<div class='well well-sm convers-date' label='" +id+ "'></div>");
			if(value && value != '0000-00-00'){
				var label = date_transform(value);
				var arr = value.split("-");
				var input = arr[2] + '.' + arr[1] + '.' + arr[0];
				$(this).hide().val(input).attr('date', value);
				$(this).parent().find('.convers-date').show().html(label);
				if(id == 'arrival')
					view_date_out();
			}else
				$(this).val('');
		}
	});
	$('.convers-date').click(function(){
		var input = $(this).attr('label');
		$('#'+input).show();
		$('#'+input).focus();
		$(this).hide();
	});
	$('.datepicker').change(function(){
		var id = $(this).attr('id');
		var date = $(this).val();
		if(!date){
			var label = "Неправильная дата";
			$(this).attr('date', '');
		}else{
			var arr = date.split(".");
			var attr = arr[2] + '-' + arr[1] + '-' + arr[0];
			var label = date_transform(attr);
			$(this).attr('date', attr);
		}
		$(this).hide();
		$(this).parent().find('.convers-date').show().html(label);
		if(id == 'arrival')
			view_date_out();
	});
	$('.datepicker').blur(function(){
		if($(this).val()){
			$(this).hide();
			$(this).parent().find('.convers-date').show();
		}
	});
	$('.datepicker').datepicker({dateFormat: 'dd.mm.yy'}).inputmask('d.m.y', {'placeholder': 'дд.мм.гггг'});
}

function date_transform(date){
	var arr = date.split("-");
	var month = new Array('янв', 'фер', 'март', 'апр', 'май', 'июнь', 'июль', 'авг', 'сен', 'окт', 'ноя', 'дек')
	date = arr[2] + " " + month[parseInt(arr[1]-1)] + " " + arr[0];
	return date;
}

function date_sum(date, days){
	var new_date = new Date();
	date = new Date(date);
	new_date.setTime(date.getTime() + days * 24 * 60 * 60 * 1000);
	var answer = new_date.getFullYear() + '-' + (new_date.getMonth() + 1) + '-' + new_date.getDate()
	return answer;
}

function view_date_out(){
	$('#view_date_v').show();
	var date_z = $('#arrival').attr('date');
	var days = $('#days').val();
	if(days && date_z){
		var add_one_day = $('#add_one_day input:checked').val();
		if(add_one_day == '0')
			days--;
		date_v = date_sum(date_z, days);
		date_v = date_transform(date_v);
		$('#view_date_v').html(date_v);
	}else
		$('#view_date_v').html('Не определена');
}

function show_tooltip(container){
	if(!container){
		container = 'body';
	}
	jQuery('[data-toggle="tooltip"]').tooltip({
		html: true,
		container: container
	});
}

function escapeHtml(text) {
  var map = {
    '&': '&amp;',
    '<': '&lt;',
    '>': '&gt;',
    '"': '&quot;',
    "'": '&#039;'
  };

  return text.replace(/[&<>"']/g, function(m) { return map[m]; });
}
