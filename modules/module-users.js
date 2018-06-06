function see_users(type){
	var str = 'func=see_users';
	select_menu('users_menu', '2');
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			$('#body').html(html);
		}
	});
}

function see_accounts(type){
  var str = 'func=see_accounts';
  select_menu('accounts-menu', '2');
  $.ajax({
    url: 'mysql.php',
    type: 'POST',
    data: str,
    success: function(html){
      $('#body').html(html);
    }
  });
}

function add_new_users(){
	var str = 'func=add_new_user';
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			$('#body').html(html);
		}
	});
}

function save_user(){
	var login = $('#login').val();
	var password = $('#password').val();
	var password_1 = $('#password_1').val();
	var rights = $('#dostup_id').val();
	var name = $('#name').val();
	var surname = $('#surname').val();
	var telephone = $('#telephone').val();
	var email = $('#email').val();
	if(login == '')
		show_warning('.new-user', 'Не введен логин');
	else if(password == '')
		show_warning('.new-user', 'Не введен пароль');
	else if(password != password_1)
		show_warning('.new-user', 'Пароли не совпадают');
	else{
		var str = 'func=save_new_user&login=' + login + '&password=' + password + '&rights=' + rights + '&name=' + name + '&surname=' + surname + '&telephone=' + telephone + '&email=' + email;
		$.ajax({
			url: 'mysql.php',
			type: 'POST',
			data: str,
			success: function(a){
				if(a == 1){
					see_users();
					show_alert('Пользователь сохранен...');
				}else if(a == 2)
					show_warning('.new-user', 'Пользователь с таким логином уже существует');
			}
		});
	}
}

function edit_user(id){
	var str = 'func=edit_user&id=' + id;
	$.ajax({
		type: 'POST',
		url: 'mysql.php',
		data: str,
		success: function(html){
			$('#body').html(html);
		}

	});

}

function edit_account(id){
  var str = 'func=edit_account&id=' + id;
  $.ajax({
    type: 'POST',
    url: 'mysql.php',
    data: str,
    success: function(html){
      $('#body').html(html);
    }

  });

}



function update_user(id){
	var login = $('#login').val();
	var password = '', password_1;
	if($('#password').val() != '')
		password = $('#password').val();
	if($('#password_1').val() != '')
		password_1 = $('#password_1').val();
	var rights = $('#dostup_id').val();
	var name = $('#name').val();
	var surname = $('#surname').val();
	var telephone = $('#telephone').val();
	var email = $('#email').val();
	var channel = $('#channel').val();
	var note = $('#note').val();
	var office = $('#user-office').val();
	var group = $('#user-group').val();
	if(login == '')
		show_warning('.edit-user', 'Не введен логин');
	else if((password != password_1) && (password != ''))
		show_warning('.edit-user', 'Пароли не совпадают');
	else{
		var str = 'func=update_user&login=' + login + '&password=' + password + '&rights=' + rights + '&name=' + name + '&surname=' + surname + '&telephone=' + telephone + '&email=' + email + '&id=' + id + '&office=' + office + '&group=' + group;
		$.ajax({
			url: 'mysql.php',
			type: 'POST',
			data: str,
			success: function(a){
				if(a == 1){
					see_users();
					show_alert('Пользователь изменен...');
				}else
					show_warning('.edit-user', 'Пользователь с таким логином уже существует');
			}
		});
	}
}

function update_account(id){
	var $name = $('.edit-account #name');
  var name = $name.val().trim();

  var $surname = $('.edit-account #surname');
  var surname = $surname.val().trim();

  var $otch = $('.edit-account #otch');
  var otch = $otch.val().trim();

  var $status = $('.edit-account #status');
	var status = parseInt($status.val());

	var $moderation_comment = $('.edit-account #moderation-comment');
	var moderation_comment = $moderation_comment.val().trim();
  if(!surname)
    show_warning('.edit-account', 'Фамилия обязательна для заполнения');
  else if(!name)
    show_warning('.edit-account', 'Имя обязательно для заполнения');
  else if(!otch)
    show_warning('.edit-account', 'Отчество обязательно для заполнения');
  else if((status === 0 || status === 2) && !moderation_comment)
    show_warning('.edit-account', 'Укажите причину блокировки аккаунта в комментарии модератора');
  else{
    var str = 'func=update_account&name='+name+"&surname="+surname+"&otch="+otch+"&status="+status+"&moderation_comment="+moderation_comment+"&id="+id;
    $.ajax({
      url: 'mysql.php',
      type: 'POST',
      data: str,
			dataType:"JSON",
      success: function(data){
        if(data['success']){
          see_accounts();
          show_alert('Данные пользователя изменены...');
        }else
          show_warning('.edit-account', data['msg']);
      }
    });
  }
}


var left = 0, height = 0, topq = 0;

function cancel(){
	$('#select_photo').removeClass('select_photo_active').addClass('select_p').show();
	$('.crop-area').remove();
}

function delete_photo(){
	$('#select_photo').removeClass('select_photo_active').addClass('select_p').show();
	$('.crop-area').remove();
	var attr = document.getElementById('photo_view').getAttribute('src');
	document.getElementById('photo').setAttribute('src', 'images/NoPicture.jpg');
	if(attr != 'images/NoPicture.jpg'){
		var str = 'act=delete&file=' + attr;
		$.ajax({
			url: 'upload.php',
			type: 'POST',
			data: str
		});
	}
	$('.add_pic').show();
	$('#down').removeClass('down_users').addClass('down');
	$('#photo_view').removeClass('photo_view_users').addClass('photo_view');
}

function download_photo(){
	var attr = document.getElementById('photo_view').getAttribute('src');
	$('#select_photo').removeClass('select_photo_active').addClass('select_p').show();
	$('.add_pic').hide();
	var str = 'act=image_cut&height=' + height + '&top=' + topq + '&left=' + left + '&photo=' + attr;
	$.ajax({
		url: 'upload.php',
		type: 'POST',
		data: str,
		success: function(a){
			document.getElementById('photo').setAttribute('src', attr);
		}
	});
	$('.crop-area').remove();
}


function Croppable(options) {
	var self = this;
	var elem = options.elem;
	var cropArea;
	var cropStartX, cropStartY;
	elem.on('selectstart dragstart', false).on('mousedown', onImgMouseDown);

	function initCropArea(pageX, pageY) {
		cropArea = $('<div class="crop-area" />').appendTo('body');
		cropStartX = pageX;
		cropStartY = pageY;
	}

	function onDocumentMouseMove(e) {
		drawCropArea(e.pageX, e.pageY);
	}

	function onDocumentMouseUp(e) {
		endCrop(e.pageX, e.pageY);
		$(document).off('.croppable');
	}

	function onImgMouseDown(e){
		if(cropArea)
			cropArea.remove();
		initCropArea(e.pageX, e.pageY);
	
		$(document).on({
			'mousemove.croppable': onDocumentMouseMove,
			'mouseup.croppable': onDocumentMouseUp
		});
		return false;
	}

	function drawCropArea(pageX, pageY) {
		var dims = getCropDimensions(pageX, pageY);
		
		cropArea.css(dims);
		cropArea.css({
			height: Math.max(dims.width - 2, 0),
			width: Math.max(dims.width - 2,0),
		});
	//	if(dims)
	}

	function endCrop(pageX, pageY) {
		var dims = getCropDimensions(pageX, pageY);

		var coords = elem.offset();
		dims.left -= coords.left;
		dims.top -= coords.top;

		$(self).triggerHandler($.extend({ type: "crop" }, dims));
	}

	function getCropDimensions(pageX, pageY) {

		var left = Math.min(cropStartX, pageX);
		var right = Math.max(cropStartX, pageX);
		var top = Math.min(cropStartY, pageY);
		var bottom = Math.max(cropStartY, pageY);
		var coords = elem.offset();
		left = Math.max(left, coords.left);
		top = Math.max(top, coords.top);
		right = Math.min(right, coords.left + elem.outerWidth());
		bottom = Math.min(bottom, coords.top + elem.outerHeight());
		width = right-left;
		height = bottom-top;
		width = Math.min(width, height);
		return { left: left, top: top, width: width, height: bottom-top };
	}

}
