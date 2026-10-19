var root=document.getElementById(dom),$root=$(root),button=$root.find('.ptl-start'),result=$root.find('.ptl-result'),timer,busy=false;
function displayDate(value){return String(value||'').replace(/^(\d{4})-(\d{2})-(\d{2})$/, '$3.$2.$1');}
function present(data){
    initial=data;button.prop('disabled',data.active||!data.eligible).text(data.active?(data.status==='queued'?'Проверка в очереди…':'Проверяем наличие и цену…'):'Проверить наличие и цену');result.empty();
    function line(t,cls){$('<div>').text(t).addClass(cls||'').appendTo(result);}
    if(data.reason)line(data.reason,'text-warning');
    var p=data.parameters,r=data.result;
    if(p&&data.status!=='idle'){
        line('Номер: '+p.room_name);
        line('Тариф: '+p.rate_name);
        line(displayDate(p.arrival)+' — '+displayDate(p.departure)+', взрослых: '+p.adults);
    }
    if(data.active)line('Можно закрыть заявку: проверка продолжится на сервере. Обычно результат появляется в течение нескольких минут.');
    if(data.stale)line('Параметры заявки изменились. Этот результат относится к прежним параметрам.','text-warning');
    if(r){
        if(data.status==='available'){
            line('Номер по выбранному тарифу доступен на момент проверки','text-success');
            line('Текущая стоимость за весь заезд: '+Number(r.total).toLocaleString('ru-RU',{minimumFractionDigits:2,maximumFractionDigits:2})+' '+(r.currency||'RUB'));
            if(p&&r.currency==='RUB'){
                var difference=Math.round((Number(r.total)-Number(p.quoted_total))*100)/100;
                line('Стоимость номера в заявке: '+Number(p.quoted_total).toLocaleString('ru-RU')+' ₽. '+(difference===0?'Цена совпадает.':'Разница: '+(difference>0?'+':'')+difference.toLocaleString('ru-RU')+' ₽.'));
            }
        }else if(data.status==='unavailable')line(r.message||'Выбранное предложение недоступно.','text-warning');
        else if(data.status==='error')line(r.message||'Не удалось выполнить проверку.','text-danger');
        if(r.checked_at)line('Проверено: '+new Date(r.checked_at).toLocaleString('ru-RU'));
        if(r.url&&/^https:\/\//i.test(r.url))$('<a>').attr({href:r.url,target:'_blank',rel:'noopener noreferrer'}).text('Посмотреть наличие и цены по этой заявке на сайте объекта').appendTo(result);
    }
    if(data.warning)line(data.warning,'text-warning');
}
function schedule(){clearTimeout(timer);if(document.getElementById(dom)===root)timer=setTimeout(function(){send('status');},initial.active?6000:30000);}
function send(action){
    if(busy||document.getElementById(dom)!==root)return;
    busy=true;if(action==='start')button.prop('disabled',true).text('Запускаем проверку…');
    $.ajax({url:'price_tonia_live_check.php',type:'POST',dataType:'json',timeout:12000,data:{booking_id:booking,action:action,csrf:csrf}}).done(present).fail(function(xhr){
        button.prop('disabled',true);var message=xhr.responseJSON&&xhr.responseJSON.error;
        result.empty().append($('<div>').addClass('text-warning').text(message||'Уточняем статус проверки. Повторный запуск пока недоступен.'));
        if(action==='start'||!message)initial.active=true;
    }).always(function(){busy=false;schedule();});
}
button.on('click',function(){send('start');});present(initial);if(initial.active)send('status');else schedule();
