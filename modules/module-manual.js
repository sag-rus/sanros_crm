function show_manual(){
  select_menu('manual-menu');
  show_manual_directory('/');
}

function show_manual_directory(directory){
  if(!directory){
    directory = '/';
  }
  var str = 'func=show_manual_directory&dir=' + directory;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		dataType: 'JSON',
		success: function(data){
      var html = '<div class="manual-directory-view" dir="' +directory+ '" desc="' +data['desc']+ '">' +
                '<div class="list-group">' +
                  '<div class="list-group-item">' +
                    '<div class="pull-right">' +
                      '<div class="form-inline">' +
                        '<div class="input-group">' +
                          '<input type="text" class="form-control search-manual" placeholder="Поиск">' +
                          '<span class="input-group-btn">' +
                            '<button class="btn btn-sm btn-default btn-search-manual"><i class="fa fa-search"></i></button>' +
                          '</span>' +
                        '</div> ' +
                        '<button class="btn btn-default btn-sm" onclick="show_manual_directory()"><i class="fa fa-code"></i> на главную</button> ' +
                        '<button class="btn btn-default btn-sm btn-new-file-directory"><i class="fa fa-plus"></i> создать</button> ' +
                        '<button class="btn btn-default btn-sm btn-edit-description"><i class="fa fa-circle-thin"></i> описание</button> ' +
                      '</div>' +
                    '</div>' +
                    '<h3><i class="fa fa-folder-open"></i> ' +directory+ '</h3>' +
                    '<div class="clearfix"></div>' +
                  '</div>' +
                  '<div class="list-group-item row display-manual-files">';
      html+= display_manual_files(data);
      html+= '</div></div></div><div class="manual-file-view">';
      $('#body').html(html);
      show_tooltip('.manual-directory-view');

      $('.btn-new-file-directory').click(function(){
        var html = '<div class="modal fade">' +
        	'<div class="modal-dialog">' +
        		'<div class="modal-content">' +
        			'<div class="modal-header">' +
        				'<button type="button" class="close" data-dismiss="modal" aria-label="Close"><i class="fa fa-times"></i></button>' +
        				'<h4 class="modal-title">Создать</h4>' +
        			'</div>' +
        			'<div class="modal-body form-horizontal create-file" dir="' +directory+ '">' +
                '<div class="form-group">' +
                  '<label class="col-sm-4 control-label">Тип</label>' +
                  '<div class="col-sm-8 btn-group type-create" data-toggle="buttons">' +
                    '<label class="btn btn-default btn-sm active"><input type="radio" checked name="type" value="dir" />&nbsp;каталог&nbsp;</label>' +
                    '<label class="btn btn-default btn-sm"><input type="radio" name="type" value="file" />&nbsp;файл&nbsp;</label>' +
                  '</div>' +
                '</div>' +
        				'<div class="form-group form-group-margin">' +
        					'<label class="col-sm-4 control-label">Название</label>' +
        					'<div class="col-sm-8">' +
        						'<input type="text" class="form-control name" />' +
        					'</div>' +
        				'</div>' +
        			'</div>' +
        			'<div class="modal-footer">' +
        				'<button type="button" class="btn btn-success btn-sm btn-create-file"><i class="fa fa-check"></i> сохранить</button>' +
        			'</div>' +
        		'</div>' +
        	'</div>' +
        '</div>';
        show_modal(html);

        $('.btn-create-file').click(function(){
          var name = $('.create-file .name').val();
          var type = $('.create-file .type-create input:checked').val();
          var directory = $('.create-file').attr('dir');
          var str = 'func=create_file_manual&dir=' + directory + '&name=' + name + '&type=' + type;
        	$.ajax({
        		url: 'mysql.php',
        		type: 'POST',
        		data: str,
        		success: function(){
              remove_all_windows();
              show_manual_directory(directory);
            }
          });
        });

      });

      $('.btn-search-manual').click(function(){
        var search = $('.search-manual').val();
        var dir = $('.manual-directory-view').attr('dir');
        if(search.length < 3){
          show_mistake('.search-manual');
        }else{
          clear_mistake('.manual-directory-view');
          var str = 'func=search_manual&search=' + search + '&dir=' + dir;
        	$.ajax({
        		url: 'mysql.php',
        		type: 'POST',
        		data: str,
            dataType: 'JSON',
        		success: function(data){
              var html = display_manual_files(data);
              $('.display-manual-files').html(html);
              show_tooltip('.manual-directory-view');
            }
          });
        }
      });

      $('.btn-edit-description').click(function(){

        var desc = $('.manual-directory-view').attr('desc');
        var html = '<div class="modal fade">' +
        	'<div class="modal-dialog">' +
        		'<div class="modal-content">' +
        			'<div class="modal-header">' +
        				'<button type="button" class="close" data-dismiss="modal" aria-label="Close"><i class="fa fa-times"></i></button>' +
        				'<h4 class="modal-title">Изменить описание</h4>' +
        			'</div>' +
        			'<div class="modal-body form-horizontal update-desc">' +
        				'<div class="form-group form-group-margin">' +
        					'<label class="col-sm-4 control-label">Описание</label>' +
        					'<div class="col-sm-8">' +
        						'<textarea class="form-control desc">' +desc+ '</textarea>' +
        					'</div>' +
        				'</div>' +
        			'</div>' +
        			'<div class="modal-footer">' +
        				'<button type="button" class="btn btn-success btn-sm btn-update-desc"><i class="fa fa-check"></i> сохранить</button>' +
        			'</div>' +
        		'</div>' +
        	'</div>' +
        '</div>';
        show_modal(html);

        $('.btn-update-desc').click(function(){
          var desc = $('.update-desc .desc').val();
          var directory = $('.manual-directory-view').attr('dir');
          var str = 'func=update_desc_directory_manual&dir=' + directory + '&desc=' + desc;
        	$.ajax({
        		url: 'mysql.php',
        		type: 'POST',
        		data: str,
        		success: function(){
              remove_all_windows();
              $('.manual-directory-view').attr('desc', desc);
            }
          });
        });

      });

    }
  });
}

function display_manual_files(data){
  var html = '';
  if(data['exist'] == 1){
    for(var index in data['files']){
      var file = data['files'][index];
      var path = file['path'];
      if(file['type'] == 'dir'){
        html+= '<div class="col-sm-4"><div class="manual-block" onclick="show_manual_directory(\'' +path+ '\')" data-toggle="tooltip" title="' +file['desc']+ '"><i class="fa fa-folder"></i> ' +file['name']+ '</div></div>';
      }else{
        html+= '<div class="col-sm-4"><div class="manual-block" onclick="open_file_manual(\'' +path+ '\')" data-toggle="tooltip" title="' +path+ '"><i class="fa fa-file-text"></i> ' +file['name']+ '</div></div>';
      }
    }
    if(html == ''){
      html = '<div class="alert alert-info"><i class="fa fa-info-circle"></i> файлов не найдено</div>'
    }
  }
  return html;
}

function open_file_manual(file){
  var str = 'func=open_file_manual&file=' + file;
  $.ajax({
    url: 'mysql.php',
    type: 'POST',
    data: str,
    dataType: 'JSON',
    success: function(data){
      var html = '';
      if(data['exist'] == 1){
        $('.manual-directory-view').hide();
        html = '<div class="list-group update-file" name="' +data['name']+ '">' +
                    '<div class="list-group-item">' +
                      '<div class="pull-right">' +
                        '<button class="btn btn-info btn-sm btn-edit-file"><i class="fa fa-pencil"></i> изменить</button>' +
                        '<button class="btn btn-success btn-sm btn-update-file hidden"><i class="fa fa-check-circle"></i> сохранить</button>' +
                        ' <button class="btn btn-default btn-sm btn-close-file"><i class="fa fa-times"></i> закрыть</button>' +
                      '</div>' +
                      '<h3><i class="fa fa-file-o"></i> ' +data['name']+ '</h3>' +
                      '<div class="clearfix"></div>' +
                    '</div>' +
                    '<div class="list-group-item">' +
                      '<div class="file-text-view">' +data['text-view']+ '</div>' +
                      '<textarea class="form-control hidden file-text" style="height: 500px">' +data['text']+ '</textarea>'
                    '</div>' +
                  '</div>';
        $('.manual-file-view').html(html);

        $('.btn-edit-file').click(function(){
          $(this).remove();
          $('.file-text-view').remove();
          $('.btn-update-file').removeClass('hidden');
          $('.file-text').removeClass('hidden');
        });

        $('.btn-update-file').click(function(){
          var text = $('.update-file .file-text').val();
          var file = $('.update-file').attr('name');
          var str = 'func=update_file_manual&file=' + file + '&text=' + text;
        	$.ajax({
        		url: 'mysql.php',
        		type: 'POST',
        		data: str,
        		success: function(){
              open_file_manual(file);
            }
          });
        });

        $('.btn-close-file').click(function(){
          $('.manual-directory-view').show();
          $('.manual-file-view').html('');
        });

      }

    }
  });
}
