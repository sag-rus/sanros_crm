function show_question_client(){
	select_menu('question-turist');
	show_loader_element('#body');
	var str = 'func=show_question_client';
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			$('#body').html(html);
		}
	});
}

function show_question_agency(){
	select_menu('question-agency');
	show_loader_element('#body');
	var str = 'func=show_question_agency';
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			$('#body').html(html);
		}
	});
}

function show_question_object(){
	select_menu('question-object');
	show_loader_element('#body');
	var str = 'func=show_question_object';
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			$('#body').html(html);
		}
	});
}

function view_talk(id){
	save_old_html_for_back();
	var str = 'func=view_talk&id=' + id;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			$('#body').html(html);
			$('.send_comment').scrollTo('button');
		}
	});
}

function answer_client_question(id, reck){
	var answer = $('.text-answer').val();
	if(!answer)
		show_mistake('.text-answer');
	else{
		clear_mistake('.talk-client');
		$('.btn-send-answer').attr('disabled', 'disabled');
		answer = answer.replace(/\+/g, 'plus');
		var str = 'func=answer_client_question&reck=' + reck + '&id=' + id + '&answer=' + answer;
		$.ajax({
			url: 'mysql.php',
			type: 'POST',
			data: str,
			success: function(html){
				$('.text-answer').val('');
				$('.btn-send-answer').removeAttr('disabled');
				remove_all_windows();
				$('.talk-messages').append(html);
			}
		});
	}
}

function no_comment_client_question(id, reck){
	var str = 'func=no_comment_client_question&id=' + id;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(){
			if($('.send_comment').attr('view') == 'reckoning')
				show_talk_reckoning(reck);
			else
				view_talk(id);
			show_alert('Готово...');
		}
	});
}
