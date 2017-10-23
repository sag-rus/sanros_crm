function show_change_password(){
	select_menu('my-password');
	var str = 'func=show_change_password';
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			$('#body').html(html);
		}
	});
}

function update_my_password(){
	var old_pass = $('.change-password .old-password').val();
	var new_pass = $('.change-password .new-password').val();
	var repeat_pass = $('.change-password .repeat-new-password').val();
	if(!old_pass)
		show_warning('.change-password', 'Введите старый пароль');
	else if(!new_pass)
		show_warning('.change-password', 'Введите новый пароль');
	else if(new_pass != repeat_pass)
		show_warning('.change-password', 'Неверно введен повтор пароля');
	else{
		var str = 'func=update_my_password&old=' + old_pass + '&new=' + new_pass + '&repeat=' + repeat_pass;
		$.ajax({
			url: 'mysql.php',
			type: 'POST',
			data: str,
			success: function(answer){
				if(answer == 0)
					show_warning('.change-password', 'Неверно введен старый пароля');
				else
					show_warning('.change-password', 'Новый пароль сохранен');
			}
		});
	}
}

function show_my_chat_log(){
	select_menu('my-chat-log');
	var str = 'func=show_my_chat_log';
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

function filter_my_chat_log(){
	var date = $('#date-chat').attr('date');
	if(!date)
		show_warning('.form-chat-log', 'Укажите дату');
	else{
		show_loader_element('.result-chat-log');
		var str = 'func=filter_chat_log&date=' + date;
		$.ajax({
			url: 'mysql.php',
			type: 'POST',
			data: str,
			success: function(html){
				$('.result-chat-log').html(html);
			}
		});
	}
}

function show_chat_users(){
	select_menu('chat-manager');
	var str = 'func=show_chat_users';
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			$('#body').html(html);
			show_chat_log();
		}
	});
}

function show_chat_log(){
	$('.head-menu-chat button').removeClass('btn-success');
	$('.head-menu-chat .btn-chat-log').addClass('btn-success');
	var str = 'func=show_chat_log';
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			$('.chat-body').html(html);
			show_datepicker();
			filter_chat_log_users();
		}
	});
}

function filter_chat_log_users(){
	var date = $('#date-chat').attr('date');
	var manager = $('.manager-chat').val();
	if(!date)
		show_warning('.form-chat-log', 'Укажите дату');
	else{
		show_loader_element('.result-chat-log');
		var str = 'func=filter_chat_log_users&date=' + date + '&manager=' + manager;
		$.ajax({
			url: 'mysql.php',
			type: 'POST',
			data: str,
			success: function(html){
				$('.result-chat-log').html(html);
			}
		});
	}
}

function show_sitehelp_operator(){
	$('.head-menu-chat button').removeClass('btn-success');
	$('.head-menu-chat .btn-chat-operator').addClass('btn-success');
	var str = 'func=show_sitehelp_operator';
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			$('.chat-body').html(html);
		}
	});
}

function show_sitehelp_template(){
	$('.head-menu-chat button').removeClass('btn-success');
	$('.head-menu-chat .btn-chat-template').addClass('btn-success');
	var str = 'func=show_sitehelp_template';
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			$('.chat-body').html(html);
		}
	});
}

function append_sitehelp_template(){
	$('.sitehelp-tepmlate:last').clone().appendTo('.sitehelp-template-block').find('input').val('').removeAttr('number');
}

function save_sitehelp_template(){
	var template = new Array();
	$('.sitehelp-tepmlate').each(function(){
		var array = new Object();
		var number = $(this).attr('number');
		if(number)
			array['number'] = number;
		array['name'] = $(this).find('.template-name').val();
		array['text'] = $(this).find('.template-text').val();
		if(array['name'] || array['text'])
			template.push(array);
	});
	var data = JSON.stringify(template);
	var str = 'func=save_sitehelp_template&data=' + data;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(){
			show_sitehelp_template();
		}
	});
}
