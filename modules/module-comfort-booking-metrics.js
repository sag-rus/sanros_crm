/* Site-owned monitoring; no object or metric records are persisted in CRM. */
function comfort_booking_metrics(parent) {
    var root = $('<section class="panel panel-default">').appendTo(parent);
    var controls = $('<div class="panel-heading">').appendTo(root);
    $('<strong>').text('Нагрузка san-ros.rf и активность Comfort Booking').appendTo(controls);
    var period = $('<select class="form-control" aria-label="Период графиков">').css({display:'inline-block',width:'auto',margin:'0 15px'}).appendTo(controls);
    [[1,'1 час'],[6,'6 часов'],[24,'24 часа'],[168,'7 дней'],[720,'30 дней']].forEach(function(v) {
        $('<option>').val(v[0]).text(v[1]).appendTo(period);
    });
    period.val('6');
    var refresh = $('<button type="button" class="btn btn-default">Обновить графики</button>').appendTo(controls);
    var body = $('<div class="panel-body">').appendTo(root);
    var status = $('<p role="status">').appendTo(body);
    $('<p class="text-muted">').text('Сбор каждую минуту, хранение 30 дней. Время — московское. Все графики имеют одинаковый период; выделите участок мышью для увеличения. Пропуски означают отсутствие измерений.').appendTo(body);
    $('<p class="text-muted">').text('CPU обработчиков CB — только процессорное время PHP при приёме вебхуков/цен и обработке очередей контента/цен; общая БД, фотографии, поиск и бронирования сюда не входят. Это показатель для сопоставления, а не полная доля нагрузки интеграции. Длительность и CPU задания учитываются в минуте его завершения, включая неудачные попытки. Длительность включает ожидание БД и сети; при параллельной работе может превышать 60 с/мин.').appendTo(body);
    var plots = $('<div>').appendTo(body), charts = [], request = null;
    function timeLabel(ts) { return new Date(ts).toLocaleString('ru-RU',{timeZone:'Europe/Moscow'}); }
    function clearCharts() { charts.forEach(function(c) { c.destroy(); }); charts=[]; plots.empty(); }
    function load() {
        if(request) return;
        refresh.prop('disabled',true); period.prop('disabled',true); status.text('Загрузка графиков…');
        request=$.ajax({url:'comfort_booking.php',type:'POST',dataType:'json',timeout:45000,
            data:{action:'server-metrics',hours:period.val()}}).done(function(result) {
            if(!$.contains(document,root[0]))return;
            if(!result.success) {status.text('Не удалось загрузить графики.');return;}
            var d=result.data, latest=d.latest;
            clearCharts();
            if(!latest) {status.text('Измерений пока нет. Первый замер появится после запуска сборщика.');return;}
            var stale=d.now-Number(latest.recorded_at)>180;
            status.css('color',stale?'#a94442':'').text((stale?'Сборщик давно не присылал данные. ':'')+
                'Последний замер: '+timeLabel(Number(latest.recorded_at)*1000)+' МСК. Память: '+
                (Number(latest.memory_used_mb)/1024).toFixed(1)+' из '+(Number(latest.memory_total_mb)/1024).toFixed(1)+' ГБ. CPU: '+latest.cores+' ядер. Шаг графика: '+d.step/60+' мин.');
            var server={},activity={},times=[];
            d.server.forEach(function(v){server[Number(v.time)]=v;});
            d.activity.forEach(function(v){activity[Number(v.time)]=v;});
            for(var t=Number(d.from);t<=Math.floor(d.now/d.step)*d.step;t+=Number(d.step))times.push(t);
            function series(name,source,key,factor) {
                return {name:name,data:times.map(function(t) {
                    var v=source[t], n=v&&v[key];
                    return [t*1000,n===undefined||n===null?null:Number(n)*(factor||1)];
                })};
            }
            function graph(title,unit,items,max) {
                var el=$('<div>').css({height:'280px',marginBottom:'20px'}).appendTo(plots);
                el.highcharts({chart:{type:'line',zoomType:'x',animation:false},credits:{enabled:false},
                    title:{text:title},xAxis:{type:'datetime',min:Number(d.from)*1000,max:d.now*1000,
                        labels:{formatter:function(){return new Date(this.value).toLocaleString('ru-RU',{timeZone:'Europe/Moscow',day:'2-digit',month:'2-digit',hour:'2-digit',minute:'2-digit'});}},
                        events:{setExtremes:function(e){if(e.trigger==='cb-sync')return;var own=this;
                            charts.forEach(function(c){if(c.xAxis[0]!==own)c.xAxis[0].setExtremes(e.min,e.max,true,false,{trigger:'cb-sync'});});}}},
                    yAxis:{min:0,max:max,title:{text:unit}},plotOptions:{series:{animation:false,connectNulls:false,marker:{enabled:false}}},
                    tooltip:{shared:true,formatter:function(){var text=timeLabel(this.x)+' МСК';
                        this.points.forEach(function(p){text+='<br>'+p.series.name+': <b>'+p.y.toFixed(2)+'</b> '+unit;});return text;}},series:items});
                charts.push(el.highcharts());
            }
            graph('Процессор', '%', [series('CPU сервера, среднее',server,'cpu'),series('CPU сервера, максимум',server,'cpu_max'),
                series('Ожидание диска (iowait)',server,'iowait')],100);
            graph('Оперативная память (без доступного файлового кэша)', '%',
                [series('RAM, среднее',server,'memory'),series('RAM, максимум',server,'memory_max')],100);
            graph('Активность Comfort Booking', 'в минуту',[
                series('Входящие пакеты вебхуков',activity,'webhook',60/d.step),
                series('Приём запросов цен / наличия',activity,'receive',60/d.step),
                series('Обработка контента / сопоставлений',activity,'content',60/d.step),
                series('Применение обновлений цен / наличия',activity,'apply',60/d.step),
                series('Неудачные попытки',activity,'errors',60/d.step)]);
            graph('Время работы обработчиков Comfort Booking', 'с/мин',[
                series('Длительность с ожиданием сети и БД',activity,'work_seconds',60/d.step),
                series('Процессорное время PHP',activity,'cpu_seconds',60/d.step)]);
        }).fail(function(){if($.contains(document,root[0]))status.text('Не удалось получить графики. Попробуйте обновить.');})
            .always(function(){request=null;refresh.prop('disabled',false);period.prop('disabled',false);});
    }
    refresh.on('click',load);period.on('change',load);load();
    var timer=setInterval(function(){
        if(!$.contains(document,root[0])) {clearInterval(timer);if(request)request.abort();clearCharts();return;}
        if(!document.hidden)load();
    },60000);
}
