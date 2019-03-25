function select_klient(id, type, cache){
	if(cache != 'no-cache')
		select_menu('schet_menu');
	if(type == 'agency')
		var str = 'func=select_agency&id=' + id;
	else
		var str = 'func=select_client&id=' + id;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			if(html){
				$('#body').html(html);
				var reck = Get_Cookie('reck');
				if(reck){
					view_schet(reck);
				}else
					klient_schet(id, type);
			}else{
				alert('Такого клиента не существует');
				location.reload();
			}
		}
	});
}

function view_schet(id, view){
	if(document.getElementById('klient')){
		var klient = $('#klient').attr('name');
		var type = 'klient';
	}else{
		var klient = $('#agency').attr('name');
		var type = 'agency';
	}
	var str = 'func=show_schet_klient&id=' + id + '&klient=' + klient + '&type=' + type;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		dataType: 'JSON',
		success: function(data){
			var html = data['html'];
			if(html){
				$('#info_turist').html(html);
				if(view == 'payment')
					$('.desc-schet button').trigger('click');
				var info = data['object-info'];
				var have_contract = 0;
				var object_html = '';
				if(info['website'])
					object_html+= '<p><a href="' +info['website']+ '" target="_blank">Официальный сайт</a></p>';
				if(info['arrival'])
					object_html+= '<p>Заезд ' +info['arrival']+ ', выезд ' +info['leaving']+ '</p>';
				if(info['contract']){
					for(var index in info['contract']){
						var dogovor = info['contract'][index];
						var type = 'Договор санатория';
						if(dogovor['type'] == 'sanata')
							type = 'Договор Саната';
						object_html+= '<p class="text-success"><i class="fa fa-thumbs-o-up"></i> ' +type+ ' (' +dogovor['number']+ ' ' +dogovor['date_cont']+ ')</p>';
						have_contract = 1;
					}
				}
				if(have_contract == 0)
					object_html+= '<p>Договор не указан</p>';
				object_html+= '<div class="text-center text-danger pointer" onclick="$(\'.object-info\').trigger(\'click\')">Закрыть</div>';
				$('.object-info').popover({
					placement: 'bottom',
					content: object_html,
					html: true,
					trigger: 'click'
				});
			}else
				klient_schet();
		}
	});
}

function add_new_position(id){
	var str = 'func=add_new_position&id=' + id;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			show_modal(html);
		}
	});
}

function add_new_turist(id){
	var str = 'func=add_new_turist&id=' + id;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			show_modal(html);
		}
	});
}

function change_manager(id){
	var str = 'func=get_manager_change&id=' + id;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			show_modal(html);
		}
	});
}

function save_manager_to_reck(id){
	var manager = $('#all_manager').val();
	var str = 'func=change_manager_reckoning&id=' + id + '&manager=' + manager;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(){
			remove_all_windows();
			view_schet(id);
			show_alert('Готово...');
		}
	});
}

function save_new_position(id){
	var $modalBody = $('.model-reconing .modal-body');
	var room = $('.select_room').val();
	var add_one_day = $('#add_one_day input:checked').val();
	if(typeof add_one_day === 'undefined') add_one_day = 0;
	var sum = $('#sum').val();
	var reward = $('#commis').val();
	var days = $('#days').val();
	var date_z = $('#arrival').attr('date');
	var number = $('#number').val();
	var note = $('#note').val();
	var type = $('#type').val();
	var reck_type = parseInt($('#reck_type').val());
	var id_rights = parseInt($modalBody.find('*[name="id_rights"]').val());
	var str;

	var services = new Array();
	$('.services:checkbox:checked').each(function () {
	       services.push($(this).val());
	});

	if(isNaN(sum) || (sum == ''))
		show_warning('.new-position', 'Не верно введено поле: Цена', false);
	else if(isNaN(number) || (number == ''))
		show_warning('.new-position', 'Не верно введено поле: Кол-во', false);
	else {
    if(reck_type === 0) {
      if(days == '')
        show_warning('.new-position', 'Не введено поле: Дней', false);
      else if(date_z == '')
        show_warning('.new-position', 'Не введено поле: Заезд', false);
      else if(!check_true_dates(date_z) && id_rights < 6)
        show_warning('.new-position', 'Дата заезда введена неправильно', false);
      else{
        str = 'func=save_new_position&id=' + id + '&services=' + JSON.stringify(services) + '&id_room=' + room + '&sum=' + sum + '&number=' + number + '&note=' + note + '&type=' + type + '&days=' + days + '&date_z=' + date_z + '&add_one_day=' + add_one_day + '&reward=' + reward+'&reck_type='+reck_type;
        $.ajax({
          url: 'mysql.php',
          type: 'POST',
          data: str,
          success: function(){
            remove_all_windows();
            view_schet(id);
            show_alert('Позиция добавлена...');
          }
        });
        $('.btn-update').button('loading');
      }
    }
    else {
      str = 'func=save_new_position&id=' + id + '&sum=' + sum + '&number=' + number + '&note=' + note+'&reck_type='+reck_type;
      $.ajax({
        url: 'mysql.php',
        type: 'POST',
        data: str,
        success: function(){
          remove_all_windows();
          view_schet(id);
          show_alert('Позиция добавлена...');
        }
      });
      $('.btn-update').button('loading');
    }
	}

}


function save_new_klient(id){
	var surname = $('#surname').val();
	var name = $('#name').val();
	var otch = $('#otch').val();
	var date_b = $('#date_b').attr('date');
	var passport = $('#passport').val();
	var birth_certificate = $('#birth_certificate').val();
	if(!surname)
		show_warning('.new-turist', 'Не введена фамилия');
	else if((!name) || (name.length < 2))
		show_warning('.new-turist', 'Не верно введено имя');
	else{
		var str = 'func=save_new_turist&surname=' + surname + '&name=' + name + '&otch=' + otch + '&date=' + date_b + '&passport=' + passport + '&id=' + id + '&birth_certificate=' + birth_certificate;
		$('.btn-update').button('loading');
		$.ajax({
			url: 'mysql.php',
			type: 'POST',
			data: str,
			success: function(){
				remove_all_windows();
				view_schet(id);
				show_alert('Отдыхающий добавлен...');
			}
		});
	}
}

function select_turist_to_schet(id, schet){
	var str = 'func=select_turist_to_schet&id=' + id + '&schet=' + schet;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(answer){
			if(answer == 1){
				remove_all_windows();
				view_schet(schet);
				show_alert('Отдыхающий добавлен...');
			}else
				alert('Отдыхающий уже добавлен в заявку');
		}
	});
}

function edit_schet(id){
	var str = 'func=edit_schet&id=' + id;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			show_modal(html);
		}
	});
}

function change_object_schet(){
	if(!$('.input-check-object').length){
		var html = "<div class='col-sm-8 input-check-object'><span id='object_name'><input type='text' onkeyup='find_klient(\"object\", \"object\", \"use_object\")' id='object' class='form-control' /></span></div>";
		$('.label-check-object').after(html);
		$('#object').focus();
	}else{
		$('.input-check-object').remove();
	}
}

function update_schet(id){

  var note = $('#note_schet').val();
	var reck_type = parseInt($('#reck_type').val());

	if(reck_type === 0) {
    var id_obj = '', id_tour = '', check_obj = 0, id_com = 0, id_dis;
    if($('.id-object').length){
      id_obj = $('.id-object').attr('name');
      if($('.id-tour-operator').length)
        id_tour = $('.id-tour-operator').val();
      check_obj = 1;
    }
    var number_turist = $('#number_turist').val();
    var schet_san = $('#schet_san').val();
    var date_schet_san = $('#date_schet_san').attr('date');
    if($('#id_com').length)
      id_com = $('#id_com').val();
    else if($('#id_dis').length)
      id_dis = $('#id_dis').val();
    if($('.input-check-object').length && !id_obj)
      show_warning('.edit-reck', 'Выберите объект', false);
    else{
      note = note.replace("+", "plus");
      note = note.replace("+", "plus");
      var str = 'func=update_schet&id=' + id + '&number_turist=' + number_turist + '&id_obj=' + id_obj + '&id_tour=' + id_tour + '&check=' + check_obj + '&id_com=' + id_com + '&note=' + note + '&id_dis=' + id_dis + '&schet_san=' + schet_san + '&date_schet_san=' + date_schet_san+"&reck_type="+reck_type;
      $('.btn-update').button('loading');
      $.ajax({
        url: 'mysql.php',
        type: 'POST',
        data: str,
        success: function(){
          remove_all_windows();
          view_schet(id);
          show_alert('Заявка изменена...');
        }
      });
    }
	}
	else {
    note = note.replace("+", "plus");
    note = note.replace("+", "plus");
    var str = 'func=update_schet&id=' + id +'&note=' + note+"&reck_type="+reck_type;
    $('.btn-update').button('loading');
    $.ajax({
      url: 'mysql.php',
      type: 'POST',
      data: str,
      success: function(){
        remove_all_windows();
        view_schet(id);
        show_alert('Заявка изменена...');
      }
    });
	}
}

function remove_klient_reck(turist, id){
	if(confirm('Удалить клиента из счета?')){
		var str = 'func=remove_klient_schet&turist=' + turist+ '&id=' + id;
		$.ajax({
			url: 'mysql.php',
			type: 'POST',
			data: str,
			success: function(){
				view_schet(id);
			}
		});
	}
}

function edit_klient_reck(id, schet){
	var str = 'func=edit_klient_reck&id=' + id + '&schet=' + schet;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			show_modal(html);
		}
	});
}

function update_klient_schet(id, schet){
	var surname = $('#surname').val();
	var name = $('#name').val();
	var otch = $('#otch').val();
	var date_b = $('#date_b').attr('date');
	var passport = $('#passport').val();
	var output = $('#output').val();
	var date_pass = $('#date_pass').attr('date');
	var birth_certificate = $('#birth_certificate').val();
	if(!surname)
		show_warning('.edit-turist', 'Не введена фамилия');
	else if((!name) || (name.length < 2))
		show_warning('.edit-turist', 'Не правильно введено имя');
	else{
		var str = 'func=update_klient_schet&surname=' + surname + '&name=' + name + '&otch=' + otch + '&date=' + date_b + '&passport=' + passport + '&id=' + id + '&output=' + output + '&date_pass=' + date_pass + '&birth_certificate=' + birth_certificate + '&schet=' + schet;
		$('.btn-update').button('loading');
		$.ajax({
			url: 'mysql.php',
			type: 'POST',
			data: str,
			success: function(){
				remove_all_windows();
				view_schet(schet);
				show_alert('Отдыхающий изменен...');
			}
		});
	}
}

function show_history_schet(id){
	var str = 'func=show_history_schet&id=' + id;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			$('#info_turist').html(html);
		}
	});
}

function show_page_history(page){
	$('.tr-history').hide();
	$('.tr-'+page).show();
	$('.pagination li').removeClass('active');
	$('.pagination .page-'+page).addClass('active');
}

function remove_position_reck(id, reck){
	if(confirm('Удалить позицию счета?')){
		var str = 'func=remove_position&id=' + id + '&reck=' + reck;
		$.ajax({
			url: 'mysql.php',
			type: 'POST',
			data: str,
			success: function(){
				view_schet(reck);
			}
		});
	}
}

function edit_position_reck(id, reck){
	var str = 'func=edit_position&id=' + id;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			show_modal(html);
			change_label_number();
		}
	});
}

function update_position(id){
	var $modalBody = $('.model-reconing .modal-body');

	var room = $('.select_room').val();
	if(typeof room === 'undefined')
		room = 0;
	var sum = $('#sum').val();
	var reward = $('#commis').val();
	var days = $('#days').val();
	var date_z = $('#arrival').attr('date');
	var number = $('#number').val();
	var note = $('#note').val();
	var type = $('#type').val();
  var reck_type = parseInt($('#reck_type').val());
	var add_one_day = $('#add_one_day input:checked').val();
	if(typeof add_one_day === 'undefined') add_one_day = 1;
	var id_rights = parseInt($modalBody.find('*[name="id_rights"]').val());


	var services = new Array();
	$('.services:checkbox:checked').each(function () {
	       services.push($(this).val());
	});

	if(isNaN(sum) || (sum == ''))
		show_warning('.edit-position', 'Не верно введено поле: Цена', false);
	else if(isNaN(number) || (number == ''))
		show_warning('.edit-position', 'Не верно введено поле: Кол-во', false);
	else {
    if(reck_type === 0) {
      if(days == '')
        show_warning('.edit-position', 'Не введено поле: Дней', false);
      else if(date_z == '')
        show_warning('.edit-position', 'Не введено поле: Заезд', false);
      else if(!check_true_dates(date_z) && id_rights < 6)
        show_warning('.edit-position', 'Дата заезда введена неправильно', false);
      else{
        note = note.replace("+", "plus");
        note = note.replace("+", "plus");
        note = note.replace("+", "plus");
        var str = 'func=update_position&id=' + id + '&services=' + JSON.stringify(services) + '&id_room=' + room + '&sum=' + sum + '&number=' + number + '&note=' + note + '&type=' + type + '&days=' + days + '&date_z=' + date_z + '&add_one_day=' + add_one_day + '&reward=' + reward+"&reck_type="+reck_type;
        $('.btn-update').button('loading');
        $.ajax({
          url: 'mysql.php',
          type: 'POST',
          data: str,
          success: function(answer){
            remove_all_windows();
            $('.reload-btn').trigger('click');
            show_alert('Позиция изменена...');
            if(answer == 'check')
              show_form_reset_status_bid(id);
          }
        });
      }
    }
    else {
      note = note.replace("+", "plus");
      note = note.replace("+", "plus");
      note = note.replace("+", "plus");
      var str = 'func=update_position&id=' + id + '&sum=' + sum + '&number=' + number + '&note=' + note+"&reck_type="+reck_type;
      $('.btn-update').button('loading');
      $.ajax({
        url: 'mysql.php',
        type: 'POST',
        data: str,
        success: function(answer){
          remove_all_windows();
          $('.reload-btn').trigger('click');
          show_alert('Позиция изменена...');
          if(answer == 'check')
            show_form_reset_status_bid(id);
        }
      });
		}
	}
}

function show_form_reset_status_bid(position){
	var str = 'func=show_form_reset_status_bid&position=' + position;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			show_modal(html);
		}
	});
}

function confirm_reset_status_bid(id){
	var str = 'func=confirm_reset_status_bid&id=' + id;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(){
			remove_all_windows();
			$('.reload-btn').trigger('click');
		}
	});
}

function new_reck(type){
	remove_all_windows();
	$('#see_schet').removeClass('see_schet_a').addClass('see_schet_noa');
	$('#see_bonus').removeClass('see_bonus_a').addClass('see_bonus_noa');
	var str = 'func=new_reckoning&type=' + type;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			$('#info_turist').html(html);
			show_datepicker();
		}
	});
}

function save_new_schet(){
	var b, id_com = '', discount, id_tour = '', type_dis = '1';
	var sum = $('#sum').val();
	var days = $('#days').val();
	var note = $('#note').val();
	var manager = $('#manager').val();
	var date_z = $('#arrival').attr('date');
	var number = $('#number').val();
	var number_turist = $('#number_turist').val();
	var id_obj = $('.id-object').attr('name');
	if($('.id-tour-operator').length)
		id_tour = $('.id-tour-operator').val();
	var id_room = $('.select_room').val();
	var type_price = $('#type').val();
	var commis = $('#commis').val();
	var add_one_day = $('#add_one_day input:checked').val();
  var type_schet = parseInt(jQuery('#reck_type').val());
	var str;
	type = 0;

	if($('#klient').length){
		var id_klient = $('#klient').attr('name');
		discount = $('#id_dis').val();
	}else{
		var id_klient = $('#agency').attr('name');
		var type = 'agency';
		id_com = $('#id_com').val();
	}

	if(type_schet != 0) {
    str = 'func=save_schet&id_klient=' + id_klient + '&note=' + note + '&manager=' + manager + "&type_schet="+type_schet;
    $.ajax({
      url: 'mysql.php',
      type: 'POST',
      data: str,
      success: function(answer){
        if(!answer){
          show_warning('.new-reckoning', 'В поле сумма ошибка');
        }else{
          view_schet(answer);
          show_alert('Заявка сохранена...');
        }
      }
    });
	}
	else {
    if(!date_z)
      show_warning('.new-reckoning', 'Введите дату заезда');
    else if(!id_obj)
      show_warning('.new-reckoning', 'Не выбран объект');
    else if((!sum) || (isNaN(sum)))
      show_warning('.new-reckoning', 'Неправильно введена цена');
    else if((!days) || (isNaN(days)))
      show_warning('.new-reckoning', 'Неправильно введено количество дней');
    else if(isNaN(number))
      show_warning('.new-reckoning', 'Неправильно введено количество');
    else if(!check_true_dates(date_z, 'new'))
      show_warning('.new-reckoning', 'Дата заезда введена неправильно');
    else{
      str = 'func=save_schet&id_klient=' + id_klient + '&sum=' + sum + '&days=' + days + '&note=' + note + '&date_z=' + date_z + '&id_obj=' + id_obj + '&id_tour=' + id_tour + '&id_room=' + id_room + '&manager=' + manager + '&number=' + number + '&number_turist=' + number_turist + '&type=' + type + '&type_price=' + type_price + '&id_com=' + id_com + '&id_dis=' + discount + '&commis=' + commis + '&add_one_day=' + add_one_day+"&type_schet="+type_schet;
      $.ajax({
        url: 'mysql.php',
        type: 'POST',
        data: str,
        success: function(answer){
          if(!answer){
            show_warning('.new-reckoning', 'В поле сумма ошибка');
          }else{
            view_schet(answer);
            show_alert('Заявка сохранена...');
          }
        }
      });
    }
	}
}

function all_bonus_klient(){
	if($('#klient').length){
		var id = $('#klient').attr('name');
		var str = 'func=select_all_client_bonus&id=' + id;
		$.ajax({
			url: 'mysql.php',
			type: 'POST',
			data: str,
			success: function(bonus){
				$('.all-bonus').html(bonus);
			}
		});
	}
}

function select_bonus_schet(id){
	var str = 'func=select_bonus_reckoning&id=' + id;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			show_modal(html);
		}
	});
}

function save_bonus_schet(id){
	var bonus = $('.get-bonus').val();
	if(!bonus)
		show_warning('.select-bonus', 'Введите бонусы');
	else{
		var str = 'func=save_bonus_reckoning&id=' + id + '&bonus=' + bonus;
		$.ajax({
			url: 'mysql.php',
			type: 'POST',
			data: str,
			success: function(bonus){
				if(bonus == 0)
					show_warning('.select-bonus', 'Неправильно введены бонусы');
				else if(bonus == 1){
					remove_all_windows();
					all_bonus_klient();
					view_schet(id);
					show_alert('Бонусы использованы...');
				}
			}
		});
	}
}

function klient_schet(id){
	$('.see_tab').removeClass('see_tab_active');
	$('#see_schet').addClass('see_tab_active');
	var type;
	if($('#klient').length)
		var id = $('#klient').attr('name');
	else{
		var id = $('#agency').attr('name');
		type = 'agency';
	}
	if(type != 'agency')
		type = '';
	var str = 'func=view_schet_client&id=' + id + '&type=' + type;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			$('#info_turist').html(html);
		}
	});
}

function klient_bonus(){
	$('.see_tab').removeClass('see_tab_active');
	$('#see_bonus').addClass('see_tab_active');
	var id = $('#klient').attr('name');
	var str = 'func=view_bonus_client&id=' + id;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			$('#info_turist').html(html);
		}
	});
}

function edit_klient(){
	remove_all_windows();
	var turist = $('#klient').attr('name');
	var str = 'func=edit_klient&id=' + turist;
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

function update_klient(turist){
	var telephone = $('#telephone').val();
	var name = $('#name').val();
	var surname = $('#surname').val();
	var otch = $('#otch').val();
	var email = $('#email').val();
	var passport = $('#passport').val();
	var output = $('#output').val();
	var date_pas = $('#date_pas').attr('date');
	var date = $('#date').attr('date');
	var address = $('#address').val();
	var note_k = $('#note_k').val();
	var icq = $('#ICQ').val();
	var vk = $('#vk').val();
	var facebook = $('#facebook').val();
	var od_cl = $('#od_cl').val();
	var twitter = $('#twitter').val();
	var myWorld = $('#mail').val();
	var skype = $('#skype').val();
	var sex = parseInt($('select#sex').val());
	var service_note = $('#service-note').val();
	var set = '&skype=' + skype + '&icq=' + icq + '&vk=' + vk + '&facebook=' + facebook + '&od_cl=' + od_cl + '&twitter=' + twitter + '&mail=' + myWorld;
	if(surname == '')
		show_warning('.edit-turist', 'Не введены данные: фамилия');
	else if(name == '')
		show_warning('.edit-turist', 'Не введены данные: имя');
	else if(otch == '')
		show_warning('.edit-turist', 'Не введены данные: отчество');
	else if(sex === -1)
    show_warning('.edit-turist', 'Не указан пол туриста');
	else if(!check_email(email))
		show_warning('.edit-turist', 'Не верно введено поле: Email');
	else if((telephone.length < 10) && (telephone != ''))
		show_warning('.edit-turist', 'Не верно введено поле: Телефон');
	else{
		var str = 'func=update_klient&id=' + turist + '&surname=' + surname + '&name=' + name + '&otch=' + otch +'&sex='+sex+ '&telephone=' + telephone + '&email=' + email + '&passport=' + passport + '&date=' + date + set + '&address=' + address + '&note_k=' + note_k + '&output=' + output + '&date_pas=' + date_pas + '&service_note=' + service_note;
		$.ajax({
			url: 'mysql.php',
			type: 'POST',
			data: str,
			success: function(){
				select_klient(turist, '', 'no-cache');
				show_alert('Клиент изменен...');
			}
		});
	}
}

function show_social_edit(){
	$('#tr_social').toggle('slow');
}

function edit_payment(id){
	var str = 'func=edit_payment&id=' + id;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			show_modal(html);
		}
	});
}

function save_payment(id){
	var pay_method = '', pay_number = '', pay_to_prepay = '', office = '';
	var date_payment = $('#date_payment').attr('date');
	var sum_payment = $('#sum_payment').val();
	if($('#pay_method').length)
		pay_method = $('#pay_method').val();
	if($('#pay_number').length)
		pay_number = $('#pay_number').val();
	if($('#office-pay').length)
		office = $('#office-pay').val();
	if($('#pay_to_prepay').length)
		pay_to_prepay = document.getElementById('pay_to_prepay').checked;
	if(!check_date_interval(date_payment, 20))
		show_warning('.edit-payment', 'Неправильно указана дата');
	else{
		var str = 'func=update_payment&id=' + id + '&date_payment=' + date_payment + '&sum_payment=' + sum_payment + '&pay_method=' + pay_method + '&pay_number=' + pay_number + '&pay_to_prepay=' + pay_to_prepay + '&office=' + office;
		$.ajax({
			url: 'mysql.php',
			type: 'POST',
			data: str,
			success: function(id){
				remove_all_windows();
				view_schet(id, 'payment');
				show_alert('Сохранено...');
			}
		});
	}
}


function delete_payment_prepare(id){
  var str = 'func=delete_payment_prepare&id=' + id;
  $.ajax({
    url: 'mysql.php',
    type: 'POST',
    data: str,
    success: function(html){
      show_modal(html);
    }
  });
}

function delete_payment(id, not_confirm){
  var str;
	if(not_confirm) {
    str = 'func=delete_payment&id=' + id;
    $.ajax({
      url: 'mysql.php',
      type: 'POST',
      data: str,
      success: function(id){
        remove_all_windows();
        view_schet(id, 'payment');
        show_alert('Платеж удален...');
      }
    });
	}
	else if(confirm('Удалить платеж')){
		str = 'func=delete_payment&id=' + id;
		$.ajax({
			url: 'mysql.php',
			type: 'POST',
			data: str,
			success: function(id){
				remove_all_windows();
				view_schet(id, 'payment');
				show_alert('Платеж удален...');
			}
		});
	}
}

function transfer_bonuses(id){
	var str = 'func=transfer_bonuses&id=' + id;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			show_modal(html);
		}
	});
}

function select_klient_id(id){
	var str = 'func=select_klient_id&id=' + id;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(turist){
			$('.transfet-to-client').addClass('well well-sm').html(turist + "<input type='hidden' id='klient_id' value='" +id+ "' />");
			$('#find').remove();
		}
	});
}

function transfer_bonuses_to_new_klient(old_klient){
	var new_klient = '';
	if($('#klient_id').length)
		new_klient = $('#klient_id').val();
	var bonus = $('#sum_bonus').val();
	if(!new_klient)
		show_warning('.transfer', 'Ошибка. Уважите получателя бонусов', false);
	else if(!bonus)
		show_warning('.transfer', 'Ошибка. Введите кол-во бонусов', false);
	else if(parseInt(bonus) > parseInt($('.bonus-trans').html()))
		show_warning('.transfer', 'Ошибка. Невозможно перевести данную сумму бонусов', false);
	else{
		var str = 'func=transfer_bonuses_to_new_klient&old_klient=' + old_klient + '&new_klient=' + new_klient + '&bonus=' + bonus;
		$.ajax({
			url: 'mysql.php',
			type: 'POST',
			data: str,
			success: function(answer){
				if(answer == 1){
					remove_all_windows();
					all_bonus_klient();
					show_alert('Бонусы переведены...');
				}else
					show_warning('.transfer', 'Ошибка. Невозможно перевести данную сумму бонусов', false);
			}
		});
	}
}

function search_similar_klient(id){
	var str = 'func=search_similar_klients&id=' + id;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			show_modal(html);
		}
	});
}

function assign_reckoning(id){
	var str = 'func=assign_reckoning&id=' + id;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(){
			remove_all_windows();
			view_schet(id);
			show_alert('Заявка присвоена...');
		}
	});
}

function reckoning_to_upsorted(id){
	var str = 'func=form_reckoning_to_upsorted&id=' + id;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			show_modal(html);
		}
	});
}

function save_reckoning_to_upsorted(id){
	var reason = $('.delete-form #reason-delete').val();
	if(!reason)
		show_warning('.delete-form', 'Укажите причину удаления', false);
	else{
		var str = 'func=reckoning_to_upsorted&id=' + id + '&reason=' + reason;
		$.ajax({
			url: 'mysql.php',
			type: 'POST',
			data: str,
			success: function(){
				remove_all_windows();
				view_schet(id);
				show_alert('Заявка удалена...');
			}
		});
	}
}

function delete_reckoning(id){
	if(confirm('Удалить заявку?')){
		var str = 'func=delete_reckoning&id=' + id;
		$.ajax({
			url: 'mysql.php',
			type: 'POST',
			data: str,
			success: function(){
				remove_all_windows();
				klient_schet();
				show_alert('Заявка удалена...');
			}
		});
	}
}

function reestablish_reckoning(id){
	var str = 'func=reestablish_reckoning&id=' + id;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(){
			remove_all_windows();
			view_schet(id);
			show_alert('Заявка восстановлена...');
		}
	});
}

function select_last_manager_assign(id){
	var str = 'func=select_last_manager_assign';
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			show_div_buttons('last-manager', html);
		}
	});
}

function unite_klients(id){
	var id_radio = $('.unite-klient input:radio[name=sim_klient]:checked').val();
	if(!id_radio)
		show_warning('.unite-klient', 'Выберите клиента', false);
	else{
		var str = 'func=unite_client&id=' + id + '&id_radio=' + id_radio;
		$.ajax({
			url: 'mysql.php',
			type: 'POST',
			data: str,
			success: function q(a){
				remove_all_windows();
				select_klient(id_radio);
			}
		});
	}
}

function show_menu_client(id){
	var str = 'func=show_menu_client&id=' + id;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			show_div_buttons('menu-client', html);
		}
	});
}



function delete_klient(id){
	if(confirm('Удалить клиента?')){
		var str = 'func=delete_client_from_system&id=' + id;
		$.ajax({
			url: 'mysql.php',
			type: 'POST',
			data: str,
			success: function(answer){
				if(answer == 1){
					alert('Клиент удален');
					head_page();
				}else
					alert('Невозможно удалить клиента');
			}
		});
	}
}

function show_reward_schet(id){
	var str = 'func=display_reward_schet&id=' + id;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			show_div_buttons('span_reward', html);
		}
	});
}

function client_payers(id){
	var str = 'func=client_payers&id=' + id;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			remove_all_windows();
			show_modal(html);
		}
	});
}

function add_new_payer(id){
	var str = 'func=show_new_payer_form&id=' + id;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			$('.modal-new-payer').html(html);
		}
	});
}

function add_new_individual_payer(id){
	var str = 'func=add_new_individual_payer&id=' + id;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			$('.modal-new-payer').html(html);
			show_datepicker();
		}
	});
}

function add_new_legal_payer(id){
	var str = 'func=add_new_legal_payer&id=' + id;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			$('.modal-new-payer').html(html);
		}
	});
}

function save_new_individual_payer(id){
	var name = $('#name').val();
	var date_b = $('#date_b').attr('date');
	var passport = $('#passport').val();
	if(!name)
		show_warning('.new-payer', 'Введите имя', false);
	else{
		var str = 'func=save_new_individual_payer&name=' + name + '&date_b=' + date_b + '&passport=' + passport + '&id=' + id;
		$.ajax({
			url: 'mysql.php',
			type: 'POST',
			data: str,
			success: function(){
				remove_all_windows();
				client_payers(id);
				show_alert('Плательщик сохранен...');
			}
		});
	}
}

function save_new_legal_payer(id){
	var name = $('#name').val();
	var short = $('#short').val();
	var present = $('#present').val();
	var post = $('#post').val();
	var doc = $('#doc').val();
	var post_im = $('#post_im').val();
	var present_im = $('#present_im').val();
	var inn = $('#inn').val();
	var kpp = $('#kpp').val();
	var bik = $('#bik').val();
	var rs = $('#rs').val();
	var ks = $('#ks').val();
	var bank = $('#bank').val();
	var address = $('#address').val();
	var ur_address = $('#ur_address').val();
	var email = $('#email').val();
	var bin = $('#bin').val();
	var iik = $('#iik').val();
	if(!name)
		show_warning('.new-payer', 'Введите имя', false);
	else{
		var str = 'func=save_new_legal_payer&name=' + name + '&short=' + short + '&present=' + present + '&post=' + post + '&doc=' + doc + '&post_im=' + post_im + '&present_im=' + present_im + '&inn=' + inn + '&kpp=' + kpp + '&bik=' + bik + '&rs=' + rs + '&ks=' + ks + '&bank=' + bank + '&address=' + address + '&ur_address=' + ur_address + '&id=' + id + '&email=' + email + '&iik=' + iik + '&bin=' + bin;
		$.ajax({
			url: 'mysql.php',
			type: 'POST',
			data: str,
			success: function(){
				remove_all_windows();
				client_payers(id);
				show_alert('Плательщик сохранен...');
			}
		});
	}
}

function edit_individual_payer(id){
	var str = 'func=edit_individual_payer&id=' + id;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			$('.modal-new-payer').html(html);
			show_datepicker();
		}
	});
}

function update_individual_payer(id){
	var name = $('#name').val();
	var date_b = $('#date_b').val();
	var address = $('#payer_address');
	if(typeof date_b === 'undefined')
		date_b = '';
	var passport = $('#passport').val();
	if(!name)
		show_warning('.edit-payer', 'Введите имя', false);
	else{
		var str = 'func=update_individual_payer&name=' + name + '&date_b=' + date_b + '&passport=' + passport + '&id=' + id+'&address='+address;
		$.ajax({
			url: 'mysql.php',
			type: 'POST',
			data: str,
			success: function(){
				var client = $('#klient').attr('name');
				client_payers(client);
				show_alert('Плательщик сохранен...');
			}
		});
	}
}

function edit_legal_payer(id){
	var str = 'func=edit_legal_payer&id=' + id;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			$('.modal-new-payer').html(html);
		}
	});
}

function update_legal_payer(id){
	var name = $('#name').val();
	var short = $('#short').val();
	var present = $('#present').val();
	var post = $('#post').val();
	var doc = $('#doc').val();
	var post_im = $('#post_im').val();
	var present_im = $('#present_im').val();
	var inn = $('#inn').val();
	var kpp = $('#kpp').val();
	var bik = $('#bik').val();
	var rs = $('#rs').val();
	var ks = $('#ks').val();
	var bank = $('#bank').val();
	var address = $('#address').val();
	var ur_address = $('#ur_address').val();
	var email = $('#email').val();
	var bin = $('#bin').val();
	var iik = $('#iik').val();
	var code = $('#1C_code').val();
	if(!name)
		show_warning('.edit-payer', 'Введите имя', false);
	else{
		var str = 'func=update_legal_payer&name=' + name + '&short=' + short + '&present=' + present + '&post=' + post + '&doc=' + doc + '&post_im=' + post_im + '&present_im=' + present_im + '&inn=' + inn + '&kpp=' + kpp + '&bik=' + bik + '&rs=' + rs + '&ks=' + ks + '&bank=' + bank + '&address=' + address + '&ur_address=' + ur_address + '&id=' + id + '&email=' + email + '&bin=' + bin + '&iik=' + iik + '&code=' + code;
		$.ajax({
			url: 'mysql.php',
			type: 'POST',
			data: str,
			success: function(){
				var client = $('#klient').attr('name');
				client_payers(client);
				show_alert('Плательщик сохранен...');
			}
		});
	}
}

function unblock_reckoning(id){
	var str = 'func=unblock_reckoning&id=' + id;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(){
			view_schet(id);
			remove_all_windows();
			show_alert('Разблокировано...');
		}
	});
}

function block_reckoning(id){
	var str = 'func=block_reckoning&id=' + id;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(){
			view_schet(id);
			remove_all_windows();
			show_alert('Заблокировано...');
		}
	});
}

function postponed_san_reckoning(id){
	var str = 'func=postponed_san_reckoning&id=' + id;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(){
			view_schet(id);
			remove_all_windows();
			show_alert('Готово...');
		}
	});
}

function return_san_reckoning(id){
	var str = 'func=return_san_reckoning&id=' + id;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(){
			view_schet(id);
			remove_all_windows();
			show_alert('Готово...');
		}
	});
}

function new_gift_certificate(id){
	var str = 'func=new_gift_certificate&id=' + id;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			show_modal(html);
		}
	});
}

function save_new_gift_certificate(id){
	var sum = $('#sum_cer').val();
	if(!sum)
		show_warning('.add-certificate', 'Укажите сумму сертификата', false);
	else{
		var str = 'func=save_new_gift_certificate&sum=' + sum + '&id=' + id;
		$.ajax({
			url: 'mysql.php',
			type: 'POST',
			data: str,
			success: function(){
				remove_all_windows();
				select_klient(id);
				show_alert('Готово...');
			}
		});
	}
}

function klient_certificate(){
	$('.see_tab').removeClass('see_tab_active');
	$('#see_cert').addClass('see_tab_active');
	var id = $('#klient').attr('name');
	var str = 'func=see_certificate_client&id=' + id;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			$('#info_turist').html(html);
		}
	});
}

function show_certificate(){
	select_menu('certificate_menu');
	var str = 'func=show_certificate';
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			$('#body').html(html);
		}
	});
	show_loader_element('#body');
}

function show_history_certificate(id){
	var str = 'func=show_history_certificate&id=' + id;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			show_modal(html);
		}
	});
}

function show_menu_certificate(id){
	var str = 'func=show_menu_certificate&id=' + id;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			var elem = $('.btn-'+id);
			show_floating_div(elem, html);
		}
	});
}

function change_status_certificate(id, status){
	var str = 'func=change_status_certificate&id=' + id + '&status=' + status;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(){
			show_certificate();
		}
	});
}

function schet_certificate(id){
	window.open('document.php?func=review_schet_certificate&id=' + id, 'Счет PDF');
}

function pay_by_certificate(id){
	var str = 'func=show_pay_by_certificate&id=' + id;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			show_modal(html);
		}
	});
	var html = "";
}

function check_key_certificate(id){
	var key = $('.key-cert').val();
	if(key.length != 7)
		$('.pay-cert-result').html('<div class="alert alert-danger">Код сертификата должен состоять из 7 цифр</div>');
	else{
		var str = 'func=check_key_certificate&id=' + id + '&key=' + key;
		$.ajax({
			url: 'mysql.php',
			type: 'POST',
			data: str,
			success: function(html){
				$('.pay-cert-result').html(html);
			}
		});
	}
}

function select_certificate_for_schet(id_cert, id){
	var str = 'func=pay_certificate_for_schet&id=' + id + '&id_cert=' + id_cert;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(){
			view_schet(id);
			remove_all_windows();
			show_alert('Оплачено сертификатом...');
		}
	});
}

function show_certificate_forma(id){
	window.open('document.php?func=review_forma_certificate&id=' + id, 'Подарочный сертификат');
}

function add_service_reckoning(id){
	var str = 'func=add_service_reckoning&id=' + id;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			show_modal(html);
		}
	});
}

function save_service_reckoning(id){
	var service = $('#service').val();
	$('.btn-update').button('loading');
	var str = 'func=save_service_reckoning&id=' + id + '&service=' + service;
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

function form_upload_document(id, type){
	var str = 'func=show_form_upload_document';
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			show_modal(html);
			activate_ajax_document(id, type);
		}
	});
}

function upload_document(id, type, file){
	var str = 'func=upload_document&file=' + file + '&id=' + id + '&type=' + type;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			remove_all_windows();
			if(type == 'news'){
				if(html == 1)
					alert('Загружаемый файл не картинка');
				else if(html == 2)
					alert('Такая картинка уже существует');
				else{
					var html = '<img src="' +html+ '" class="img-thumbnail img-head" />';
					$('.images-website').append(html);
				}
			}else if(type != 'chat'){
				view_schet(id);
				show_alert('Документ загружен...');
			}else{
				$('#chat-message .scrollTo').before(html);
				$('#chat-message .chat-message-block').scrollTo('.scrollTo');
			}
		}
	});
}

function delete_schet_san(id, index){
	if(confirm('Удалить счет санатория?')){
		var str = 'func=delete_schet_san&id=' + id + '&index=' + index;
		$.ajax({
			url: 'mysql.php',
			type: 'POST',
			data: str,
			success: function(){
				view_schet(id);
				show_alert('Документ удален...');
			}
		});
	}
}

function view_history_klient(){
	var id = $('#klient').attr('name');
	var str = 'func=view_history_klient&id=' + id;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			remove_all_windows();
			$('#info_turist').html(html);
		}
	});
}

function counted_bonus_client(id){
	var str = 'func=counted_bonus_client&id=' + id;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			select_klient(id);
			show_alert('Готово...');
		}
	});
}

function add_bonus_client(id){
	var str = 'func=add_bonus_client&id=' + id;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			show_modal(html);
		}
	});
}

function save_bonus_client(id){
	var bonus = $('.add-bonus #bonus').val();
	var note = $('.add-bonus #note').val();
	if(!bonus)
		show_warning('.add-bonus', 'Укажите сумму бонусов');
	else if(!note)
		show_warning('.add-bonus', 'Укажите примечание');
	else{
		var str = 'func=save_bonus_client&id=' + id + '&bonus=' + bonus + '&note=' + note;
		$.ajax({
			url: 'mysql.php',
			type: 'POST',
			data: str,
			success: function(){
				remove_all_windows();
				show_alert('Бонусы добавлены...');
				all_bonus_klient(id);
			}
		});
	}
}

function view_rating_schet(id){
	var str = 'func=view_rating_schet&id=' + id;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			$('#info_turist').html(html);
		}
	});
}

function edit_note_bid_reckoning(id){
	var str = 'func=edit_note_bid_reckoning&id=' + id;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			show_modal(html);
		}
	});
}

function update_note_bid_reckoning(id){
	var note = $('.note-bid').val();
	note = note.replace("+", "plus");
	note = note.replace("+", "plus");
	note = note.replace("+", "plus");
	$('.btn-update').button('loading');
	var str = 'func=update_note_bid_reckoning&id=' + id + '&note=' + note;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			remove_all_windows();
			view_schet(id);
		}
	});
}

function show_talk_reckoning(id){
	var str = 'func=show_talk_reckoning&id=' + id;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			$('#info_turist').html(html);
		}
	});
}

function clear_login_client(id){
	var str = 'func=clear_login_client&id=' + id;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			show_modal(html);
		}
	});
}

function confirm_clear_login_client(id){
	var str = 'func=confirm_clear_login_client&id=' + id;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			select_klient(id);
		}
	});
}

function show_quota_object_bid(object){
	var html = '<div class="modal fade"><div class="modal-dialog modal-giant"><div class="modal-content"><div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-label="Close"><i class="fa fa-times"></i></button><button class="close hide-button-modal" type="button" name="Квота санатория" type="quota-bid"><i class="fa fa-window-minimize"></i></button><h4 class="modal-title">Бронирование из квоты мест</h4></div><div class="modal-body form-horizontal"><div class="data-object"></div></div></div></div></div>';
	show_modal(html);
	show_object_qouta_params(object);
}

function check_status_bonus(id){
	var str = 'func=check_status_bonus&id=' + id;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(active){
			$('.bonus-turist-'+id).removeClass('danger');
			if(active == 0){
				$('.bonus-turist-'+id).addClass('danger');
			}
		}
	});
}

function cancel_payment_show_modal(id) {
  var modal = '<div class="modal fade">';
		modal += '<div class="modal-dialog">';
  		modal += '<div class="modal-content">';
  				modal +='<div class="modal-header">';
  					modal +='<button type="button" class="close" data-dismiss="modal" aria-label="Close"><i class="fa fa-times"></i></button>';
  					modal +='<h4 class="modal-title">Подтвердите отмену платежа</h4>';
  				modal += '</div>';
  				modal += '<div class="modal-body text-center">';
  						modal += 'Данное действие нельзя будет отменить!';
  				modal += '</div>';
  				modal += '<div class="modal-footer text-center">';
 	 					modal+= '<button type="button" class="btn btn-success btn-sm btn-cancel-payment-confirm" onclick="cancel_payment('+id+')"><i class="fa fa-check"></i> Подтвердить</button>';
  					modal+= '<button type="button" class="btn btn-danger btn-sm btn-cancel-payment-cancel" data-dismiss="modal"><i class="fa fa-ban"></i> Отмена</button>';
  				modal += '</div>';
  		modal += '</div>';
		modal += '</div>';
  modal += '</div>';
	show_modal(modal);
}

function confirm_payment_show_modal(id) {
	var reckStatus = parseInt($('[data-reckoning-status]').attr('data-reckoning-status'));
	var modal = '';
	if(reckStatus === 3  || reckStatus === 4) {
    modal += '<div class="modal fade">';
    modal += '<div class="modal-dialog">';
    modal += '<div class="modal-content">';
    modal +='<div class="modal-header">';
    modal +='<button type="button" class="close" data-dismiss="modal" aria-label="Close"><i class="fa fa-times"></i></button>';
    modal +='<h4 class="modal-title">Подтвердить платеж?</h4>';
    modal += '</div>';
    modal += '<div class="modal-body text-center">';
    	modal += 'Данное действие нельзя будет отменить!';
    modal += '</div>';
    modal += '<div class="modal-footer text-center">';
    modal+= '<button type="button" class="btn btn-success btn-sm btn-confirm-payment-confirm" onclick="confirm_payment('+id+')"><i class="fa fa-check"></i> Подтвердить</button>';
    modal+= '<button type="button" class="btn btn-danger btn-sm btn-confirm-payment-cancel" data-dismiss="modal"><i class="fa fa-ban"></i> Отмена</button>';
    modal += '</div>';
    modal += '</div>';
    modal += '</div>';
    modal += '</div>';
	}
	else if(reckStatus === 1 || reckStatus === 2) {
    modal += '<div class="modal fade">';
    modal += '<div class="modal-dialog">';
    modal += '<div class="modal-content">';
    modal +='<div class="modal-header">';
    modal +='<button type="button" class="close" data-dismiss="modal" aria-label="Close"><i class="fa fa-times"></i></button>';
    modal +='<h4 class="modal-title">Статус заявки</h4>';
    modal += '</div>';
    modal += '<div class="modal-body text-center">';
    	modal += 'Заявка должна быть подтверженной! Пожалуйста, измените её статус. ';
    modal += '</div>';
    modal += '<div class="modal-footer text-center">';
    modal+= '<button type="button" class="btn btn-success btn-sm" data-dismiss="modal"><i class="fa fa-check"></i> Хорошо</button>';
    modal += '</div>';
    modal += '</div>';
    modal += '</div>';
    modal += '</div>';
	}
	else {
    modal += '<div class="modal fade">';
    modal += '<div class="modal-dialog">';
    modal += '<div class="modal-content">';
    modal +='<div class="modal-header">';
    modal +='<button type="button" class="close" data-dismiss="modal" aria-label="Close"><i class="fa fa-times"></i></button>';
    modal +='<h4 class="modal-title">Статус заявки</h4>';
    modal += '</div>';
    modal += '<div class="modal-body text-center">';
    modal += 'По данной заявке нельзя принимать платежи. ';
    modal += '</div>';
    modal += '<div class="modal-footer text-center">';
    modal+= '<button type="button" class="btn btn-success btn-sm" data-dismiss="modal"><i class="fa fa-check"></i> Хорошо</button>';
    modal += '</div>';
    modal += '</div>';
    modal += '</div>';
    modal += '</div>';
	}
  show_modal(modal);
}

function cancel_payment(id) {
  var str = 'func=cancel_payment&id=' + id;
  $('.btn-cancel-payment-confirm').button('loading');
  $('.btn-cancel-payment-cancel').hide();
  $.ajax({
    url: 'mysql.php',
    type: 'POST',
    data: str,
		dataType: 'json',
    success: function(data){
      remove_all_windows();
      if(data['success']) {
				$('.payment-element[data-payment-id='+id+']').removeClass('not-confirmed').addClass('cancelled');
        $('.payment-element[data-payment-id='+id+']').next().find('.payment-confirm-button').removeClass('hidden');
        $('.payment-element[data-payment-id='+id+'] .payment-actions-block').remove();
        alert("Платеж отменен");
        view_schet(data['reck_id']);
			}
			else {
      	alert(data['msg']);
			}
    }
  });
}

function confirm_payment(id) {
  var str = 'func=confirm_payment&id=' + id;
  $('.btn-confirm-payment-confirm').button('loading');
  $('.btn-confirm-payment-cancel').hide();
  $.ajax({
    url: 'mysql.php',
    type: 'POST',
    data: str,
    dataType: 'json',
    success: function(data){
      remove_all_windows();
      if(data['success']) {
        $('.payment-element[data-payment-id='+id+']').removeClass('not-confirmed');
        $('.payment-element[data-payment-id='+id+']').next().find('.payment-confirm-button').removeClass('hidden');
        $('.payment-element[data-payment-id='+id+'] .payment-actions-block').remove();
        alert("Платеж подтвержден");
        view_schet(data['reck_id']);
      }
      else {
        alert(data['msg']);
      }
    }
  });
}
