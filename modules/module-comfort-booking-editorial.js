(function(w,$){
    'use strict';
    w.cbEditorialOpen=function(row,csrf){
        if($('#cb-editorial-modal').length)return;
        var modal=$('<div class="modal fade" id="cb-editorial-modal" role="dialog" aria-modal="true" aria-labelledby="cb-editorial-title" tabindex="-1">');
        var dialog=$('<div class="modal-dialog" style="width:90%;max-width:900px">').appendTo(modal),panel=$('<div class="modal-content">').appendTo(dialog);
        var header=$('<div class="modal-header">').appendTo(panel);
        var close=$('<button type="button" class="close" aria-label="Закрыть">').text('×').appendTo(header);
        $('<h4 class="modal-title" id="cb-editorial-title">').text('Изменить: '+row.name).appendTo(header);
        var form=$('<form>'),body=$('<div class="modal-body">').appendTo(form),notice=$('<div role="status" class="text-muted">').text('Загрузка данных с сайта…').appendTo(body);
        var fields={},revision='',busy=false,ready=false;
        $('<p class="text-muted">').text('Пустые SEO-поля используют автоматические значения сайта. Пустой текст верхнего блока скрывает его. Основное описание обновляется из Content API и здесь не редактируется.').appendTo(body);
        [['h1','H1',500,1],['title','Title',500,1],['description','Description',2000,3],['keywords','Keywords',2000,2],['summary','Текст верхнего блока (.sagrus1)',20000,6]].forEach(function(f){
            var group=$('<div class="form-group">').appendTo(body),id='cb-editorial-'+f[0];
            $('<label>').attr('for',id).text(f[1]).appendTo(group);
            fields[f[0]]=(f[3]===1?$('<input type="text">'):$('<textarea>').attr('rows',f[3])).addClass('form-control').attr({id:id,maxlength:f[2]}).prop('disabled',true).appendTo(group);
        });
        $('<p class="text-muted">').text('Текст вводится без HTML. Переносы строк в верхнем блоке сохраняются.').appendTo(body);
        var footer=$('<div class="modal-footer">').appendTo(form);
        var save=$('<button type="submit" class="btn btn-success">').text('Сохранить на сайте').prop('disabled',true).appendTo(footer);
        var cancel=$('<button type="button" class="btn btn-default">').text('Закрыть').appendTo(footer);
        panel.append(form);modal.appendTo(document.body).modal({backdrop:'static',keyboard:false});
        function setBusy(value){busy=value;save.prop('disabled',value||!ready);close.add(cancel).prop('disabled',value);$.each(fields,function(_,input){input.prop('disabled',value||!ready);});}
        function failure(xhr){notice.attr('class','alert alert-danger').text(xhr.responseJSON&&xhr.responseJSON.error||'Ответ сайта не получен. Закройте и откройте окно заново, чтобы проверить сохранённые данные.');}
        function request(action,extra,done){setBusy(true);return $.ajax({url:'comfort_booking.php',type:'POST',dataType:'json',timeout:45000,data:$.extend({action:action,id:row.id,csrf:csrf},extra||{})})
            .done(function(result){if(!result.success){failure({responseJSON:result});return;}csrf=result.csrf;done(result.data);})
            .fail(failure).always(function(){setBusy(false);});}
        close.add(cancel).on('click',function(){if(!busy)modal.modal('hide');});modal.on('hidden.bs.modal',function(){modal.remove();});
        form.on('submit',function(e){e.preventDefault();if(busy||!ready)return;var values={};$.each(fields,function(k,input){values[k]=input.val();});notice.attr('class','text-muted').text('Сохранение на сайте…');
            request('editorial-save',{revision:revision,fields:JSON.stringify(values)},function(data){revision=data.revision;$.each(fields,function(k,input){input.val(data.fields[k]);});notice.attr('class','alert alert-success').text('Изменения сохранены на сайте.');});
        });
        request('editorial-read',{},function(data){revision=data.revision;$.each(fields,function(k,input){input.val(data.fields[k]||'');});ready=true;notice.empty();});
    };
})(window,jQuery);
