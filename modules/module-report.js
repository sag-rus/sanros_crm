function show_filter(){
	select_menu('filter_menu');
	var str = 'func=show_filter_manager';
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			$('#body').html(html);
			onscroll_activete();
			show_datepicker();
		}
	});
}

function filter_do(){
	var show_delete = '', through_tour = '';
	var surname = $('#surname').val();
	var manager = $('#all_manager').val();
	var object = $('.id-object').attr('name');
	var id_tour = $('.id-tour-operator').attr('name');
	var date_z = $('#date_z').attr('date');
	var date_z2 = $('#date_z2').attr('date');
	var date_op = $('#date_op').attr('date');
	var date_op2 = $('#date_op2').attr('date');
	var date_v = $('#date_v').attr('date');
	var date_v2 = $('#date_v2').attr('date');
	var id = $('#id_schet').val();
	var status = select_checkbox('status');
	var place_object = $('#place_object').val();
	if($('#show-delete').is(':checked'))
		show_delete = '1';
	if($('#through-tour').is(':checked'))
		through_tour = '1';
	if(status_agent.checked)
		var st_agent = "0";
	else
		var st_agent = "";
	var st_san = '';
	var tour = '';
	if($('#get_tour').length && $('#get_tour').prop('checked'))
		tour = '&tour_operator=1';
	if(!manager && !surname && !object && !date_op && !date_z && !id && !status && !st_san && !st_agent && !date_v)
		alert('Введите хотя бы одно поле.');
	else{
		var str = 'func=filter_do&type_filter=manager&surname=' + surname + '&id_obj=' + object + '&date_z=' + date_z + '&date_z2=' + date_z2 + '&date_op=' + date_op + '&date_op2=' + date_op2 + '&id_schet=' + id + '&status_id=' + status + '&st_san=' + st_san + '&date_v=' + date_v + '&date_v2=' + date_v2 + '&st_agent=' + st_agent + '&all_manager=' + manager + '&show_delete=' + show_delete + '&place_object=' + place_object + '&id_tour=' + id_tour + '&through_tour=' + through_tour;
		$('#tbl_head').html('');
		$.ajax({
			url: 'mysql.php',
			type: 'POST',
			data: str,
			success: function(code){
				var html = "";
				$('#filter_res').html(code);
				if($('#hide_id').length){
					var all_id = $('#hide_id').val();
					html = "<button type='button' class='btn btn-success btn-xs' onclick='form_agent_report(\"" +all_id+ "\")'><i class='fa fa-file-word-o'></i> Сформировать</button>";
				}
				$("#tbl_filter").tablesorter({ widgets: ['zebra'] });
				$('.button_agent').html(html);
				show_tooltip('#filter_res');
			}
		});
		show_loader_element('#filter_res');
	}
}

function filter_do_update(){
	var str = 'func=filter_do&type=update';
	$('#filter_tr').removeClass("tr_fixed");
	$('.btn-search').attr('disabled', 'disabled');
	$('.btn-hide').attr('disabled', 'disabled');
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(code){
			$('.btn-search').removeAttr('disabled');
			if(code == '')
				$('.btn-hide').attr('disabled', 'disabled');
			else{
				$('#filter_res').html(code);
				$('.btn-hide').removeAttr('disabled');
			}
			$("#tbl_filter").tablesorter({
				headers:{
					0:{ sorter: false }
				},
				widgets: ['zebra']
			});
		}
	});
	show_loader_element('#filter_res');
}

function show_reports(){
	select_menu('report_menu');
	var id_rights = parseInt($('*[data-id-rights]').attr('data-id-rights'));
	var html;
	if(id_rights > 3)
		html = '<div class="btn-group btn-group-justified head-menu-report"><div class="btn-group"><button type="button" class="btn btn-default btn-general" onclick="general_report()"><i class="fa fa-tasks"></i> Общий</button></div><div class="btn-group"><button type="button" class="btn btn-default btn-calendar" onclick="calendar_report()"><i class="fa fa-calendar"></i> Календарь</button></div><div class="btn-group"><button type="button" class="btn btn-default btn-plan" onclick="plan_report()"><i class="fa fa-calculator"></i> План</button></div><div class="btn-group"><button type="button" class="btn btn-default btn-payment" onclick="payment_report()"><i class="fa fa-university"></i> Платежи</button></div><div class="btn-group"><button type="button" class="btn btn-default btn-bonus" onclick="bonus_report()"><i class="fa fa-gift"></i> Бонусы</button></div><div class="btn-group"><button type="button" class="btn btn-default btn-graphics" onclick="graphics_report()"><i class="fa fa-bar-chart"></i> Графики</button></div><div class="btn-group"><button type="button" class="btn btn-default btn-advertising" onclick="report_advertising()"><i class="fa fa-hacker-news"></i> Реклама</button></div><div class="btn-group"><button type="button" class="btn btn-default btn-cabinet-object" onclick="cabinet_object_report()"><i class="fa fa-home"></i> ЛК санатория</button></div><div class="btn-group"><button type="button" class="btn btn-default btn-history" onclick="history_report()";><i class="fa fa-clock-o"></i> История по заявкам</button></div><div class="btn-group"><button type="button" class="btn btn-default btn-history-global" onclick="history_report_global()";><i class="fa fa-clock-o"></i> История общая</button></div></div><div id="data" style="margin-top: 10px"></div>';
	else if(id_rights > 2)
		html = '<div class="btn-group"><button type="button" class="btn btn-default btn-plan" onclick="plan_report()"><i class="fa fa-calculator"></i> План</button></div><div class="btn-group"><button type="button" class="btn btn-default btn-payment" onclick="payment_report()"><i class="fa fa-university"></i> Платежи</button></div><div id="data" style="margin-top: 10px"></div>';
	else {
		html = '<div class="btn-group btn-group-justified head-menu-report"><div class="btn-group"><button type="button" class="btn btn-default btn-general" onclick="general_report()"><i class="fa fa-tasks"></i> Общий</button></div><div class="btn-group"><button type="button" class="btn btn-default btn-graphics" onclick="graphics_report()"><i class="fa fa-bar-chart"></i> Графики</button></div><div class="btn-group"><button type="button" class="btn btn-default btn-advertising" onclick="report_advertising()"><i class="fa fa-hacker-news"></i> Реклама</button></div></div><div id="data" style="margin-top: 10px"></div>';
	}
	$('#body').html(html);

	if(id_rights == 3)
		plan_report();
	else general_report();
}

function general_report(){
	var str = 'func=show_general_report_menu';
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			$('#data').html(html);
			$('.head-menu-report button').removeClass('btn-success');
			$('.head-menu-report .btn-general').addClass('btn-success');
			report_by_params();
		}
	});
}

function report_by_params(){
	$('.small-menu-report button').removeClass('btn-info');
	$('.small-menu-report .btn-all').addClass('btn-info');
	var str = 'func=show_filter_report';
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			$('#panel').html(html);
			onscroll_activete();
			show_datepicker();
		}
	});
}

function filter_do_report(type){
	if(!type)
		type = '';
	var website = '';
	var show_delete = '', show_office = '', through_tour = '', site_bid = 1, crm_bid = 1;;
	var tour = $('.id-tour-operator').attr('name');
	if($('#url').length)
		website = $('#url').html();
	var surname = $('#surname').val();
	var object = $('.id-object').attr('name');
	var manager = $('#all_manager').val();
	var date_z = $('#date_z').attr('date');
	var date_z2 = $('#date_z2').attr('date');
	var date_op = $('#date_op').attr('date');
	var date_op2 = $('#date_op2').attr('date');
	var date_v = $('#date_v').attr('date');
	var date_v2 = $('#date_v2').attr('date');
	var id = $('#id_schet').val();
	var state_program = null;

	if($('#show-delete').is(':checked'))
		show_delete = 1;
	if($('#through-tour').is(':checked'))
		through_tour = 1;
	if(!$('#show-site-bid').is(':checked'))
		site_bid = 2;
	if(!$('#show-crm-bid').is(':checked'))
		crm_bid = 2;


	if($('#show-state-program-bid').is(':checked'))
		state_program = 1;

	if($('#show-no-state-program-bid').is(':checked')) {
		if(state_program) {
			state_program = null;
		}
		else {
			state_program = 0;
		}
	}

	var status = select_checkbox('status');
	var st_san = select_checkbox('status_san');
	var st_agent = select_checkbox('status_agent');
	var source = select_checkbox('source');
	var region = $('#regions').val();
	var office = $('#office').val();
	var place_object = $('#place_object').val();
	if(!manager && !surname && !object && !date_op && !date_z && !id && !status && !st_san && !type && !st_agent && !tour)
		alert('Введите хотя бы одно поле');
	else{
		$('.btn-search').button('loading');
		$('.btn-hide').attr('disabled', 'disabled');
		var str = 'func=filter_do&type_filter=report&surname=' + surname + '&id_obj=' + object + '&all_manager=' + manager + '&date_z=' + date_z + '&date_z2=' + date_z2 + '&date_op=' + date_op + '&date_op2=' + date_op2 + '&id_schet=' + id + '&status_id=' + status + '&st_san=' + st_san + '&type=' + type + '&date_v=' + date_v + '&date_v2=' + date_v2 + '&st_agent=' + st_agent + '&id_tour=' + tour + '&show_delete=' + show_delete + '&region=' + region + '&website=' + website + '&place_object=' + place_object + '&office=' + office + '&through_tour=' + through_tour + '&source=' + source + '&site_bid=' + site_bid + '&crm_bid=' + crm_bid;

		if(null !== state_program) {
			str += '&state_program='+state_program;
		}

		$('#tbl_head').html('');
		$.ajax({
			url: 'mysql.php',
			type: 'POST',
			data: str,
			success: function(html){
				$('.btn-search').button('reset');
				if(html == '')
					$('.btn-hide').attr('disabled', 'disabled');
				else{
					$('#filter_res').html(html);
					$('.btn-hide').removeAttr('disabled');
					$("#tbl_filter").tablesorter({
						headers:{
							0:{ sorter: false }
						},
						widgets: ['zebra']
					});
				}
				show_tooltip('#filter_res');
			}
		});
		show_loader_element('#filter_res');
	}
}

function general_report_by_agency(){
	$('.small-menu-report button').removeClass('btn-info');
	$('.small-menu-report .btn-agency').addClass('btn-info');
	var str = 'func=show_filter_report_agency';
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			$('#panel').html(html);
		}
	});
}

function filter_report_agency(){
	var month = $('#month').val();
	var year = $('#year').val();
	var stat = $('#select-stat').val();
	var str = 'func=filter_report_agency&month=' + month + '&year=' + year + '&stat=' + stat;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			$('.result').html(html);
		}
	});
	show_loader_element('.result');
}

function general_report_by_object(){
	$('.small-menu-report button').removeClass('btn-info');
	$('.small-menu-report .btn-object').addClass('btn-info');
	var str = 'func=show_filter_report_object';
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			$('#panel').html(html);
			show_datepicker();
		}
	});
}

function filter_do_by_object(){
	var compare = '';
	var month = $('.month-filter').val();
	var year = $('.year-filter').val();
	var start = $('#date-start').val();
	var end = $('#date-end').val();
	if($('.compare-prev-year').prop('checked') == true)
		compare = 1;
	$('.btn-search').button('loading');
	show_loader_element('.result');
	var str = 'func=filter_do_by_object&month=' + month + '&year=' + year + '&compare=' + compare + '&start=' + start + '&end=' + end;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			$('.btn-search').button('reset');
			if(html == '')
				$('.result').html('Не найдено');
			else{
				$('.result').html(html);
				$(".tbl-filter").tablesorter({
					widgets: ['zebra']
				});
			}
		}
	});
}

function payment_report(){
	$('.head-menu-report button').removeClass('btn-success');
	$('.head-menu-report .btn-payment').addClass('btn-success');
	var id_rights = parseInt($('*[data-id-rights]').attr('data-id-rights'));
	if(id_rights > 5)
		var html = '<div class="btn-group btn-group-justified small-menu-report"><div class="btn-group"><button type="button" class="btn btn-default btn-sm btn-all" onclick="payment_report_general()"><i class="fa fa-tasks"></i> Общий</button></div><div class="btn-group"><button type="button" class="btn btn-default btn-sm btn-month" onclick="payment_report_month()"><i class="fa fa-calendar"></i> По месяцам</button></div><div class="btn-group"><button type="button" class="btn btn-default btn-sm btn-return" onclick="return_query_report()"><i class="fa fa-mail-reply"></i> Ожидание возврата</button></div><div class="btn-group"><button type="button" class="btn btn-default btn-sm btn-request-card" onclick="report_request_payment()"><i class="fa fa-credit-card-alt"></i> Запросы оплат картой</button></div><div class="btn-group"><button type="button" class="btn btn-default btn-sm btn-expected" onclick="report_expected_cash_receipts()"><i class="fa fa-clock-o"></i> Ожидаемые поступления</button></div></div><div id="panel" style="margin-top: 10px"></div>';
	else
		var html = '<div class="btn-group btn-group-justified small-menu-report"></div><div id="panel" style="margin-top: 10px"></div>';
	$('#data').html(html);
	payment_report_general();
}

function return_query_report(){
	$('.small-menu-report button').removeClass('btn-info');
	$('.small-menu-report .btn-return').addClass('btn-info');
	var str = 'func=return_query_report';
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			$('#panel').html(html);
		}
	});
}

function payment_report_general(){
	$('.small-menu-report button').removeClass('btn-info');
	$('.small-menu-report .btn-all').addClass('btn-info');
	var str = 'func=general_payment_report';
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			$('#panel').html(html);
			show_datepicker();
		}
	});
}

function filter_payment(){
	var method_opl = $('#method_opl').val();

	var periodSelector = $('#period_selector').val();
	var month_opl = $('#month_opl').val();
	var year_opl = $('#year_opl').val();

	var card_payment_types = 0;
	if(method_opl === "5-1") {
		method_opl = '5,6';
		card_payment_types = 1;
	}
	else if(method_opl === "5-2") {
		method_opl = '5,6';
    card_payment_types = 2;
	}
  else if(method_opl === "5-3") {
    method_opl = '5,6';
    card_payment_types = 3;
  }
  else if(method_opl === "5-4") {
    method_opl = '5,6';
    card_payment_types = 4;
  }
	else {
		//method_opl = parseInt(method_opl);
	}

	var type_opl = $('#type_opl').val();
	var type_pay = $('#type_pay').val();
	var date_opl = $('#date_opl').attr('date');
	var date_opl2 = $('#date_opl2').attr('date');
	var showHoldings = parseInt($('#show-holdings').prop('checked')*1);
	var managerId = parseInt($('#all_manager').val(),10);
	if(!date_opl && periodSelector === 'dates')
		show_warning('#filter_res', 'Введите дату');
	else{
		var str = 'func=filter_payment&date_opl=' + date_opl + '&date_opl2=' + date_opl2 + '&method_opl=' + method_opl + '&type_opl=' + type_opl + '&type_pay=' + type_pay+'&show_holdings='+showHoldings+"&card_payment_types="+card_payment_types+'&manager_id='+managerId+'&period_selector='+periodSelector+'&month_opl='+month_opl+'&year_opl='+year_opl;
		$.ajax({
			url: 'mysql.php',
			type: 'POST',
			data: str,
			success: function(html){
				$('.btn-hide').show();
				$('#filter_res').html(html);
				$(".tbl-filter").tablesorter({ widgets: ['zebra'] });
			}
		});
		show_loader_element('#filter_res');
	}
}

function filter_payment_update(){
	var str = 'func=filter_payment&type=update';
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			$('#filter_res').html(html);
			$("#tbl_filter").tablesorter({ widgets: ['zebra'] });
		}
	});
	show_loader_element('#filter_res');
}

function payment_report_month(){
	$('.small-menu-report button').removeClass('btn-info');
	$('.small-menu-report .btn-month').addClass('btn-info');
	var str = 'func=month_payment_report';
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			$('#panel').html(html);
		}
	});
}

function filter_payment_report_month(){
	var month = $('#month').val();
	var year = $('#year').val();
	var method = $('#payment-method').val();
	var region = $('#regions').val();
	if(!year)
		show_warning('.payment-result', 'Выберите год', false);
	else{
		var str = 'func=filter_payment_month&year=' + year + '&month=' + month + '&method=' + method + '&region=' + region;
		$.ajax({
			url: 'mysql.php',
			type: 'POST',
			data: str,
			success: function(html){
				$('.payment-result').html(html);
			}
		});
		show_loader_element('.payment-result');
	}
}

function check_all(class_check){
	if(document.getElementById('all_'+class_check).checked)
		$('.'+class_check).prop('checked', true);
	else
		$('.'+class_check).prop('checked', false);
}

function plan_report(){
	$('.head-menu-report button').removeClass('btn-success');
	$('.head-menu-report .btn-plan').addClass('btn-success');
	var str = 'func=plan_report';
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			$('#data').html(html);
		}
	});
}

function block_reckoning_month(start, end, user){
	var str = 'func=block_reckoning_month&start=' + start + '&end=' + end + '&user=' + user;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(){
			view_my_profit();
			show_alert('Готово...');
		}
	});
}

function view_all_profit(){
	var month = $('#months').val();
	show_loader('result');
	var str = 'func=view_all_profit&month=' + month;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			$('#result').html(html);
		}
	});
}

function calc_payment_to_san(){
	var all_id = select_checkbox('check_mass');
	if(!all_id)
		alert('Выберите хотя бы одну заявку');
	else{
		var str = 'func=calc_payment_to_san&id=' + all_id;
		$.ajax({
			url: 'mysql.php',
			type: 'POST',
			data: str,
			success: function(html){
				show_modal(html);
			}
		});
	}
}

function show_mass_action(type){
	var all_id = select_checkbox('check_mass');

	if(all_id[all_id.length-1] === '_')
		all_id = all_id.substring(0,all_id.length-1);

	if(all_id){
		var array = all_id.split('_');
		var table = '';
		for(var id in array){
			var id_tr = array[id];
			if($('#tr_'+id_tr).length)
				table+= "<tr>" +$('#tr_'+id_tr).html()+ "</tr>";
		}
		if(type)
			var html = '<div class="modal fade"><div class="modal-dialog modal-giant"><div class="modal-content"><div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-label="Close"><i class="fa fa-times"></i></button><h4 class="modal-title">Массовое действие</h4></div><div class="modal-body modal-body-content"><table class="table table-condensed table-hover">'+table+'</table></div><div class="modal-footer"><button type="button" class="btn btn-success btn-sm" onclick="update_mass_reckoning(\''+all_id+'\', \''+type+'\')"><i class="fa fa-check"></i> Подтвердить</button></div></div></div></div>';
		else
			var html = '<div class="modal fade"><div class="modal-dialog modal-giant"><div class="modal-content"><div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-label="Close"><i class="fa fa-times"></i></button><h4 class="modal-title">Массовое действие</h4></div><div class="modal-body modal-body-content"><table class="table table-condensed table-hover">'+table+'</table></div></div></div></div>';
		show_modal(html);
		$('.modal tr').each(function(){
			$(this).find('td:first').remove();
		});
	}else
		alert('Выберите хотя бы одну заявку');
}

function update_mass_reckoning(all_id, type){
	var str = 'func=update_mass_reckoning&id=' + all_id + '&type=' + type;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			if(html){
				$('.modal-body-content').html(html);
				$('.modal-footer').remove();
			}else{
				remove_all_windows();
				filter_do_update();
			}
		}
	});
}

function menu_mass_action(page){
	var str = 'func=menu_mass_action';
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			show_div_buttons('button_mass', html);
		}
	});
}

function bonus_report(){
	$('.head-menu-report button').removeClass('btn-success');
	$('.head-menu-report .btn-bonus').addClass('btn-success');
	var str = 'func=show_bonus_report_menu';
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			$('#data').html(html);
			bonus_report_general();

		}
	});
}

function bonus_report_general(){
	$('.small-menu-report button').removeClass('btn-info');
	$('.small-menu-report .btn-all').addClass('btn-info');
	var str = 'func=bonus_report_general';
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			$('#panel').html(html);
			show_datepicker();
		}
	});
}

function bonus_report_month(){
	$('.small-menu-report button').removeClass('btn-info');
	$('.small-menu-report .btn-month').addClass('btn-info');
	var str = 'func=bonus_report_month';
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			$('#panel').html(html);
		}
	});
}

function filter_bonus(){
	var date1 = $('#date_bonus').attr('date');
	var date2 = $('#date_bonus2').attr('date');
	var type = $('#type').val();
	if(!date1)
		show_warning('#filter_res', 'Заполните первую дату');
	else{
		var str = 'func=filter_bonus&date1=' + date1 + '&date2=' + date2 + '&type=' + type;
		$.ajax({
			url: 'mysql.php',
			type: 'POST',
			data: str,
			success: function(html){
				$('#filter_res').html(html);
			}
		});
		show_loader('filter_res');
	}
}

function filter_bonus_report_month(){
	var month = $('#month').val();
	var year = $('#year').val();
	if(!year)
		show_warning('#filter_res', 'Выберите год');
	else{
		var str = 'func=filter_bonus_month&year=' + year + '&month=' + month;
		$.ajax({
			url: 'mysql.php',
			type: 'POST',
			data: str,
			success: function(html){
				$('#filter_res').html(html);
			}
		});
		show_loader('filter_res');
	}
}

function history_report(){
	$('.head-menu-report button').removeClass('btn-success');
	$('.head-menu-report .btn-history').addClass('btn-success');
	var str = 'func=history_report';
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			$('#data').html(html);
			show_datepicker();
		}
	});
}

function history_report_global(){
	$('.head-menu-report button').removeClass('btn-success');
	$('.head-menu-report .btn-history-global').addClass('btn-success');
	var str = 'func=history_report_global';
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			$('#data').html(html);
			show_datepicker();
		}
	});
}

function filter_history(){
	var date_1 = $('#date_1').attr('date');
	var date_2 = $('#date_2').attr('date');
	if(!date_1)
		show_warning('#filter_res', 'Введите хотя бы одно поле');
	else{
		var str = 'func=filter_history&date_1=' + date_1 + '&date_2=' + date_2;
		$.ajax({
			url: 'mysql.php',
			type: 'POST',
			data: str,
			success: function(html){
				$('#filter_res').html(html);
				$("#tbl_filter").tablesorter({ widgets: ['zebra'] });
			}
		});
		show_loader('filter_res');
	}
}

function filter_history_global(){
	var date_1 = $('#date_1').attr('date');
	var date_2 = $('#date_2').attr('date');
	if(!date_1)
		show_warning('#filter_res', 'Введите хотя бы одно поле');
	else{
		var str = 'func=filter_history_global&date_1=' + date_1 + '&date_2=' + date_2;
		$.ajax({
			url: 'mysql.php',
			type: 'POST',
			data: str,
			success: function(html){
				$('#filter_res').html(html);
				$("#tbl_filter").tablesorter({ widgets: ['zebra'] });
			}
		});
		show_loader('filter_res');
	}
}

function module_report(){
	$('.head-menu-report button').removeClass('btn-success');
	$('.head-menu-report .btn-module').addClass('btn-success');
	var str = 'func=get_bid_module_agency';
	show_loader_element('#data');
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			$('#data').html(html);
			show_bid_module_agency('new');
		}
	});
}

function show_bid_module_agency(type){
	$('.small-menu-report button').removeClass('btn-info');
	$('.small-menu-report .btn-'+type).addClass('btn-info');
	var str = 'func=show_bid_module_agency&type=' + type;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			$("#panel").html(html);
		}
	});
}

function calendar_report(){
	$('.head-menu-report button').removeClass('btn-success');
	$('.head-menu-report .btn-calendar').addClass('btn-success');
	show_loader_element('#data');
	var str = 'func=calendar_report';
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			$('#data').html(html);
			create_calendar_report();
		}
	});
}

function create_calendar_report(){
	$('#calendar').fullCalendar({
		height: 400,
		header:{
			left: 'prev,next,prevYear,nextYear',
			center: 'title',
			right: ''
		},
		events: function(start, end, callback){
			var date = $("#calendar").fullCalendar('getDate');
			var month = (date.getMonth()) + 1;
			var year = date.getFullYear();
			var object = $('.id-object').attr('name');
			var str = 'func=filter_calendar&year=' + year + '&month=' + month + '&object=' + object;
			$.ajax({
				url: 'mysql.php',
				type: 'POST',
				dataType: 'json',
				data: str,
				success: function(json){
					callback(json['data']);
					$('#itog').html(json['itog']);
				}
			});
		},
		eventClick: function(event){
			var date = event['start'];
			var day = date.getDate();
			var month = (date.getMonth()) + 1;
			var year = date.getFullYear();
			date = year + '-' + month + '-' + day;
			var object = $('.id-object').attr('name');
			var str = 'func=find_reckoning_calendar&date=' + date + '&object=' + object;
			$.ajax({
				url: 'mysql.php',
				type: 'POST',
				data: str,
				success: function(html){
					$('.result').html(html);
				}
			});
		}
	});
}

//графики

var type_chart;

function graphics_report(){
	$('.head-menu-report button').removeClass('btn-success');
	$('.head-menu-report .btn-graphics').addClass('btn-success');
	var str = 'func=show_graphics_report_menu';
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			$('#data').html(html);
			var width = parseInt($('#body').width());
			$('#placeholder').height(width * 0.3);
			graph_current();
		}
	});
}

function clear_panel(){
	$('#placeholder').html("");
	$('#legend').html("");
	$('.small-menu-report button').removeClass('btn-info');
}

function change_choice_graph(){
	var select = $('.choice-graph input:checked').val();
	if(!select || select == 'office'){
		$('.choice-html').html('');
		$('.choice-div').hide();
	}else
		$('.choice-div').show();
	if(select == 'region'){
		var str = 'func=select_regions';
		$.ajax({
			url: 'mysql.php',
			type: 'POST',
			data: str,
			success: function(html){
				$('.choice-html').html(html);
			}
		});
	}else if(select == 'object')
		$('.choice-html').html("<span id='object_name'><input type='hidden' id='id_obj' /><input type='hidden' id='sel_room'><input type='text' onkeyup='find_klient(event, \"object\", \"object\", \"use_object\")' id='object' class='form-control' /></span>");
	else if(select == 'manager'){
		$('.choice-html').html("<div id='managers'></div>");
		var str = 'func=select_managers';
		$.ajax({
			url: 'mysql.php',
			type: 'POST',
			data: str,
			success: function(html){
				$('.choice-html').html(html);
			}
		});
	}
}

function change_status_graph(){
	var date = $('.range-graph input:checked').val();
	if(date == 'my-date')
		$('.my-date-ranges').show();
	else
		$('.my-date-ranges').hide();
}

function create_graph(array){

	var type_chart = $('.type-chart-graph input:checked').val();
	if(type_chart != 'pie'){

		$('#placeholder').highcharts({
			chart: {
				type: type_chart
			},
			title: {
				text: array['title']
			},
			xAxis: {
				categories: array['categories']
			},
			yAxis: {
				min: 0,
				max: array['max'],
				title: {
					text: 'Заявки'
				}
			},
			tooltip: {
				shared: true,
				useHTML: true
			},
			plotOptions: {
				column: {
					pointPadding: 0,
					borderWidth: 0
				},
		      spline: {
		          marker: {
		              enabled: false,
		          }
		      },
		      area: {
				marker: {
					enabled: false,
				}
			}
			},
			series: array['data']
		});

	}else{

		$('#placeholder').highcharts({
			chart: {
				plotBackgroundColor: null,
				plotBorderWidth: null,
				plotShadow: false
			},
			plotOptions: {
				pie: {
					allowPointSelect: true,
					dataLabels: {
		                  	enabled: false
					},
					showInLegend: true
				}
			},
			title: {
				text: array['title']
			},
			series: [{
				type: 'pie',
				name: 'Кол-во заявок',
				data: array['data']
			}]
		});

	}

}

function graph_current(){
	clear_panel();
	$('.small-menu-report .btn-bid').addClass('btn-info');
	var date = new Date();
	year = date.getFullYear();
	var year_select = '', month_select = '', status_checkbox = '', source = '';
	for(var i = 2013; i <= year; i++)
		year_select+= '<option value="' +i+ '">' +i+ '</option>';
	for(var key in month)
		month_select+= '<option value="' +key+ '">' +month[key]+ '</option>';
	for(var key in dataCRM['status-bid'])
		status_checkbox+= '<label class="block"><input type="checkbox" value="' +key+ '" /> ' +dataCRM['status-bid'][key]+ '</label>';
	for(var key in dataCRM['source'])
		source+= '<label class="btn btn-primary btn-xs"><input type="radio" name="source" value="' +key+ '" /> ' +dataCRM['source'][key]['name']+ '</label>';
	var html = '<div class="form-horizontal"><div class="form-group"><div class="col-sm-5"><div class="form-group"><label class="col-sm-3 control-label control-label-padding">Диапазон</label><div class="col-sm-9 btn-group range-graph" data-toggle="buttons"><label class="btn btn-primary btn-xs active"><input type="radio" checked name="range" value="day" onChange="change_status_graph()" />&nbsp;дни&nbsp;</label><label class="btn btn-primary btn-xs"><input type="radio" name="range" value="week" onChange="change_status_graph()" />&nbsp;недели&nbsp;</label><label class="btn btn-primary btn-xs"><input type="radio" name="range" value="month" onChange="change_status_graph()" />&nbsp;месяцы&nbsp;</label><label class="btn btn-primary btn-xs"><input type="radio" name="range" value="my-date" onChange="change_status_graph()" />&nbsp;свои даты&nbsp;</label></div></div><div class="form-group my-date-ranges" style="display: none"><label class="col-sm-3 control-label control-label-padding">Даты</label><div class="col-sm-9"><div class="form-inline">от <select class="form-control input-sm first-month-date">' +month_select+ '</select><select class="form-control input-sm first-year-date">' +year_select+ '</select></div><div class="form-inline">до <select class="form-control input-sm second-month-date">' +month_select+ '</select><select class="form-control input-sm second-year-date">' +year_select+ '</select></div></div></div><div class="form-group"><label class="col-sm-3 control-label control-label-padding">Дата</label><div class="col-sm-9 btn-group date-graph" data-toggle="buttons"><label class="btn btn-primary btn-xs active"><input type="radio" checked name="date" value="date" />&nbsp;заявки&nbsp;</label><label class="btn btn-primary btn-xs"><input type="radio" name="date" value="date_z" />&nbsp;заезда&nbsp;</label><label class="btn btn-primary btn-xs"><input type="radio" name="date" value="date_v" />&nbsp;выезда&nbsp;</label></div></div><div class="form-group"><label class="col-sm-3 control-label control-label-padding">Значения</label><div class="col-sm-9 btn-group value-graph" data-toggle="buttons"><label class="btn btn-primary btn-xs active"><input type="radio" checked name="value" value="allsum" />&nbsp;суммарные&nbsp;</label><label class="btn btn-primary btn-xs"><input type="radio" name="value" value="middle" />&nbsp;среднесуточные&nbsp;</label></div></div><div class="form-group"><label class="col-sm-3 control-label control-label-padding">Данные</label><div class="col-sm-9 btn-group data-graph" data-toggle="buttons"><label class="btn btn-primary btn-xs active"><input type="radio" name="data" checked value="number" />&nbsp;кол-во&nbsp;</label><label class="btn btn-primary btn-xs"><input type="radio" name="data" value="percent" />&nbsp;проценты&nbsp;</label></div></div><div class="form-group"><label class="col-sm-3 control-label control-label-padding">Статус</label><div class="col-sm-9 btn-group status-delete-graph" data-toggle="buttons"><label class="btn btn-primary btn-xs active"><input type="checkbox" name="status" checked value="no-delete" />&nbsp;неудаленные&nbsp;</label><label class="btn btn-primary btn-xs"><input type="checkbox" name="status" value="delete" />&nbsp;удаленные&nbsp;</label></div></div><div class="form-group"><label class="col-sm-3 control-label control-label-padding">Источник</label><div class="col-sm-9 btn-group source-graph" data-toggle="buttons"><label class="btn btn-primary btn-xs active"><input type="radio" checked name="source" value="" /> не важно</label>' +source+ '</div></div><div class="form-group"><label class="col-sm-3 control-label control-label-padding">Выбор</label><div class="col-sm-9 btn-group choice-graph" data-toggle="buttons"><label class="btn btn-primary btn-xs active"><input type="radio" checked name="choice" value="" onChange="change_choice_graph()" />&nbsp;все&nbsp;</label><label class="btn btn-primary btn-xs"><input type="radio" name="choice" value="region" onChange="change_choice_graph()" />&nbsp;регион&nbsp;</label><label class="btn btn-primary btn-xs"><input type="radio" name="choice" value="object" onChange="change_choice_graph()" />&nbsp;объект&nbsp;</label><label class="btn btn-primary btn-xs"><input type="radio" name="choice" value="manager" onChange="change_choice_graph()" />&nbsp;менеджер&nbsp;</label><label class="btn btn-primary btn-xs"><input type="radio" name="choice" value="office" onChange="change_choice_graph()" />&nbsp;офис&nbsp;</label></div></div><div class="form-group choice-div" style="display: none"><label class="col-sm-3 control-label"></label><div class="col-sm-9"><div class="choice-html"></div></div></div><div class="form-group form-group-margin"><div class="col-sm-3"></div><div class="col-sm-9"><button type="button" class="btn btn-success btn-form-graph" onclick="get_data_for_graph_current()"><i class="fa fa-area-chart"></i> Сформировать</button></div></div></div><div class="col-sm-4"><div id="status" class="status-bid-checkbox"><label class="block"><input type="checkbox" value="all" /> Все заявки</label>' +status_checkbox+ '</div></div><div class="col-sm-3 right"><div class="btn-group type-chart-graph" data-toggle="buttons"><label class="btn btn-danger btn-sm active"><input type="radio" checked name="type-chart" value="line" />&nbsp;<i class="fa fa-line-chart"></i>&nbsp;</label><label class="btn btn-danger btn-sm"><input type="radio" name="type-chart" value="column" />&nbsp;<i class="fa fa-bar-chart"></i>&nbsp;</label><label class="btn btn-danger btn-sm"><input type="radio" name="type-chart" value="area" />&nbsp;<i class="fa fa-area-chart"></i>&nbsp;</label><label class="btn btn-danger btn-sm"><input type="radio" name="type-chart" value="pie" />&nbsp;<i class="fa fa-pie-chart"></i>&nbsp;</label></div></div></div></div>';
	$('#panel').html(html);
}

function graph_call_back(){
	clear_panel();
	$('.small-menu-report .btn-call-back').addClass('btn-info');
	var str = 'func=graph_call_back';
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			$('#panel').html(html);
		}
	});
}

function graph_by_cancel(){
	clear_panel();
	$('.small-menu-report .btn-cancel').addClass('btn-info');
	var str = 'func=graph_cancel';
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			$('#panel').html(html);
			$('#reason-delete input').change(function(){
				var status = select_checkbox('reason_delete', 'reason-delete');
				if(status)
					$('.btn-form-graph').removeAttr('disabled');
				else
					$('.btn-form-graph').attr('disabled', 'disabled');
			});
		}
	});
}

function graph_by_client(){
	clear_panel();
	$('.small-menu-report .btn-client').addClass('btn-info');
	var str = 'func=graph_client';
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			$('#panel').html(html);
		}
	});
}

function get_data_for_graph_current(){
	var str;
	var type_chart = $('.type-chart-graph input:checked').val();
	if(type_chart == 'pie'){
		str = 'func=get_data_for_pie_current';
//		str+= '&graph=' + current_params['graph'];
	}else
		str = 'func=get_data_for_graph_current';
	var choice = $('.choice-graph input:checked').val();
	var status = select_checkbox_json('.status-delete-graph');
	var range = $('.range-graph input:checked').val();
	var date = $('.date-graph input:checked').val();
	var source = $('.source-graph input:checked').val();
	str+= '&date_params=' + date + '&span_params=' + range + '&value_params=' + $('.value-graph input:checked').val() + '&choice_params=' + choice + '&choice_data=' + $('.data-graph input:checked').val() + '&source=' + source;
	if(range == 'my-date')
		str+= '&month1=' + $('.first-month-date').val() + '&year1=' + $('.first-year-date').val() + '&month2=' + $('.second-month-date').val() + '&year2=' + $('.second-year-date').val();
	if(choice == 'region')
		str+= '&id_reg=' + $('.choice-html select').val();
	else if(choice == 'object')
		str+= '&id_obj=' + $('.choice-html .id-object').attr('name');
	else if(choice == 'manager')
		str+= '&manager=' + $('.choice-html select').val();
	str+= '&status_delete=' + status + '&status=' + select_checkbox_json('.status-bid-checkbox');
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		dataType: 'JSON',
		success: function(data){
			create_graph(data);
		}
	});
	if(!$('#placeholder').html())
		show_loader_element('#placeholder');
}

function get_data_for_graph_call_back(){
	var status = $('.status-graph input:checked').val();
	var range = $('.range-graph input:checked').val();
	var type = $('.type-graph input:checked').val();
	var str = 'func=get_data_for_graph_call_back&type=' + type + '&status=' + status + '&range=' + range;
	if(range == 'my-date')
		str+= '&month1=' + $('.first-month-date').val() + '&year1=' + $('.first-year-date').val() + '&month2=' + $('.second-month-date').val() + '&year2=' + $('.second-year-date').val();
	$('.btn-form-graph').button('loading');
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		dataType: 'JSON',
		success: function(data){
			create_graph(data);
			$('.btn-form-graph').button('reset');
		}
	});
	if(!$('#placeholder').html())
		show_loader_element('#placeholder');
}

function get_data_for_graph_cancel(){
	var date_params = $('.range-graph input:checked').val();
	var type = $('.choice-graph input:checked').val();
	var id_obj = $('.id-object').attr('name');
	var reason = select_checkbox('reason_delete', 'reason-delete');
	var str = 'func=get_data_for_graph_cancel&date_params=' + date_params + '&id_obj=' + id_obj + '&reason=' + reason + '&type=' + type;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		dataType: 'JSON',
		success: function(data){
			create_graph(data);
		}
	});
	show_loader('placeholder');
	$('#legend').html("");
}

function get_data_for_graph_client(){
	var date_params = $('.range-graph input:checked').val();
	var str = 'func=get_data_for_graph_client&date_params=' + date_params;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		dataType: 'JSON',
		success: function(data){
			create_graph(data);
		}
	});
	show_loader_element('#placeholder');
}

function selected_element(div){
	var answer = "";
	$('#'+div+' select').each(function(){
		if(this.value)
			answer+= this.value + "_";
	});
	return answer;
}

function select_fil_object(name){
	document.getElementById('fil_object_text').value = name;
	$('#result_obj').html("");
	$('#result_obj').hide();
}

function select_fil_manager(name){
	document.getElementById('fil_manager_text').value = name;
	$('#result_man').html("");
	$('#result_man').hide();
}

function hide_fil(obj){
	if(obj == 1){
		var id = 'result_obj';
		var text = 'Объект';
		var text_obj = 'fil_object_text';
	}
	else if(obj == 2){
		var id = 'result_man';
		var text = 'Менеджер';
		var text_obj = 'fil_manager_text';
	}
	if(document.getElementById(text_obj).value == '')
		document.getElementById(text_obj).value = text;
	$('#'+id).hide();
}

function onscroll_activete(){
	var height = $(window).height();
	window.onscroll = function(){
		if(document.getElementById('head_filter')){
			var scroll = getBodyScrollTop();
 			if((scroll > 200) && (document.documentElement.scrollHeight > height))
				$('#filter_tr').addClass("tr_fixed");
			else if(scroll <= 200)
				$('#filter_tr').removeClass("tr_fixed");
		}
	}
}

function form_agent_report(id){
	window.open('document.php?func=report_agent&id=' + id, 'Отчеты агента');
}

function open_schet_san(){
	var all_id = select_checkbox('check_mass');
	if(all_id){
		var str = 'func=get_schet_san&all_id=' + all_id;
		$.ajax({
			url: 'mysql.php',
			type: 'POST',
			data: str,
			dataType: 'JSON',
			success: function(data){
				for(var id in data['schet']){
					var schet = data['schet'][id]['doc'];
					if(schet)
						window.open('temp/schet/' + schet, '_blank');
				}
				if(data['no-schet']){
					var html = 'Счета не добавлены: ' + data['no-schet'];
					show_popup(html);
				}
			}
		});
	}else
		alert('Выберите хотя бы одну заявку');
}

function graph_by_rating(){
	clear_panel();
	$('.small-menu-report .btn-rating').addClass('btn-info');
	var str = 'func=graph_rating';
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			$('#panel').html(html);
		}
	});
}

function get_data_for_graph_rating(){
	var date = $('.range-graph input:checked').val();
	var value = $('.value-graph input:checked').val();
	$('.btn-graph').button('loading');
	var str = 'func=get_data_for_graph_rating&date=' + date + '&value=' + value;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		dataType: 'JSON',
		success: function(data){
			create_graph(data);
			$('.btn-graph').button('reset');
		}
	});
}

function show_order_call_back_report(){
	$('.small-menu-report button').removeClass('btn-info');
	$('.small-menu-report .btn-call-back').addClass('btn-info');
	var str = 'func=show_order_call_back_report';
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			$('#panel').html(html);
			show_datepicker();
		}
	});
}

function filter_order_call_back_report(){
	var date1 = $('#date-1').attr('date');
	var date2 = $('#date-2').attr('date');
	var manager = $('#all_manager').val();
	var status = $('.status-call-back').val();
	if(!date1)
		show_warning('.result', 'Укажите дату заказа', false);
	else{
		$('.btn-search').button('loading');
		show_loader_element('.result');
		var str = 'func=filter_order_call_back_report&date1=' + date1 + '&date2=' + date2 + '&manager=' + manager + '&status=' + status;
		$.ajax({
			url: 'mysql.php',
			type: 'POST',
			data: str,
			success: function(html){
				$('.btn-search').button('reset');
				$('.result').html(html);
				$(".tbl-filter").tablesorter({
					widgets: ['zebra']
				});
			}
		});
	}
}

function report_request_payment(){
	$('.small-menu-report button').removeClass('btn-info');
	$('.small-menu-report .btn-request-card').addClass('btn-info');
	var str = 'func=report_request_payment';
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		dataType: 'JSON',
		success: function(data){
			var html = '<table class="table table-bordered table-condensed"><tr><th>Заявка</th><th>Сумма</th><th>Тип</th><th>Дата</th></tr>';
			for(var index in data){
				var request = data[index];
				var status = request['status'];
				var type = request['type'];
				var type_pay = 'Оплата';
				if(type == 2)
					type_pay = 'Предоплата';
				var bgClass = '';
				if(status == 1)
					bgClass = 'warning';
				if(status == 2)
					bgClass = 'success';
				if(status == 6 || status == 3)
					bgClass = 'danger';
				if(status == 4)
					bgClass = 'info';
				html+= '<tr class="' +bgClass+ '"><td>' +request['bid']+ '</td><td>' +request['sum']+ '</td><td>' +type_pay+ '</td><td>' +request['date']+ '</td></tr>';
			}
			html+= '</table>';
			$('#panel').html(html);
		}
	});
}

function report_expected_cash_receipts(){
	$('.small-menu-report button').removeClass('btn-info');
	$('.small-menu-report .btn-expected').addClass('btn-info');
	var str = 'func=report_expected_cash_receipts';
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		dataType: 'JSON',
		success: function(data){
			var html = '<table class="table table-bordered table-condensed"><tr><th>Дата</th><th>Заявка</th><th>Сумма</th><th>Придут</th></tr>';
			for(var index in data){
				for(var ind in data[index]['pay']){
					var payment = data[index]['pay'][ind];
					html+= '<tr><td>' +payment['date']+ '</td><td>' +payment['bid']+ '</td><td>' +payment['sum']+ '</td><td>' +payment['day']+ '</td></tr>';
				}
				html+= '<tr class="success"><td colspan="2"></td><td>' +data[index]['sum']+ '</td><td></td></tr>';
			}
			html+= '</table>';
			$('#panel').html(html);
		}
	});
}

function report_advertising(){
	$('.head-menu-report button').removeClass('btn-success');
	$('.head-menu-report .btn-advertising').addClass('btn-success');
	var str = 'func=select_direct_campain';
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		dataType: 'JSON',
		success: function(data){
			var html = '<div class="form-horizontal panel panel-default"><div class="panel-body"><div class="form-group form-group-margin"><div class="col-sm-4"><input type="text" class="form-control datepicker" id="start-date" placeholder="Укажите дату" /></div><div class="col-sm-4"><input type="text" class="form-control datepicker" id="end-date" placeholder="Укажите дату" /></div><div class="col-sm-4"><button class="btn btn-success btn-lt btn-block btn-report-ad" onclick="form_report_advertising()"><i class="fa fa-search"></i> Сформировать</button></div></div></div></div><table class="table tbl-advertising">';
			html+= '<tr number="0"><th width="40%">Кампания</th><th width="30%"><label><input type="checkbox" onChange="$(\'.check-form\').toggleChecked(\'.toggle-check-form\')" class="toggle-check-form" />Выбрать все</label></th><th width="30%"><label><input type="checkbox" onchange="$(\'.check-use-bid\').toggleChecked(\'.toggle-check-use-bid\')" class="toggle-check-use-bid" />Выбрать все</label></th></tr>';
			for(var index in data){
				var row = data[index];
				html+= '<tr number="' +index+ '"><td width="40%">' +row['name']+ '</td><td width="30%"><label><input type="checkbox" class="check-form" /> сформировать</label></td><td width="30%"><label><input type="checkbox" class="check-use-bid" /> учитывать заявки вручную</label></td></tr>';
			}
			html+= '</table>';
			$('#data').html(html);
			show_datepicker();
		}
	});
}

function form_report_advertising(){
	var zapros = new Object();
	$('.tbl-advertising tr').each(function(){
		var index = $(this).attr('number');
		if($(this).find('.check-form').prop('checked')){
			zapros[index] = 1;
			if($(this).find('.check-use-bid').prop('checked'))
				zapros[index] = 2;
		}
	});
	var data = JSON.stringify(zapros);
	var start = $('#start-date').attr('date');
	var end = $('#end-date').attr('date');
	if(!start)
		alert('Укажите начальную дату');
	else{
		$('.btn-report-ad').attr('disabled', 'disabled');
		var str = 'func=form_report_advertising&data=' + data + '&start=' + start + '&end=' + end;
		$.ajax({
			url: 'mysql.php',
			type: 'POST',
			data: str,
			dataType: 'JSON',
			success: function(data){
				var html = '<table class="table table-condensed table-hover"><tr><th>Кампания</th><th>Расход</th><th>Заявок</th><th>В работе</th><th>Опл</th><th>Ср.$ заявки</th><th>Доход</th><th>+/-</th></tr>';
				for(var index in data){
					var row = data[index];
					var avg_reck=row['spend']/row['count'];
					avg_reck=avg_reck.toFixed(2);
					if (isNaN(avg_reck)||avg_reck==Infinity)
						avg_reck=0;
					html+= '<tr><td width="30%">' +row['name']+ '</td><td width="10%">' +row['spend']+ '</td><td width="10%">' +row['count']+ '</td><td width="10%">' +row['count-work']+ '</td><td width="10%">' +row['count-pay']+ '</td><td width="10%">' +avg_reck+ '</td><td width="10%">' +row['reward']+ '</td><td width="10%">' +row['itog']+ '</td></tr>';
				}
				html+= '</table>';
				$('#data').html(html);
			}
		});
	}
}







function cabinet_object_report(){
	$('.head-menu-report button').removeClass('btn-success');
	$('.head-menu-report .btn-cabinet-object').addClass('btn-success');
	var html = '<div class="btn-group small-menu-cabinet"><div class="btn-group"><button type="button" class="btn btn-default btn-sm btn-booking-module" onclick="report_booking_module_cabinet()"><i class="fa fa-home"></i> Модуль бронирования</button></div><div class="btn-group"><button type="button" class="btn btn-default btn-sm btn-booking-request-module" onclick="report_booking_request_module_cabinet()"><i class="fa fa-search"></i> Заявки с модуля бронирования</button></div><div class="btn-group"><button type="button" class="btn btn-default btn-sm btn-comparison-module" onclick="report_comparison_object()"><i class="fa fa-rub"></i> Модуль сравнения цен</button></div></div><div id="panel" style="margin-top: 10px"></div><div id="report-html"></div>';
	$('#data').html(html);
	report_booking_module_cabinet();
  report_comparison_objects_updates();
}

function report_booking_module_cabinet(){
	$('.small-menu-cabinet button').removeClass('btn-info');
	$('.small-menu-cabinet .btn-booking-module').addClass('btn-info');
	var str = 'func=report_booking_module_cabinet';
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		dataType: 'JSON',
		success: function(data){
			var html = '<table class="table table-condensed table-hover"><tr><th>Дата создания</th><th>Объект</th><th>Сайт</th><th>Телефон</th><th>Email</th></tr>';
			for(var index in data){
				var row = data[index];
				html+= '<tr><td width="15%">' +row['date']+ '</td><td width="25%">' +row['object']+ '</td><td width="20%">' +row['website']+ '</td><td width="20%">' +row['telephone']+ '</td><td width="20%">' +row['email']+ '</td></tr>';
			}
			html+= '</table>';
			$('#report-html').html(html);
		}
	});
}

function report_comparison_objects_updates() {
  $('.small-menu-cabinet button').removeClass('btn-info');
  $('.small-menu-cabinet .btn-booking-module').addClass('btn-info');
  var str = 'func=report_comparison_objects_updates';
  $.ajax({
    url: 'mysql.php',
    type: 'POST',
    data: str,
    dataType: 'JSON',
    success: function(data){
      if(data['updates_count'] > 0) {
      	$('.btn-comparison-module').append('<span class="badge count-red pull-right">'+data['updates_count']+'</span>');
			}
    }
  });
}

function report_booking_request_module_cabinet(){
	$('.small-menu-cabinet button').removeClass('btn-info');
	$('.small-menu-cabinet .btn-booking-request-module').addClass('btn-info');
	var str = 'func=report_booking_request_module_cabinet';
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		dataType: 'JSON',
		success: function(data){
			var html = '<table class="table table-condensed table-hover"><tr><th>Дата</th><th>Объект</th><th>Заезд</th><th>Сумма</th><th>Статус</th></tr>';
			for(var index in data){
				var row = data[index];
				html+= '<tr><td width="10%">' +row['date']+ '</td><td width="30%">' +row['object']+ '</td><td width="10%">' +row['arrival']+ '</td><td width="10%">' +row['sum']+ '</td><td width="30%">' +row['status']+ '</td></tr>';
			}
			html+= '</table>';
			$('#report-html').html(html);
		}
	});
}

function report_comparison_object(){
	$('.small-menu-cabinet button').removeClass('btn-info');
	$('.small-menu-cabinet .btn-comparison-module').addClass('btn-info');
	var str = 'func=report_comparison_object';
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		dataType: 'JSON',
		success: function(data){
			var html = '<div class="form-horizontal list-group">';
			var i;
			for(i = 0; i <data.length; i++){
				var row = data[i];
				var bgColor = 'success';
				var changedStatus = '';
				if(row['class'] == 0){
					bgColor = 'danger';
				}
				var btn_update = 'default';
				if(row['update'] == 1){
					btn_update = 'danger';
				}

				if(row['changed_status'] == 1) {
					changedStatus = ' changed-status-row';
				}
				var payment_btn = '';
				if(row['contract_request']) {
					payment_btn = row['contract_request'];
				}
				html+= '<div class="list-group-item list-group-item-' +bgColor+ changedStatus +'"><div class="form-group form-group-margin"><div class="col-sm-1">' +row['date']+ '</div><div class="col-sm-3">' +row['object']+ '</div><div class="col-sm-1">' +row['validity']+ '</div><div class="col-sm-2">' +row['rate']+ '</div><div class="col-sm-2"><button class="btn btn-default btn-sm" onclick="edit_comparison_object(' +row['object_id']+ ')"><i class="fa fa-pencil"></i></button> <button type="button" class="btn btn-' +btn_update+ ' btn-update-' +row['object_id']+ ' btn-sm" onclick="sync_comparison_object(' +row['object_id']+ ')"><i class="fa fa-check-circle"></i> Обновить</button></div><div class="col-sm-2">'+payment_btn+'</div></div></div>';
			}
			html+= '</div>';
			$('#report-html').html(html);
			setTimeout('$(".btn-comparison-module .badge").remove()',3000);
		}
	});
}

function edit_comparison_object(object){
	var str = 'func=edit_comparison_object&object=' + object;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		dataType: 'JSON',
		success: function(data){
			var html = '<div class="modal fade">' +
				'<div class="modal-dialog">' +
					'<div class="modal-content">' +
						'<div class="modal-header">' +
							'<button type="button" class="close" data-dismiss="modal" aria-label="Close"><i class="fa fa-times"></i></button>' +
							'<h4 class="modal-title">Изменить модуль сравнения цен</h4>' +
						'</div>' +
						'<div class="modal-body form-horizontal edit-module">' +
							'<div class="form-group">' +
								'<label class="col-sm-4 control-label">Дествует до</label>' +
								'<div class="col-sm-8">' +
									'<input type="text" class="form-control datepicker" id="validity-date" value="' +data['module']['validity_date']+ '" />' +
								'</div>' +
							'</div>' +
							'<div class="form-group form-group-margin">' +
								'<label class="col-sm-4 control-label">Тариф</label>' +
								'<div class="col-sm-8">' +
									'<select class="form-control rate">';
			for(var index in data['rate']){
				var selected = '';
				var rate = data['rate'][index];
				if(index == data['module']['rate']){
					selected = ' SELECTED ';
				}
				html+= '<option value="' +index+ '" ' +selected+ '>' +rate['name']+ '</option>';
			}
			html+=	'</select>' +
								'</div>' +
							'</div>' +
						'</div>' +
						'<div class="modal-footer">' +
							'<button type="button" class="btn btn-success btn-sm" onclick="update_comparison_object(' +object+ ')"><i class="fa fa-check"></i> Сохранить</button>' +
						'</div>' +
					'</div>' +
				'</div>' +
			'</div>';
			show_modal(html);
			show_datepicker();
		}
	});
}

function update_comparison_object(object){
	var rate = $('.edit-module .rate').val();
	var date = $('.edit-module #validity-date').val();
	var str = 'func=update_comparison_object&object=' + object + '&rate=' + rate + '&date=' + date;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(){
			remove_all_windows();
			report_comparison_object();
		}
	});
}

function sync_comparison_object(object){
	$('.btn-update-'+object).attr('disabled', 'disabled');
	var str = 'func=sync_comparison_object&object=' + object;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(){
			$('.btn-update-'+object).removeClass('btn-danger').addClass('btn-default');
		}
	});
}

$(document).ready(function (e) {
	$(document).on('change','#period_selector', function (e) {
		var periodSelector = $(this).val();
		var $date_opl = $('#date_opl');
		var $date_opl2 = $('#date_opl2');
		var $month_opl = $('#month_opl');
		var $year_opl = $('#year_opl');
		$date_opl.addClass('hidden');
		$date_opl2.addClass('hidden');
		$month_opl.addClass('hidden');
		$year_opl.addClass('hidden');

		if(periodSelector === 'dates') {
			$date_opl.removeClass('hidden');
			$date_opl2.removeClass('hidden');
		}
		else if(periodSelector === 'month') {
			$month_opl.removeClass('hidden');
			$year_opl.removeClass('hidden');
		}
		else if(periodSelector === 'year') {
			$year_opl.removeClass('hidden');
		}
	});
});
