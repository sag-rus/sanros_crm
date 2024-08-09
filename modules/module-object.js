function objects(){
	select_menu('obj_menu');
	var id_rights = parseInt($('*[data-id-rights]').attr('data-id-rights'));
	var html = '<ul class="nav nav-tabs nav-justified menu-object"><li class="li-object" onclick="search_object()"><a><i class="fa fa-home"></i> Объекты</a></li><li class="li-no-price" onclick="find_object_no_price()"><a><i class="fa fa-warning"></i> Нет цен</a></li><li class="li-promo" onclick="menu_all_promotions()"><a><i class="fa fa-star"></i> Акции</a></li><li class="li-rating" onclick="view_all_rating()"><a><i class="fa fa-comments-o"></i> Отзывы</a></li></ul><div class="data-object" style="padding-top: 10px"></div><div class="clearfix"></div>';
	if (id_rights > 3) {
		html = '<ul class="nav nav-tabs nav-justified menu-object"><li class="li-object" onclick="search_object()"><a><i class="fa fa-home"></i> Объекты</a></li><li class="li-no-price" onclick="find_object_no_price()"><a><i class="fa fa-warning"></i> Нет цен</a></li><li class="li-reservation" onclick="search_object_reservation()"><a><i class="fa fa-calendar"></i> Блоки мест</a></li><li class="li-search-reservation" onclick="show_form_search_engine_reservation()"><a><i class="fa fa-search-plus"></i> Поиск</a></li><li class="li-promo" onclick="menu_all_promotions()"><a><i class="fa fa-star"></i> Акции</a></li><li class="li-rating" onclick="view_all_rating()"><a><i class="fa fa-comments-o"></i> Отзывы</a></li><li class="li-commission" onclick="view_all_commission_object()"><a><i class="fa fa-percent"></i> Вознаграждение</a></li></ul><div class="data-object" style="padding-top: 10px"></div><div class="clearfix"></div>';
	}
	$('#body').html(html);
	search_object();
}

function search_object(){
	$('.menu-object li').removeClass('active');
	$('.menu-object .li-object').addClass('active');
	var str = 'func=show_head_page_object';
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			$('.data-object').html(html);
		}
	});
	show_loader_element('.data-object');
}

function find_object_no_price(){
	$('.menu-object li').removeClass('active');
	$('.menu-object .li-no-price').addClass('active');
	var str = 'func=find_object_no_price';
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			$('.data-object').html(html);
		}
	});
	show_loader_element('.data-object');
}

function search_object_reservation(){
	$('.menu-object li').removeClass('active');
	$('.menu-object .li-reservation').addClass('active');
	var str = 'func=search_object_reservation';
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			$('.data-object').html(html).append('<div class="clearfix"></div><div class="object-qouta-reserv" style="margin-top: 20px"></div>');
			select_objects_quota();
		}
	});
	show_loader_element('.data-object');
}

function view_all_rating(){
	$('.menu-object li').removeClass('active');
	$('.menu-object .li-rating').addClass('active');
	var str = 'func=show_review_rating';
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			$('.data-object').html(html);
		}
	});
	show_loader_element('.data-object');
}

function search_rating(){
	var id_obj = $('.id-object').attr('name');
	var manager = $('#all_manager').val();
	var str = 'func=search_rating&id_obj=' + id_obj + '&manager=' + manager;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			$('#rating-html').html(html);
		}
	});
	show_loader_element('#rating-html');
}

function find_object(head){
	var str = 'func=find_object&head=' + head;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			$('.result-object').html(html);
		}
	});
}

function find_object_by_region(id){
	$('.div-country .well-sm').removeClass('well-success');
	$('.div-region .well-sm').removeClass('well-success');
	$('.div-region .region-'+id).addClass('well-success');
	var str = 'func=find_object&region=' + id;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			$('.result-object').html(html);
		}
	});
}

function find_object_by_country(id){
	$('.div-region .well-sm').removeClass('well-success');
	$('.div-country .well-sm').removeClass('well-success');
	$('.div-country .country-'+id).addClass('well-success');
	var str = 'func=find_object&country=' + id;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			$('.result-object').html(html);
		}
	});
}

function find_object_by_direction(id){
	$('.div-direction .well-sm').removeClass('well-success');
	$('.div-direction .direction-'+id).addClass('well-success');
	var str = 'func=find_object&direction=' + id;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			$('.div-object').html(html);
		}
	});
}

function view_object(id, type){
	remove_all_windows();
	save_old_html_for_back();
	var str = 'func=select_object&id=' + id;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			$('.data-object').html(html);
			view_description_object(id);

			$('.update').on('change', function() {
               var form = $(this).closest('form');
               var data = form.serializeArray();
               update_commission_object(form.data('object-id'));
            });
		}
	});
}

function select_object_on_request(id, name){
	if (id==-1) alert('Этот объект уже пивязан к другому аккаунту');
	else {
		$('.same_name_objects input').prop('checked', false);
		$('.same_name_objects').append('<input type="radio" checked="checked" name="id_object" value="'+id+'"> '+name+'<br>');
		$('#find').remove();
	}
}

function edit_object_info(id){
	remove_all_windows();
	var str = 'func=edit_object&id=' + id;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			$('.data-object').html(html);
		}
	});
}

function object_agency_report(id){
	remove_all_windows();
	var months = '';
	var years = [];
  var mdate = new Date();
  var i;
	for(i = mdate.getFullYear()-3; i <= mdate.getFullYear(); i++) {
		if(i === mdate.getFullYear()) {
      years += '<option class="form-control" value="'+i+'" selected>'+i+'</option>';
    }
		else {
      years += '<option class="form-control" value="'+i+'">'+i+'</option>';
    }
	}

	var monthAr = [
		null,
		'Январь',
    'Февраль',
    'Март',
    'Апрель',
    'Май',
		'Июнь',
    'Июль',
    'Август',
    'Сентябрь',
    'Октябрь',
    'Ноябрь',
		'Декабрь'
  ];

	for(i = 1; i < 13; i++) {
		if(i === mdate.getMonth()+1) {
      months += '<option class="form-control" value="'+i+'" selected>'+monthAr[i]+'</option>';
    }
		else {
      months += '<option class="form-control" value="'+i+'">'+monthAr[i]+'</option>';
    }
	}

	var modal = '<div class="modal fade">'+
								'<div class="modal-dialog">'+
  								'<div class="modal-content">'+
										'<div class="modal-header">' +
      								'<button type="button" class="close" data-dismiss="modal" aria-label="Close"><i class="fa fa-times"></i></button>' +
      								'<h4 class="modal-title">Отчет агента за месяц</h4>' +
										'</div>' +
      							'<div class="modal-body form-horizontal">' +
											'<div class="form-group">' +
												'<div class="col-md-6">'+
													'<label class="control-label">Месяц</label>' +
													'<select class="form-control" id="agency-report-month">'+
														months +
													'</select>'+
												'</div>'+
												'<div class="col-md-6">'+
													'<label class="control-label">Год</label>' +
													'<select class="form-control" id="agency-report-year">'+
															years +
													'</select>'+
												'</div>'+
											'</div>'+
										'</div>' +
										'<div class="modal-footer text-center">' +
												'<button type="button" class="btn btn-success btn-sm" onclick="object_agency_report_generate('+id+')"><i class="fa fa-check"></i> Сформировать</button>' +
										'</div>' +
									'</div>'+
								'</div>' +
							'</div>';
	show_modal(modal);
}

function object_agency_report_generate(id) {
	var month = $('#agency-report-month').val();
	var year = $('#agency-report-year').val();
	window.open('./document.php?func=object_agency_report&id='+id+"&year="+year+"&month="+month,"_blank");
  remove_all_windows();
}

function add_new_contact_object(type){
	if(type == "telephone")
		icon = "<i class='fa fa-phone'></i>";
	else
		icon = "@";
	var value = $('.'+type+' .new .value').val();
	var note = $('.'+type+' .new .note').val();
	$('.'+type+' .new .value').val('');
	$('.'+type+' .new .note').val('');
	var html = "<div class='object_infa'><div class='col-sm-5'><div class='input-group'><span class='input-group-addon'>" +icon+ "</span><input type='text' class='form-control value' value='" +value+ "' /></div></div><div class='col-sm-5'><input type='text' class='form-control note' value='" +note+ "' /></div><div class='col-sm-2'><button class='btn btn-danger btn-xs' onclick='$(this).parent().parent().remove()'><i class='fa fa-times-circle'></i> Удалить</button></div></div>";
	$('.'+type+' .new').before(html);
}

function save_object_info(id){
	var name = $('#full_name').val();
	var arrival = $('#arrival').val();
	var leaving = $('#leaving').val();
	var add_one_day = $('#add_one_day').val();
	var address = $('#address').val();
	var fax = $('#fax').val();
	var website = $('.object-website').val();
	var note_reward = $('.object-note-reward').val();
	var list = new Array();
	var index = 0;
	$('.telephone .object_infa').each(function(){
		var value = $(this).find('.value').val();
		var note = $(this).find('.note').val();
		if(value != ''){
			list[index] = new Object();
			list[index]['value'] = value;
			list[index]['note'] = note;
			index++;
		}
	});
	var  telephone = JSON.stringify(list);
	var list = new Array();
	var index = 0;
	$('.email .object_infa').each(function(){
		var value = $(this).find('.value').val();
		var note = $(this).find('.note').val();
		if(value != ''){
			list[index] = new Object();
			list[index]['value'] = value;
			list[index]['note'] = note;
			index++;
		}
	});
	var  email = JSON.stringify(list);
	var str = 'func=update_object_info&id=' + id + '&name=' + name + '&arrival=' + arrival + '&leaving=' + leaving + '&email=' + email + '&telephone=' + telephone + '&fax=' + fax + '&address=' + address + '&add_one_day=' + add_one_day + '&website=' + website + '&note_reward=' + note_reward;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(){
			view_object(id);
			show_alert('Объект изменен...');
		}
	});
}

function show_menu_object(id){
	var str = 'func=show_menu_object&id=' + id;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			show_div_buttons('object-active', html);
		}
	});
}

function edit_service_information_object(id){
	var str = 'func=edit_service_information_object&id=' + id;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			show_modal(html);
		}
	});
}

function update_service_information_object(id){
	var info = $('.information-object').val();
	var str = 'func=update_service_information_object&id=' + id + '&info=' + info;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(){
			view_description_object(id);
			remove_all_windows();
			show_alert("Сохранено...");
		}
	});
}

function view_description_object(id){
	$('.nav-object .btn-success').removeClass('btn-success');
	$('.nav-object .desc-object').addClass('btn-success');
	var str = 'func=view_description_object&id=' + id;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			$('#infa_object').html(html);
		}
	});
}

function add_new_picture_object(id){
	var str = 'func=form_new_document';
	$.ajax({
		type: 'POST',
		data: str,
		url: 'mysql.php',
		success: function(html){
			show_modal(html);
			var button = $('#uploadButton'), interval;
			$.ajax_upload(button, {
				action : 'core/upload.php?func=upload_document',
				name : 'file',
				onSubmit : function(file, ext){
					this.disable();
				},
				onComplete : function(file, response){
					if(response == 1){
						show_warning('.new-document', 'Ошибка при загрузке файла.', false);
						this.enable();
					}else{
						var name = $('.name-document').val();
						var str = 'func=upload_new_image_object&file=' + response + '&id=' + id + '&name=' + name;
						$.ajax({
							url: 'mysql.php',
							type: 'POST',
							data: str,
							success: function(){
								remove_all_windows();
								view_description_object(id);
								show_alert('Картинка загружена...');
							}
						});

					}
				}
			});
		}
	});
}

function click_checkbox_room(id){
	if($('#check_'+id).prop('checked')){
		$('#tr_'+id).addClass('tr_active');
		$('#tr_'+id + ' select').show();
		$('#tr_'+id + ' .label_place').show();
		$('#tbl_price'+id).show();
	}else{
		$('#tr_'+id).removeClass('tr_active');
		$('#tr_'+id + ' select').hide();
		$('#tr_'+id + ' .label_place').hide();
		$('#tbl_price'+id).hide();
	}
	check_max_place();
}

function get_id_price(room){
	var answer = "";
	var select = $('#tr_'+room+' select').each(function(){
		if($('#tr_'+room).hasClass('tr_active')){
			var value = parseInt($(this).val());
			if(value > 0){
				if(answer)
					answer+= '=';
				var id = $(this).attr('id');
				answer+= id + '-' + value;
			}
		}
	});
	return answer;
}

function view_dates_price_object(id){
	$('.nav-object .btn-success').removeClass('btn-success');
	$('.nav-object .price-object').addClass('btn-success');
	show_loader_element('#infa_object');
	var str = 'func=view_dates_price_object&id=' + id;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			$('#infa_object').html(html);
			view_prices_object();
		}
	});
}

function edit_range_counter(id){
	var div = '#tc_'+id;
	var input = 'val_tc'+id;
	var price = $(div).html();
	var html = "<input type='text' class='form-control' id='" +input+ "' value='" +price+ "' onblur='update_range_counter(\"" +id+ "\")'>";
	$(div).html(html);
	$('#'+input).focus();
}

function update_range_counter(id){
	var counter = $('#val_tc'+id).val();
	var str = 'func=update_range_counter&id=' + id + '&counter=' + counter;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(){
			view_prices_object();
		}
	});
}

function update_price_manager(element, id_room, id_range, id_price){
	if(!id_price)
		id_price = "";
	var input = $(element);
	var value = input.val();
	var str = 'func=update_price_manager&id=' + id_price + '&room=' + id_room + '&range=' + id_range + '&price=' + value;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(value){
			input.val(value);
		}
	});
}

function edit_range_manager(id){
	var str = 'func=edit_range_manager&id=' + id;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			show_modal(html);
		}
	});
}

function update_range_manager(id){
	var name = $('#range_name').val();
	var date = $('#range_date').val();
	var type = $('#range_type').val();
	var place = $('#range_place').val();
	var rate_plan_id = $('#price-range-rate-plan').val();
	var treatment = $('#treatment_type').val();
	name = name.replace(/\+/g, 'plus');
	var str = 'func=update_range_manager&name=' + name + '&date=' + date + '&id=' + id + '&type=' + type + '&place=' + place + '&treatment=' + treatment+'&rate_plan_id='+rate_plan_id;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(value){
			remove_all_windows();
			if(value)
				$('#th_'+id).html(value);
			else
				view_prices_object(date);
		}
	});
}

function check_delete_range_manager(){
	var check = $('.check-delete').prop('checked');
	if(check == false)
		$('.btn-delete-range').attr('disabled', 'disabled');
	else
		$('.btn-delete-range').removeAttr('disabled');
}

function delete_range_manager(id){
	var str = 'func=delete_range_manager&id=' + id;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(){
			remove_all_windows();
			view_prices_object();
		}
	});
}

function add_new_range_manager(object){
	var date = $('.id-date-price').val();
	var rate_plan_id = $('.price-rate-plan').val();
	var str = 'func=edit_range_manager&object=' + object + '&date=' + date+'&rate_plan_id='+rate_plan_id;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			show_modal(html);
		}
	});
}

function save_range_manager(object){
	var name = $('#range_name').val();
	var date = $('#range_date').val();
	var type = $('#range_type').val();
	var place = $('#range_place').val();
	var treatment = $('#treatment_type').val();
	var rate_plan_id = $('#price-range-rate-plan').val();
	var str = 'func=save_range_manager&name=' + name + '&date=' + date + '&object=' + object + '&type=' + type + '&place=' + place+'&treatment='+treatment+'&rate_plan_id='+rate_plan_id;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function (){
			remove_all_windows();
			view_prices_object(date);
		}
	});
}

function add_new_date_manager(id){
	var str = 'func=edit_date_price_manager&object=' + id;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			show_modal(html);
		}
	});
}

function save_date_price_manager(id){
	var start = $('#date-start').attr('date');
	var end = $('#date-end').attr('date');
	if(!start)
		show_warning('.date-price', 'Введите дату начала');
	else if(!end)
		show_warning('.date-price', 'Введите дату окончания');
	else{
		var str = 'func=save_date_price_manager&id=' + id + '&start=' + start + '&end=' + end;
		$.ajax({
			url: 'mysql.php',
			type: 'POST',
			data: str,
			success: function(html){
				remove_all_windows();
				show_alert('Готово...');
				view_dates_price_object(id);
			}
		});
	}
}

function edit_date_price_manager(){
	var id_date = $('.id-date-price').val();
	var str = 'func=edit_date_price_manager&id=' + id_date;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			show_modal(html);
		}
	});
}

function update_date_price_manager(id){
	var start = $('#date-start').attr('date');
	var end = $('#date-end').attr('date');
	if(!start)
		show_warning('.date-price', 'Введите дату начала', false);
	else if(!end)
		show_warning('.date-price', 'Введите дату окончания', false);
	else{
		var str = 'func=update_date_price_manager&id=' + id + '&start=' + start + '&end=' + end;
		$.ajax({
			url: 'mysql.php',
			type: 'POST',
			data: str,
			success: function(object){
				remove_all_windows();
				show_alert('Готово...');
				view_dates_price_object(object);
			}
		});
	}
}

function show_dates_copy_prices(){
	var id_date = $('.id-date-price').val();
	var str = 'func=show_dates_copy_prices&date=' + id_date;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			show_modal(html);
		}
	});
}

function copy_date_price(){
	var id_date = $('.id-date-price').val();
	var new_id_date = $('.new-id-date').val();
	var str = 'func=copy_date_price&date=' + id_date + '&new_date=' + new_id_date;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(id){
			remove_all_windows();
			view_dates_price_object(id);
		}
	});
}

function view_prices_object(id_date, type){
	if($('.id-date-price').length){
		if(id_date)
			$('.id-date-price').val(id_date);
		var id_date = $('.id-date-price').val();
		var rate_plan_id = $('.price-rate-plan').val();
		var str = 'func=view_prices_object&date=' + id_date + '&type=' + type+'&rate_plan_id='+rate_plan_id;
		$.ajax({
			url: 'mysql.php',
			type: 'POST',
			data: str,
			success: function(html){
				$('.html-price').html(html);
			}
		});
	}
}

function view_calendar_rooms(id){
	var str = 'func=view_calendar_rooms&id=' + id;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			$('.data-object').html(html);
		}
	});
	show_loader_element('.data-object');
}

function show_calendar_room(id){
	var str = 'func=show_calendar_room&id=' + id;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			$('.room-block-'+id+' .calendar-block').html(html);
			activate_calendar(id);
		}
	});
	show_loader_element('.room-block-'+id+' .calendar-block');
}

function activate_calendar(id){
	$('.room-block-'+id+' .calendar-block .tr-range').each(function(){
		$(this).selectable({
			cancel: 'th',
			stop: function(event, ui){
				var id = $(this).attr('name');
				$(this).find('th').removeClass('ui-selected');
				$('.tr-'+id+' .first-td').removeClass('ui-selectee');
				$(this).find('th').find('*').removeClass('ui-selected');
				$(this).find('span').removeClass('ui-selected');
				var clear = 0;
				$(this).find('.ui-selected').each(function(){
					var attr = $(this).attr('name');
					if(attr != 'on-sale' && attr != 'reserv')
						clear = 1;
					else if(attr == 'reserv')
						clear = 2;
				});
				if(clear == 0){
					var tr = $(this);
					var colspan = tr.find('.ui-selected').length;
					var date = tr.find('.ui-selected:first').attr('date');
					if(confirm('Создать новую заявку?')){
						var str = 'func=save_new_reservation&room=' + id + '&date=' + date + '&day=' + colspan;
						$.ajax({
							url: 'mysql.php',
							type: 'POST',
							data: str,
							dataType: 'JSON',
							success: function(data){
								var new_id = data['id'];
								var type = data['type'];
								if(id)
									edit_reservation(new_id);
								else{
									show_alert_popup('Ошибка! Возможно, вы пытаетесь выбрать даты, которые уже заняты.');
									tr.find('.ui-selected').removeClass('ui-selected');
								}
							}
						});
					}else
						tr.find('.ui-selected').removeClass('ui-selected');
				}else if(clear == 1){
					show_alert_popup('Ошибка! Возможно, вы пытаетесь выбрать даты, которые заняты или недоступны.');
					$(this).find('*').removeClass('ui-selected');
				}else
					$(this).find('*').removeClass('ui-selected');
			}
		});
	});
	$('.div-calendar').mousewheel(function(event, delta){
		this.scrollLeft-= (delta * 30);
		event.preventDefault();
	});
	$('.room-block-'+id+' .div-calendar').scroll(function(){
		var width = $('.room-block-'+id+' .div-calendar')[0].scrollWidth;
		var left = $('.room-block-'+id+' .div-calendar').scrollLeft() + $('.room-block-'+id+' .div-calendar').width() + 25;
		if(width <= left)
			append_calendar_room(id);
	});
	var width = $('.room-block-'+id+' .div-calendar')[0].scrollWidth;
	var left = $('.room-block-'+id+' .div-calendar').scrollLeft() + $('.room-block-'+id+' .div-calendar').width() + 25;
	if(width <= left)
		append_calendar_room(id);
	activate_hover_info_select();
}

function append_calendar_room(id){
	var date = $('.room-block-'+id+' .append-td').attr('date');
	if(!date)
		return;
	$('.room-block-'+id+' .append-td').remove();
	var str = 'func=append_calendar_room&id=' + id + '&date=' + date;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		dataType: 'JSON',
		success: function(data){
			$('.room-block-'+id+' .head-calendar').append(data['head']);
			for(var index in data['room']){
				$('.room-block-'+id+' .tr-range.tr-'+index+'.head-tr').append(data['room'][index]['head']);
				$('.room-block-'+id+' .tr-range.tr-'+index+'.add-tr').append(data['room'][index]['add']);
				$('.room-block-'+id+' .tr-'+index+'.append-room-tr').append(data['room'][index]['append-tr']);
			}
			activate_hover_info_select();
		}
	});
}

function paint_reservation(id){
	var str = 'func=paint_reservation&id=' + id;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		dataType: 'JSON',
		success: function(data){
			$('.tr-'+data['room'] + ' .td-reserv[reserv='+id+']').css('background', data['color']);
		}
	});
}

function edit_reservation(id){
	var str = 'func=edit_reservation&id=' + id;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			show_modal(html);
		}
	});
}

function update_reservation(id){
	var id_reck = $('.add-note .id').val();
	var sum = $('.add-note .sum').val();
	var note = $('.add-note .note').val();
	var service_note = $('.add-note .service-note').val();
	var type_place = $('.add-note .type-place').val();
	var str = 'func=update_reservation&id=' + id + '&id_reck=' + id_reck + '&note=' + note + '&sum=' + sum + '&service_note=' + service_note + '&type_place=' + type_place;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(room){
			remove_all_windows();
			show_calendar_room(room);
		}
	});
}

function show_history_reservation(id){
	var str = 'func=show_history_reservation&id=' + id;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			show_modal(html);
		}
	});
}

function change_date_reservation(id){
	var str = 'func=change_date_reservation&id=' + id;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			show_modal(html);
		}
	});
}

function update_date_reservation(id){
	var date = $('.change-date .date').val();
	var day = $('.change-date .day').val();
	if(!date)
		show_warning('.change-date', 'Выберите дату заезда', false);
	else if(!day)
		show_warning('.change-date', 'Выберите количество дней', false);
	else{
		var str = 'func=update_date_reservation&id=' + id + '&date=' + date + '&day=' + day;
		$.ajax({
			url: 'mysql.php',
			type: 'POST',
			data: str,
			dataType: 'JSON',
			success: function(data){
				var room = data['room'];
				if(room){
					remove_all_windows();
					show_calendar_room(room);
				}else
					show_warning('.change-date', 'Ошибка! Возможно, вы пытаетесь выбрать даты, которые уже заняты.');
			}
		});
	}
}

function delete_reservation(id){
	if(confirm('Удалить заявку')){
		var str = 'func=delete_reservation&id=' + id;
		$.ajax({
			url: 'mysql.php',
			type: 'POST',
			data: str,
			dataType: 'JSON',
			success: function(data){
				var room = data['room'];
				var date = data['date'];
				if(room){
					remove_all_windows();
					show_calendar_room(room);
				}
			}
		});
	}
}

function deferred_reservation(id){
	var str = 'func=deferred_reservation&id=' + id;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(){
			edit_reservation(id);
		}
	});
}

function activate_hover_info_select(){
	$(".td-reserv").click(function(){
		var elem = $(this);
		var id = elem.attr('reserv');
		var str = 'func=show_info_window_calendar&id=' + id;
		$.ajax({
			url: 'mysql.php',
			type: 'POST',
			data: str,
			success: function(html){
				show_floating_div(elem, html);
			}
		});
	});
}

function upload_reservation_object(id){
	var str = 'func=upload_reserv_object_on_server&id=' + id;
	$.ajax({
		type: 'POST',
		data: str,
		url: 'mysql.php',
		success: function(html){
			$('#infa_object').html(html);
		}
	});
	show_loader_element('#infa_object');
}

function upload_object_price_on_server(id){
	show_loader_element('.object-infa');
	var str = 'func=upload_price_on_server&id=' + id;
	$.ajax({
		type: 'POST',
		data: str,
		url: 'mysql.php',
		success: function(html){
			//console.log(html); 
			if($('.object-infa-price-table').length) {
				$('.object-infa-price-table').html(html);
			}
			else {
				if($('.menu-upload.active').length)
				$('.object-infa').html(html);
			else if($('.menu-object').length)
				select_object_upload(id);
			else
				check_changes_cabinet_object();
			}
		}
	});
}

function edit_room_manager(id){
	var str = 'func=edit_room_manager&id=' + id;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			show_modal(html);
		}
	});
}

function update_room_manager(id){
	var name = $('.name-room').val();
	var main = $('.main-place').val();
	var add = $('.add-place').val();
	var note = $('.note-room').val();
	var food = $('.food-room').val();
	var square = $('.square-room').val();
	var housing = $('#housing-object').val();
	var comfort = select_checkbox('comfort', 'comfort');
	var best_comfort = select_checkbox('comfort', 'best-comfort');
	if(!name)
		show_warning('.edit-room', 'Укажите название номера', false);
	else{
		var str = 'func=update_room_manager&id=' + id + '&name=' + name + '&main=' + main + '&add=' + add + '&note=' + note + '&housing=' + housing + '&comfort=' + comfort + '&best_comfort=' + best_comfort + '&square=' + square + '&food=' + food;
		$.ajax({
			url: 'mysql.php',
			type: 'POST',
			data: str,
			success: function(room){
				remove_all_windows();
				$('.name-room-'+id).html(room);
			}
		});
	}
}

function add_new_room_manager(object){
	var str = 'func=add_new_room_manager&object=' + object;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			show_modal(html);
		}
	});
}

function save_new_room_manager(object){
	var name = $('.name-room').val();
	if(!name)
		show_warning('.new-room', 'Укажите название номера', false);
	else{
		var str = 'func=save_new_room_manager&object=' + object + '&name=' + name;
		$.ajax({
			url: 'mysql.php',
			type: 'POST',
			data: str,
			success: function(){
				remove_all_windows();
				view_object_rooms(object);
			}
		});
	}
}

function view_object_rooms(id){
	show_loader_element('#infa_object');
	$('.nav-object .btn-success').removeClass('btn-success');
	$('.nav-object .room-object').addClass('btn-success');
	var str = 'func=view_object_rooms&id=' + id;
	$.ajax({
		type: 'POST',
		data: str,
		url: 'mysql.php',
		success: function(html){
			$('#infa_object').html(html);
			$(".rooms").sortable({
				axis: 'y',
				delay: 150,
				handle: '.handle',
				update: function(){
					update_priority_room(id);
				}
			});
		}
	});
}

function update_priority_room(){
	var array = new Array();
	$('.rooms .list-group-item').each(function(){
		var room = $(this).attr('room');
		array.push(room);
	});
	var data = JSON.stringify(array);
	var str = 'func=update_priority_room&data=' + data;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			show_popup(html);
		}
	});
}






function show_form_search_engine_reservation(){
	$('.menu-object li').removeClass('active');
	$('.menu-object .li-search-reservation').addClass('active');
	var str = 'func=show_form_search_engine_reservation';
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			$('.data-object').html(html);
			jQuery('.date').datepicker({
				defaultDate: 'today',
				changeMonth: false,
				numberOfMonths: 2,
				minDate: 'today'
			});
		}
	});
}

function search_engine_reservation(){
	var date = $('.search-form .date').val();
	var days = $('.search-form .days').val();
	var object = $('.id-object').attr('name');
	if(!date)
		show_warning('.search-form', 'Укажите дату заезда');
	else{
		$('.search-form .btn-search').button('loading');
		var str = 'func=search_engine_reservation&date=' + date + '&days=' + days + '&object=' + object;
		$.ajax({
			url: 'mysql.php',
			type: 'POST',
			data: str,
			success: function(html){
				$('.search-form .btn-search').button('reset');
				$('.search-result').html(html);
			}
		});
	}
}

function show_form_booking_reservation(room){
	var str = 'func=show_form_booking_reservation&room=' + room;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			show_modal(html);
			$('.booking-arrival .date').html($('.search-form .date').val());
			$('.booking-arrival .day').html($('.search-form .days').val());
		}
	});
}

function booking_reservation(room){
	var date = $('.search-form .date').val();
	var days = $('.search-form .days').val();
	var surname = $('.booking-reservation .surname').val();
	var name = $('.booking-reservation .name').val();
	var otch = $('.booking-reservation .otch').val();
	var email = $('.booking-reservation .email').val();
	var telephone = $('.booking-reservation .telephone').val();
	var address = $('.booking-reservation .address').val();
	var price = $('.booking-reservation .price').val();
	var type = $('.booking-reservation .type').val();
	var number = $('.booking-reservation .number').val();
	if(!surname)
		show_warning('.booking-reservation', 'Укажите фамилию туриста', false);
	else if(!name)
		show_warning('.booking-reservation', 'Укажите имя туриста', false);
	else if(!price)
		show_warning('.booking-reservation', 'Укажите цену', false);
	else if(!number)
		show_warning('.booking-reservation', 'Укажите количество', false);
	else{
		$('.btn-update').button('loading');
		var str = 'func=booking_reservation&room=' + room + '&date=' + date + '&days=' + days + '&surname=' + surname + '&name=' + name + '&otch=' + otch + '&email=' + email + '&telephone=' + telephone + '&address=' + address + '&price=' + price + '&type=' + type + '&number=' + number;
		$.ajax({
			url: 'mysql.php',
			type: 'POST',
			data: str,
			success: function(client){
				if(!client)
					show_warning('.booking-reservation', 'Ошибка сохранения', false);
				else
					select_klient(client);
			}
		});
	}

}

function edit_object_sync_info(id){
	var str = 'func=edit_object_sync_info&id=' + id;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			show_modal(html);
		}
	});
}

function update_object_sync_info(id){
	var full = $('.edit-object .full-name-object').val();
	var code = $('.edit-object .inn-object').val();
	var inn = $('.edit-object .code-1C').val();
	var nomenclature = $('.edit-object .nomenclature').val();
	var login = $('.edit-object .bank-login').val();
	if(!code || !inn)
		show_warning('.edit-object', 'Укажите правильно поля кода и ИНН', false);
	else{
		var str = 'func=update_object_sync_info&id=' + id + '&full=' + full + '&code=' + code + '&inn=' + inn + '&nomenclature=' + nomenclature + '&login=' + login;
		$.ajax({
			url: 'mysql.php',
			type: 'POST',
			data: str,
			success: function(){
				view_object(id);
			}
		});
	}
}

function view_all_commission_object(){
	$('.menu-object li').removeClass('active');
	$('.menu-object .li-commission').addClass('active');
	var str = 'func=view_all_commission_object';
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		dataType: 'JSON',
		success: function(data){
			var disabled = ' disabled ';
			if(data['right'] == 1)
				disabled = '';
			var html = '<div class="form-horizontal list-group">';
			for(var index in data['region']){
				var region = data['region'][index];
				html+= '<div class="list-group-item"><div class="form-group form-group-margin"><div class="col-sm-4"><strong class="pointer" onclick="$(\'.region-objects-' +index+ '\').toggle()">' +region['name']+ '</strong></div><div class="col-sm-2">Вознаграждение объектов</div><div class="col-sm-2">Вознаграждение агентству</div><div class="col-sm-4">Примечание</div></div></div><div class="region-objects-' +index+ '" style="display: none">';
				for(var id in region['object']){
					var object = region['object'][id];
					html+= '<div class="list-group-item list-hover-item object-' +id+ '"><div class="form-group form-group-margin"><div class="col-sm-4">' +object['name']+ '</div><div class="col-sm-2"><input type="text" class="form-control reward-value" value="' +object['reward']+ '" onchange="update_commission_object(' +id+ ')" ' +disabled+ ' /></div><div class="col-sm-2"><input type="text" class="form-control regular-value" value="' +object['commis']+ '" onchange="update_commission_object(' +id+ ')" ' +disabled+ ' /></div><div class="col-sm-4"><textarea class="form-control note-reward-value" onchange="update_commission_object(' +id+ ')">'+object['note_reward']+'</textarea></div></div></div>';
				}
				html+= '</div>';
			}
			html+= '</div>';
			$('.data-object').html(html);
		}
	});
	show_loader_element('.data-object');
}

function update_commission_object(object){
	var regular = $('.object-'+object+' .regular-value').val();
	var reward = $('.object-'+object+' .reward-value').val();
	var note_reward = $('.object-'+object+' .note-reward-value').val();
	var str = 'func=update_commission_object&object=' + object + '&regular=' + regular + '&reward=' + reward+'&note_reward='+note_reward;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		dataType: 'JSON',
		success: function(data){
			$('.object-'+object+' .label-status').html('Сохранено');
			$('.object-'+object+' .regular-value').val(data['regular_com']);
			$('.object-'+object+' .reward-value').val(data['reward']);
			$('.object-'+object+' .note-reward-value').val(data['note_reward']);
		}
	});
}


function reestablish_price_date(){
	var date = $('.id-date-price').val();
	var str = 'func=reestablish_price_date&date=' + date;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(object){
			view_dates_price_object(object);
		}
	});
}

function delete_date_price_manager(id){
	var str = 'func=delete_date_price_manager&id=' + id;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(object){
			remove_all_windows();
			view_dates_price_object(object);
		}
	});
}






function select_objects_quota(){
	var str = 'func=select_objects_quota';
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		dataType: 'JSON',
		success: function(data){
			var body = '<div class="list-group"><div class="list-group-item list-group-item-info">Всего подключенных объектов: ' +data['info']['all']+ '</div>';
			body+= '<div class="list-group-item list-group-item-info">Объектов с квотой: ' +data['info']['quota']+ '</div></div>';
			var region = '';
			for(var ind in data['object']){
				var object = data['object'][ind];
				if(region == '' || region != object['region']){
					region = object['region'];
					body+= '<div class="list-group-item list-hover-item list-group-item-success" onclick="$(\'.region-quota-' +region+ '\').toggle()">' +data['region'][region]['name'] + '<button class="btn btn-success pull-right"><i class="fa fa-list-ol"></i> ' +data['region'][region]['count']+ '</button><div class="clearfix"></div></div>';
				}
				var index = data['object'][ind]['id'];
				body+= '<div class="list-group-item region-quota-' +region+ '" style="display: none"><div class="row"><div class="col-sm-4">' +object['name']+ '<address><i class="fa fa-map-marker"></i> ' +object['address']+ '</address></div><div class="col-sm-2"><button class="btn btn-link" onclick="view_object(' +index+ ')"><i class="fa fa-angle-double-right"></i> Описание</button></div><div class="col-sm-2">';
				if(object['contract'] != ''){
					var contract = 'Договор санатория';
					if(object['contract'] == 'sanata')
						contract = 'Договор Саната';
					body+= '<i class="fa fa-file-text-o text-success fa-2x" data-toggle="tooltip" title="' +contract+ '"></i> ';
				}else
					body+= '<i class="fa fa-file-text-o text-danger fa-2x" data-toggle="tooltip" title="Договор не указан"></i> ';

				//if(object['id'] == 57) alert(object['check-places']);

				if(object['check-places'] == 1)
					body+= '<i class="fa fa-text-width fa-2x" data-toggle="tooltip" title="Выгрузка через Travelline"></i>';
				else if(object['check-places'] == 2)
					body+= '<i class="fa fa-check-square fa-2x" data-toggle="tooltip" title="Выгрузка через наш канал"></i>';
				else if(object['check-places'] == 3)
          body+= '<i class="fa fa-product-hunt fa-2x" data-toggle="tooltip" title="Выгрузка через Profkurort"></i>';

				if(object['have-places'] == 1)
					body+= ' <i class="fa fa-calendar-check-o fa-2x text-success" data-toggle="tooltip" title="Доступные места для бронирования"></i>';
				body+= '</div><div class="col-sm-4 text-right">';
				if(object['have-places'] == 1)
					body+= '<button class="btn btn-success" onclick="show_object_qouta_params(' +index+ ')"><i class="fa fa-calendar-check-o"></i> Смотреть квоту мест</button>';
				else
					body+= '<div class="alert alert-danger pull-right"><i class="fa fa-calendar-times-o"></i> Квоты нет</div>';
				body+= '</div></div></div>';
			}
			if(body == '')
				body = '<div class="list-group-item list-group-item-info">Объектов не найдено</div>';
			var html = '<div class="list-group">' +body+ '</div>';
			$('.object-qouta-reserv').html(html);
			$('[data-toggle="tooltip"]').tooltip({ html: true, container: 'body', placement: 'bottom' });
		}
	});
	show_loader_element('.object-qouta-reserv');
}

function show_quota_object_card(object){
	var html = '<div class="modal fade"><div class="modal-dialog modal-giant"><div class="modal-content"><div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-label="Close"><i class="fa fa-times"></i></button><button class="close hide-button-modal" type="button" name="Квота санатория" type="quota-bid"><i class="fa fa-window-minimize"></i></button><h4 class="modal-title">Бронирование из квоты мест</h4></div><div class="modal-body form-horizontal"><div class="data-object"></div></div></div></div></div>';
	show_modal(html);
	show_object_qouta_params(object);
}

function show_object_qouta_params(object){
	var date = new Date();
	var current_month = parseInt(date.getMonth()) + 1;
	var current_year = parseInt(date.getFullYear());
	var date_select = '';
	for(var id = current_month; id <= 12; id++){
		var date_value = id + '-' + current_year;
		var text = month[parseInt(id)] + ' ' + current_year;
		date_select+= '<option value="' +date_value+ '">' +text+ '</option>';
	}
	current_year++;
	for(var id = 1; id <= current_month; id++){
		var date_value = id + '-' + current_year;
		var text = month[parseInt(id)] + ' ' + current_year;
		date_select+= '<option value="' +date_value+ '">' +text+ '</option>';
	}
	var html = '<div class="form-horizontal"><div class="form-group"><div class="col-sm-2"><button class="btn btn-sm btn-default btn-block btn-prev-availability-calendar" disabled="disabled"><i class="fa fa-angle-double-left"></i></button></div><div class="col-sm-4"><select class="form-control date-select-quota">' +date_select+ '</select></div><div class="col-sm-2"><button class="btn btn-sm btn-default btn-block btn-next-availability-calendar"><i class="fa fa-angle-double-right"></i></button></div><div class="col-sm-4"><button class="btn btn-sm btn-success btn-block btn-show-availability-calendar" onclick="view_quota_object(' +object+ ')"><i class="fa fa-calendar-check-o"></i> Показать доступные номера</button></div></div></div><div class="quota-object"></div>';
	if($('.modal .data-object').length)
		$('.modal .data-object').html(html);
	else
		$('.data-object').html(html);

	$('.btn-prev-availability-calendar').click(function(){
		if($('.date-select-quota option:selected').prev().length){
			var value = $('.date-select-quota option:selected').prev().val();
			$('.date-select-quota').val(value);
		}else
			$(this).attr('disabled', 'disabled');
		view_quota_object(object);
	});

	$('.btn-next-availability-calendar').click(function(){
		if($('.date-select-quota option:selected').next().length){
			var value = $('.date-select-quota option:selected').next().val();
			$('.date-select-quota').val(value);
		}else
			$(this).attr('disabled', 'disabled');
		view_quota_object(object);
	});

	view_quota_object(object);
}

function view_quota_object(object){
	show_loader_element('.quota-object');
	var date = $('.date-select-quota').val();
	var str = 'func=view_quota_object&object=' + object + '&date=' + date;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		dataType: 'JSON',
		success: function(data){
			var body = '',
			label_type_quota = '';

			switch(+data['type']) {
				case 2:
					label_type_quota = 'Квота из системы САНАТА';
				break;
				case 3:
					label_type_quota = 'Квота из системы ПРОФКУРОРТ';
				break;
				default:
					label_type_quota= 'Квота из Travelline';
			}

			console.log(data);
      		var object_name = data['object-name'];
      		var check_bid = $('.quota-object-bid').length;

			if(0) {

			} else {
	        for(var room in data['room']){
	        	var max_quota = data['room'][room]['max-quota'];
	          	var quota = data['room'][room]['quota'];
	          	var table = '';
	          	for(tr = 1; tr <= max_quota; tr++){

	            	table+= '<tr class="tr-range" room="' +room+ '">';
	            	for(var month in data['room'][room]['quota']) {
						if(month.length === 1) {
							continue;
						}

	            		var month_quota = data['room'][room]['quota'][month];
	            		console.log('month_quota');
	            		console.log(month_quota);
	              		for(var day in month_quota) {
	              			var class_td = ' no-accessible-place ';
	                		var prices = '';
	                		var name_prices = '';
	                		if(quota[month][day]['quota'] >= tr) {
	                  			class_td = ' accessible-place ';
	                  			prices = quota[month][day]['price'];
	            			}
		                	var price_label = '';
		                	var price_label_array = new Object();
	                		for(var ratePlanId in prices) {
			                  	if(ratePlanId in data['ratePlan']) {

	                    			if(price_label != '') price_label+= '<hr />';

	                    			price_label+= '<div>' +data['ratePlan'][ratePlanId]['name']+ '</div>';
	                    			price_label_array[ratePlanId] = new Object();
	                				for(var index in prices[ratePlanId]['price']) {

	                					if(prices[ratePlanId]['name'] !== undefined) name_prices = prices[ratePlanId]['name'];

					                    var price_ratePlan = prices[ratePlanId]['price'][index];

	                      				if(price_ratePlan > 0) {

	                      					var priceTypesAr = [
	                      						null,
												'за чел/сутки',
												'за дом/сутки',
												'за номер/сутки',
												'за заезд'
											];

					                        var label = '';
					                        var type_place = 1;
					                        var type_range = 1;
					                        var label_place = 'за чел/сутки';


											if(data['is_profkurort']) {
	                          					if(index == 0) {
						                            label = 'размещение';
						                            type_range = 3;
						                            label_place = 'за номер';
	                          					} else if(index == 1) {
						                            label = 'Место';
						                            type_place = 2;
	                          					} else if(index == 2) {
	                            					label = 'Доп. место';
	                            					type_place = 2;
	                      						} else if(index == 3) {
	                            					label = 'Детское место';
	                            					type_place = 2;
	                          					} else if(index == 4) {
						                            label = 'Доп. детское место';
						                            type_place = 2;
	                          					}
	                    					} else {
												if(name_prices != '' && name_prices[index] !== undefined) {
													label = name_prices[index]['n'];
													type_place = name_prices[index]['t'];
													type_range = name_prices[index]['p'];
													if(type_range == 2 || type_range == 3) {
														label_place = 'за номер';
														if('default_price_type' in data) {
															var default_price_type = parseInt(data['default_price_type']);
															if (priceTypesAr.length > default_price_type && default_price_type !== 0) {
																type_range = default_price_type;
																label_place = priceTypesAr[default_price_type];
															}
														}
													}
												} else if(index != 'add') {
													label = index + '-местное размещение';
													type_range = 3;
													label_place = 'за номер';

													if('default_price_type' in data) {
														var default_price_type = parseInt(data['default_price_type']);
														if(priceTypesAr.length > default_price_type && default_price_type !== 0) {
															type_range = default_price_type;
															label_place = priceTypesAr[default_price_type];
														}
													}

													} else if(index == 'add') {
							                            label = 'Доп.место';
							                            type_place = 2;
						                          	}
	                        				}

					                        price_label+= '<div>' + label + ' ' + price_ratePlan + ' ' + label_place + '</div>';
					                        var price_place = new Object();
					                        price_place['v'] = price_ratePlan;
					                        price_place['n'] = label;
					                        price_place['t'] = type_place;
					                        price_place['p'] = type_range;
					                        price_label_array[ratePlanId][index] = price_place;
	                      				}
	                    			}
								}
	                		}
	                		var date = quota[month][day]['date'];
	                		var price_label_json = JSON.stringify(price_label_array);
	                		table+= '<td class="' +class_td+ '" title="' +price_label+ '" price=\'' +price_label_json+ '\' date="' +date+ '" data-toggle="tooltip">' +day+ '</td>';
	                		console.log('day='+day);
	              		}
	        		}
	          	}
	          	if(table != ''){
		            body+= '<div class="list-group-item room-calendar" room="' +room+ '" main="' +data['room'][room]['main']+ '" add="' +data['room'][room]['add']+ '"><h4 class="list-group-item-heading"><i class="fa fa-codepen"></i> <span class="room-name">' + data['room'][room]['name'] + '</span></h4><div class="quota-room-container quota-room-container-' +room+ '"><table class="table table-bordered table-condensed">' + table + '</table></div>';
	            	body+= '<div class="text-right" style="margin-top: 10px"><button class="btn btn-success btn-show-booking-form" disabled="disabled" onclick="show_booking_quota_form(' +room+ ')"><i class="fa fa-shopping-cart"></i> Забронировать</button> <button class="btn btn-default" onclick="$(\'.ui-selected\').removeClass(\'ui-selected\'); $(\'.btn-show-booking-form\').attr(\'disabled\', \'disabled\');"><i class="fa fa-paint-brush"></i> Очистить</button></div>';
	            	body+= '</div>';
	          	}
	        }

	        var add_booking_select = '';
	        if(check_bid == 0) {
	          	for(var bid in data['bid']){
		            var bid_info = data['bid'][bid];
	            	add_booking_select+= '<option value="' +bid+ '">Заявка №' +bid+ ' заезд ' +bid_info['date']+ '</option>';
	          	}
	        } else {
	          	var bid = $('.quota-object-bid').attr('bid');
	          	add_booking_select = '<option value="' +bid+ '">Заявка №' +bid+ '</option>';
	        }

	        if(body == '') body = '<div class="list-group-item list-group-item-info">Квоты номеров не найдено</div>';
	        var html = '<h3 class="text-danger">' +object_name+ ' [' +label_type_quota+ ']</h3><div class="list-group id-object" object="' +object+ '">' +body+ '</div>';

	        var new_booking = '';

	        var addTuristButton = '';
	        var profkurortRequired = '';

	        if(!data['is_profkurort']) {
	        	addTuristButton = '<button class="btn btn-primary" onclick="add_turist_booking_quota_form()">Добавить туриста</button> ';
			} else {
	          	profkurortRequired = ' form-control-required';
			}

        	if(check_bid == 0)
		        new_booking = '<div class="list-group new-booking-type-form booking-type-form" data-is-profkurort="'+data['is_profkurort']+'"><div class="list-group-item turist-info turist-info-head" head="1"><div class="form-group"><div class="col-sm-4"><input type="text" class="form-control surname-turist form-control-required" placeholder="Фамилия" /></div><div class="col-sm-4"><input type="text" class="form-control name-turist form-control-required" placeholder="Имя" /></div><div class="col-sm-4"><input type="text" class="form-control otch-turist'+profkurortRequired+'" placeholder="Отчество" /></div></div><div class="form-group"><div class="col-sm-4"><select class="form-control sex-turist'+profkurortRequired+'">' +
									'<option value="-1" selected="">Укажите пол</option>' +
									'<option value="0">Мужской</option>' +
									'<option value="1">Женский</option>' +
	          				    '</select></div>' +
								'<div class="col-sm-4">' +
									'<select class="form-control agecode-turist'+profkurortRequired+'">' +
										'<option value="0">Взрослый или ребенок?</option>' +
	              						'<option value="1">Взрослый</option>' +
	              						'<option value="2">Ребенок</option>' +
									'</select>'+
								'</div>'+
								'<div class="col-sm-4"><input type="text" class="form-control email-turist" placeholder="Email" /></div></div><div class="form-group form-group-margin"><div class="col-sm-4"><input type="text" class="form-control telephone-turist" placeholder="Телефон" /></div></div></div><div class="list-group-item text-right">'+addTuristButton+'<button class="btn btn-success btn-booking-room" onclick="booking_quota_room()"><i class="fa fa-cart-plus"></i> Забронировать</button> <button class="btn btn-default" onclick="$(\'.room-calendar\').show(); $(\'.booking-form\').addClass(\'hidden\');"><i class="fa fa-times"></i> Отмена</button></div></div>';

	        var add_booking = '<div class="list-group add-booking-type-form booking-type-form hidden"><div class="list-group-item"><div class="form-group form-group-margin"><div class="col-sm-4">Выбрать заявку</div><div class="col-sm-8"><select class="form-control select-bid-booking">' +add_booking_select+ '</select></div></div></div><div class="list-group-item text-right"><button class="btn btn-success btn-booking-room" onclick="booking_quota_room_add_bid()"><i class="fa fa-cart-plus"></i> Забронировать</button> <button class="btn btn-default" onclick="$(\'.room-calendar\').show(); $(\'.booking-form\').addClass(\'hidden\');"><i class="fa fa-times"></i> Отмена</button></div></div>';

	        html+= '<div class="form-horizontal booking-form hidden"><ul class="nav nav-tabs nav-booking-type">';
	        if(check_bid == 0)
	          	html+= '<li class="active new-booking-quota" onclick="change_type_booking_quota_form(\'new\')"><a>Создать новую заявку</a></li>';
	        html+= '<li class="add-booking-quota" onclick="change_type_booking_quota_form(\'add\')"><a>Добавить к созданной заявке</a></li></ul>';

	        html+= '<div class="list-group rooms-info" style="margin: 10px 0"></div>' + new_booking + add_booking;

	        $('.quota-object').html(html).attr('ratePlan', JSON.stringify(data['ratePlan']));

	        $('[data-toggle="tooltip"]').tooltip({ html: true, container: '.data-object', placement: 'top', template: '<div class="tooltip" role="tooltip"><div class="tooltip-arrow"></div><div class="tooltip-inner" style="max-width: 400px"></div></div>' });

			$('.tr-range').each(function(){
				var room = $(this).attr('room');
				$(this).selectable({
					start: function(){
						$('.quota-room-container-'+room+' td').removeClass('ui-selected');
					},
					stop: function(event, ui){
						if ($('.tr-range .ui-selected.no-accessible-place').length){
							$('.ui-selected').removeClass('ui-selected');
							$('.btn-show-booking-form').attr('disabled', 'disabled');
						} else
							$('.btn-show-booking-form').removeAttr('disabled');
					}
				});
			});

        	if($('.date-select-quota option:selected').prev().length)
          		$('.btn-prev-availability-calendar').removeAttr('disabled');
        	if($('.date-select-quota option:selected').next().length)
          		$('.btn-next-availability-calendar').removeAttr('disabled');
		}
	}
	});
}

function add_turist_booking_quota_form(){
	var html = '<div class="list-group-item"><div class="form-group form-group-margin turist-info"><div class="col-sm-4"><input type="text" class="form-control surname-turist" placeholder="Фамилия" /></div><div class="col-sm-4"><input type="text" class="form-control name-turist" placeholder="Имя" /></div><div class="col-sm-4"><input type="text" class="form-control otch-turist" placeholder="Отчество" /></div></div></div>';
	$('.turist-info-head').after(html);
}

function change_type_booking_quota_form(type){
	$('.nav-booking-type li').removeClass('active');
	$('.nav-booking-type li.'+type+'-booking-quota').addClass('active');
	$('.booking-type-form').addClass('hidden');
	$('.'+type+'-booking-type-form').removeClass('hidden');
}

function show_booking_quota_form(room){
	var html = '';
	var ratePlans = JSON.parse($('.quota-object').attr('ratePlan'));
	var name = $('.quota-room-container-'+room).parent().find('.room-name').html();
	$('.quota-room-container-'+room+' .tr-range').each(function(){
		if($(this).find('.ui-selected').length){
			var date = $(this).find('.ui-selected:first').attr('date');
			var price = $(this).find('.ui-selected:first').attr('price');
			var days = $(this).find('.ui-selected').length;
			console.log(date);
			console.log(price);
			console.log(days);
			var value_price = { main: 0, add: 0 };
			var prices_array = JSON.parse(price);
			var price_label_all = '';
			var select_label = '';
			for(var ratePlan in prices_array){
				var name_rate_plan = ratePlans[ratePlan]['name'];
				var price_label_main = '';
				var price_label_add = '';
				select_label+= '<option value="' +ratePlan+ '">' +name_rate_plan+ '</option>';
				for(var index in prices_array[ratePlan]){
					var type_range_label = 'за чел/сутки';
					var prices_data = prices_array[ratePlan][index];
					var type_range = prices_data['p'];
					if(type_range == 3)
						type_range_label = 'за номер/сутки';
					if(prices_data['t'] == 1)
						price_label_main+= '<div class="booking-place booking-place-main"><label><input type="radio" name="main-place-booking" data-price-index="'+index+'"/> <span class="text-danger value-price">' + prices_data['v'] + '</span> <span class="name-price">' + prices_data['n'] + '</span> <span class="type-place" type="' +type_range+ '">' +type_range_label+ '</span></label></div>';
					else
						price_label_add+= '<div class="booking-place booking-place-add"><label><input type="radio" name="add-place-booking" data-price-index="'+index+'"/> <span class="text-danger value-price">' + prices_data['v'] + '</span> <span class="name-price">' + prices_data['n'] + '</span> <span class="type-place" type="' +type_range+ '">' +type_range_label+ '</span></label></div>';
				}
				var class_price = '';
				if(price_label_all != '')
					class_price = 'hidden';
				price_label_all+= '<div class="booking-quota-prices booking-quota-prices-' +ratePlan+ ' ' +class_price+ '">' + price_label_main + ' <hr /> ' + price_label_add + '</div>';
			}
			var label = name + '. Заезд ' + date + ' на ' + days + ' дней';
			html+= '<div class="list-group-item"><div class="form-group form-group-margin room-info" arrival="' +date+ '" days="' +days+ '" room="' +room+ '"><div class="col-sm-4 h4">' +label+ '</div><div class="col-sm-8"><p><select class="form-control check-rate-plan-booking">' +select_label+ '</select></p>' +price_label_all+ '</div></div></div>';
			$('.rooms-info').html(html);
			$('.booking-quota-prices label:first').trigger('click');
		}
	});
	if($('.new-booking-quota').length)
		change_type_booking_quota_form('new');
	else
		change_type_booking_quota_form('add');
	$('.check-rate-plan-booking').change(function(){
		var ratePlan = $(this).val();
		$('.booking-quota-prices').addClass('hidden');
		$('.booking-quota-prices-'+ratePlan).removeClass('hidden').find('label:first').trigger('click');
	});
	$('.room-calendar').hide();
	$('.booking-form').removeClass('hidden');
}

function booking_quota_room(){
	var check = 0;
	var data = new Object();
	var room = new Array();
	var turist = new Array();
	var id_room = $('.room-info').attr('room');
	var arrival = $('.room-info').attr('arrival');
	var days = $('.room-info').attr('days');
	var ratePlan = $('.check-rate-plan-booking').val();

	$('.booking-quota-prices-' +ratePlan+ ' .booking-place').each(function(){
		if($(this).find('input').prop('checked')){
			var array = new Object();
			array['room'] = id_room;
			array['arrival'] = arrival;
			array['days'] = days;
			array['note'] = $(this).find('.name-price').html();
			array['number'] = 1;
			array['price'] = $(this).find('span.value-price').html();
			array['price-index'] = $(this).find('span.value-price').closest('label').find('input').attr('data-price-index');
			array['type'] = $(this).find('span.type-place').attr('type');
			array['ratePlan'] = 0;
			if($(this).hasClass('booking-place-main'))
				array['ratePlan'] = ratePlan;
			room.push(array);
		}
	});
	$('.turist-info').each(function(){
		var array = new Object();
		array['head'] = 0;
		array['name'] = $(this).find('.name-turist').val();
		array['surname'] = $(this).find('.surname-turist').val();
		array['otch'] = $(this).find('.otch-turist').val();
		array['email'] = '';
		array['telephone'] = '';
		array['sex'] = parseInt($(this).find('.sex-turist').val());
		array['agecode'] = parseInt($(this).find('.agecode-turist').val());
		if($(this).attr('head') == 1){
			array['head'] = 1;
			array['email'] = $(this).find('.email-turist').val();
			array['telephone'] = $(this).find('.telephone-turist').val();;
		}
		if(array['name'] && array['surname'])
			turist.push(array);
		if(array['name'] && array['surname'] && (array['otch'] || !$(this).find('.otch-turist').hasClass('form-control-required')) && array['head'] == 1 && (array['sex'] !== -1 || !$(this).find('.sex-turist').hasClass('form-control-required')) && (array['agecode'] > 0 || !$(this).find('.agecode-turist').hasClass('form-control-required')))
			check = 1;
	});
	if(check == 1 && room.length > 0){
		data['object'] = $('.id-object').attr('object');
		data['room'] = room;
		data['turist'] = turist;
		data = JSON.stringify(data);
		var str = 'func=booking_quota_room&data=' + data;
		$.ajax({
			url: 'mysql.php',
			type: 'POST',
			data: str,
			dataType: 'JSON',
			success: function(client){
				select_klient(client);
			}
		});
	}else
		alert('Укажите данные туриста');
}

function booking_quota_room_add_bid(){
	if($('.select-bid-booking').length)
		var bid = $('.select-bid-booking').val();
	else
		var bid = $('.quota-object-bid').attr('bid');
	var room = new Array();

	var id_room = $('.room-info').attr('room');
	var arrival = $('.room-info').attr('arrival');
	var days = $('.room-info').attr('days');
	var ratePlan = $('.check-rate-plan-booking').val();

	$('.booking-quota-prices-' +ratePlan+ ' .booking-place').each(function(){
		if($(this).find('input').prop('checked')){
			var array = new Object();
			array['room'] = id_room;
			array['arrival'] = arrival;
			array['days'] = days;
			array['note'] = $(this).find('.name-price').html();
			array['number'] = 1;
			array['price'] = $(this).find('span.value-price').html();
			array['type'] = $(this).find('span.type-place').attr('type');
			array['ratePlan'] = 0;
			if($(this).hasClass('booking-place-main'))
				array['ratePlan'] = ratePlan;
			room.push(array);
		}
	});
	if(bid && room.length > 0){
		data = JSON.stringify(room);
		var str = 'func=booking_quota_room_add_bid&room=' + data + '&bid=' + bid;
		$.ajax({
			url: 'mysql.php',
			type: 'POST',
			data: str,
			dataType: 'JSON',
			success: function(client){
				remove_all_windows();
				if($('.quota-object-bid').length)
					view_schet($('.quota-object-bid').attr('bid'));
				else
					select_klient(client);
			}
		});
	}else
		alert('Укажите заявку');
}

function add_new_contract_object(object){
	var html = '<div class="modal fade"><div class="modal-dialog"><div class="modal-content"><div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-label="Close"><i class="fa fa-times"></i></button><h4 class="modal-title">Добавить новый договор</h4></div><div class="modal-body form-horizontal new-object-contract"><div class="form-group"><label class="col-sm-4 control-label">Форма договора</label><div class="col-sm-8"><select class="form-control type-contract"><option value="object">Договор санатория</option><option value="sanata">Договор Саната</option></select></div></div><div class="form-group"><label class="col-sm-4 control-label">Действует до</label><div class="col-sm-8"><input type="text" class="form-control datepicker" id="date-contract" /></div></div><div class="form-group form-group-margin"><label class="col-sm-4 control-label">Номер договора</label><div class="col-sm-8"><input type="text" class="form-control number-contract" /></div></div></div><div class="modal-footer"><button class="btn btn-success btn-sm" onclick="save_new_contract_object(' +object+ ')"><i class="fa fa-check-circle"></i> Сохранить</button></div></div></div></div>';
	show_modal(html);
	show_datepicker();
}

function save_new_contract_object(object){
	var type = $('.new-object-contract .type-contract').val();
	var date = $('.new-object-contract #date-contract').attr('date');
	var number = $('.new-object-contract .number-contract').val();
	if(!date)
		show_warning('.new-object-contract', 'Укажите дату', false);
	else{
		remove_all_windows();
		var str = 'func=save_new_contract_object&object=' + object + '&type=' + type + '&date=' + date + '&number=' + number;
		$.ajax({
			url: 'mysql.php',
			type: 'POST',
			data: str,
			dataType: 'JSON',
			success: function(data){
				var html = '<div class="contract-object-' +data['id']+ '">' +data['html']+ '</div>';
				$('.contracts-object').append(html);
			}
		});
	}
}

function edit_contract_object(id){
	var str = 'func=edit_contract_object&id=' + id;
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
			var html = '<div class="modal fade"><div class="modal-dialog"><div class="modal-content"><div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-label="Close"><i class="fa fa-times"></i></button><h4 class="modal-title">Изменить договор</h4></div><div class="modal-body form-horizontal edit-contract"><div class="form-group"><label class="col-sm-4 control-label">Форма договора</label><div class="col-sm-8"><select class="form-control type-contract"><option value="object" ' +select[1]+ '>Договор санатория</option><option value="sanata" ' +select[2]+ '>Договор Саната</option></select></div></div><div class="form-group"><label class="col-sm-4 control-label">Действует до</label><div class="col-sm-8"><input type="text" class="form-control datepicker" id="date-contract" value="' +data['date']+ '" date="' +data['date']+ '" /></div></div><div class="form-group form-group-margin"><label class="col-sm-4 control-label">Номер договора</label><div class="col-sm-8"><input type="text" class="form-control number-contract" value="' +data['number']+ '" /></div></div></div><div class="modal-footer"><button class="btn btn-success btn-sm" onclick="update_contract_object(' +id+ ')"><i class="fa fa-check-circle"></i> Сохранить</button></div></div></div></div>';
			show_modal(html);
		}
	});
}

function update_contract_object(id){
	var type = $('.edit-contract .type-contract').val();
	var date = $('.edit-contract #date-contract').attr('date');
	var number = $('.edit-contract .number-contract').val();
	if(!date)
		show_warning('.edit-contract', 'Укажите дату', false);
	else{
		remove_all_windows();
		var str = 'func=update_contract_object&id=' + id + '&type=' + type + '&date=' + date + '&number=' + number;
		$.ajax({
			url: 'mysql.php',
			type: 'POST',
			data: str,
			dataType: 'JSON',
			success: function(html){
				$('.contract-object-'+id).html(html);
			}
		});
	}
}

function update_status_contract_object(id, status){
	var str = 'func=update_status_contract_object&id=' + id + '&status=' + status;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		dataType: 'JSON',
		success: function(html){
			$('.contract-object-'+id).html(html);
		}
	});
}

$(document).on('change','.edit-object #direction-object',function (e) {
	var $obj = $(this);
	var direction = parseInt($obj.val());
	var $form = $obj.closest('.edit-object');
	var $object_region = $form.find('#object_region');
	var $object_regionFormG = $object_region.closest('.form-group');
	var object_region = parseInt($object_region.val());

	var $object_region_direction = $form.find('#object_region_direction');
	var $object_region_directionFormG = $object_region_direction.closest('.form-group');
	var object_region_direction = parseInt($object_region_direction.val());
	$object_region_directionFormG.addClass('hidden');
	$object_region_direction.val(0);
	$object_region_direction.find('*[value!="0"]').remove();

	$object_region.val(0);
	if(direction === 0) {
		$object_regionFormG.addClass('hidden');
		object_region = 0;
		$object_region.find('*[value!="0"]').remove();
	}
	else {
		$.ajax({
			type: 'GET',
			data: {
				func: 'get_regions_options',
				direction_id: direction
			},
			dataType: 'html',
			url: 'mysql.php',
			success: function (data) {
				$object_region.html(data);
				$object_regionFormG.removeClass('hidden');
			}
		});
	}
});

$(document).on('change','.edit-object #object_region',function (e) {
	var $obj = $(this);
	var region = parseInt($obj.val());
	var $form = $obj.closest('.edit-object');
	var $object_region_direction = $form.find('#region_direction_id');
	var $object_region_directionFormG = $object_region_direction.closest('.form-group');
	var object_region_direction = parseInt($object_region_direction.val());
	$object_region_direction.val(0);
	$object_region_direction.find('*[value!="0"]').remove();
	$object_region_directionFormG.addClass('hidden');
	if(region > 0) {
		$.ajax({
			type: 'GET',
			data: {
				func: 'get_regions_directions_options',
				region_id: region
			},
			dataType: 'html',
			url: 'mysql.php',
			success: function (data) {
				$object_region_direction.html(data);
				if($object_region_direction.find('option').length > 1)
					$object_region_directionFormG.removeClass('hidden');
			}
		});
	}
});

