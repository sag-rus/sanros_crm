function open_my_chat(){
	if(!$('#chat-message').length){
		var str = 'func=open_my_chat';
		$.ajax({
			url: 'mysql.php',
			type: 'POST',
			data: str,
			success: function(html){
				$('body').append(html);
				$('#chat-message').draggable({
					containment: 'window',
					handle: '.heading-chat'
				});
				$('#chat-message').resizable({
					minWidth: 400,
					maxWidth: 400,
					minHeight: 300,
					resize: function(event, ui){
						var height = parseInt($('#chat-message').css('height'));
						$('#chat-message .panel-chat').css('height', height - 25);
						$('#chat-message .chat-users-block').css('height', height - 50);
						$('#chat-message .chat-message-block').css('height', height - 125);
					},
					stop: function(event, ui){
						var height = parseInt($('#chat-message').css('height'));
						$('#chat-message .panel-chat').css('height', height - 25);
						$('#chat-message .chat-users-block').css('height', height - 50);
						$('#chat-message .chat-message-block').css('height', height - 125);
					}
				});
				$('#chat-message').show();
				show_users_my_chat();
			}
		});
	}else if($('#chat-message').is(':visible'))
		$('#chat-message').hide();
	else{
		$('#chat-message').show();
	}
	if(!$('.send-new-message').attr('chat'))
		show_users_my_chat();
}

function hide_chat_message(){
	$('#chat-message').hide();
}

function check_button_new_message(data, show){
	if(data['all'] > 0){
		$('.chat-menu').addClass('btn-danger');
		if(data['chat'] > 0)
			$('.btn-show-chat .count-new-message').html('<span class="badge">' +data['chat']+ '</span>');
		else
			$('.btn-show-chat .count-new-message').html('');
		if(data['sitehelp'] > 0)
			$('.btn-show-sitehelp .count-new-message').html('<span class="badge">' +data['sitehelp']+ '</span>');
		else
			$('.btn-show-sitehelp .count-new-message').html('');
		if(data['alert'] == 1 && show != 'no-alert')
			alert('Новое сообщение');
		else if(data['alert'] == 2 && data['sitehelp'] > 0 && show != 'no-alert')
			alert('Новое сообщение');
		$(document).attr('title', 'Новое сообщение');
	}else{
		$('.chat-menu').removeClass('btn-danger');
		$('.btn-show-chat .count-new-message').html('');
		$('.btn-show-sitehelp .count-new-message').html('');
		$(document).attr('title', 'CRM БОНУСЫ');
	}
	if(!connection && data['sitehelp_login']){
		$('.status-chat').html('<i class="fa fa-times-circle text-danger"></i> offline');
		if(data['status'] == 1)
			change_sitehelp_status('connect');
	}else if(connection){
		$('.status-chat').html('<i class="fa fa-check-circle text-success"></i> online');
	}
}

function show_users_my_chat(){
	$('.btn-chat-window .btn').removeClass('btn-primary');
	$('.btn-chat-window .btn-show-chat').addClass('btn-primary');
	var str = 'func=show_users_my_chat';
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			var height = parseInt($('#chat-message').css('height'));
			$('#chat-message .message-body').html(html);
			$('#chat-message .chat-users-block').css('height', height - 50);
		}
	});
}

function show_chat_room(user, type){
	var str = 'func=show_chat_room&user=' + user + '&type=' + type;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		dataType: 'JSON',
		success: function(data){
			var height = parseInt($('#chat-message').css('height'));
			$('#chat-message .message-body').html(data['html']);
			$('#chat-message .chat-message-block').css('height', height - 130);
			$('#chat-message .chat-message-block').scrollTo('.scrollTo');
			check_button_new_message(data['new']);
		}
	});
}

function show_smile_chat(){
	$('.smile-button').popover('destroy');
	var str = 'func=show_smile_chat';
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			$('.smile-button').popover({
				placement: 'top',
				trigger: 'focus',
				html: true,
				content: html
			}).popover('show');
			$('#chat-message .chat-smile').click(function(){
				var code = $(this).attr('code');
				var text = $('.send-new-message').val() + ' ' + code;
				$('.send-new-message').val(text);
				$('.smile-button').popover('destroy');
			});
		}
	});
}

function send_new_message_chat(){
	var text = $('.send-new-message').val();
	var chat = $('.send-new-message').attr('chat');
	if(text){
		text = text.replace(/\+/g, 'plus');
		var str = 'func=send_new_message_chat&chat=' + chat + '&text=' + text;
		$.ajax({
			url: 'mysql.php',
			type: 'POST',
			data: str,
			success: function(html){
				if(html){
					$('#chat-message .scrollTo').before(html);
					$('#chat-message .chat-message-block').scrollTo('.scrollTo');
					$('.send-new-message').val('').focus();
				}
			}
		});
	}
}


function show_sitehelp_chat(){
	$('.btn-chat-window .btn').removeClass('btn-primary');
	$('.btn-chat-window .btn-show-sitehelp').addClass('btn-primary');
	if(connection){
		var str = 'func=show_sitehelp_chat';
		$.ajax({
			url: 'mysql.php',
			type: 'POST',
			data: str,
			success: function(html){
				var height = parseInt($('#chat-message').css('height'));
				$('#chat-message .message-body').html(html);
				$('#chat-message .chat-users-block').css('height', height - 60);
			}
		});
	}else
		$('#chat-message .message-body').html('<div class="alert alert-danger"><i class="fa fa-frown-o"></i> нет подключения</div>');
}

function show_sitehelp_room(id){
	var str = 'func=show_sitehelp_room&id=' + id;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		dataType: 'JSON',
		success: function(data){
			var height = parseInt($('#chat-message').css('height'));
			$('#chat-message .message-body').html(data['html']);
			$('#chat-message .chat-message-block').css('height', height - 160);
			$('#chat-message .chat-message-block').scrollTo('.scrollTo');
			$('.message-body .btn-change-manager').popover({
				title: 'Изменить менеджера',
				placement: 'bottom',
				content: show_change_manager_sitehelp
			});
			$('.message-body .btn-template-manager').popover({
				title: 'Вставить шаблон',
				placement: 'bottom',
				content: show_template_sitehelp
			});
			check_button_new_message(data['new']);
		}
	});
}

function send_new_message_sitehelp(event){
	var key = event.keyCode;
	var text = $('.send-new-message').val();
	var jid = $('.sitehelp-room').attr('jid');
	var from = $('.sitehelp-room').attr('from');
	if(text && from && jid && (key == 13 || event == 'send')){
		var reply = $msg({to: jid, from: from, type: 'chat'}).c('body').t(text);
		connection.send(reply.tree());
		connection.send($pres({to: jid, type: 'subscribed'}));
		$('.send-new-message').removeAttr('composing');
		text = text.replace('+', 'plus');
		var str = 'func=send_new_message_sitehelp&jid=' + jid + '&text=' + text;
		$.ajax({
			url: 'mysql.php',
			type: 'POST',
			data: str,
			success: function(html){
				if(html){
					$('#chat-message .scrollTo').before(html);
					$('#chat-message .chat-message-block').scrollTo('.scrollTo');
					$('.send-new-message').val('').focus();
				}
			}
		});
	}else if(!$('.send-new-message').attr('composing')){
		var composing = $msg({to: jid, 'type': 'chat'}).c('composing', {xmlns: 'http://jabber.org/protocol/chatstates'});
		connection.send(composing);
		$('.send-new-message').attr('composing', 1);
	}
}

function show_change_manager_sitehelp(){
	var chat = $('.sitehelp-room').attr('room');
	var str = 'func=show_change_manager_sitehelp&chat=' + chat;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			var id = $('.message-body .btn-change-manager').attr('aria-describedby');
			$('#'+id+' .popover-content').html(html).removeClass('popover-content');
		}
	});
}

function change_manager_sitehelp(manager){
	var chat = $('.sitehelp-room').attr('room');
	var str = 'func=change_manager_sitehelp&manager=' + manager + '&chat=' + chat;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		dataType: 'JSON',
		success: function(data){
			var reply = $msg({to: data['to'], from: data['from'], type: 'chat'}).c('body').t('!transfer');
			connection.send(reply.tree());
			show_sitehelp_chat();
		}
	});
}

function show_template_sitehelp(){
	var str = 'func=show_template_sitehelp';
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			var id = $('.message-body .btn-template-manager').attr('aria-describedby');
			$('#'+id+' .popover-content').html(html).removeClass('popover-content');
		}
	});
}

function send_template_sitehelp(text){
	$('.send-new-message').val(text);
	$('.popover').hide();
	$('.message-body .btn-template-manager').trigger('click');
	send_new_message_sitehelp('send');
}

function trash_sitehelp_chat(){
	var chat = $('.sitehelp-room').attr('room');
	var str = 'func=trash_sitehelp_chat&chat=' + chat;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(){
			show_sitehelp_chat();
		}
	});
}

function show_chat_setting(){
	var str = 'func=show_chat_setting';
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			var id = $('.main-header .btn-setting').attr('aria-describedby');
			$('#'+id+' .popover-content').html(html);
			show_status_chat_setting();
		}
	});
}

function show_status_chat_setting(){
	$('.btn-setting-chat button').removeClass('btn-primary');
	$('.btn-setting-chat .btn-status-chat').addClass('btn-primary');
	$('.chat-setting-body .form-horizontal').addClass('hidden');
	$('.chat-setting-body .status-chat-setting').removeClass('hidden');
	$('.my-chat-status input').change(function(){
		var status = $('.my-chat-status input:checked').val();
		var str = 'func=save_my_chat_status&status=' + status;
		$.ajax({
			url: 'mysql.php',
			type: 'POST',
			data: str,
			success: function(){}
		});
	});
	$('.my-chat-alert input').change(function(){
		var alert = $('.my-chat-alert input:checked').val();
		var str = 'func=save_my_chat_alert&alert=' + alert;
		$.ajax({
			url: 'mysql.php',
			type: 'POST',
			data: str,
			success: function(){}
		});
	});
	$('.my-chat-size input').change(function(){
		var size = $('.my-chat-size input:checked').val();
		var str = 'func=save_my_chat_size&size=' + size;
		$.ajax({
			url: 'mysql.php',
			type: 'POST',
			data: str,
			success: function(){}
		});
	});
	$('.btn-connect-sitehelp').click(function(){
		change_sitehelp_status();
	});
}

function change_sitehelp_status(type){
	var str = 'func=change_sitehelp_status&type=' + type;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		dataType: 'JSON',
		success: function(data){
			if(!connection)
				connection = new Strophe.Connection(BOSH_SERVICE);
			if(data['status'] == 0){
				$('.btn-connect-sitehelp').removeClass('btn-danger').addClass('btn-success').removeAttr('disabled').html('Войти');
				connection.disconnect();
				connection = null;
			}else{
				$('.btn-connect-sitehelp').html('<i class="fa fa-spinner fa-spin"></i> Авторизация').attr('disabled', 'disabled');
				connection.connect(data['login'], data['password'], on_connect_sitehelp);
			}
		}
	});
}

function show_login_chat_setting(){
	$('.btn-setting-chat button').removeClass('btn-primary');
	$('.btn-setting-chat .btn-login-chat').addClass('btn-primary');
	$('.chat-setting-body .form-horizontal').addClass('hidden');
	$('.chat-setting-body .login-chat-setting').removeClass('hidden');
}

function show_sitehelp_chat_setting(){
	$('.btn-setting-chat button').removeClass('btn-primary');
	$('.btn-setting-chat .btn-sitehelp-chat').addClass('btn-primary');
	$('.chat-setting-body .form-horizontal').addClass('hidden');
	$('.chat-setting-body .sitehelp-chat-setting').removeClass('hidden');
}

function save_login_sitehelp(){
	var login = $('.sitehelp-login').val();
	var password = $('.sitehelp-password').val();
	clear_mistake('.sitehelp-login-block');
	clear_mistake('.sitehelp-password-block');
	if(!login)
		show_mistake('.sitehelp-login-block');
	else if(!password)
		show_mistake('.sitehelp-password-block');
	else{
		var str = 'func=save_login_sitehelp&login=' + login + '&password=' + password;
		$.ajax({
			url: 'mysql.php',
			type: 'POST',
			data: str,
			success: function(){}
		});
	}
}



/* SITEHELP */


function on_connect_sitehelp(status){
	if(status == Strophe.Status.CONNECTING) {
		$('.btn-connect-sitehelp').html('<i class="fa fa-spinner fa-spin"></i> Авторизация').attr('disabled', 'disabled');
	}else if(status == Strophe.Status.CONNFAIL){
		$('.btn-connect-sitehelp').removeClass('btn-danger').addClass('btn-success').removeAttr('disabled').html('Войти');
		logout_sitehelp();
	}else if(status == Strophe.Status.DISCONNECTING){
		$('.btn-connect-sitehelp').removeClass('btn-danger').addClass('btn-success').removeAttr('disabled').html('Войти');
	}else if (status == Strophe.Status.DISCONNECTED){
		$('.btn-connect-sitehelp').removeClass('btn-danger').addClass('btn-success').removeAttr('disabled').html('Войти');
		logout_sitehelp();
	}else if (status == Strophe.Status.CONNECTED){
		$('.btn-connect-sitehelp').removeClass('btn-success').addClass('btn-danger').removeAttr('disabled').html('Выйти');
		connection.addHandler(on_message_sitehelp, null, 'message', null, null, null);
		
		connection.send($pres().tree());
	}
}

function on_message_sitehelp(message){
	var to = message.getAttribute('to');
	var from = message.getAttribute('from');
	var type = message.getAttribute('type');
	var website = message.getAttribute('website');
	var time = message.getAttribute('time');
	var elems = message.getElementsByTagName('body');
	var body = elems[0];
	var text = Strophe.getText(body);
	var arr = from.split('/');
	var jid = arr[0];
	if(type == 'chat' && elems.length > 0 && text != ''){
		var current_jid = '';
		if($('.sitehelp-room').length)
			current_jid = $('.sitehelp-room').attr('jid');
		var str = 'func=new_message_sitehelp&jid=' + jid + '&text=' + text + '&website=' + website + '&time=' + time + '&current_jid=' + current_jid;
		$.ajax({
			url: 'mysql.php',
			type: 'POST',
			data: str,
			dataType: 'JSON',
			success: function(data){
				if(data['html'] && $('.sitehelp-room').length && $('.sitehelp-room').attr('jid') == jid){
					$('#chat-message .scrollTo').before(data['html']);
					$('#chat-message .chat-message-block').scrollTo('.scrollTo');
				}else
					check_button_new_message(data['new']);
				if(data['request'] == 1){
					var iq = $iq({type: "set"}).c("query", {xmlns: "jabber:iq:roster"}).c("item", data);
					connection.sendIQ(iq);
					var subscribe = $pres({to: data.jid, type: 'subscribe'});
					connection.send(subscribe);
					connection.send($pres({to: data.jid, type: 'subscribed'}));
				}
			}
		});
	}
	return true;
}

function logout_sitehelp(){
	var str = 'func=logout_sitehelp';
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(){}
	});
}

function replaceAll(str, find, replace) {
  return str.replace(new RegExp(escapeRegExp(find), 'g'), replace);
}

function select_by_number_reckoning(){
	var id = $('.number-reckoning').val();
	if(id){
		id = id.replaceAll('+', '');
		id = id.replaceAll(' ', '');
		id = id.replaceAll('-', '');
		id = id.replaceAll('(', '');
		id = id.replaceAll(')', '');
		console.log('id='+id);
		console.log('id.length='+id.length);
		console.log('id.first='+id[0]);
		console.log('id.indexOf='+id.indexOf('@'));
		if ((id.length==11 && (id[0]=='7' || id[0]=='8'))  || (id.indexOf('@')>0 && id.length>4) ) {
			show_my_bid_menu('1');
		} else {
			clear_mistake('.panel-number-reckoning');
			var str = 'func=select_by_number_reckoning&id=' + id;
			$.ajax({
				url: 'mysql.php',
				type: 'POST',
				data: str,
				dataType: 'JSON',
				success: function(data){
					if(data){
						if(data['turist'])
							show_turist(data['turist'], data['id']);
						else if(data['agency'])
							show_turist(data['agency'], data['id'], 'agency');
					}else
						show_mistake('.panel-number-reckoning');
				}
			});
		}
	}else
		show_mistake('.panel-number-reckoning');
}

function show_mailing_chat_message(){
	$('.btn-chat-window .btn').removeClass('btn-primary');
	var str = 'func=show_mailing_chat_message';
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			var height = parseInt($('#chat-message').css('height'));
			$('#chat-message .message-body').html(html);
			$('#chat-message .chat-users-block').css('height', height - 50);
		}
	});
}

function send_mailing_chat_message(){
	var users = select_checkbox('user');
	var message = $('.new-message').val();
	if(users && message){
		var str = 'func=send_mailing_chat_message&message=' + message + '&users=' + users;
		$.ajax({
			url: 'mysql.php',
			type: 'POST',
			data: str,
			success: function(){
				show_users_my_chat();
			}
		});
	}
}
