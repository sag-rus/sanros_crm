function my_reminder(){
	select_menu('reminder_menu');
	var str = 'func=select_my_reminder';
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			$('#body').html(html);
		}
	});
}

function add_new_reminder(type){
	var str = 'func=add_new_reminder';
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			show_modal(html);
			if(type == 'klient'){
				$('#surname_reminder').val($('#surname').val());
				$('#name_reminder').val($('#name').val());
				$('#otch_reminder').val($('#otch').val());
				$('#telephone_reminder').val($('#telephone1').val());
				$('#email_reminder').val($('#email').val());
			}
			show_datepicker();
		}
	});
}

function save_new_reminder(){
	var id = $('#id_reminder').val();
	var surname = $('#surname_reminder').val();
	var name = $('#name_reminder').val();
	var otch = $('#otch_reminder').val();
	var telephone = $('#telephone_reminder').val();
	var email = $('#email_reminder').val();
	var note = $('#note_reminder').val();
	var date = $('#date_reminder').attr('date');
	var str = 'func=save_new_reminder&surname=' + surname + '&name=' + name + '&otch=' + otch + '&telephone=' + telephone + '&email=' + email + '&date=' + date + '&note=' + note + '&schet=' + id;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(){
			remove_all_windows();
			show_alert('Уведомление сохранено...');
			my_reminder();
		}
	});
}

function edit_reminder(id){
	var str = 'func=edit_reminder&id=' + id;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			show_modal(html);
			show_datepicker();
		}
	});
}

function update_reminder(id){
	var schet = $('#id_reminder').val();
	var surname = $('#surname_reminder').val();
	var name = $('#name_reminder').val();
	var otch = $('#otch_reminder').val();
	var telephone = $('#telephone_reminder').val();
	var email = $('#email_reminder').val();
	var note = $('#note_reminder').val();
	var date = $('#date_reminder').attr('date');
	var str = 'func=update_reminder&id=' + id +'&surname=' + surname + '&name=' + name + '&otch=' + otch + '&telephone=' + telephone + '&email=' + email + '&date=' + date + '&note=' + note + '&schet=' + schet;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(){
			remove_all_windows();
			show_alert('Уведомление сохранено...');
			my_reminder();
		}
	});
}

function delete_reminder(id){
	if(confirm('Отправить в архив?')){
		var str = 'func=delete_reminder&id=' + id;
		$.ajax({
			url: 'mysql.php',
			type: 'POST',
			data: str,
			success: function(){
				remove_all_windows();
				show_alert('Уведомление удалено...');
				$('#rem_'+id).remove();
			}
		});
	}
}


function show_notification_user(){
	var str = 'func=show_notification_user';
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			var id = $('.main-header .btn-notification').attr('aria-describedby');
			$('#'+id+' .popover-content').html(html).css('padding', '0px');
		}
	});
}

function confirm_notification(id){
	var str = 'func=confirm_notification&id=' + id;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		dataType: 'JSON',
		success: function(data){
			$('.notification-'+id).remove();
			check_new_notification(data);
		}
	});
}
