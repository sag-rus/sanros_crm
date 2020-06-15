(function($) {
    window.BlobBuilder = window.MozBlobBuilder || window.WebKitBlobBuilder || window.BlobBuilder;
    Array.prototype.removeElementByIndex = function(index)
    {
        var newAr = [];
        var i;
        for(i = 0; i<this.length; i++)
        {
            if(i != index)
                newAr.push(this[i]);
        }
        for(i = 0; i<this.length; i++)
        {
            if(i<newAr.length)
            {
                this[i] = newAr[i];
            }
            else
            {
                this.splice(i);
            }
        }
    };
    String.prototype.shatterUploader = function (partSize) {
        var partAr = [""];
        for(var i = 0; i<this.length; i++) {
            partAr[partAr.length-1] += this[i];
            if(partAr[partAr.length-1].length == partSize && i<this.length-1)
                partAr.push("");
        }
        return partAr;
    };
    var Base64 = {

        // private property
        _keyStr : "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=",

        // public method for encoding
        encode : function (input) {
            var output = "";
            var chr1, chr2, chr3, enc1, enc2, enc3, enc4;
            var i = 0;

            input = Base64._utf8_encode(input);

            while (i < input.length) {

                chr1 = input.charCodeAt(i++);
                chr2 = input.charCodeAt(i++);
                chr3 = input.charCodeAt(i++);

                enc1 = chr1 >> 2;
                enc2 = ((chr1 & 3) << 4) | (chr2 >> 4);
                enc3 = ((chr2 & 15) << 2) | (chr3 >> 6);
                enc4 = chr3 & 63;

                if (isNaN(chr2)) {
                    enc3 = enc4 = 64;
                } else if (isNaN(chr3)) {
                    enc4 = 64;
                }

                output = output +
                    this._keyStr.charAt(enc1) + this._keyStr.charAt(enc2) +
                    this._keyStr.charAt(enc3) + this._keyStr.charAt(enc4);

            }

            return output;
        },

        // public method for decoding
        decode : function (input) {
            var output = "";
            var chr1, chr2, chr3;
            var enc1, enc2, enc3, enc4;
            var i = 0;

            input = input.replace(/[^A-Za-z0-9\+\/\=]/g, "");

            while (i < input.length) {

                enc1 = this._keyStr.indexOf(input.charAt(i++));
                enc2 = this._keyStr.indexOf(input.charAt(i++));
                enc3 = this._keyStr.indexOf(input.charAt(i++));
                enc4 = this._keyStr.indexOf(input.charAt(i++));

                chr1 = (enc1 << 2) | (enc2 >> 4);
                chr2 = ((enc2 & 15) << 4) | (enc3 >> 2);
                chr3 = ((enc3 & 3) << 6) | enc4;

                output = output + String.fromCharCode(chr1);

                if (enc3 != 64) {
                    output = output + String.fromCharCode(chr2);
                }
                if (enc4 != 64) {
                    output = output + String.fromCharCode(chr3);
                }

            }

            output = Base64._utf8_decode(output);

            return output;

        },

        // private method for UTF-8 encoding
        _utf8_encode : function (string) {
            string = string.replace(/\r\n/g,"\n");
            var utftext = "";

            for (var n = 0; n < string.length; n++) {

                var c = string.charCodeAt(n);

                if (c < 128) {
                    utftext += String.fromCharCode(c);
                }
                else if((c > 127) && (c < 2048)) {
                    utftext += String.fromCharCode((c >> 6) | 192);
                    utftext += String.fromCharCode((c & 63) | 128);
                }
                else {
                    utftext += String.fromCharCode((c >> 12) | 224);
                    utftext += String.fromCharCode(((c >> 6) & 63) | 128);
                    utftext += String.fromCharCode((c & 63) | 128);
                }

            }

            return utftext;
        },

        // private method for UTF-8 decoding
        _utf8_decode : function (utftext) {
            var string = "";
            var i = 0;
            var c = c1 = c2 = 0;

            while ( i < utftext.length ) {

                c = utftext.charCodeAt(i);

                if (c < 128) {
                    string += String.fromCharCode(c);
                    i++;
                }
                else if((c > 191) && (c < 224)) {
                    c2 = utftext.charCodeAt(i+1);
                    string += String.fromCharCode(((c & 31) << 6) | (c2 & 63));
                    i += 2;
                }
                else {
                    c2 = utftext.charCodeAt(i+1);
                    c3 = utftext.charCodeAt(i+2);
                    string += String.fromCharCode(((c & 15) << 12) | ((c2 & 63) << 6) | (c3 & 63));
                    i += 3;
                }

            }

            return string;
        }

    };
    jQuery.extend({
        multUploderObjects:{},
        sendImageFilesStringToServerByNum: function (fileNum,loaderID) {
            var $prev = $($.multUploderObjects[loaderID].newPrevArray[fileNum]);
            if(!$prev.hasClass('removed-el')) {
                $.multUploderObjects[loaderID].files[fileNum]['file'].fileSlice = $.multUploderObjects[loaderID].files[fileNum]['file'].mozSlice || $.multUploderObjects[loaderID].files[fileNum]['file'].webkitSlice || $.multUploderObjects[loaderID].files[fileNum]['file'].slice;
                $.sendImageFileByFragments(0,fileNum,loaderID);
            }
            else if(fileNum+1 <$.multUploderObjects[loaderID].files.length){
                $.sendImageFilesStringToServerByNum(fileNum+1,loaderID);
            }

        },
        sendImageFileByFragments: function (start,fileNum,loaderID,id,partnum,parts,upload_id) {
            var $uploader = $(".multiple-uploader[data-uploader-target-id='"+loaderID+"']");
            var $prevBlock = $uploader.find('.files-preview-block');
            var $obj = $('#'+loaderID);
            var $prev = $($.multUploderObjects[loaderID].newPrevArray[fileNum]);
            var fd = new FormData();

            if(start >=0 && start< $.multUploderObjects[loaderID].files[fileNum]['file'].size) {
                var blob = $.multUploderObjects[loaderID].files[fileNum]['file'].fileSlice(start,start+1*$uploader.attr('data-fragment-size'));
                fd.append('format',$.multUploderObjects[loaderID].files[fileNum]['format']);
                fd.append('type',$.multUploderObjects[loaderID].files[fileNum]['file'].type);
                fd.append('plength',Math.ceil($.multUploderObjects[loaderID].files[fileNum]['file'].size/(1*$uploader.attr('data-fragment-size'))));
                fd.append('name',$.multUploderObjects[loaderID].files[fileNum]['file'].name);

                if(start === 0) {
                    $prev.addClass('upload-state-el');
                    fd.append('partnum', 0);
                }
                else {
                    fd.append('id',id);
                    fd.append('partnum',partnum);
                    fd.append('parts',parts);
                    fd.append('upload_id',upload_id);
                }
                fd.append('upload',blob);
                $.multUploderObjects[loaderID].fragSent = $.ajax({
                    url: $uploader.attr('data-action'),
                    type: "POST",
                    data: fd,
                    dataType: "json",
                    processData: false,
                    contentType: false,
                    beforeSend: function(xhr){
                        if($uploader.is('[data-token]')) {
                            xhr.setRequestHeader('X-Secret-Token', $uploader.attr('data-token'));
                        }
                    },
                    success: function(data){
                        if(data['success']) {
                            if(typeof $prev.attr('data-upload-id') == 'undefined') $prev.attr('data-upload-id',data['id']);
                            $.multUploderObjects[loaderID].files[fileNum]['progress']++;
                            $prev.find('.upload-progress .upload-progress-rel').css('width',Math.round($.multUploderObjects[loaderID].files[fileNum]['progress']/Math.ceil($.multUploderObjects[loaderID].files[fileNum]['file'].size/(1*$uploader.attr('data-fragment-size')))*100)+'%');
                            if(data['loaded']) {
                                $prev.find('.uploadProgress .uploadProgressRel').css('width','0%');
                                //загрузка завершена
                                $prev.removeClass('upload-state-el').addClass('uploaded-el');
                                $obj.trigger({
                                    type: "fileuploaded",
                                    filetype:$.multUploderObjects[loaderID].files[fileNum]['format'],
                                    filename:$.multUploderObjects[loaderID].files[fileNum]['file']['name'],
                                    data: data
                                });
                                var ids = [];
                                if($obj.val().length > 0) ids = JSON.parse($obj.val());

                                if(!('length' in ids)) {
                                    ids = [];
                                }

                                $prev.find('a.uploader-el-url').attr('href',data['uri']);

                                $prev.attr('data-upload-id', data.id);

                                if($prevBlock.find('*[data-upload-id="'+data.id+'"].uploader-preview-element').length !== 0) {
                                    ids.push({
                                        id: data['id'],
                                        title: $prev.find('input.element-title').val(),
                                        description: $prev.find('textarea.element-description').val(),
                                        uri:data['uri']
                                    });
                                }

                                $obj.val(JSON.stringify(ids));
                                var typeFile = data['mime'].split('/')[0];

                                if(typeFile === 'image') {
                                    if('uri_thumbnail' in data)
                                        $prev.css('background-image', 'url(' + data['uri_thumbnail'] + ')');
                                    else
                                        $prev.css('background-image', 'url(' + data['uri'] + ')');
                                }

                                if(fileNum < $.multUploderObjects[loaderID].files.length-1) {
                                    $.sendImageFilesStringToServerByNum(fileNum+1,loaderID);
                                }
                                else {
                                    $obj.trigger({
                                        type: "uploadend",
                                        filetype:$.multUploderObjects[loaderID].files[fileNum]['format'],
                                        filename:$.multUploderObjects[loaderID].files[fileNum]['file']['name'],
                                        data: data
                                    });
                                    $.multUploderObjects[loaderID].files = [];
                                    $.multUploderObjects[loaderID].newPrevArray = [];
                                    $.multUploderObjects[loaderID].uploadState = false;
                                    $prevBlock.find('.uploader-preview-element.removed-el').remove();
                                }
                            }
                            else {
                                $.sendImageFileByFragments(start+1*$uploader.attr('data-fragment-size'),fileNum,loaderID,data['id'],data['partnum']+1,data['parts'],data['upload_id']);
                            }
                        }
                        else {
                            console.log(data['msg']);
                            $prevBlock.find('.uploader-preview-element.upload-state-el').remove();
                            if(fileNum < $.multUploderObjects[loaderID].files.length-1) {
                                $.sendImageFilesStringToServerByNum(fileNum+1,loaderID);
                            }
                            else {
                                $obj.trigger({
                                    type: "uploadend",
                                    filetype:$.multUploderObjects[loaderID].files[fileNum]['format'],
                                    filename:$.multUploderObjects[loaderID].files[fileNum]['file']['name'],
                                    data: data
                                });
                                $.multUploderObjects[loaderID].files = [];
                                $.multUploderObjects[loaderID].newPrevArray = [];
                                $.multUploderObjects[loaderID].uploadState = false;
                                $prevBlock.find('.uploader-preview-element.removed-el').remove();
                            }
                        }
                    },
                    error: function (data) {
                        console.log('upload continue error');
                    }
                });
            }
        }
    });
    jQuery.fn.setUploaderVal = function(val) {
        var make = function () {
            var $obj = $(this);

            if($obj.hasClass('damirez-def-input-hide') && typeof $obj.attr('id') != 'undefined') {
                var $uploader = $(".multiple-uploader[data-uploader-target-id='"+$obj.attr('id')+"']");
                var $prevBlock = $uploader.find('.files-preview-block');
                var idAr = [];
                var loaderID = $obj.attr('id');

                if($.multUploderObjects[loaderID].fragSent) {
                    $.multUploderObjects[loaderID].fragSent.abort();
                    $.multUploderObjects[loaderID].fragSent = null;
                }
                if(!val) val = [];
                $prevBlock.find('*').remove();

                for(let valIt = 0; valIt < val.length; valIt++) {
                    if('uri' in val[valIt]) {
                        var bim = '', elSettings;
                        if('mime' in val[valIt]) {
                            var typeFile = val[valIt]['mime'].split('/')[0];
                            if(typeFile === 'image') {
                                if('uri_thumbnail' in val[valIt])
                                    bim = 'background-image: url(' + val[valIt]['uri_thumbnail'] + ');';
                                else
                                    bim = 'background-image: url(' + val[valIt]['uri'] + ');';
                            }
                        }
                        if(!('title' in val[valIt]))
                            val[valIt]['title'] = '';
                        if(!('description' in val[valIt]))
                            val[valIt]['description'] = '';
                        elSettings = '<div class="element-settings-block default-shadow-min">';
                        elSettings += '<div class="darkish-form-mask-form-block-cont">';
                        elSettings +='<div class="input-block">';
                        elSettings +='<div class="input-title">Заголовок файла</div>';
                        elSettings += '<input type="text" placeholder="Заголовок файла" class="element-title" value="'+val[valIt]['title']+'"/>';
                        elSettings += '</div>';
                        elSettings +='<div class="input-block">';
                        elSettings +='<div class="input-title">Описание</div>';
                        elSettings += '<textarea class="element-description" placeholder="Описание">'+val[valIt]['description']+'</textarea>';
                        elSettings += '</div>';
                        elSettings += '</div>';
                        elSettings += '<div class="darkish-form-mask-buttons">';
                        elSettings += '<button class="set-button darkish-form-mask-button edit-save-button">Закрыть</button>';
                        elSettings += '</div>';
                        elSettings +='</div>';
                        elSettings += '<button class="set-button edit-button"><i class="fa fa-pencil-square-o fa-1" aria-hidden="true"></i></button>';
                        $prevBlock.append('<div class="uploader-preview-element uploaded-el" style="'+bim+'" data-upload-id="'+val[valIt]['id']+'">'+elSettings+'<button class="set-button remove-button"></button><div class="upload-progress"><div class="upload-progress-rel"></div></div><a class="uploader-el-url" target="_blank" href="'+val[valIt]['uri']+'">Ссылка</a></div>');
                        idAr.push(val[valIt]);
                    }
                }
                $obj.val(JSON.stringify(idAr));
            }
            else {
                //console.log('This object is not multUploader');
            }
        };
        return this.each(make);
    };
    jQuery.fn.multUploader = function(options) {
        if(typeof options == 'undefined') options = {};
        var RandomString = function(strlength)
        {
            var str ="";
            var chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
            for(var i = 0; i<strlength; i++)
            {
                str += chars[Math.floor(Math.random() * (chars.length-1))];
            }
            return str;
        };
        var make = function(){
            var $obj = $(this), $uploader, $inputmask;
            if(!$obj.hasClass('damirez-def-input-hide')) {
                $obj.addClass('damirez-def-input-hide').attr('type','text');
                $obj.val('{}');
                $obj.uniqueId();
                $obj.after('<div class="multiple-uploader" data-uploader-target-id="'+$obj.attr('id')+'"></div>');
                $uploader = $(".multiple-uploader[data-uploader-target-id='"+$obj.attr('id')+"']");
                $uploader.uniqueId();
                $.multUploderObjects[$obj.attr('id')] = {
                    files: [],
                    newPrevArray: [],
                    uploadState: false,
                    fragSent: null,
                    contentType: []
                };
                $uploader.append('<div class="input-mask">Выбрать файл</div>');
                var acceptStr = "";
                if('contentType' in options) {
                    if(!Array.prototype.isPrototypeOf(options['contentType']) && options['contentType'] !== '*')
                        options['contentType'] = [options['contentType']];
                    $.multUploderObjects[$obj.attr('id')]['contentType'] = options['contentType'];
                    if(options['contentType'] !== '*') {
                        var i;
                        for(i = 0; i<options['contentType'].length; i++) {
                            if(acceptStr.length !== 0)
                                acceptStr += ", "
                            acceptStr +=options['contentType'][i];
                        }
                    }

                }
                else $.multUploderObjects[$obj.attr('id')]['contentType'] = '*';
                var accept = 'accept="'+acceptStr+'" ';
                $uploader.append('<input type="file" multiple '+accept+'class="multiple-uploader-input">');
                $uploader.append('<div class="files-preview-block"></div>');
                $inputmask = $uploader.find('.input-mask');
                if('maxcount' in options)
                    $uploader.attr('data-maxcount',options['maxcount']);
                if('action' in options)
                    $uploader.attr('data-action',options['action']);
                if('fragmentSize' in options)
                    $uploader.attr('data-fragment-size',options['fragmentSize']);
                else $uploader.attr('data-fragment-size',1024*1024);

                if('token' in options) {
                    $uploader.attr('data-token', options.token);
                }

                $uploader.on('click','.input-mask',function(e)
                {
                    e = e || event;
                    $(this).parent().find('.multiple-uploader-input').click();
                });
                $inputmask.on('drop', function(e)
                {
                    e = e || event;
                    $(this).mouseup();
                    //e.preventDefault();
                    var $jQu = $(this).parent();
                    $jQu.find('input.multiple-uploader-input').get(0).files = e.originalEvent.dataTransfer.files;
                });
                $uploader.on('change','.multiple-uploader-input',function (e) {
                    e = e || event;
                    var r = e.target,
                        $jQu = $(r).parent(),
                        loaderID = $jQu.attr('data-uploader-target-id'),
                        maxcount = null,
                        format = 'jpg';
                    if(typeof $jQu.attr('data-maxcount') != 'undefined')
                        maxcount = $jQu.attr('data-maxcount');
                    if(r.files.length > 0) {
                        var $imagePreviews = $jQu.find('.files-preview-block'),
                            $imprevEl = $imagePreviews.find('.uploader-preview-element').not('.removed-el'),
                            lastPrev,
                            $loaderTarg = $('#'+loaderID),
                            elSettings;
                        for(var i = 0; i<r.files.length && (!maxcount || i<(maxcount-$imprevEl.length)); i++) {
                            if($.multUploderObjects[loaderID]['contentType'] === '*' || $.multUploderObjects[loaderID]['contentType'].indexOf(r.files[i].type) >= 0) {
                                elSettings = '<div class="element-settings-block default-shadow-min">';
                                elSettings += '<div class="darkish-form-mask-form-block-cont">';
                                elSettings +='<div class="input-block">';
                                elSettings +='<div class="input-title">Заголовок файла</div>';
                                elSettings += '<input type="text" placeholder="Заголовок файла" class="element-title"/>';
                                elSettings += '</div>';
                                elSettings +='<div class="input-block">';
                                elSettings +='<div class="input-title">Описание</div>';
                                elSettings += '<textarea class="element-description" placeholder="Описание"></textarea>';
                                elSettings += '</div>';
                                elSettings += '</div>';
                                elSettings += '<div class="darkish-form-mask-buttons">';
                                elSettings += '<button class="set-button darkish-form-mask-button edit-save-button">Закрыть</button>';
                                elSettings += '</div>';
                                elSettings +='</div>';
                                elSettings += '<button class="set-button edit-button"><i class="fa fa-pencil-square-o fa-1" aria-hidden="true"></i></button>';
                                $imagePreviews.append('<div class="uploader-preview-element">'+elSettings+'<button class="set-button remove-button"></button><div class="upload-progress"><div class="upload-progress-rel"></div></div><a class="uploader-el-url" target="_blank">Ссылка</a></div>');
                                lastPrev = $imagePreviews.find('.uploader-preview-element').not('.removed-el');
                                lastPrev = lastPrev.get(lastPrev.length-1);
                                $.multUploderObjects[loaderID].newPrevArray.push(lastPrev);
                                $.multUploderObjects[loaderID].files.push({
                                    file:r.files[i],
                                    format:format,
                                    progress:0
                                });
                            }
                            else {

                                $loaderTarg.trigger({
                                    type:"incorrectformat",
                                    format: r.files[i].type,
                                    filename: r.files[i].name
                                });
                            }
                        }
                        if(!$.multUploderObjects[loaderID].uploadState && $.multUploderObjects[loaderID].files.length > 0) {
                            $.multUploderObjects[loaderID].uploadState  = true;
                            $.sendImageFilesStringToServerByNum(0,loaderID);
                            $loaderTarg.trigger({
                                type:"uploadstart",
                                format: r.files[0].type,
                                filename: r.files[0].name
                            });
                        }
                        r.files = null;
                        $(r).val(null);
                    }
                });
                $uploader.on('click','.uploader-preview-element .set-button',function (e) {
                    e.preventDefault();
                    $(this).blur();
                });
                $uploader.on('click','.uploader-preview-element .remove-button',function(e)
                {
                    var $parEl = $(this).closest('.uploader-preview-element');
                    var $jQu = $parEl.closest('.multiple-uploader');
                    var loaderID = $jQu.attr('data-uploader-target-id');
                    var $obj = $('#'+loaderID);
                    if(!$.multUploderObjects[loaderID].uploadState)
                    {
                        if($parEl.hasClass('uploaded-el')) {
                            var ids = JSON.parse($obj.val()+"");

                            if(!('length' in ids)) {
                                ids = [];
                            }

                            let idsNew = [];
                            for(let idsI = 0; idsI < ids.length; idsI++) {
                                if(parseInt($parEl.index(),10) !== parseInt(idsI,10)) {
                                    idsNew.push(ids[idsI]);
                                }
                            }
                            ids = idsNew;
                            $obj.val(JSON.stringify(ids))
                        }
                        $parEl.remove();
                    }
                    else
                    {
                        $parEl.addClass('removed-el');
                        if($parEl.hasClass('upload-state-el'))
                        {
                            $parEl.removeClass('upload-state-el');
                            if($.multUploderObjects[loaderID].fragSent)
                            {
                                $.multUploderObjects[loaderID].fragSent.abort();
                                $.multUploderObjects[loaderID].fragSent = null;
                            }
                            var findUpload = false;
                            for(var i = 0; i<$.multUploderObjects[loaderID].newPrevArray.length-1; i++)
                            {
                                if($.multUploderObjects[loaderID].newPrevArray[i] == $parEl.get(0))
                                {
                                    if(!$($.multUploderObjects[loaderID].newPrevArray[i+1]).hasClass('removed-el'))
                                    {
                                        findUpload = true;
                                        $.sendImageFilesStringToServerByNum(i+1,loaderID);
                                    }
                                    else if(i+2 < $.multUploderObjects[loaderID].newPrevArray.length)
                                    {
                                        var stopIt = false;
                                        for(var j = i+2; j<$.multUploderObjects[loaderID].newPrevArray.length && !stopIt; j++)
                                        {
                                            if(!$($.multUploderObjects[loaderID].newPrevArray[j]).hasClass('removed-el'))
                                            {
                                                findUpload = true;
                                                $.sendImageFilesStringToServerByNum(j,loaderID);
                                                stopIt = true;
                                            }
                                        }
                                    }
                                }
                            }
                            if(!findUpload)
                            {
                                //multUploderObjects[loaderID].onuploadend();
                                $.multUploderObjects[loaderID].files = [];
                                $.multUploderObjects[loaderID].newPrevArray = [];
                                $.multUploderObjects[loaderID].uploadState = false;
                                $jQu.find('.uploader-preview-element.removed-el').remove();
                            }
                        }
                    }
                    return false;
                });
                $uploader.on('click','.uploader-preview-element .edit-button',function (e) {
                    var $but = $(this);
                    var $element = $but.closest('.uploader-preview-element');
                    var $setBlock = $element.find('.element-settings-block');
                    var $allActives = $('.uploader-preview-element .element-settings-block.active, .uploader-preview-element .edit-button.active');
                    $allActives.removeClass('active');
                    if($setBlock.length > 0) {
                        if(!$setBlock.hasClass('active')) {
                            $setBlock.addClass('active');
                            $but.addClass('active');
                        }
                        else {
                            $setBlock.removeClass('active');
                            $but.removeClass('active');
                        }
                    }
                });
                $uploader.on('click','.uploader-preview-element .edit-save-button',function (e) {
                    var $but = $(this);
                    var $setBlock = $but.closest('.element-settings-block');
                    var $element = $setBlock.closest('.uploader-preview-element');
                    var $editBut = $element.find('.edit-button');
                    if($setBlock.length > 0) {
                        if($setBlock.hasClass('active')) {
                            $setBlock.removeClass('active');
                            $editBut.removeClass('active');
                        }
                    }
                });
                $uploader.on('change','.uploader-preview-element input.element-title',function (e) {
                    var $titleInp = $(this);
                    var $parEl = $titleInp.closest('.uploader-preview-element');
                    var $jQu = $parEl.closest('.multiple-uploader');
                    var loaderID = $jQu.attr('data-uploader-target-id');
                    var $obj = $('#'+loaderID);
                    var ids = JSON.parse($obj.val()+"");
                    if(typeof $parEl.attr('data-upload-id') != 'undefined') {
                        let arIndex = $uploader.find('.uploader-preview-element[data-upload-id]').index($parEl);
                        ids[arIndex]['title'] = $titleInp.val();
                        $obj.val(JSON.stringify(ids));

                    }
                });
                $uploader.on('change','.uploader-preview-element textarea.element-description',function (e) {
                    var $descrInp = $(this);
                    var $parEl = $descrInp.closest('.uploader-preview-element');
                    var $jQu = $parEl.closest('.multiple-uploader');
                    var loaderID = $jQu.attr('data-uploader-target-id');
                    var $obj = $('#'+loaderID);
                    var ids = JSON.parse($obj.val()+"");
                    if(typeof $parEl.attr('data-upload-id') != 'undefined') {
                        let arIndex = $uploader.find('.uploader-preview-element[data-upload-id]').index($parEl);
                        ids[arIndex]['description'] = $descrInp.val();
                        $obj.val(JSON.stringify(ids));
                    }
                });
            }
        };
        $(document).on("dragover", function(event) {
            event.preventDefault();
            $('.multiple-uploader').addClass('on-drag-enter-window');
        });
        $(document).on("dragenter", function(event) {
            event.preventDefault();
            $('.multiple-uploader').addClass('on-drag-enter-window');
        });
        $(document).on("dragleave", function(event) {
            event.preventDefault();
            event.stopPropagation();
            $('.multiple-uploader').removeClass('on-drag-enter-window');
        });

        $(document).on("drop", function(event) {
            event.preventDefault();
            $('.multiple-uploader').removeClass('on-drag-enter-window');
        });
        return this.each(make);
    };
    $.event.special["incorrectformat"] = {
        delegateType: "incorrectformat",
        bindType: "incorrectformat",
        handle: function(event) {
            event.handleObj.handler.apply(this, arguments);
        }
    };
    $.fn["incorrectformat"] = function(data,fn) {
        if(arguments.length > 0)
        {
            return this.on("incorrectformat", null, data, fn );
        }
        else
        {
            return this.trigger({
                type: "incorrectformat",
                filetype:'',
                filename:''
            });
        }
    };
    $.event.special["uploadstart"] = {
        delegateType: "uploadstart",
        bindType: "uploadstart",
        handle: function(event) {
            event.handleObj.handler.apply(this, arguments);
        }
    };
    $.fn["uploadstart"] = function(data,fn) {
        if(arguments.length > 0)
        {
            return this.on("uploadstart", null, data, fn );
        }
        else
        {
            return this.trigger({
                type: "uploadstart",
                filetype:'',
                filename:''
            });
        }
    };
    $.event.special["fileuploaded"] = {
        delegateType: "fileuploaded",
        bindType: "fileuploaded",
        handle: function(event) {
            event.handleObj.handler.apply(this, arguments);
        }
    };
    $.fn["fileuploaded"] = function(data,fn) {
        if(arguments.length > 0)
        {
            return this.on("fileuploaded", null, data, fn );
        }
        else
        {
            return this.trigger({
                type: "fileuploaded",
                filetype:'',
                filename:'',
                data: {}
            });
        }
    };
    $.event.special["uploadend"] = {
        delegateType: "uploadend",
        bindType: "uploadend",
        handle: function(event) {
            event.handleObj.handler.apply(this, arguments);
        }
    };
    $.fn["uploadend"] = function(data,fn) {
        if(arguments.length > 0)
        {
            return this.on("uploadend", null, data, fn );
        }
        else
        {
            return this.trigger({
                type: "uploadend",
                filetype:'',
                filename:'',
                data: {}
            });
        }
    };
    $(document).ready(function (e) {
        $(document).click(function (e) {
            var $targ = $(e.target);
            if(!$targ.hasClass('element-settings-block') && !$targ.hasClass('edit-button')) {
                var $par = $targ.closest('.element-settings-block');
                var $editBut = $targ.closest('.edit-button');
                if($par.length === 0 && $editBut.length === 0) {
                    var $allActives = $('.uploader-preview-element .element-settings-block.active, .uploader-preview-element .edit-button.active');
                    $allActives.removeClass('active');
                }
            }
        });
    });
})(jQuery);