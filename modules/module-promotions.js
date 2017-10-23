function view_promotions_object(id){
	$('.nav-object .btn-success').removeClass('btn-success');
	$('.nav-object .promo-object').addClass('btn-success');
	var str = 'func=view_promotions_object&id=' + id;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			$('#infa_object').html(html);
		}
	});
	show_loader_element('#infa_object');
}

function add_new_promotion(id){
	var str = 'func=add_new_promotion&id=' + id;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			show_modal(html);
		}
	});
}

function edit_type_promotions(id){
	var type = $('.type-promo').val();
	if(type == 'warranty' || type == 'fire'){
		$('.select-room').show();
		select_room(id);
	}else
		$('.select-room').hide();
}

function save_new_promotion(id){
	var room = '';
	var type = $('.type-promo').val();
	var title = $('.title-promo').val();
	var text = $('.text-promo').val();
	var date_end = $('#date-promo').attr('date');
	if($('#select-room').length)
		room = $('#select-room').val();
	if(!title)
		show_warning('.new-promo', 'Введите название', false);
	else if(!text)
		show_warning('.new-promo', 'Введите текст', false);
	else if(!date_end)
		show_warning('.new-promo', 'Введите дату окончания', false);
	else if(text.length > 250)
		show_warning('.new-promo', 'Текст объявления больше допустимого', false);
	else{
		var str = 'func=save_new_promotion&id=' + id + '&end=' + date_end + '&title=' + title + '&text=' + text + '&room=' + room + '&type=' + type;
		$.ajax({
			url: 'mysql.php',
			type: 'POST',
			data: str,
			success: function(html){
				remove_all_windows();
				$('.promotions-object').append(html);
			}
		});
	}
}

function edit_promotion(id){
	var str = 'func=edit_promotion&id=' + id;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			show_modal(html);
		}
	});
}

function update_promotion(id){
	var room = '';
	var type = $('.type-promo').val();
	var title = $('.title-promo').val();
	var text = $('.text-promo').val();
	var date_end = $('#date-promo').attr('date');
	if($('#select-room').length)
		room = $('#select-room').val();
	if(!title)
		show_warning('.edit-promo', 'Введите название');
	else if(!text)
		show_warning('.edit-promo', 'Введите текст');
	else if(!date_end)
		show_warning('.edit-promo', 'Введите дату окончания');
	else if(text.length > 250)
		show_warning('.edit-promo', 'Текст объявления больше допустимого');
	else{
		var str = 'func=update_promotion&id=' + id + '&end=' + date_end + '&title=' + title + '&text=' + text + '&room=' + room + '&type=' + type;
		$.ajax({
			url: 'mysql.php',
			type: 'POST',
			data: str,
			success: function(html){
				remove_all_windows();
				$('.promo-'+id).html(html);
			}
		});
	}
}

function check_status_promotion(id, status){
	var str = 'func=check_status_promotion&id=' + id + '&status=' + status;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			$('.promo-'+id).html(html).removeClass('hidden');
			$('.promo-'+id+' .hidden').removeClass('hidden');
		}
	});
}

function upload_promotions_object(id){
	var str = 'func=upload_promo_object_on_server&id=' + id;
	$.ajax({
		type: 'POST',
		data: str,
		url: 'mysql.php',
		success: function(html){
			$('.data-object').html(html);
		}
	});
	show_loader_element('.data-object');
}

function upload_promotions(){
	var str = 'func=upload_promo_object_on_server';
	$.ajax({
		type: 'POST',
		data: str,
		url: 'mysql.php',
		success: function(html){
			$('.promo-html').html(html);
		}
	});
	show_loader_element('.promo-html');
}

function menu_all_promotions(){
	$('.menu-object li').removeClass('active');
	$('.menu-object .li-promo').addClass('active');
	var html = '<div class="btn-group btn-group-justified promo-menu" style="margin: 10px 0px"><div class="btn-group"><button type="button" class="btn btn-default btn-promo-all" onclick="view_all_promotions(\'all\')"><i class="fa fa-bolt"></i> Всё</button></div><div class="btn-group"><button type="button" class="btn btn-default btn-promo-VIP" onclick="view_all_promotions(\'VIP\')"><i class="fa fa-bolt"></i> ВИП акции</button></div><div class="btn-group"><button type="button" class="btn btn-default btn-promo-action" onclick="view_all_promotions(\'action\')"><i class="fa fa-star"></i> Акции</button></div><div class="btn-group"><button type="button" class="btn btn-default btn-promo-warranty" onclick="view_all_promotions(\'warranty\')"><i class="fa fa-check-circle-o"></i> Гарант.номера</button></div><div class="btn-group"><button type="button" class="btn btn-default btn-promo-fire" onclick="view_all_promotions(\'fire\')"><i class="fa fa-fire"></i> Горящие путевки</button></div><div class="btn-group"><button type="button" class="btn btn-default btn-promo-info" onclick="view_all_promotions(\'info\')"><i class="fa fa-info-circle"></i> Информация</button></div></div><div class="promo-html"></div>';
	$('.data-object').html(html);
	view_all_promotions('all');
}

function view_all_promotions(type){
	$('.promo-menu button').removeClass('btn-success');
	$('.promo-menu .btn-promo-'+type).addClass('btn-success');
	var str = 'func=view_all_promotions&type=' + type;
	$.ajax({
		type: 'POST',
		data: str,
		url: 'mysql.php',
		success: function(html){
			$('.promo-html').html(html);
		}
	});
	show_loader_element('.promo-html');
}
