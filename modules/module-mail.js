function show_send_mail(id, doc){
	var str = 'func=show_send_mail&id=' + id + '&doc=' + doc;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			show_modal(html);
		}
	});
}

function show_send_login_agency(id){
	var str = 'func=form_send_password_agency&id=' + id;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			show_modal(html);
		}
	});
}

function send_password_klient(id){
	var str = 'func=form_send_password_client&id=' + id;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			show_modal(html);
		}
	});
}

function create_account_client(id){
	$('.btn-send-mail').attr('disabled', 'disabled').html('<i class="fa fa-spinner fa-spin"></i> Отправка письма...');
	var str = 'func=create_account_client&id=' + id;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			remove_all_windows();
			select_klient(id);
			show_alert('Отправлено...');
		}
	});
}

function send_login_agency(id){
	$('.btn-send-mail').attr('disabled', 'disabled').html('<i class="fa fa-spinner fa-spin"></i> Отправка письма...');
	var str = 'func=send_login_agency&id=' + id;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(){
			remove_all_windows();
			select_agency(id);
			show_alert('Отправлено...');
		}
	});
}

function send_mail_client(id, doc){
	$('.btn-send-mail').attr('disabled', 'disabled').html('<i class="fa fa-spinner fa-spin"></i> Отправка письма...');
	var str = 'func=send_mail_client_document&id=' + id + '&doc=' + doc;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(){
			remove_all_windows();
			view_schet(id);
			show_alert('Письмо отправлено...');
		}
	});
}

function send_mail_agency(id, doc){
	$('.btn-send-mail').attr('disabled', 'disabled').html('<i class="fa fa-spinner fa-spin"></i> Отправка письма...');
	var str = 'func=send_mail_agency_document&id=' + id + '&doc=' + doc;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(){
			remove_all_windows();
			view_schet(id);
			show_alert('Письмо отправлено...');
		}
	});
}

function show_send_confirm_rating(id){
	var str = 'func=form_send_confirm_rating&id=' + id;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			show_modal(html);
		}
	});
}

function send_mail_confirm_rating(id){
	$('.btn-send-mail').attr('disabled', 'disabled').html('<i class="fa fa-spinner fa-spin"></i> Отправка письма...');
	var str = 'func=send_mail_confirm_rating&id=' + id;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(){
			remove_all_windows();
			show_alert('Письмо отправлено...');
		}
	});
}

function show_send_confirm_rating_comment(id){
	var str = 'func=form_send_confirm_rating_comment&id=' + id;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			show_modal(html);
		}
	});
}

function send_mail_confirm_rating_comment(id){
	var email = $('.email-string').html();
	$('.btn-send-mail').attr('disabled', 'disabled').html('<i class="fa fa-spinner fa-spin"></i> Отправка письма...');
	var str = 'func=send_mail_confirm_rating_comment&id=' + id + '&email=' + email;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(){
			remove_all_windows();
			show_alert('Письмо отправлено...');
		}
	});
}

function show_send_login_object_account(account){
	var str = 'func=show_send_login_object_account&account=' + account;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		dataType: 'JSON',
		success: function(email){
			if(!email)
				return;
			var html = '<div class="modal fade"><div class="modal-dialog"><div class="modal-content"><div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-label="Close"><i class="fa fa-times"></i></button><h4 class="modal-title">Выслать логин объекту</h4></div><div class="form-horizontal list-group list-group-margin"><div class="list-group-item"><div class="form-group form-group-margin"><label class="col-sm-3 control-label-element">Email</label><div class="col-sm-9 label-text">' +email+ '</div></div></div></div><div class="modal-footer"><button type="button" class="btn btn-success btn-sm btn-send-login-object-account"><i class="fa fa-envelope-o"></i> Выслать доступ в личный кабинет</button></div></div></div></div>';
			show_modal(html);
			$('.btn-send-login-object-account').click(function(){
				$('.btn-send-login-object-account').attr('disabled', 'disabled').html('<i class="fa fa-spinner fa-spin"></i> Отправка письма...');
				var str = 'func=send_login_object_account&account=' + account;
				$.ajax({
					url: 'mysql.php',
					type: 'POST',
					data: str,
					success: function(){
						remove_all_windows();
					}
				});
			});
		}
	});
}
