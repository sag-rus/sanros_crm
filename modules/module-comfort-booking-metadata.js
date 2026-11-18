(function (w, $) {
    'use strict';
    function dialog(title) {
        if ($('#cb-metadata-modal').length) return null;
        var modal = $('<div class="modal fade" id="cb-metadata-modal" role="dialog" aria-modal="true" aria-labelledby="cb-metadata-title" tabindex="-1">');
        var content = $('<div class="modal-content">').appendTo($('<div class="modal-dialog">').appendTo(modal));
        $('<div class="modal-header">').append($('<h4 class="modal-title" id="cb-metadata-title">').text(title)).appendTo(content);
        var body = $('<div class="modal-body">').appendTo(content);
        var status = $('<p role="status">').appendTo(body);
        var errors = $('<ul class="text-danger">').appendTo(body);
        var close = $('<button type="button" class="btn btn-default">Закрыть</button>').appendTo($('<div class="modal-footer">').appendTo(content));
        var busy = false;
        close.on('click', function () { if (!busy) modal.modal('hide'); });
        modal.on('hidden.bs.modal', function () { $(w).off('beforeunload.cbMetadata'); modal.remove(); });
        modal.appendTo(document.body).modal({backdrop: 'static', keyboard: false});
        return {
            body: body, status: status, errors: errors,
            busy: function (value) {
                busy = value;
                close.prop('disabled', value);
                $(w).off('beforeunload.cbMetadata');
                if (value) $(w).on('beforeunload.cbMetadata', function () { return 'Обработка файла ещё выполняется.'; });
            }
        };
    }
    function message(xhr) {
        return xhr.responseJSON && xhr.responseJSON.error || 'Не получен ответ сервера.';
    }

    w.cbMetadataExport = function (ids, csrf) {
        if (!ids.length) return;
        var ui = dialog('Выгрузить метатеги');
        if (!ui) return;
        ui.busy(true);
        ui.status.text('Получение метатегов с сайта и подготовка Excel…');
        var data = new FormData();
        data.append('action', 'metadata-export');
        data.append('csrf', csrf);
        data.append('ids', JSON.stringify(ids));
        var xhr = new XMLHttpRequest();
        xhr.open('POST', 'comfort_booking.php');
        xhr.responseType = 'blob';
        xhr.timeout = 60000;
        function fail(text) { ui.status.addClass('text-danger').text(text); ui.busy(false); }
        xhr.onerror = xhr.ontimeout = function () { fail('Выгрузка не завершена. Повторите запрос.'); };
        xhr.onload = function () {
            if (xhr.status === 200 && (xhr.getResponseHeader('Content-Type') || '').indexOf('spreadsheetml.sheet') !== -1) {
                var url = URL.createObjectURL(xhr.response);
                var link = document.createElement('a');
                link.href = url;
                link.download = 'comfort-booking-metatags.xlsx';
                document.body.appendChild(link);
                link.click();
                link.remove();
                setTimeout(function () { URL.revokeObjectURL(url); }, 60000);
                ui.status.text('Файл подготовлен. Объектов: ' + ids.length + '.');
                ui.busy(false);
            } else {
                var reader = new FileReader();
                reader.onload = function () {
                    try { fail(JSON.parse(reader.result).error || 'Не удалось выгрузить метатеги.'); }
                    catch (e) { fail('Не удалось выгрузить метатеги.'); }
                };
                reader.onerror = function () { fail('Не удалось выгрузить метатеги.'); };
                reader.readAsText(xhr.response);
            }
        };
        xhr.send(data);
    };

    w.cbMetadataImport = function (csrf) {
        var ui = dialog('Загрузить метатеги');
        if (!ui) return;
        $('<p>').text('Выберите .xlsx в формате выгрузки. Объекты определяются по ID сайта. Обновятся Title, Description, H1 и Keywords. Пустые SEO-поля возвращают автоматические значения сайта.').prependTo(ui.body);
        $('<p class="text-muted">').text('Один лист, до 5000 объектов и 5 МБ. Обработка начнётся после выбора файла. Дождитесь результата в этом окне.').prependTo(ui.body);
        var input = $('<input type="file" id="cb-metadata-file" accept=".xlsx,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" class="form-control">').prependTo(ui.body);
        $('<label for="cb-metadata-file">Файл Excel</label>').prependTo(ui.body);
        input.on('change', function () {
            var file = input[0].files[0];
            if (!file) return;
            ui.errors.empty();
            ui.status.removeClass('text-danger');
            if (!/\.xlsx$/i.test(file.name) || file.size > 5242880) {
                ui.status.addClass('text-danger').text('Выберите файл .xlsx размером до 5 МБ.');
                input.val('');
                return;
            }
            input.prop('disabled', true);
            ui.busy(true);
            ui.status.text('Проверка файла…');
            var data = new FormData();
            data.append('action', 'metadata-upload');
            data.append('csrf', csrf);
            data.append('file', file);
            $.ajax({url: 'comfort_booking.php', type: 'POST', data: data, processData: false,
                contentType: false, dataType: 'json', timeout: 60000})
                .done(function (result) {
                    if (!result.success) { finishUpload(result.error); return; }
                    csrf = result.csrf;
                    applyRows(result.data.rows);
                })
                .fail(function (xhr) { finishUpload(message(xhr)); });
        });
        function finishUpload(error) {
            ui.status.addClass('text-danger').text(error);
            input.prop('disabled', false).val('');
            ui.busy(false);
        }
        function applyRows(rows) {
            var index = 0, changed = 0, unchanged = 0, failed = 0;
            function finish(stopped) {
                var text = 'Обновлено: ' + changed + '. Без изменений: ' + unchanged + '. Ошибок: ' + failed + '.';
                if (stopped) text += ' Обработка остановлена. Не обработано строк: ' + (rows.length - index) + '.';
                ui.status.text(text);
                input.prop('disabled', false).val('');
                ui.busy(false);
            }
            function next() {
                if (index === rows.length) { finish(false); return; }
                var row = rows[index++];
                ui.status.text('Обработка ' + index + ' из ' + rows.length + ' — ID ' + row.id + '…');
                $.ajax({url: 'comfort_booking.php', type: 'POST', dataType: 'json', timeout: 45000,
                    data: {action: 'metadata-save', id: row.id, fields: JSON.stringify(row.fields), csrf: csrf}})
                    .done(function (result) {
                        if (!result.success) { error(result.error, true); return; }
                        csrf = result.csrf;
                        if (result.data.changed) changed++; else unchanged++;
                        next();
                    })
                    .fail(function (xhr) {
                        var stopped = xhr.status !== 400 && xhr.status !== 409;
                        error(message(xhr) + (stopped ? ' Проверьте метатеги объекта перед повторной загрузкой.' : ''), stopped);
                    });
                function error(text, stopped) {
                    failed++;
                    $('<li>').text('Строка ' + row.line + ', ID ' + row.id + ': ' + text).appendTo(ui.errors);
                    if (stopped) finish(true); else next();
                }
            }
            next();
        }
    };
})(window, jQuery);
