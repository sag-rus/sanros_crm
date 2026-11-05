/* Booking data is kept by the website; this panel holds it only while the request is open. */
(function (w, $) {
    'use strict';
    var poll = null;
    function el(tag, text, cls) { return $('<' + tag + '>').addClass(cls || '').text(text || ''); }
    function button(text, cls, fn) { return el('button', text, 'btn ' + cls).attr('type', 'button').on('click', fn); }
    function date(v) { return /^\d{4}-\d{2}-\d{2}$/.test(v || '') ? v : ''; }
    w.cbBookingMount = function (requestId, autoOpen) {
        if (poll) { clearTimeout(poll); poll = null; }
        var box = $('<section class="panel panel-default" style="margin-top:15px"></section>');
        var body = $('<div class="panel-body"></div>').hide(), state = null, csrf = '', caps = {}, defaults = {}, busy = false;
        var alert = $('<div class="alert alert-danger"></div>').hide(), content = $('<div></div>');
        var head = $('<div class="panel-heading"></div>').append(el('strong', 'Бронь Comfort Booking'));
        head.append(button('Открыть / обновить', 'btn-default btn-sm', function () { body.show(); load(); }).css('margin-left', '15px'));
        box.append(head, body.append(alert, content)); $('#info_turist').prepend(box);
        function alive() { return $.contains(document, box[0]); }
        function error(message) { alert.text(message).show(); }
        function api(action, extra, done, fail) {
            if (busy || !alive()) return;
            busy = true; alert.hide(); box.find('button').prop('disabled', true);
            $.ajax({url: 'comfort_booking_reservations.php', type: 'POST', dataType: 'json', timeout: 50000,
                data: $.extend({action: action, requestId: requestId, csrf: csrf}, extra || {})
            }).done(function (r) {
                if (!alive()) return;
                if (!r.success) { error(r.error); if (fail) fail(); return; }
                csrf = r.csrf; caps = r; defaults = r.defaults || {}; done(r.data);
            }).fail(function (xhr) {
                if (!alive()) return;
                var r = xhr.responseJSON; error(r && r.error ? r.error : 'Ответ не получен. Обновите состояние брони перед новым действием.'); if (fail) fail();
            }).always(function () { busy = false; if (alive()) box.find('button').not('[data-cb-disabled]').prop('disabled', false); });
        }
        function load() { api('detail', {}, function (d) { state = d; render(); }); }
        function line(target, label, value) { target.append(el('p', label + ': ' + (value === null || value === undefined || value === '' ? '—' : value))); }
        function list(target, values) { $.each(values || [], function (_, t) { target.append(el('p', t, 'text-muted')); }); }
        function personName(p) { return [p.lastName, p.firstName, p.middleName].filter(Boolean).join(' '); }
        function summary(target, s, hideHotel) {
            if(!hideHotel)line(target, 'Объект', s.hotel); line(target, 'Проживание', s.arrival + ' — ' + s.departure);
            line(target, 'Номер / тариф', s.room + ' / ' + s.rate); line(target, 'Гости', s.adults + ' взрослых, ' + s.children + ' детей');
            line(target, 'Стоимость', s.total + ' RUB'); line(target, 'Предоплата в СРМ', s.prepayment + ' RUB'); line(target, 'Оплата', s.payment);
            line(target, 'Заказчик', personName(s.customer || {}));
            $.each(s.guests || [], function (_, g) { line(target, 'Гость', personName(g) + (g.isChild ? ' (' + g.age + ' лет)' : '')); });
            if (s.comment) line(target, 'Комментарий', s.comment);
        }
        function render() {
            content.empty(); if (poll) clearTimeout(poll);
            content.append(el('p', 'Изменение или закрытие заявки в СРМ не меняет бронь. Отмена здесь не выполняет денежный возврат.', 'text-muted'));
            if (!state.booking) {
                content.append(el('p', 'По этой заявке нет связанной брони Comfort Booking. Новые брони создаются при отправке формы сайта. Старая заявка сама по себе не является бронью.'));

                return;
            }
            var b = state.booking, stay = b.roomStays[0], h = state.hotel || {};
            line(content, 'Номер на сайте', b.number); line(content, 'Номер TravelLine', state.externalNumber);
            line(content, 'Операция', {new:'Создание', modified:'Изменение', cancelled:'Отмена'}[b.status] || b.status);
            line(content, 'Доставка в TravelLine', {waiting:'Ожидается подтверждение TravelLine', confirmed:'TravelLine подтвердил доставку', error:'TravelLine вернул ошибку'}[state.delivery]);
            if (state.confirmation && state.confirmation.error) line(content, 'Ошибка TravelLine', JSON.stringify(state.confirmation.error));
            var hasHotelLink = h.url && /^https:\/\/(санатории-россии\.рф|xn----7sba6aaba8akdsdekah\.xn--p1ai)\//.test(h.url);
            if(hasHotelLink)content.append(el('a', h.name).attr({href:h.url,target:'_blank',rel:'noopener noreferrer'}));
            summary(content, {hotel:h.name,arrival:b.arrivalDate,departure:b.departureDate,room:state.selection.room,rate:state.selection.rate,
                adults:stay.adults,children:stay.children,total:stay.total.amountAfterTaxes,prepayment:b.paymentInfo.prepayment.value,
                payment:b.paymentMethodComment,customer:b.customer,guests:stay.guests,comment:b.guestComment},hasHotelLink);
            if (b.paymentInfo.cancellation) line(content, 'Штраф при отмене', b.paymentInfo.cancellation.penaltyAmount + ' ' + b.paymentInfo.cancellation.currency);
            content.append(el('strong', 'Сохранённые условия отмены')); list(content, state.rules);
            if (caps.canEdit && state.canChange) {
                content.append(button('Изменить бронь', 'btn-primary', function () { editor(true); }).css('margin-right','8px'));
                content.append(button('Отменить бронь', 'btn-danger', function () { cancel(false); }).css('margin-right','8px'));
                if (caps.canWaive) content.append(button('Отменить без штрафа', 'btn-danger', function () { cancel(true); }));
            }
            if(caps.canEdit && !state.canChange && b.status!=='cancelled') {
                content.append(button('Изменить бронь','btn-primary',function(){}).attr('data-cb-disabled','1').prop('disabled',true));
                var reason=state.delivery==='waiting'?'Редактирование и отмена станут доступны после подтверждения предыдущей операции TravelLine. Статус обновляется автоматически.':
                    (state.delivery==='error'?'TravelLine вернул ошибку. Перед изменением брони требуется проверить её передачу.':'Изменение этой брони сейчас недоступно.');
                content.append(el('p',reason,'text-muted'));
            }
            var log = $('<details style="margin-top:15px"></details>').append(el('summary', 'История брони'));
            var labels = {create:'Создание',modify:'Изменение',cancel:'Отмена',waive:'Отмена без штрафа','travelline-confirmed':'Ответ TravelLine','travelline-error':'Ошибка TravelLine'};
            $.each(state.history || [], function (_, item) {
                var row = $('<div style="padding:10px 0;border-bottom:1px solid #ddd"></div>');
                row.append(el('strong', new Date(item.created * 1000).toLocaleString() + ' — ' + (labels[item.action] || item.action)));
                if (+item.actor_id) line(row,'Сотрудник',item.actor_id);
                var d=item.details || {}; if(d.reason)line(row,'Причина',d.reason);
                if(d.chargedPenalty !== undefined)line(row,'Штраф',d.chargedPenalty+' RUB (по правилу: '+d.standardPenalty+')');
                if(d.externalNumber)line(row,'Номер TravelLine',d.externalNumber);
                if(d.error)line(row,'Ошибка',JSON.stringify(d.error));
                if(d.before){row.append(el('b','До изменения'));summary(row,d.before);}
                if(d.after){row.append(el('b','После операции'));summary(row,d.after);}
                log.append(row);
            }); content.append(log);
            if (state.delivery === 'waiting') poll = setTimeout(function () { if (alive() && body.is(':visible')) load(); }, 30000);
        }
        function field(parent, label, type, value) {
            var group=$('<label style="display:inline-block;margin:0 12px 12px 0;vertical-align:top"></label>').append(el('span',label).css('display','block'));
            var input=$('<input class="form-control">').attr('type',type).val(value === undefined ? '' : value); group.append(input);parent.append(group);return input;
        }
        function editor(modify) {
            if(poll)clearTimeout(poll);content.empty();var b=state.booking, selected=state.selection || {}, def=defaults.customer || {};
            var title=modify?'Изменение брони':'Новая бронь'; content.append(el('h4',title));
            content.append(el('p','Изменения отправляются после проверки и подтверждения. Предоплата берётся из проведённых платежей заявки; при её отсутствии передаётся оплата при заезде.','text-muted'));
            var hotelId=modify ? +b.hotelId : (+defaults.hotelId || 0), hotelLabel=el('p',modify?state.hotel.name:(hotelId?'Объект из заявки: '+hotelId:'Выберите объект'));
            content.append(hotelLabel);
            if(!modify){
                var search=field(content,'Поиск объекта по названию или внутреннему ID','text','');
                var hotels=$('<select class="form-control" style="max-width:600px;margin:10px 0"></select>');
                content.append(button('Найти объект','btn-default',function(){api('hotels',{search:search.val()},function(d){hotels.empty().append($('<option value="">Выберите объект</option>'));$.each(d.hotels,function(_,h){hotels.append(el('option',h.name+' ('+h.id+')').val(h.id));});});}),hotels);
                hotels.on('change',function(){hotelId=+hotels.val();hotelLabel.text(hotels.find(':selected').text());clearOffers();});
            }
            var trip=$('<div></div>');content.append(trip);
            var arrival=field(trip,'Заезд','date',date(modify?b.arrivalDate:defaults.arrivalDate));
            var departure=field(trip,'Выезд','date',date(modify?b.departureDate:defaults.departureDate));
            var adults=field(trip,'Взрослых','number',modify?selected.adults:1).attr({min:1,max:6});
            var children=field(trip,'Возраст детей через запятую','text',modify?(selected.childrenAges||[]).join(','):'');
            var guestBox=$('<div></div>'),guestInputs=[];content.append(el('strong','Гости: сначала взрослые, затем дети'),guestBox);
            function ages(){return children.val().trim()?children.val().split(',').map(function(v){return Number(v.trim());}):[];}
            function guests(){return guestInputs.map(function(g){return {lastName:g[0].val(),firstName:g[1].val(),middleName:g[2].val()};});}
            function guestFields(initial){var saved=initial||guests();guestBox.empty();guestInputs=[];var count=Math.max(1,Math.min(10,+adults.val()+ages().length));
                for(var i=0;i<count;i++){var row=$('<div></div>'),g=saved[i]||{};guestBox.append(row);row.append(el('span',(i<+adults.val()?'Взрослый ':'Ребёнок ')+(i+1)).css('margin-right','10px'));
                    guestInputs.push([field(row,'Фамилия','text',g.lastName),field(row,'Имя','text',g.firstName),field(row,'Отчество','text',g.middleName)]);}}
            guestFields(modify?b.roomStays[0].guests:[def]);
            adults.add(children).on('change',function(){guestFields();clearOffers();}); arrival.add(departure).on('change',clearOffers);
            var customer=modify?b.customer:def, buyer=$('<div></div>');content.append(el('strong','Заказчик'),buyer);
            var surname=field(buyer,'Фамилия','text',customer.lastName),name=field(buyer,'Имя','text',customer.firstName),middle=field(buyer,'Отчество','text',customer.middleName);
            var phone=field(buyer,'Телефон','text',customer.phone),email=field(buyer,'E-mail','email',customer.email);
            var comment=field(content,'Комментарий для объекта','text',modify?b.guestComment:'');
            var offerSelect=$('<select class="form-control" style="max-width:900px;margin:12px 0"></select>'),offerRules=$('<div></div>'),offers=[];
            function clearOffers(){offers=[];offerSelect.empty();offerRules.empty();}
            function input(){var o=offers[+offerSelect.val()]||{};return {hotelId:hotelId,arrivalDate:arrival.val(),departureDate:departure.val(),adults:+adults.val(),childrenAges:ages(),roomId:o.roomId,rateId:o.rateId,guests:guests(),customer:{lastName:surname.val(),firstName:name.val(),middleName:middle.val(),phone:phone.val(),email:email.val()},comment:comment.val()};}
            content.append(button('Проверить цены и наличие','btn-primary',function(){api('offers',{input:JSON.stringify(input())},function(d){clearOffers();offers=d.offers;offerSelect.append($('<option value="">Выберите номер и тариф</option>'));$.each(offers,function(i,o){offerSelect.append(el('option',o.room+' / '+o.rate+' — '+o.total+' RUB за весь период').val(i));});if(!offers.length)error('На выбранные даты и состав гостей нет подходящих предложений.');});}),offerSelect,offerRules);
            offerSelect.on('change',function(){offerRules.empty();if(offerSelect.val()!=='')list(offerRules,(offers[+offerSelect.val()]||{}).rules);});
            content.append(button('Проверить перед отправкой','btn-success',function(){if(offerSelect.val()===null||offerSelect.val()===''||!offers.length){error('Сначала выберите доступный номер и тариф.');return;}prepare(modify?'modify':'create',input());}).css('margin-right','10px'),button('Назад','btn-default',render));
        }
        function cancel(waive) {
            if(poll)clearTimeout(poll);content.empty();content.append(el('h4',waive?'Отмена без штрафа — только для администратора':'Отмена брони'));
            var reason=field(content,'Причина отмены (обязательно)','text','').css('min-width','450px');
            content.append(button('Рассчитать и проверить','btn-danger',function(){prepare(waive?'waive':'cancel',{reason:reason.val()});}),button('Назад','btn-default',render).css('margin-left','10px'));
        }
        function prepare(operation,input) {
            api('prepare',{operation:operation,revision:state.revision,input:JSON.stringify(input)},function(d){
                content.empty();content.append(el('h4','Проверьте перед отправкой'));var s=d.summary;
                if(s.before){content.append(el('b','Сейчас'));summary(content,s.before);}
                if(s.after){content.append(el('b','Будет передано'));summary(content,s.after);}
                if(s.penaltyAmount!==undefined){line(content,'Бронь',s.number);line(content,'Штраф к передаче',s.penaltyAmount+' '+s.currency);line(content,'Штраф по сохранённому правилу',s.standardPenalty+' '+s.currency);line(content,'Причина',s.reason);}
                list(content,s.rules);content.append(el('p','Расчёт действует 2 минуты. После отправки нужно дождаться подтверждения TravelLine.','text-muted'));
                var confirmText=s.after?'Передать '+(operation==='create'?'новую бронь':'изменение брони')+' на '+s.after.total+' RUB?':'Отменить бронь '+s.number+' со штрафом '+s.penaltyAmount+' '+s.currency+'?';
                var submit=button('Подтвердить и отправить',operation==='cancel'||operation==='waive'?'btn-danger':'btn-success',function(){if(!w.confirm(confirmText))return;commit();});
                function commit(){api('commit',{token:d.token},function(result){state=result;render();},function(){submit.text('Повторить тот же запрос');});}
                content.append(submit,button('Вернуться к состоянию брони','btn-default',load).css('margin-left','10px'));
            });
        }
        if(autoOpen){body.show();load();}
    };
})(window, jQuery);
