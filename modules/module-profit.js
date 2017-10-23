function show_profit(){
	select_menu('commission_menu');
	var str = 'func=show_profit';
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			$('#body').html(html);
		}
	});
	
}

function view_my_profit(){
	var id = '', month = '';
	if($('#all_manager').length)
		id = $('#all_manager').val();
	if($('#months').length)
		month = $('#months').val();
	var str = 'func=view_my_profit&id=' + id + '&month=' + month;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			$('#result').html(html);
		}
	});
	show_loader_element('#result');
}

function see_managers(){
	select_menu('plan-manager');
	var str = 'func=see_managers';
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			$('#body').html(html);
			see_plan_manager();
		}
	});
}

function see_plan_manager(){
	var month = $('#months').val();
	if(!month)
		month = "";
	show_loader('results');
	var str = 'func=see_plan_manager&month=' + month;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			$('#results').html(html);
		}
	});
}

function edit_plan_manager(id){
	var str = 'func=edit_plan_manager&id=' + id;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			show_modal(html);
		}
	});
}

function update_plan_manager(id){
	var plan = $('#plan').val();
	var commis = $('#commis').val();
	var str = 'func=update_plan_manager&plan=' + plan + '&commis=' + commis + '&id=' + id;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(){
			remove_all_windows();
			see_plan_manager();
			show_alert('Сохранено...');
		}
	});
}

function add_plan_manager(manager){
	var str = 'func=add_plan_manager&manager=' + manager;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			show_modal(html);
		}
	});
}

function save_plan_manager(manager){
	var plan = $('#new_plan').val();
	var commis = $('#new_commis').val();
	var month = $('#months').val();
	if(!commis)
		show_warning('.plan', 'Укажите комиссию', false);
	else{
		var str = 'func=save_plan_manager&plan=' + plan + '&commis=' + commis + '&manager=' + manager + '&month=' + month;
		$.ajax({
			url: 'mysql.php',
			type: 'POST',
			data: str,
			success: function(){
				remove_all_windows();
				see_plan_manager();
				show_alert('Сохранено...');
			}
		});
	}
}
