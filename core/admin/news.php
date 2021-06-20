<?php
require_once __DIR__.'/../../vendor/autoload.php';

function save_new_news($connect){
	$date = $_POST["date"];
	$title = trim($_POST["title"]);
	$url = mb_strtolower($_POST["url"], "UTF-8");
	$website = $_POST["website"];
	$text = $_POST["text"];
	$desc = $_POST["desc"];
	$image = $_POST["image"];
	$website = $connect->getOne("SELECT id FROM st_website WHERE url=?s", $website);
	if(!$website)
		return json_encode("no");
	if($connect->getOne("SELECT id FROM news WHERE website=?i AND url=?s", $website, $url))
		return json_encode("exist");
	$connect->query("INSERT INTO news(date, title, url, website, text, image, description) VALUES (?s, ?s, ?s, ?s, ?s, ?s, ?s)", $date, $title, $url, $website, $text, $image, $desc);
	return json_encode($website);
}

function show_websites_news($connect){
	$result = array();
	$data = $connect->getAll("SELECT website, COUNT(*) as count FROM news GROUP BY website");
	foreach($data as $row){
		$array = array();
		$website = $row["website"];
		$array["website"] = $connect->getOne("SELECT url FROM st_website WHERE id=?i", $website);
		$array["count"] = $row["count"];
		$result[$website] = $array;
	}
	return json_encode($result);
}

function show_news_website($connect){
	global $directory;
	$result = array("news" => array(), "images" => array());
	$website = $_POST["website"];
	$url = $connect->getOne("SELECT url FROM st_website WHERE id=?i", $website);
	$data = $connect->getAll("SELECT id, DATE_FORMAT(date, '%d.%m.%Y') as post, title, text, active, website FROM news WHERE website=?i ORDER BY date DESC", $website);
	foreach($data as $row){
		$id = $row["id"];
		$array = array();
		$array["id"] = $id;
		$array["active"] = $row["active"];
		$array["date"] = $row["post"];
		$array["title"] = $row["title"];
		$array["text"] = str_replace("src=\"/images", "src=\"http://$url/images", $row["text"]);
		$result["news"][] = $array;
	}
	$folder = $directory."/temp/news/".$website;
	if(!file_exists($folder))
		mkdir($folder, 0777);
	$open = opendir($folder);
	while($image = readdir($open)){
		if(($image != ".") AND ($image != "..") AND ($image)){
			$result["images"][] = "temp/news/".$website."/".$image;
		}
	}
	$result["url"] = $url;
	return json_encode($result);
}

function show_sites_list($connect) {
    global $id_rights, $session_login;
	$sites = $connect->getAll("SELECT id, name, domain FROM sites ORDER BY id ASC");
	ob_start();
	?>
	<div class="panel panel-default">
		<div class="panel-heading"><i class="fa fa-list"></i> Сайты (новые)</div>
		<div class="panel-body">
			<table class="table table-hover table-condensed">
				<thead>
				<tr>
					<th>
						ID
					</th>
					<th>
						Название
					</th>
					<th>
						Домен
					</th>
                    <th>
                        Действия
                    </th>
				</tr>
				</thead>
				<tbody>
        <?php
        foreach ($sites as $site) {
          ?>
          <tr>
              <td><?=$site['id'];?></td>
              <td><?=$site['name'];?></td>
              <td><a href="//<?=idn_to_utf8($site['domain'],0,INTL_IDNA_VARIANT_UTS46);?>" target="_blank"><?=idn_to_utf8($site['domain'],0,INTL_IDNA_VARIANT_UTS46);?></a></td>
              <td>
                  <button class="btn btn-default btn-sm" onclick="show_sites_contents_list(<?=$site['id'];?>);">Материалы</button>
                  <button class="btn btn-default btn-sm" onclick="show_sites_addresses_list(<?=$site['id'];?>);">Адреса</button>
                  <button class="btn btn-default btn-sm" onclick="show_sites_menu_items_list(<?=$site['id'];?>);">Элементы меню</button>
                  <button class="btn btn-default btn-sm" onclick="show_sites_meta_templates_list(<?=$site['id'];?>);">Шаблоны мета-тегов</button>
                  <button class="btn btn-default btn-sm" onclick="show_sites_phones_list(<?=$site['id'];?>);">Телефоны</button>
                  <?php if($id_rights > 5)  { ?>
                      <button class="btn btn-default btn-sm"><i class="fa fa-trash-o"></i></button>
                  <?php } ?>
                  <?php if($id_rights > 4 || $session_login == 62)  { ?>
                      <button class="btn btn-default btn-sm" onclick="edit_site(<?=$site['id'];?>);"><i class="fa fa-pencil"></i></button>
                      <button class="btn btn-default btn-sm" onclick="edit_site_tech(<?=$site['id'];?>);"><i class="fa fa-gear"></i></button>
                      <button class="btn btn-default btn-sm" onclick="edit_site_icons(<?=$site['id'];?>);"><i class="fa fa-image"></i> Иконки сайта</button>

                  <?php } ?>
              </td>
          </tr>
          <?php
        }
        ?>
				</tbody>
			</table>
		</div>
		<div class="panel-footer text-right">
          <?php if($id_rights > 5)  { ?>
                <button type="button" class="btn btn-primary btn-sm" onclick="add_new_site()"><i class="fa fa-plus-circle"></i> Добавить сайт</button>
          <?php } ?>
		</div>
	</div>
	<?php
	return ob_get_clean();
}

function show_sites_contents_list($connect) {
  global $id_rights;

  $contentTypesRows = $connect->getAll("SELECT * FROM `app_models_site_contenttype` WHERE `status` = 1");

  $content_types = [
  ];

  foreach ($contentTypesRows as $contentTypesRow) {
      $content_types[$contentTypesRow['machine_name']] = $contentTypesRow['name'];
  }

  $site_id = isset($_POST['site_id'])?(int)$_POST['site_id']:0;
  $type = isset($_POST['type'])?trim($_POST['type']):'all';
  $sort = isset($_POST['sort'])?trim($_POST['sort']):'id';
  $body2 = isset($_POST['body2'])?(int)$_POST['body2']:0;
  $filter_empty_field_name = isset($_POST['filter_empty_field_name'])?$_POST['filter_empty_field_name']:'description';
  $filter_field_has_string = isset($_POST['filter_field_has_string'])?$_POST['filter_field_has_string']:'title';


  $filterEmptyFields = [
    'description' => 'Мета-описание',
    'keywords' => 'Ключевые слова',
    'summary' => 'Анонс',
    'title_h1' => 'Заголовок H1',
    'title_h2' => 'Заголовок H2',
    'breadcrumb_title' => 'Заголовок к крошкам',
    'body' => 'Содержимое',
    'body2' => 'Доп. содержимое'
  ];

  $filterFieldHasStrings = [
     'title' => 'Заголовок',
     'path' => 'Адрес'
  ];


  if(!array_key_exists($filter_empty_field_name,$filterEmptyFields))
      $filter_empty_field_name = "description";

  if(!array_key_exists($filter_field_has_string,$filterFieldHasStrings))
    $filter_field_has_string = "title";

  if(!in_array($sort,['id','created','published','title']))
      $sort = 'id';

  $q = isset($_POST['q'])?$_POST['q']:"";
  $qp = mb_strtolower(trim($q));
  $site = NULL;
  if($site_id) {
    $site = $connect->getRow("SELECT `id`, `name`, `domain` FROM `sites` WHERE `id`=?i",$site_id);
    if($site) {
      if($type === 'all') {
        if(mb_strlen($qp) > 0) {
          if($body2 === 1)
              $sites_contents = $connect->getAll("SELECT id, title, published, synchronized, type, status, path FROM `sites_contents` WHERE `site_id`=?i AND `status` <> 2 AND `".$filter_field_has_string."` LIKE ?s AND `".$filter_empty_field_name."` = '' ORDER BY " . $sort . " ASC", $site_id, "%" . $qp . "%");
          elseif ($body2 === 2)
              $sites_contents = $connect->getAll("SELECT id, title, published, synchronized, type, status, path FROM `sites_contents` WHERE `site_id`=?i AND `status` <> 2 AND `".$filter_field_has_string."` LIKE ?s AND `".$filter_empty_field_name."` != '' ORDER BY " . $sort . " ASC", $site_id, "%" . $qp . "%");
          else
              $sites_contents = $connect->getAll("SELECT id, title, published, synchronized, type, status, path FROM `sites_contents` WHERE `site_id`=?i AND `status` <> 2 AND `".$filter_field_has_string."` LIKE ?s ORDER BY " . $sort . " ASC", $site_id, "%" . $qp . "%");

        }
        else {
          if($body2 === 1)
              $sites_contents = $connect->getAll("SELECT id, title, published, synchronized, type, status, path FROM `sites_contents` WHERE `site_id`=?i AND `status` <> 2 AND `".$filter_empty_field_name."` = '' ORDER BY " . $sort . " ASC", $site_id);
          elseif ($body2 === 2)
              $sites_contents = $connect->getAll("SELECT id, title, published, synchronized, type, status, path FROM `sites_contents` WHERE `site_id`=?i AND `status` <> 2 AND `".$filter_empty_field_name."` != '' ORDER BY " . $sort . " ASC", $site_id);
          else
              $sites_contents = $connect->getAll("SELECT id, title, published, synchronized, type, status, path FROM `sites_contents` WHERE `site_id`=?i AND `status` <> 2 ORDER BY " . $sort . " ASC", $site_id);

        }
      }
      else {
        if(mb_strlen($qp) > 0) {
          if($body2 === 1)
              $sites_contents = $connect->getAll("SELECT id, title, published, synchronized, type, status, path FROM `sites_contents` WHERE `site_id`=?i AND `type` = ?s AND `status` <> 2 AND `".$filter_field_has_string."` LIKE ?s AND `".$filter_empty_field_name."` = '' ORDER BY " . $sort . " ASC", $site_id, $type, "%" . $qp . "%");
          elseif ($body2 === 2)
              $sites_contents = $connect->getAll("SELECT id, title, published, synchronized, type, status, path FROM `sites_contents` WHERE `site_id`=?i AND `type` = ?s AND `status` <> 2 AND `".$filter_field_has_string."` LIKE ?s AND `".$filter_empty_field_name."` != '' ORDER BY " . $sort . " ASC", $site_id, $type, "%" . $qp . "%");
          else
              $sites_contents = $connect->getAll("SELECT id, title, published, synchronized, type, status, path FROM `sites_contents` WHERE `site_id`=?i AND `type` = ?s AND `status` <> 2 AND `".$filter_field_has_string."` LIKE ?s ORDER BY " . $sort . " ASC", $site_id, $type, "%" . $qp . "%");

        }
        else {
          if($body2 === 1)
            $sites_contents = $connect->getAll("SELECT id, title, published, synchronized, type, status, path FROM `sites_contents` WHERE `site_id`=?i AND `type` = ?s AND `status` <> 2 AND `".$filter_empty_field_name."` = '' ORDER BY " . $sort . " ASC", $site_id, $type);
          elseif ($body2 === 2)
            $sites_contents = $connect->getAll("SELECT id, title, published, synchronized, type, status, path FROM `sites_contents` WHERE `site_id`=?i AND `type` = ?s AND `status` <> 2 AND `".$filter_empty_field_name."` != '' ORDER BY " . $sort . " ASC", $site_id, $type);
          else
            $sites_contents = $connect->getAll("SELECT id, title, published, synchronized, type, status, path FROM `sites_contents` WHERE `site_id`=?i AND `type` = ?s AND `status` <> 2 ORDER BY " . $sort . " ASC", $site_id, $type);
        }
      }
    }
    else
        $sites_contents = [];
  }
  elseif($type === 'all') {
    if(mb_strlen($qp) > 0) {
      if($body2 === 1)
        $sites_contents = $connect->getAll("SELECT id, title, published, synchronized, type, status, path FROM `sites_contents` WHERE `status` <> 2 AND `".$filter_field_has_string."` LIKE ?s AND `".$filter_empty_field_name."` = '' ORDER BY " . $sort . " ASC", "%" . $qp . "%");
      elseif ($body2 === 2)
        $sites_contents = $connect->getAll("SELECT id, title, published, synchronized, type, status, path FROM `sites_contents` WHERE `status` <> 2 AND `".$filter_field_has_string."` LIKE ?s AND `".$filter_empty_field_name."` != '' ORDER BY " . $sort . " ASC", "%" . $qp . "%");
      else
        $sites_contents = $connect->getAll("SELECT id, title, published, synchronized, type, status, path FROM `sites_contents` WHERE `status` <> 2 AND `".$filter_field_has_string."` LIKE ?s ORDER BY " . $sort . " ASC", "%" . $qp . "%");
    }
    else {
      if($body2 === 1)
        $sites_contents = $connect->getAll("SELECT id, title, published, synchronized, type, status, path FROM `sites_contents` WHERE `status` <> 2 AND `".$filter_empty_field_name."` = '' ORDER BY " . $sort . " ASC");
      elseif ($body2 === 2)
        $sites_contents = $connect->getAll("SELECT id, title, published, synchronized, type, status, path FROM `sites_contents` WHERE `status` <> 2 AND `".$filter_empty_field_name."` != '' ORDER BY " . $sort . " ASC");
      else
        $sites_contents = $connect->getAll("SELECT id, title, published, synchronized, type, status, path FROM `sites_contents` WHERE `status` <> 2 ORDER BY " . $sort . " ASC");

    }
  }
  else {
    if(mb_strlen($qp) > 0) {
      if($body2 === 1)
        $sites_contents = $connect->getAll("SELECT id, title, published, synchronized, type, status, path FROM `sites_contents` WHERE `status` <> 2 AND `type` = ?s AND `".$filter_field_has_string."` LIKE ?s AND `".$filter_empty_field_name."` = '' ORDER BY " . $sort . " ASC", $type, "%" . $qp . "%");
      elseif ($body2 === 2)
        $sites_contents = $connect->getAll("SELECT id, title, published, synchronized, type, status, path FROM `sites_contents` WHERE `status` <> 2 AND `type` = ?s AND `".$filter_field_has_string."` LIKE ?s AND `".$filter_empty_field_name."` != '' ORDER BY " . $sort . " ASC", $type, "%" . $qp . "%");
      else
        $sites_contents = $connect->getAll("SELECT id, title, published, synchronized, type, status, path FROM `sites_contents` WHERE `status` <> 2 AND `type` = ?s AND `".$filter_field_has_string."` LIKE ?s ORDER BY " . $sort . " ASC", $type, "%" . $qp . "%");
    }
    else {
      if($body2 === 1)
        $sites_contents = $connect->getAll("SELECT id, title, published, synchronized, type, status, path FROM `sites_contents` WHERE `status` <> 2 AND `type` = ?s AND `".$filter_empty_field_name."` = '' ORDER BY " . $sort . " ASC", $type);
      elseif ($body2 === 2)
        $sites_contents = $connect->getAll("SELECT id, title, published, synchronized, type, status, path FROM `sites_contents` WHERE `status` <> 2 AND `type` = ?s AND `".$filter_empty_field_name."` != '' ORDER BY " . $sort . " ASC", $type);
      else
        $sites_contents = $connect->getAll("SELECT id, title, published, synchronized, type, status, path FROM `sites_contents` WHERE `status` <> 2 AND `type` = ?s ORDER BY " . $sort . " ASC", $type);

    }
  }

  ob_start();
  ?>
    <div class="panel panel-default">
        <div class="panel-heading">
            <i class="fa fa-list"></i> Материалы<?php if($site) { ?> сайта «<?=$site['name'];?>»<?php } ?> <button class="btn btn-success btn-sm btn-sites-sync" onclick="sync_site(<?=($site?$site['id']:0);?>)">Синхронизировать</button> <button class="btn btn-default btn-sm" onclick="show_sites_list();">К списку сайтов</button> <button type="button" class="btn btn-primary btn-sm" onclick="add_new_sites_content(<?=$site_id;?>)"><i class="fa fa-plus-circle"></i> Добавить материал</button>

            <div class="std-bottom-margin"></div>
            <div class="row">
                <div class="col-md-3">
                    <div class="row">
                       <div class="col-sm-6">
                           <select class="form-control" id="filter-field-has-string" onchange="show_sites_contents_list(<?=$site_id;?>);">
                             <?php foreach ($filterFieldHasStrings as $filterFieldHasStringKey => $filterFieldHasStringName) { ?>
                                 <option value="<?=$filterFieldHasStringKey;?>"<?php if($filter_field_has_string === $filterFieldHasStringKey) { ?> selected<?php } ?>><?=$filterFieldHasStringName;?></option>
                             <?php } ?>
                           </select>
                       </div>
                       <div class="col-sm-6">
                           <label class="control-label admin-label">
                               содержит
                           </label>
                       </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <select class="form-control" id="filter-empty-field-name" onchange="show_sites_contents_list(<?=$site_id;?>);">
                        <?php foreach ($filterEmptyFields as $filterEmptyFieldsKey => $filterEmptyFieldsValue) { ?>
                            <option value="<?=$filterEmptyFieldsKey;?>"<?php if($filterEmptyFieldsKey === $filter_empty_field_name) { ?> selected<?php } ?>><?=$filterEmptyFieldsValue;?></option>
                        <?php } ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="control-label admin-label">
                        Тип материала
                    </label>
                </div>
                <div class="col-md-3">
                    <label class="control-label admin-label">
                        Сортировка
                    </label>
                </div>
            </div>
            <div class="row">
                <div class="col-md-3">
                    <input class="form-control" value="<?=$q;?>" id="content-text-filter" onchange="show_sites_contents_list(<?=$site_id;?>);">
                </div>
                <div class="col-md-3">
                    <select class="form-control" id="content-body2-filter" onchange="show_sites_contents_list(<?=$site_id;?>);">
                        <option value="0"<?php if($body2 === 0) { ?> selected<?php } ?>>Не важно</option>
                        <option value="1"<?php if($body2 === 1) { ?> selected<?php } ?>>Нет</option>
                        <option value="2"<?php if($body2 === 2) { ?> selected<?php } ?>>Есть</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select class="form-control" id="content-type-filter" onchange="show_sites_contents_list(<?=$site_id;?>);">
                        <option value="all"<?php if($type === 'all') { ?> selected<?php } ?>>Любой</option>
                        <?php foreach ($content_types as $content_type_machine_name => $content_type) { ?>
                        <option value="<?=$content_type_machine_name;?>"<?php if($type === $content_type_machine_name) { ?> selected<?php } ?>><?=$content_type;?></option>
                        <?php } ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <select class="form-control" id="content-sort" onchange="show_sites_contents_list(<?=$site_id;?>);">
                        <option value="id"<?php if($sort === 'id') { ?> selected<?php } ?>>ID</option>
                        <option value="created"<?php if($sort === 'created') { ?> selected<?php } ?>>Дата создания</option>
                        <option value="published"<?php if($sort === 'published') { ?> selected<?php } ?>>Дата публикации</option>
                        <option value="title"<?php if($sort === 'title') { ?> selected<?php } ?>>Заголовок</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="panel-body table-body">
            <table class="table table-hover table-condensed">
                <thead>
                <tr>
                    <th>
                        ID
                    </th>
                    <th>
                        Заголовок
                    </th>
                    <th>
                        Тип материала
                    </th>
                    <th>
                        Статус
                    </th>
                    <th>
                        Ссылка
                    </th>
                    <th>
                        Действия
                    </th>
                </tr>
                </thead>
                <tbody>
                <?php
                foreach ($sites_contents as $sites_content) {
                  ?>
                    <tr <?php if(!$sites_content['synchronized']){ ?>class="not-synchronized"<?php } ?>>
                        <td><?=$sites_content['id'];?></td>
                        <td><?=$sites_content['title'];?></td>
                        <td><?=$content_types[$sites_content['type']];?></td>
                        <td class="<?=$sites_content['status']?"success":"danger";?>">
                            <?=$sites_content['status']?"Опубликован":"Снят с публикации";?>
                        </td>
                        <td class="text-center">
                           <a href="//<?=idn_to_utf8($site['domain'],0,INTL_IDNA_VARIANT_UTS46);?><?=$sites_content['path'];?>" target="_blank"><i class="fa fa-link"></i></a>
                        </td>
                        <td>
                            <?php if($id_rights > 5) { ?>
                                <button class="btn btn-default btn-sm" onclick="remove_sites_content(<?=$sites_content['id'];?>);"><i class="fa fa-trash-o"></i></button>
                            <?php } ?>
                            <button class="btn btn-default btn-sm" onclick="edit_sites_content(<?=$sites_content['id'];?>)"><i class="fa fa-pencil"></i></button>
                            <?php if($id_rights > 5) { ?>
                                <button class="btn btn-default btn-sm" onclick="edit_sites_content(<?=$sites_content['id'];?>,1)"><i class="fa fa-copy"></i></button>
                            <?php } ?>

                        </td>
                    </tr>
                  <?php
                }
                ?>
                </tbody>
            </table>
        </div>
        <div class="panel-footer text-right">
            <button type="button" class="btn btn-primary btn-sm" onclick="add_new_sites_content(<?=$site_id;?>)"><i class="fa fa-plus-circle"></i> Добавить материал</button>
        </div>
    </div>
  <?php
  return ob_get_clean();
}

function show_sites_addresses_list($connect) {
  global $id_rights;
  $site_id = isset($_POST['site_id'])?(int)$_POST['site_id']:0;
  $site = NULL;
  if($site_id) {
    $site = $connect->getRow("SELECT `id`, `name`, `domain` FROM `sites` WHERE `id`=?i",$site_id);
    if($site)
      $sites_addresses = $connect->getAll("SELECT * FROM `app_models_site_address` WHERE `site_id`=?i ORDER BY `sort` ASC", $site_id);
    else
      $sites_addresses = [];
  }
  else
    $sites_addresses = $connect->getAll("SELECT * FROM `app_models_site_address` ORDER BY `sort` ASC");

  ob_start();
  ?>
    <div class="panel panel-default addresses-panel">
        <div class="panel-heading"><i class="fa fa-list"></i> Адреса<?php if($site) { ?> сайта «<?=$site['name'];?>»<?php } ?>  <button class="btn btn-default btn-sm" onclick="show_sites_list();">К списку сайтов</button></div>
        <div class="panel-body table-body">
            <table class="table table-hover table-condensed">
                <thead>
                <tr>
                    <th>
                        ID
                    </th>
                    <th>
                        Заголовок
                    </th>
                    <th>
                        Статус
                    </th>
                    <th>
                        Действия
                    </th>
                </tr>
                </thead>
                <tbody>
                <?php
                foreach ($sites_addresses as $sites_address) {
                  ?>
                    <tr>
                        <td><?=$sites_address['id'];?></td>
                        <td><?=$sites_address['title'];?></td>
                        <td><?=$sites_address['status'] == 1?"Активен":"Не активен";?></td>
                        <td>
                          <?php if($id_rights > 4) { ?>
                              <button class="btn btn-default btn-sm" onclick="remove_sites_address(<?=$sites_address['id'];?>);"><i class="fa fa-trash-o"></i></button>
                              <button class="btn btn-default btn-sm" onclick="sites_address(<?=$sites_address['id'];?>);"><i class="fa fa-pencil"></i></button>
                          <?php } ?>
                        </td>
                    </tr>
                  <?php
                }
                ?>
                </tbody>
            </table>
        </div>
        <div class="panel-footer text-right">
            <?php if($id_rights > 4) { ?>
                <button type="button" class="btn btn-primary btn-sm" onclick="sites_address(null,<?=$site_id;?>);"><i class="fa fa-plus-circle"></i> Добавить адрес</button>
            <?php } ?>
        </div>
    </div>
  <?php
  return ob_get_clean();
}

function show_sites_menu_items_list($connect) {
  global $id_rights;
  $menuArray = [
    1 => 'Верхнее основное',
    2 => 'Верхнее второе',
    3 => 'Нижнее'
  ];
  $site_id = isset($_POST['site_id'])?(int)$_POST['site_id']:0;
  $site = NULL;
  if($site_id) {
    $site = $connect->getRow("SELECT `id`, `name`, `domain` FROM `sites` WHERE `id`=?i",$site_id);
    if($site)
      $sites_menu_items = $connect->getAll("SELECT * FROM `app_models_site_menu_item` WHERE `site_id`=?i AND `parent_id` = 0 ORDER BY `sort` ASC", $site_id);
    else
      $sites_menu_items = [];
  }
  else
    $sites_menu_items = $connect->getAll("SELECT * FROM `app_models_site_menu_item` WHERE `parent_id` = 0 ORDER BY `sort` ASC");

  ob_start();
  ?>
    <div class="panel panel-default sites-menu-items-panel">
        <div class="panel-heading"><i class="fa fa-list"></i> Элементы меню<?php if($site) { ?> сайта «<?=$site['name'];?>»<?php } ?> <button class="btn btn-default btn-sm" onclick="show_sites_list();">К списку сайтов</button></div>
        <div class="panel-body table-body">
            <table class="table table-hover table-condensed">
                <thead>
                <tr>
                    <th>
                        Название
                    </th>
                    <th>
                        Ссылка
                    </th>
                    <th>
                        Меню
                    </th>
                    <th>
                        Статус
                    </th>
                    <th>
                        Действия
                    </th>
                </tr>
                </thead>
                <tbody>
                <?php
                foreach ($sites_menu_items as $sites_menu_item) {
                  ?>
                    <tr class="main-menu-item">
                        <td><?=$sites_menu_item['name'];?></td>
                        <td><?=$sites_menu_item['href'];?></td>
                        <td><?=$menuArray[$sites_menu_item['menu_id']];?></td>
                        <td><?=$sites_menu_item['status'] == 1?"Активен":"Не активен";?></td>
                        <td>
                          <?php if($id_rights > 4) { ?>
                              <button class="btn btn-default btn-sm" onclick="remove_sites_menu_item(<?=$sites_menu_item['id'];?>);"><i class="fa fa-trash-o"></i></button>
                              <button class="btn btn-default btn-sm" onclick="sites_menu_item(<?=$sites_menu_item['id'];?>);"><i class="fa fa-pencil"></i></button>
                              <button class="btn btn-default btn-sm" onclick="sites_menu_item(null,<?=$site_id;?>,<?=$sites_menu_item['id'];?>);"><i class="fa fa-plus"></i></button>
                          <?php } ?>
                        </td>
                    </tr>
                    <?php
                    $sites_menu_items_sub  = $connect->getAll("SELECT * FROM `app_models_site_menu_item` WHERE `site_id`=?i AND `parent_id` = ?i ORDER BY `sort` ASC", $site_id, $sites_menu_item['id']);
                    foreach ($sites_menu_items_sub as $sites_menu_item_sub) {
                        ?>
                        <tr class="simple-row">
                            <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?=$sites_menu_item_sub['name'];?></td>
                            <td><?=$sites_menu_item_sub['href'];?></td>
                            <td><?=$menuArray[$sites_menu_item_sub['menu_id']];?></td>
                            <td><?=$sites_menu_item_sub['status'] == 1?"Активен":"Не активен";?></td>
                            <td>
                              <?php if($id_rights > 4) { ?>
                                  <button class="btn btn-default btn-sm" onclick="remove_sites_menu_item(<?=$sites_menu_item_sub['id'];?>);"><i class="fa fa-trash-o"></i></button>
                                  <button class="btn btn-default btn-sm" onclick="sites_menu_item(<?=$sites_menu_item_sub['id'];?>,<?=$site_id;?>,<?=$sites_menu_item['id'];?>);"><i class="fa fa-pencil"></i></button>
                              <?php } ?>
                            </td>
                        </tr>
                      <?php
                    }
                    ?>
                  <?php
                }
                ?>
                </tbody>
            </table>
        </div>
        <div class="panel-footer text-right">
          <?php if($id_rights > 4) { ?>
              <button type="button" class="btn btn-primary btn-sm" onclick="sites_menu_item(null,<?=$site_id;?>);"><i class="fa fa-plus-circle"></i> Добавить элемент</button>
          <?php } ?>
        </div>
    </div>
  <?php
  return ob_get_clean();
}

function show_sites_phones_list($connect) {
  global $id_rights;
  $blocksArray = [
    'header' => 'Шапка сайта',
    'footer' => 'Подвал сайта'
  ];
  $site_id = isset($_POST['site_id'])?(int)$_POST['site_id']:0;
  $site = NULL;
  if($site_id) {
    $site = $connect->getRow("SELECT `id`, `name`, `domain` FROM `sites` WHERE `id`=?i",$site_id);
    if($site)
      $sites_phones = $connect->getAll("SELECT * FROM `app_models_site_phone` WHERE `site_id`=?i ORDER BY `sort` ASC", $site_id);
    else
      $sites_phones = [];
  }
  else
    $sites_phones = $connect->getAll("SELECT * FROM `app_models_site_phone` ORDER BY `sort` ASC");

  ob_start();
  ?>
    <div class="panel panel-default sites-menu-items-panel">
        <div class="panel-heading"><i class="fa fa-list"></i> Телефоны<?php if($site) { ?> сайта «<?=$site['name'];?>»<?php } ?> <button class="btn btn-default btn-sm" onclick="show_sites_list();">К списку сайтов</button></div>
        <div class="panel-body table-body">
            <table class="table table-hover table-condensed">
                <thead>
                <tr>
                    <th>
                        ID
                    </th>
                    <th>
                        Заголовок
                    </th>
                    <th>
                        Номер
                    </th>
                    <th>
                        Блок
                    </th>
                    <th>
                        Статус
                    </th>
                    <th>
                        Действия
                    </th>
                </tr>
                </thead>
                <tbody>
                <?php
                foreach ($sites_phones as $sites_phone) {
                  ?>
                    <tr>
                        <td><?=$sites_phone['id'];?></td>
                        <td><?=$sites_phone['title'];?></td>
                        <td><?=$sites_phone['number'];?></td>
                        <td><?=$blocksArray[$sites_phone['block']];?></td>
                        <td><?=$sites_phone['status'] == 1?"Активен":"Не активен";?></td>
                        <td>
                          <?php if($id_rights > 4) { ?>
                              <button class="btn btn-default btn-sm" onclick="remove_sites_phone(<?=$sites_phone['id'];?>);"><i class="fa fa-trash-o"></i></button>
                              <button class="btn btn-default btn-sm" onclick="sites_phone(<?=$sites_phone['id'];?>);"><i class="fa fa-pencil"></i></button>
                          <?php } ?>
                        </td>
                    </tr>
                  <?php
                }
                ?>
                </tbody>
            </table>
        </div>
        <div class="panel-footer text-right">
          <?php if($id_rights > 4) { ?>
              <button type="button" class="btn btn-primary btn-sm" onclick="sites_phone(null,<?=$site_id;?>);"><i class="fa fa-plus-circle"></i> Добавить телефон</button>
          <?php } ?>
        </div>
    </div>
  <?php
  return ob_get_clean();
}


function show_sites_meta_templates_list($connect) {
    global $id_rights;

    $contentTypesRows = $connect->getAll("SELECT * FROM `app_models_site_contenttype` WHERE `status` = 1 AND `system` != 1");

    $typesArray = [];


    $subTypesArray = [
        'all' => 'Любой',
        'resort' => 'Объект',
        'reviews' => 'Отзывы'
    ];

    $keys = [
        'title' => 'Заголовок (Title)',
        'description' => 'Мета-описание (description)',
        'keywords' => 'Ключевые слова (keywords)',
        'h1' => 'Заголовок H1',
        'h2' => 'Заголовок H2'
    ];


    foreach ($contentTypesRows as $contentTypesRow) {
        $typesArray[$contentTypesRow['machine_name']] = $contentTypesRow['name'];
    }

    $site_id = isset($_POST['site_id'])?(int)$_POST['site_id']:0;
    $site = NULL;
    if($site_id) {
        $site = $connect->getRow("SELECT `id`, `name`, `domain` FROM `sites` WHERE `id`=?i",$site_id);
        if($site)
            $meta_templates = $connect->getAll("SELECT * FROM `app_models_site_page_meta_templates` WHERE `site_id`=?i AND `status` <> 2 ORDER BY `created` ASC", $site_id);
        else
            $meta_templates = [];
    }
    else
        $meta_templates = $connect->getAll("SELECT * FROM `app_models_site_page_meta_templates` WHERE `status` <> 2 ORDER BY `created` ASC");

    ob_start();
    ?>
    <div class="panel panel-default sites-meta-templates-panel">
        <div class="panel-heading"><i class="fa fa-list"></i> Шаблоны мета-тегов<?php if($site) { ?> сайта «<?=$site['name'];?>»<?php } ?> <button class="btn btn-default btn-sm" onclick="show_sites_list();">К списку сайтов</button></div>
        <div class="panel-body table-body">
            <table class="table table-hover table-condensed">
                <thead>
                <tr>
                    <th>
                        ID
                    </th>
                    <th>
                        Название
                    </th>
                    <th>
                        Ключ
                    </th>
                    <th>
                        Тип страницы
                    </th>
                    <th>
                        Подтип страницы
                    </th>
                    <th>
                        Текст
                    </th>
                    <th>
                        Статус
                    </th>
                    <th>
                        Действия
                    </th>
                </tr>
                </thead>
                <tbody>
                <?php
                foreach ($meta_templates as $meta_template) {
                    ?>
                    <tr>
                        <td><?=$meta_template['id'];?></td>
                        <td><?=$meta_template['name'];?></td>
                        <td><?=$keys[$meta_template['key']];?></td>
                        <td><?=$typesArray[$meta_template['type']];?></td>
                        <td><?=$subTypesArray[$meta_template['subtype']];?></td>
                        <td><?=$meta_template['value'];?></td>
                        <td><?=$meta_template['status'] == 1?"Активен":"Не активен";?></td>
                        <td>
                            <?php if($id_rights > 4) { ?>
                                <button class="btn btn-default btn-sm" onclick="remove_sites_meta_template(<?=$meta_template['id'];?>);"><i class="fa fa-trash-o"></i></button>
                                <button class="btn btn-default btn-sm" onclick="sites_meta_template(<?=$meta_template['id'];?>);"><i class="fa fa-pencil"></i></button>
                            <?php } ?>
                        </td>
                    </tr>
                    <?php
                }
                ?>
                </tbody>
            </table>
        </div>
        <div class="panel-footer text-right">
            <?php if($id_rights > 4) { ?>
                <button type="button" class="btn btn-primary btn-sm" onclick="sites_meta_template(null,<?=$site_id;?>);"><i class="fa fa-plus-circle"></i> Добавить шаблон</button>
            <?php } ?>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

function save_site($connect) {
    $respAr = [
      'success' => 0,
      'title' => '',
      'msg' => ''
    ];

    $id = isset($_POST['id'])?(int)$_POST['id']:0;
    $site = NULL;
    if($id)
        $site = $connect->getRow("SELECT `id` FROM `sites` WHERE `id` =?i",$id);

    $siteName = isset($_POST['name'])?trim($_POST['name']):"";

    $branding_name = isset($_POST['branding_name'])?trim($_POST['branding_name']):"";
    $branding_slogan = isset($_POST['branding_slogan'])?trim($_POST['branding_slogan']):"";

    $siteDomain = isset($_POST['domain'])?mb_strtolower(trim($_POST['domain'])):"";
    $main_bg_color = isset($_POST['main_bg_color'])?mb_strtolower(trim($_POST['main_bg_color'])):"#ffffff";
    $main_bg_color2 = isset($_POST['main_bg_color2'])?mb_strtolower(trim($_POST['main_bg_color2'])):"#356d33";
    $main_bg_color3 = isset($_POST['main_bg_color3'])?mb_strtolower(trim($_POST['main_bg_color3'])):"#ba2328";

    $main_font_color = isset($_POST['main_font_color'])?mb_strtolower(trim($_POST['main_font_color'])):"#356d33";
    $main_font_color2 = isset($_POST['main_font_color2'])?mb_strtolower(trim($_POST['main_font_color2'])):"#ffffff";
    $main_link_color = isset($_POST['main_link_color'])?mb_strtolower(trim($_POST['main_link_color'])):"#356d33";
    $interface_style = isset($_POST['interface_style'])?(int)$_POST['interface_style']:1;


    $head_code = isset($_POST['head_code'])?trim($_POST['head_code']):"";
    $theme = isset($_POST['theme'])?trim($_POST['theme']):"";
    $pre_body_code = isset($_POST['pre_body_code'])?trim($_POST['pre_body_code']):"";
    $post_body_code = isset($_POST['post_body_code'])?trim($_POST['post_body_code']):"";
    $robots = isset($_POST['robots'])?trim($_POST['robots']):"";

    $type = isset($_POST['type'])?trim($_POST['type']):"objects";
    $types = [
      'no_objects',
      'objects',
      'global'
    ];

    $direction_id = isset($_POST['direction_id'])?(int)$_POST['direction_id']:0;
    $region_id = isset($_POST['region_id'])?(int)$_POST['region_id']:0;

    if($type !== 'global') {
      $direction_id = 0;
      $region_id = 0;
    }

    if($direction_id === 0)
        $region_id = 0;
    elseif ($region_id && !$connect->getOne("SELECT `id` FROM `region` WHERE `id` =?i AND `id_direction` = ?i",$region_id,$direction_id))
      $region_id = 0;


    $themes = [
        'default',
        'sanrussia',
        'simplesite'
    ];

    if($siteName && $branding_name && $siteDomain && (!$id || $site) && in_array($interface_style,[1,2]) && in_array($type,$types) && in_array($theme,$themes)) {
        $datetime = gmdate("U");
        if($id)
            $oldsite = $connect->getRow("SELECT `id`,`name`,`domain` FROM `sites` WHERE (`name`=?s OR `domain`=?s) AND `id` <> ?i LIMIT 1",$siteName,$siteDomain,$id);
        else
            $oldsite = $connect->getRow("SELECT `id`, `name`,`domain` FROM `sites` WHERE `name`=?s OR `domain`=?s LIMIT 1",$siteName,$siteDomain);

        if(!$oldsite) {
            $respAr['success'] = 1;
            if($id) {

                $entity = [
                    'id' => $id,
                    'type' => 'site'
                ];


                $boundsArrayFavicon = files_to_bounds($connect,$entity,'favicon',isset($_POST['favicon'])?$_POST['favicon']:[]);
                remove_bounds($connect,$entity,'favicon');
                set_bounds($connect,$boundsArrayFavicon,'favicon');

                $boundsArrayResortsIds = [];
                if($type === 'objects')
                    $boundsArrayResortsIds = ids_to_bounds($connect,$entity,'resorts_ids',isset($_POST['resorts_ids'])?ids_string_to_ids($_POST['resorts_ids']):[]);

                remove_bounds($connect,$entity,'resorts_ids');
                set_bounds($connect,$boundsArrayResortsIds,'resorts_ids');

                $connect->query("UPDATE `sites` SET `name` = ?s, `branding_name` = ?s, `branding_slogan` = ?s, `domain` = ?s, `main_bg_color` = ?s, `main_bg_color2` = ?s, `main_bg_color3` = ?s, `main_font_color` = ?s, `main_font_color2` = ?s, `main_link_color` = ?s, `head_code` =?s, `pre_body_code` =?s, `post_body_code` =?s, `robots` = ?s, `interface_style` = ?i, `type` = ?s, `direction_id` = ?i, `region_id` = ?i, `theme` = ?s WHERE `id`=?i",$siteName,$branding_name,$branding_slogan,$siteDomain,$main_bg_color,$main_bg_color2, $main_bg_color3,$main_font_color,$main_font_color2,$main_link_color,$head_code, $pre_body_code, $post_body_code,$robots,$interface_style, $type, $direction_id, $region_id, $theme, $id);
            }
            else {
                $connect->query("INSERT INTO `sites` (`status`,`created`,`changed`,`name`, `branding_name`, `branding_slogan`, `domain`,`main_bg_color`,`main_bg_color2`,`main_bg_color3`,`main_font_color`,`main_font_color2`,`main_link_color`,`head_code`, `pre_body_code`, `post_body_code`, `robots`, `interface_style`, `type`, `direction_id`, `region_id`, `theme`) VALUES (1, ?i, ?i, ?s, ?s, ?s, ?s, ?s, ?s, ?s, ?s, ?s, ?s, ?s, ?s, ?s, ?s, ?i, ?s, ?i, ?i, ?s)", $datetime, $datetime, $siteName, $branding_name, $branding_slogan, $siteDomain,$main_bg_color,$main_bg_color2, $main_bg_color3,$main_font_color,$main_font_color2,$main_link_color,$head_code, $pre_body_code, $post_body_code, $robots, $interface_style, $type, $direction_id, $region_id, $theme);
                $boundsArrayResortsIds = [];

                $entity = [
                    'id' => $connect->insertId(),
                    'type' => 'site'
                ];

                $boundsArrayFavicon = files_to_bounds($connect,$entity,'favicon',isset($_POST['favicon'])?$_POST['favicon']:[]);
                set_bounds($connect,$boundsArrayFavicon,'favicon');
                set_bounds($connect,$boundsArrayResortsIds,'resorts_ids');
            }
        }
        else {
            if($oldsite['name'] === $siteName) {
              $respAr['msg'] = 'Сайт с таким названием уже есть';
              $respAr['msg_field'] = 'name';
            }
            elseif ($oldsite['domain'] === $siteDomain) {
              $respAr['msg'] = 'Сайт с таким доменом уже есть';
              $respAr['msg_field'] = 'domain';
            }
        }
    }

    return json_encode($respAr);
}

function save_site_icons($connect) {
  $respAr = [
    'success' => 0,
    'title' => '',
    'msg' => ''
  ];

  $id = isset($_POST['id'])?(int)$_POST['id']:0;
  $site = NULL;
  if($id)
    $site = $connect->getRow("SELECT `id` FROM `sites` WHERE `id` =?i",$id);


  if($site) {
        $respAr['success'] = 1;

        $entity = [
          'id' => $id,
          'type' => 'site'
        ];

        $boundsArrayFavicon = files_to_bounds($connect,$entity,'favicon',isset($_POST['favicon'])?$_POST['favicon']:[]);
        remove_bounds($connect,$entity,'favicon');
        set_bounds($connect,$boundsArrayFavicon,'favicon');

        $boundsArrayLogo = files_to_bounds($connect,$entity,'logo',isset($_POST['logo'])?$_POST['logo']:[]);
        remove_bounds($connect,$entity,'logo');
        set_bounds($connect,$boundsArrayLogo,'logo');

        $boundsArrayIcon_16x16 = files_to_bounds($connect,$entity,'icon_16x16',isset($_POST['icon_16x16'])?$_POST['icon_16x16']:[]);
        remove_bounds($connect,$entity,'icon_16x16');
        set_bounds($connect,$boundsArrayIcon_16x16,'icon_16x16');

        $boundsArrayIcon_32x32 = files_to_bounds($connect,$entity,'icon_32x32',isset($_POST['icon_32x32'])?$_POST['icon_32x32']:[]);
        remove_bounds($connect,$entity,'icon_32x32');
        set_bounds($connect,$boundsArrayIcon_32x32,'icon_32x32');

        $boundsArrayIcon_apple_non_retina_57x57 = files_to_bounds($connect,$entity,'icon_apple_57x57',isset($_POST['icon_apple_57x57'])?$_POST['icon_apple_57x57']:[]);
        remove_bounds($connect,$entity,'icon_apple_57x57');
        set_bounds($connect,$boundsArrayIcon_apple_non_retina_57x57,'icon_apple_57x57');

        $boundsArrayIcon_apple_60x60 = files_to_bounds($connect,$entity,'icon_apple_60x60',isset($_POST['icon_apple_60x60'])?$_POST['icon_apple_60x60']:[]);
        remove_bounds($connect,$entity,'icon_apple_60x60');
        set_bounds($connect,$boundsArrayIcon_apple_60x60,'icon_apple_60x60');

        $boundsArrayIcon_apple_72x72 = files_to_bounds($connect,$entity,'icon_apple_72x72',isset($_POST['icon_apple_72x72'])?$_POST['icon_apple_72x72']:[]);
        remove_bounds($connect,$entity,'icon_apple_72x72');
        set_bounds($connect,$boundsArrayIcon_apple_72x72,'icon_apple_72x72');

        $boundsArrayIcon_apple_76x76 = files_to_bounds($connect,$entity,'icon_apple_76x76',isset($_POST['icon_apple_76x76'])?$_POST['icon_apple_76x76']:[]);
        remove_bounds($connect,$entity,'icon_apple_76x76');
        set_bounds($connect,$boundsArrayIcon_apple_76x76,'icon_apple_76x76');

        $boundsArrayIcon_96x96 = files_to_bounds($connect,$entity,'icon_96x96',isset($_POST['icon_96x96'])?$_POST['icon_96x96']:[]);
        remove_bounds($connect,$entity,'icon_96x96');
        set_bounds($connect,$boundsArrayIcon_96x96,'icon_96x96');

        $boundsArrayIcon_apple_114x114 = files_to_bounds($connect,$entity,'icon_apple_114x114',isset($_POST['icon_apple_114x114'])?$_POST['icon_apple_114x114']:[]);
        remove_bounds($connect,$entity,'icon_apple_114x114');
        set_bounds($connect,$boundsArrayIcon_apple_114x114,'icon_apple_114x114');

        $boundsArrayIcon_apple_120x120 = files_to_bounds($connect,$entity,'icon_apple_120x120',isset($_POST['icon_apple_120x120'])?$_POST['icon_apple_120x120']:[]);
        remove_bounds($connect,$entity,'icon_apple_120x120');
        set_bounds($connect,$boundsArrayIcon_apple_120x120,'icon_apple_120x120');

        $boundsArrayIcon_apple_144x144 = files_to_bounds($connect,$entity,'icon_apple_144x144',isset($_POST['icon_apple_144x144'])?$_POST['icon_apple_144x144']:[]);
        remove_bounds($connect,$entity,'icon_apple_144x144');
        set_bounds($connect,$boundsArrayIcon_apple_144x144,'icon_apple_144x144');

        $boundsArrayIcon_apple_152x152 = files_to_bounds($connect,$entity,'icon_apple_152x152',isset($_POST['icon_apple_152x152'])?$_POST['icon_apple_152x152']:[]);
        remove_bounds($connect,$entity,'icon_apple_152x152');
        set_bounds($connect,$boundsArrayIcon_apple_152x152,'icon_apple_152x152');

        $boundsArrayIcon_apple_180x180 = files_to_bounds($connect,$entity,'icon_apple_180x180',isset($_POST['icon_apple_180x180'])?$_POST['icon_apple_180x180']:[]);
        remove_bounds($connect,$entity,'icon_apple_180x180');
        set_bounds($connect,$boundsArrayIcon_apple_180x180,'icon_apple_180x180');

        $boundsArrayIcon_192x192 = files_to_bounds($connect,$entity,'icon_192x192',isset($_POST['icon_192x192'])?$_POST['icon_192x192']:[]);
        remove_bounds($connect,$entity,'icon_192x192');
        set_bounds($connect,$boundsArrayIcon_192x192,'icon_192x192');
  }

  return json_encode($respAr);
}

function save_site_tech($connect) {
    $respAr = [
        'success' => 0,
        'title' => '',
        'msg' => ''
    ];

    $id = isset($_POST['id'])?(int)$_POST['id']:0;
    $glue_css = isset($_POST['glue_css'])?(int)$_POST['glue_css']:0;
    $compress_css = isset($_POST['compress_css'])?(int)$_POST['compress_css']:0;
    $glue_js = isset($_POST['glue_js'])?(int)$_POST['glue_js']:0;
    $compress_js = isset($_POST['compress_js'])?(int)$_POST['compress_js']:0;

    if(!in_array($glue_css,[0,1]))
        $glue_css = 0;

    if(!in_array($compress_css,[0,1]))
        $compress_css = 0;

    if(!in_array($glue_js,[0,1]))
        $glue_js = 0;

    if(!in_array($compress_js,[0,1]))
        $compress_js = 0;

    $site = NULL;
    if($id)
        $site = $connect->getRow("SELECT `id` FROM `sites` WHERE `id` =?i",$id);


    if($site) {
        $respAr['success'] = 1;
        $connect->query("UPDATE `sites` SET `glue_css` = ?i, `compress_css` = ?i, `glue_js` = ?i, `compress_js` = ?i WHERE `id` = ?i", $glue_css, $compress_css, $glue_js, $compress_js, $id);
    }

    return json_encode($respAr);
}


function save_sites_address($connect) {
  $respAr = [
    'success' => 0,
    'title' => '',
    'msg' => ''
  ];

  $id = isset($_POST['id'])?(int)$_POST['id']:0;
  $site_id = isset($_POST['site_id'])?(int)$_POST['site_id']:0;
  $title = isset($_POST['title'])?trim($_POST['title']):"";
  $description = isset($_POST['description'])?trim($_POST['description']):"";
  $status = isset($_POST['status'])?(int)$_POST['status']:0;
  $sort = isset($_POST['sort'])?(int)$_POST['sort']:0;
  if($id)
    $address = $connect->getRow("SELECT `id` FROM `app_models_site_address` WHERE `id` =?i",$id);
  else
    $address = NULL;

  if($site_id)
      $site = $connect->getRow("SELECT `id` FROM `sites` WHERE `id`=?i",$site_id);
  else
      $site = NULL;


  if((!$id || $address) && $site && $title && in_array($status,[0,1])) {
    if($address)
        $oldAddr = $connect->getRow("SELECT `id` FROM `app_models_site_address` WHERE `title`=?s AND `id` <> ?i AND `site_id` = ?i",$title,$address['id'],$site['id']);
    else
        $oldAddr = $connect->getRow("SELECT `id` FROM `app_models_site_address` WHERE `title`=?s AND `site_id` = ?i",$title,$site['id']);

    if($oldAddr) {
      $respAr['msg'] = 'Адрес с таким заголовком уже есть';
      $respAr['msg_field'] = 'title';
    }
    else {
        $timestamp = gmdate("U");
        if($address)
            $connect->query("UPDATE `app_models_site_address` SET `changed`=?i, `title`=?s, `description`=?s, `status` =?i, `sort` =?i WHERE `id` =?i",$timestamp, $title,$description,$status,$sort,$address['id']);
        else
            $connect->query("INSERT INTO `app_models_site_address` (`created`,`changed`,`status`,`uid`,`sort`,`title`,`site_id`,`description`) VALUES (?i, ?i, ?i, ?i, ?i, ?s, ?i, ?s)",$timestamp, $timestamp, $status, 1, $sort, $title, $site['id'],$description);

        $respAr['success'] = 1;
    }
  }

  return json_encode($respAr);
}

function save_sites_menu_item($connect) {
  $menuArray = [1,2,3];
  $respAr = [
    'success' => 0,
    'title' => '',
    'msg' => ''
  ];

  $id = isset($_POST['id'])?(int)$_POST['id']:0;
  $site_id = isset($_POST['site_id'])?(int)$_POST['site_id']:0;
  $name = isset($_POST['name'])?trim($_POST['name']):"";
  $href = isset($_POST['href'])?trim($_POST['href']):"";
  $status = isset($_POST['status'])?(int)$_POST['status']:0;
  $main = isset($_POST['main'])?(int)$_POST['main']:0;
  $sort = isset($_POST['sort'])?(int)$_POST['sort']:0;
  $menu_id = isset($_POST['menu_id'])?(int)$_POST['menu_id']:0;
  $parent_id = isset($_POST['parent_id'])?(int)$_POST['parent_id']:0;
  if($id)
    $menu_item = $connect->getRow("SELECT `id` FROM `app_models_site_menu_item` WHERE `id` =?i",$id);
  else
    $menu_item = NULL;

  if($site_id)
    $site = $connect->getRow("SELECT `id` FROM `sites` WHERE `id`=?i",$site_id);
  else
    $site = NULL;

  if($parent_id)
      $parent = $connect->getRow("SELECT `id` FROM `app_models_site_menu_item` WHERE `id` =?i AND `parent_id` = 0 AND `site_id` = ?i AND `menu_id` = ?i",$parent_id,$site_id, $menu_id);
  else
      $parent = NULL;



  if((!$id || $menu_item) && (!$parent_id || $parent) && $site && $name && in_array($status,[0,1]) && in_array($main,[0,1]) && in_array($menu_id,$menuArray) && $href) {
    if($main) {
      if ($menu_item) {
        $oldMenuItem = $connect->getRow("SELECT `id` FROM `app_models_site_menu_item` WHERE `main`= '1' AND `id` <> ?i AND `site_id` = ?i AND `menu_id` = ?i", $menu_item['id'], $site['id'], $menu_id);
      }
      else {
        $oldMenuItem = $connect->getRow("SELECT `id` FROM `app_models_site_menu_item` WHERE `main`= '1' AND `site_id` = ?i AND `menu_id` = ?i", $site['id'],$menu_id);
      }
    }
    else
      $oldMenuItem = NULL;

    if($oldMenuItem) {
      $respAr['msg'] = 'В этом меню уже есть выделенный элемент';
      $respAr['msg_field'] = 'main';
    }
    else {
      $timestamp = gmdate("U");
      if($menu_item)
        $connect->query("UPDATE `app_models_site_menu_item` SET `changed`=?i, `name`=?s, `href`=?s, `main` = ?i, `menu_id` =?i, `status` =?i, `sort` =?i, `parent_id` = ?i WHERE `id` =?i",$timestamp, $name, $href, $main, $menu_id, $status,$sort,$parent_id,$menu_item['id']);
      else
        $connect->query("INSERT INTO `app_models_site_menu_item` (`created`, `changed`, `status`, `uid`, `sort`, `name`, `href`, `main`, `menu_id`, `site_id`, `parent_id`) VALUES (?i, ?i, ?i, ?i, ?i, ?s, ?s, ?i, ?i, ?i, ?i)",$timestamp, $timestamp, $status, 1, $sort, $name, $href, $main, $menu_id, $site['id'], $parent_id);

      $respAr['success'] = 1;
      $respAr['site_id'] = (int)$site_id;
    }
  }

  return json_encode($respAr);
}

function save_sites_phone($connect) {
  $blocksArray = ['header', 'footer'];
  $maxMain = [
    'header' => 1,
    'footer' => NULL
  ];
  $respAr = [
    'success' => 0,
    'title' => '',
    'msg' => ''
  ];

  $id = isset($_POST['id'])?(int)$_POST['id']:0;
  $site_id = isset($_POST['site_id'])?(int)$_POST['site_id']:0;
  $title = isset($_POST['title'])?trim($_POST['title']):"";
  $number = isset($_POST['number'])?trim($_POST['number']):"";
  $status = isset($_POST['status'])?(int)$_POST['status']:0;
  $main = isset($_POST['main'])?(int)$_POST['main']:0;
  $sort = isset($_POST['sort'])?(int)$_POST['sort']:0;
  $block = isset($_POST['block'])?trim($_POST['block']):"header";
  if($id)
    $phone = $connect->getRow("SELECT `id` FROM `app_models_site_phone` WHERE `id` =?i",$id);
  else
    $phone = NULL;

  if($site_id)
    $site = $connect->getRow("SELECT `id` FROM `sites` WHERE `id`=?i",$site_id);
  else
    $site = NULL;


  if((!$id || $phone) && $site && in_array($status,[0,1]) && in_array($main,[0,1]) && in_array($block,$blocksArray) && $number) {
    if($main && !is_null($maxMain[$block])) {
      if ($phone) {
        $oldPhonesCount = $connect->getOne("SELECT COUNT(*) FROM `app_models_site_phone` WHERE `main`= '1' AND `id` <> ?i AND `site_id` = ?i AND `block` = ?s", $phone['id'], $site['id'], $block);
      }
      else {
        $oldPhonesCount = $connect->getOne("SELECT COUNT(*) FROM `app_models_site_phone` WHERE `main`= '1' AND `site_id` = ?i AND `block` = ?s", $site['id'], $block);
      }
    }
    else
      $oldPhonesCount = 0;

    if($block === 'header') {
      if ($phone) {
        $oldPhonesCountH = $connect->getOne("SELECT COUNT(*) FROM `app_models_site_phone` WHERE `id` <> ?i AND `site_id` = ?i AND `block` = ?s", $phone['id'], $site['id'], $block);
      }
      else {
        $oldPhonesCountH = $connect->getOne("SELECT COUNT(*) FROM `app_models_site_phone` WHERE `site_id` = ?i AND `block` = ?s", $site['id'], $block);
      }
    }
    else
      $oldPhonesCountH = 0;

    if(!is_null($maxMain[$block]) && $oldPhonesCount > $maxMain[$block]-1) {
      $respAr['msg'] = 'Максимальное количество выделенных телефонов для этого блока: '.$maxMain[$block];
      $respAr['msg_field'] = 'main';
    }
    elseif ($oldPhonesCountH > 1) {
      $respAr['msg'] = 'В шапке сайта может быть не более 2 телефонов';
      $respAr['msg_field'] = 'block';
    }
    else {
      $timestamp = gmdate("U");
      if($phone)
        $connect->query("UPDATE `app_models_site_phone` SET `changed`=?i, `title`=?s, `number`=?s, `main` = ?i, `block` =?s, `status` =?i, `sort` =?i WHERE `id` =?i",$timestamp, $title, $number, $main, $block, $status,$sort,$phone['id']);
      else
        $connect->query("INSERT INTO `app_models_site_phone` (`created`, `changed`, `status`, `uid`, `sort`, `title`, `number`, `main`, `block`, `site_id`) VALUES (?i, ?i, ?i, ?i, ?i, ?s, ?s, ?i, ?s, ?i)",$timestamp, $timestamp, $status, 1, $sort, $title, $number, $main, $block, $site['id']);

      $respAr['success'] = 1;
    }
  }

  return json_encode($respAr);
}

function save_sites_meta_template($connect) {
    $contentTypesRows = $connect->getAll("SELECT * FROM `app_models_site_contenttype` WHERE `status` = 1 AND `system` != 1");

    $typesArray = [];


    $subTypesArray = [
        'all' => 'Любой',
        'resort' => 'Объект',
        'reviews' => 'Отзывы'
    ];

    $keys = [
        'title' => 'Заголовок (Title)',
        'description' => 'Мета-описание (description)',
        'keywords' => 'Ключевые слова (keywords)',
        'h1' => 'Заголовок H1',
        'h2' => 'Заголовок H2'
    ];

    foreach ($contentTypesRows as $contentTypesRow) {
        $typesArray[$contentTypesRow['machine_name']] = $contentTypesRow['name'];
    }


    $respAr = [
        'success' => 0,
        'title' => '',
        'msg' => ''
    ];

    $id = isset($_POST['id'])?(int)$_POST['id']:0;
    $site_id = isset($_POST['site_id'])?(int)$_POST['site_id']:0;
    $name = isset($_POST['name'])?trim($_POST['name']):"";
    $key = isset($_POST['key'])?$_POST['key']:"";
    $type = isset($_POST['type'])?$_POST['type']:"";
    $subtype = isset($_POST['subtype'])?$_POST['subtype']:"";
    $value = isset($_POST['value'])?$_POST['value']:"";
    $status = isset($_POST['status'])?(int)$_POST['status']:0;

    if($id)
        $meta_template = $connect->getRow("SELECT `id` FROM `app_models_site_page_meta_templates` WHERE `id` =?i",$id);
    else
        $meta_template = NULL;

    if($site_id)
        $site = $connect->getRow("SELECT `id` FROM `sites` WHERE `id`=?i",$site_id);
    else
        $site = NULL;


    if((!$id || $meta_template) && $site && in_array($status,[0,1]) && array_key_exists($key, $keys) && array_key_exists($type,$typesArray) && array_key_exists($subtype, $subTypesArray) && mb_strlen($value) > 0) {

        if ($meta_template) {
            $oldMetaTemplate = $connect->getOne("SELECT COUNT(*) FROM `app_models_site_page_meta_templates` WHERE `status`= '1' AND `id` <> ?i AND `site_id` = ?i AND `type` = ?s AND `subtype` = ?s AND `key` = ?s", $meta_template['id'], $site['id'], $type, $subtype, $key);
        }
        else {
            $oldMetaTemplate = $connect->getOne("SELECT COUNT(*) FROM `app_models_site_page_meta_templates` WHERE `status`= '1' AND `site_id` = ?i AND `type` = ?s AND `subtype` =?s AND `key` = ?s", $site['id'], $type, $subtype, $key);
        }


        if ($oldMetaTemplate > 0 && $status) {
            $respAr['msg'] = 'На сайте уже есть активный шаблон для данного ключа, типа и подтипа материалов';
            $respAr['msg_field'] = 'subtype';
        }
        else {
            $timestamp = gmdate("U");
            if($meta_template)
                $connect->query("UPDATE `app_models_site_page_meta_templates` SET `changed`=?i, `name`=?s, `key`=?s, `type` = ?s, `subtype` =?s, `value` = ?s, `status` =?i, `synchronized` = 0 WHERE `id` =?i",$timestamp, $name, $key, $type, $subtype, $value, $status, $meta_template['id']);
            else
                $connect->query("INSERT INTO `app_models_site_page_meta_templates` (`created`, `changed`, `status`, `uid`, `name`, `key`, `type`, `subtype`, `value`, `site_id`) VALUES (?i, ?i, ?i, ?i, ?s, ?s, ?s, ?s, ?s, ?i)",$timestamp, $timestamp, $status, 1, $name, $key, $type, $subtype, $value, $site['id']);

            $respAr['success'] = 1;
        }
    }

    return json_encode($respAr);
}

function remove_sites_address($connect) {
    $id = isset($_POST['id'])?(int)$_POST['id']:0;
    $address = $connect->getRow("SELECT `id`, `site_id` FROM `app_models_site_address` WHERE `id` =?i",$id);
    ob_start();
    ?>
    <div class="modal fade remove-sites-address">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><i class="fa fa-times"></i></button>
                    <h4 class="modal-title">Удалить адрес</h4>
                </div>
                <div class="modal-body form-horizontal site-name">
                    <?php if($address) { ?>
                    <input type="hidden" name="id" value="<?=$id;?>">
                    <input type="hidden" name="site_id" value="<?=$address['site_id'];?>">
                   Вы уверены, что хотите удалить адрес?
                    <?php } else { ?>
                      Некорректный ID
                    <?php } ?>
                </div>
                <div class="modal-loader"></div>
                <div class="modal-footer">
                  <?php if($address) { ?>
                    <button class="btn btn-success btn-sm btn-remove-sites-address-success" onclick="remove_sites_address_success(<?=$id;?>)" id="btn-remove-sites-address-success"><i class="fa fa-check-circle"></i> Удалить</button>
                    <button class="btn btn-danger btn-sm" data-dismiss="modal" aria-label="Close">Нет</button>
                  <?php } else { ?>
                      <button class="btn btn-danger btn-sm" data-dismiss="modal" aria-label="Close">Закрыть</button>
                  <?php } ?>
                </div>
            </div>
        </div>
    </div>
  <?php
  return ob_get_clean();
}


function remove_sites_menu_item($connect) {
  $id = isset($_POST['id'])?(int)$_POST['id']:0;
  $menu_item = $connect->getRow("SELECT `id`, `site_id` FROM `app_models_site_menu_item` WHERE `id` =?i",$id);
  ob_start();
  ?>
    <div class="modal fade remove-sites-menu-item">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><i class="fa fa-times"></i></button>
                    <h4 class="modal-title">Удалить элемент меню</h4>
                </div>
                <div class="modal-body form-horizontal site-name">
                  <?php if($menu_item) { ?>
                      <input type="hidden" name="id" value="<?=$id;?>">
                      <input type="hidden" name="site_id" value="<?=$menu_item['site_id'];?>">
                      Вы уверены, что хотите удалить элемент меню?
                  <?php } else { ?>
                      Некорректный ID
                  <?php } ?>
                </div>
                <div class="modal-loader"></div>
                <div class="modal-footer">
                  <?php if($menu_item) { ?>
                      <button class="btn btn-success btn-sm btn-remove-sites-menu-item-success" onclick="remove_sites_menu_item_success(<?=$id;?>)" id="btn-remove-sites-menu-item-success"><i class="fa fa-check-circle"></i> Удалить</button>
                      <button class="btn btn-danger btn-sm" data-dismiss="modal" aria-label="Close">Нет</button>
                  <?php } else { ?>
                      <button class="btn btn-danger btn-sm" data-dismiss="modal" aria-label="Close">Закрыть</button>
                  <?php } ?>
                </div>
            </div>
        </div>
    </div>
  <?php
  return ob_get_clean();
}

function remove_sites_phone($connect) {
  $id = isset($_POST['id'])?(int)$_POST['id']:0;
  $phone = $connect->getRow("SELECT `id`, `site_id` FROM `app_models_site_phone` WHERE `id` =?i",$id);
  ob_start();
  ?>
    <div class="modal fade remove-sites-phone">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><i class="fa fa-times"></i></button>
                    <h4 class="modal-title">Удалить телефон</h4>
                </div>
                <div class="modal-body form-horizontal site-name">
                  <?php if($phone) { ?>
                      <input type="hidden" name="id" value="<?=$id;?>">
                      <input type="hidden" name="site_id" value="<?=$phone['site_id'];?>">
                      Вы уверены, что хотите удалить этот телефон?
                  <?php } else { ?>
                      Некорректный ID
                  <?php } ?>
                </div>
                <div class="modal-loader"></div>
                <div class="modal-footer">
                  <?php if($phone) { ?>
                      <button class="btn btn-success btn-sm btn-remove-sites-phone-success" onclick="remove_sites_phone_success(<?=$id;?>)" id="btn-remove-sites-phone-success"><i class="fa fa-check-circle"></i> Удалить</button>
                      <button class="btn btn-danger btn-sm" data-dismiss="modal" aria-label="Close">Нет</button>
                  <?php } else { ?>
                      <button class="btn btn-danger btn-sm" data-dismiss="modal" aria-label="Close">Закрыть</button>
                  <?php } ?>
                </div>
            </div>
        </div>
    </div>
  <?php
  return ob_get_clean();
}

function remove_sites_content($connect) {
  $id = isset($_POST['id'])?(int)$_POST['id']:0;
  $content = $connect->getRow("SELECT `id`, `site_id` FROM `sites_contents` WHERE `id` =?i AND `status` <> 2",$id);
  ob_start();
  ?>
    <div class="modal fade remove-sites-phone">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><i class="fa fa-times"></i></button>
                    <h4 class="modal-title">Удаление материала</h4>
                </div>
                <div class="modal-body form-horizontal site-name">
                  <?php if($content) { ?>
                      <input type="hidden" name="id" value="<?=$id;?>">
                      <input type="hidden" name="site_id" value="<?=$content['site_id'];?>">
                      Вы уверены, что хотите удалить этот материал?
                  <?php } else { ?>
                      Некорректный ID
                  <?php } ?>
                </div>
                <div class="modal-loader"></div>
                <div class="modal-footer">
                  <?php if($content) { ?>
                      <button class="btn btn-success btn-sm btn-remove-sites-content-success" onclick="remove_sites_content_success(<?=$id;?>)" id="btn-remove-sites-content-success"><i class="fa fa-check-circle"></i> Удалить</button>
                      <button class="btn btn-danger btn-sm" data-dismiss="modal" aria-label="Close">Нет</button>
                  <?php } else { ?>
                      <button class="btn btn-danger btn-sm" data-dismiss="modal" aria-label="Close">Закрыть</button>
                  <?php } ?>
                </div>
            </div>
        </div>
    </div>
  <?php
  return ob_get_clean();
}

function remove_sites_meta_template($connect) {
    $id = isset($_POST['id'])?(int)$_POST['id']:0;
    $meta_template = $connect->getRow("SELECT `id`, `site_id` FROM `app_models_site_page_meta_templates` WHERE `id` =?i AND `status` <> 2",$id);
    ob_start();
    ?>
    <div class="modal fade remove-sites-meta-template">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><i class="fa fa-times"></i></button>
                    <h4 class="modal-title">Удаление шаблона мета-тегов</h4>
                </div>
                <div class="modal-body form-horizontal site-name">
                    <?php if($meta_template) { ?>
                        <input type="hidden" name="id" value="<?=$id;?>">
                        <input type="hidden" name="site_id" value="<?=$meta_template['site_id'];?>">
                        Вы уверены, что хотите удалить этот шаблон?
                    <?php } else { ?>
                        Некорректный ID
                    <?php } ?>
                </div>
                <div class="modal-loader"></div>
                <div class="modal-footer">
                    <?php if($meta_template) { ?>
                        <button class="btn btn-success btn-sm btn-remove-sites-meta-template-success" onclick="remove_sites_meta_template_success(<?=$id;?>)" id="btn-remove-sites-meta-template-success"><i class="fa fa-check-circle"></i> Удалить</button>
                        <button class="btn btn-danger btn-sm" data-dismiss="modal" aria-label="Close">Нет</button>
                    <?php } else { ?>
                        <button class="btn btn-danger btn-sm" data-dismiss="modal" aria-label="Close">Закрыть</button>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>
    <?php
    return ob_get_clean();
}


function remove_sites_address_success($connect) {
  $respAr = [
    'msg' => '',
    'title' => '',
    'success' => 0
  ];
  $id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
  $address = $connect->getRow("SELECT `id` FROM `app_models_site_address` WHERE `id` =?i", $id);
  if($address) {
      $connect->query("DELETE FROM `app_models_site_address` WHERE `id` =?i",$id);
      $respAr['success'] = 1;
  }
  return json_encode($respAr);
}

function remove_sites_menu_item_success($connect) {
  $respAr = [
    'msg' => '',
    'title' => '',
    'success' => 0
  ];
  $id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
  $menu_item = $connect->getRow("SELECT `id` FROM `app_models_site_menu_item` WHERE `id` =?i", $id);
  if($menu_item) {
    $connect->query("DELETE FROM `app_models_site_menu_item` WHERE `id` =?i",$id);
    $connect->query("DELETE FROM `app_models_site_menu_item` WHERE `parent_id` =?i",$id);
    $respAr['success'] = 1;
  }
  return json_encode($respAr);
}

function remove_sites_phone_success($connect) {
  $respAr = [
    'msg' => '',
    'title' => '',
    'success' => 0
  ];
  $id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
  $phone = $connect->getRow("SELECT `id` FROM `app_models_site_phone` WHERE `id` =?i", $id);
  if($phone) {
    $connect->query("DELETE FROM `app_models_site_phone` WHERE `id` =?i",$id);
    $respAr['success'] = 1;
  }
  return json_encode($respAr);
}

function remove_sites_content_success($connect) {
  $respAr = [
    'msg' => '',
    'title' => '',
    'success' => 0
  ];
  $id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
  $content = $connect->getRow("SELECT `id` FROM `sites_contents` WHERE `id` =?i", $id);
  if($content) {
    $connect->query("UPDATE `sites_contents` SET `status` = 2, `synchronized` = 0 WHERE `id` =?i",$id);
    $respAr['success'] = 1;
  }
  return json_encode($respAr);
}

function remove_sites_meta_template_success($connect) {
    $respAr = [
        'msg' => '',
        'title' => '',
        'success' => 0
    ];
    $id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
    $meta_template = $connect->getRow("SELECT `id` FROM `app_models_site_page_meta_templates` WHERE `id` =?i AND `status` <> 2", $id);
    if($meta_template) {
        $connect->query("UPDATE `app_models_site_page_meta_templates` SET `status` = 2, `synchronized` = 0 WHERE `id` =?i",$id);
        $respAr['success'] = 1;
    }
    return json_encode($respAr);
}

function sites_address($connect)
{
    $address_id = isset($_POST['id'])?(int)$_POST['id']:0;
    $site_id = isset($_POST['site_id'])?(int)$_POST['site_id']:0;
    if($address_id)
        $address = $connect->getRow("SELECT * FROM `app_models_site_address` WHERE `id`=?i",$address_id);
    else
        $address = NULL;

    $maxSort = NULL;

    if($address)
        $maxSort = $connect->getOne("SELECT MAX(`sort`) FROM `app_models_site_address` WHERE `site_id` = ?i",$address['site_id']);
    else
        $maxSort = $connect->getOne("SELECT MAX(`sort`) FROM `app_models_site_address` WHERE `site_id` = ?i",$site_id);

  if(is_null($maxSort))
        $maxSort = -1;

    $maxSort++;
    ob_start();
    ?>
    <div class="modal fade sites-content-modal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><i class="fa fa-times"></i></button>
                    <h4 class="modal-title"><?php if($address) { ?>Редактировать адрес<?php } else { ?>Добавить адрес<?php } ?></h4>
                </div>
                <div class="modal-body form-horizontal">
                    <?php if($address || $site_id) { ?>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">Заголовок</label>
                            <div class="col-sm-10">
                                <input type="text" class="form-control" name="title" maxlength="255" value="<?=$address?htmlspecialchars($address['title']):"";?>">
                                <input type="hidden" value="<?=$site_id?$site_id:$address['site_id'];?>" name="site_id">
                                <input type="hidden" value="<?=$address?$address['id']:0;?>" name="id">
                                <div class="input-message-block" data-for="title"></div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">Содержимое</label>
                            <div class="col-sm-10">
                                <textarea class="form-control resizable-textarea" name="description"><?=$address?htmlspecialchars($address['description']):"";?></textarea>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">Вес</label>
                            <div class="col-sm-10">
                                <input type="number" class="form-control" name="sort" value="<?=$address?$address['sort']:$maxSort;?>">
                                <div class="input-message-block" data-for="sort"></div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">Активный</label>
                            <div class="col-sm-10">
                                <input type="checkbox" name="status" class="form-control"<?php if($address && $address['status'] == 1) {?> checked<?php } ?>>
                            </div>
                        </div>
                    <?php } ?>
                </div>
                <div class="modal-loader"></div>
                <div class="modal-footer">
                    <button class="btn btn-success btn-sm btn-save-sites-address" onclick="save_sites_address()" id="btn-save-sites-address"><i class="fa fa-check-circle"></i> Сохранить</button>
                </div>
            </div>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

function sites_menu_item($connect)
{
    $menuArray = [
      1 => 'Верхнее основное',
      2 => 'Верхнее второе',
      3 => 'Нижнее'
    ];
  $menu_item_id = isset($_POST['id'])?(int)$_POST['id']:0;
  $site_id = isset($_POST['site_id'])?(int)$_POST['site_id']:0;
  $parent_id = isset($_POST['parent_id'])?(int)$_POST['parent_id']:0;
  if($menu_item_id)
    $menu_item = $connect->getRow("SELECT * FROM `app_models_site_menu_item` WHERE `id`=?i",$menu_item_id);
  else
    $menu_item = NULL;

  if($menu_item) {
      $parent_id = $menu_item['parent_id'];
  }

  if($parent_id)
      $parent = $connect->getRow("SELECT * FROM `app_models_site_menu_item` WHERE `id`=?i AND `parent_id` = 0 AND `site_id` = ?i",$parent_id,$site_id);
  else
      $parent = NULL;


  $maxSort = NULL;

  if($menu_item)
      $maxSort = $connect->getOne("SELECT MAX(`sort`) FROM `app_models_site_menu_item` WHERE `site_id` = ?i AND `menu_id` = ?i AND `parent_id` = ?i", $menu_item['site_id'], $menu_item['menu_id'],$parent_id);
  else
      $maxSort = $connect->getOne("SELECT MAX(`sort`) FROM `app_models_site_menu_item` WHERE `site_id` = ?i AND `menu_id` = 1 AND `parent_id` = ?i", $site_id, $parent_id);

  if(is_null($maxSort))
    $maxSort = -1;

  $maxSort++;
  ob_start();
  ?>
    <div class="modal fade sites-content-modal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><i class="fa fa-times"></i></button>
                    <h4 class="modal-title"><?php if($menu_item) { ?>Редактировать элемент меню<?php } else { ?>Добавить элемент меню<?php } ?></h4>
                </div>
                <div class="modal-body form-horizontal">
                  <?php if(($menu_item || $site_id) && ($parent || !$parent_id)) { ?>
                      <div class="form-group">
                          <label class="col-sm-2 control-label">Название</label>
                          <div class="col-sm-10">
                              <input type="text" class="form-control" name="name" maxlength="255" value="<?=$menu_item?htmlspecialchars($menu_item['name']):"";?>">
                              <input type="hidden" value="<?=$site_id?$site_id:$menu_item['site_id'];?>" name="site_id">
                              <input type="hidden" value="<?=$menu_item?$menu_item['id']:0;?>" name="id">
                              <input type="hidden" value="<?=$parent?$parent['id']:0;?>" name="parent_id">
                              <div class="input-message-block" data-for="name"></div>
                          </div>
                      </div>
                      <div class="form-group">
                          <label class="col-sm-2 control-label">Ссылка</label>
                          <div class="col-sm-10">
                              <input type="text" class="form-control" name="href" maxlength="1024" value="<?=$menu_item?htmlspecialchars($menu_item['href']):"";?>">
                              <div class="input-message-block" data-for="href"></div>
                          </div>
                      </div>
                      <div class="form-group<?php if($parent) { ?> hidden<?php } ?>">
                          <label class="col-sm-2 control-label">Меню</label>
                          <div class="col-sm-10">
                              <select class="form-control" name="menu_id">
                                  <?php foreach ($menuArray as $i => $item) { ?>
                                    <option value="<?=$i;?>"<?php if(($menu_item && $menu_item['menu_id'] == $i) || ($parent && $parent['menu_id'] == $i)) { ?> selected<?php } ?>><?=$item;?></option>
                                  <?php } ?>
                              </select>
                          </div>
                      </div>
                      <div class="form-group">
                          <label class="col-sm-2 control-label">Вес</label>
                          <div class="col-sm-10">
                              <input type="number" class="form-control" name="sort" value="<?=$menu_item?$menu_item['sort']:$maxSort;?>">
                              <div class="input-message-block" data-for="sort"></div>
                          </div>
                      </div>
                      <div class="form-group">
                          <label class="col-sm-2 control-label">Выделить</label>
                          <div class="col-sm-10">
                              <input type="checkbox" name="main" class="form-control"<?php if($menu_item && $menu_item['main'] == 1) {?> checked<?php } ?>>
                              <div class="input-message-block" data-for="main"></div>
                          </div>
                      </div>
                      <div class="form-group">
                          <label class="col-sm-2 control-label">Активный</label>
                          <div class="col-sm-10">
                              <input type="checkbox" name="status" class="form-control"<?php if($menu_item && $menu_item['status'] == 1) {?> checked<?php } ?>>
                          </div>
                      </div>
                  <?php } else { ?>
                    Родительский элемент не найден. Возможно он был недавно удалён...
                  <?php } ?>
                </div>
                <div class="modal-loader"></div>
                <div class="modal-footer">
                  <?php if(($menu_item || $site_id) && ($parent || !$parent_id)) { ?>
                    <button class="btn btn-success btn-sm btn-save-sites-menu-item" onclick="save_sites_menu_item()" id="btn-save-sites-menu-item"><i class="fa fa-check-circle"></i> Сохранить</button>
                  <?php } ?>
                </div>
            </div>
        </div>
    </div>
  <?php
  return ob_get_clean();
}

function sites_phone($connect)
{
  $blocksArray = [
    'header' => 'Шапка сайта',
    'footer' => 'Подвал сайта'
  ];
  $phone_id = isset($_POST['id'])?(int)$_POST['id']:0;
  $site_id = isset($_POST['site_id'])?(int)$_POST['site_id']:0;
  if($phone_id)
    $phone = $connect->getRow("SELECT * FROM `app_models_site_phone` WHERE `id`=?i",$phone_id);
  else
    $phone = NULL;

  $maxSort = NULL;

  if($phone)
    $maxSort = $connect->getOne("SELECT MAX(`sort`) FROM `app_models_site_phone` WHERE `site_id` = ?i", $phone['site_id']);
  else
    $maxSort = $connect->getOne("SELECT MAX(`sort`) FROM `app_models_site_phone` WHERE `site_id` = ?i", $site_id);

  if(is_null($maxSort))
    $maxSort = -1;

  $maxSort++;
  ob_start();
  ?>
    <div class="modal fade sites-content-modal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><i class="fa fa-times"></i></button>
                    <h4 class="modal-title"><?php if($phone) { ?>Редактировать телефон<?php } else { ?>Добавить телефон<?php } ?></h4>
                </div>
                <div class="modal-body form-horizontal">
                  <?php if($phone || $site_id) { ?>
                      <div class="form-group">
                          <label class="col-sm-2 control-label">Заголовок</label>
                          <div class="col-sm-10">
                              <input type="text" class="form-control" name="title" maxlength="255" value="<?=$phone?htmlspecialchars($phone['title']):"";?>">
                              <input type="hidden" value="<?=$site_id?$site_id:$phone['site_id'];?>" name="site_id">
                              <input type="hidden" value="<?=$phone?$phone['id']:0;?>" name="id">
                              <div class="input-message-block" data-for="title"></div>
                          </div>
                      </div>
                      <div class="form-group">
                          <label class="col-sm-2 control-label">Номер</label>
                          <div class="col-sm-10">
                              <input type="text" class="form-control" name="number" maxlength="255" value="<?=$phone?htmlspecialchars($phone['number']):"";?>">
                              <div class="input-message-block" data-for="number"></div>
                          </div>
                      </div>
                      <div class="form-group">
                          <label class="col-sm-2 control-label">Блок</label>
                          <div class="col-sm-10">
                              <select class="form-control" name="block">
                                <?php foreach ($blocksArray as $block => $label) { ?>
                                    <option value="<?=$block;?>"<?php if($phone && $phone['block'] == $block) { ?> selected<?php } ?>><?=$label;?></option>
                                <?php } ?>
                              </select>
                              <div class="input-message-block" data-for="block"></div>
                          </div>
                      </div>
                      <div class="form-group">
                          <label class="col-sm-2 control-label">Вес</label>
                          <div class="col-sm-10">
                              <input type="number" class="form-control" name="sort" value="<?=$phone?$phone['sort']:$maxSort;?>">
                              <div class="input-message-block" data-for="sort"></div>
                          </div>
                      </div>
                      <div class="form-group">
                          <label class="col-sm-2 control-label">Выделить</label>
                          <div class="col-sm-10">
                              <input type="checkbox" name="main" class="form-control"<?php if($phone && $phone['main'] == 1) {?> checked<?php } ?>>
                              <div class="input-message-block" data-for="main"></div>
                          </div>
                      </div>
                      <div class="form-group">
                          <label class="col-sm-2 control-label">Активный</label>
                          <div class="col-sm-10">
                              <input type="checkbox" name="status" class="form-control"<?php if($phone && $phone['status'] == 1) {?> checked<?php } ?>>
                          </div>
                      </div>
                  <?php } ?>
                </div>
                <div class="modal-loader"></div>
                <div class="modal-footer">
                    <button class="btn btn-success btn-sm btn-save-sites-phone" onclick="save_sites_phone()" id="btn-save-sites-phone"><i class="fa fa-check-circle"></i> Сохранить</button>
                </div>
            </div>
        </div>
    </div>
  <?php
  return ob_get_clean();
}

function sites_meta_template($connect)
{

    $contentTypesRows = $connect->getAll("SELECT * FROM `app_models_site_contenttype` WHERE `status` = 1 AND `system` != 1");

    $typesArray = [];

    $subTypesArray = [
        'all' => 'Любой',
        'resort' => 'Объект',
        'reviews' => 'Отзывы'
    ];

    $keys = [
      'title' => 'Заголовок (Title)',
      'description' => 'Мета-описание (description)',
      'keywords' => 'Ключевые слова (keywords)',
      'h1' => 'Заголовок H1',
      'h2' => 'Заголовок H2'
    ];


    foreach ($contentTypesRows as $contentTypesRow) {
        $typesArray[$contentTypesRow['machine_name']] = $contentTypesRow['name'];
    }

    $meta_template_id = isset($_POST['id'])?(int)$_POST['id']:0;
    $site_id = isset($_POST['site_id'])?(int)$_POST['site_id']:0;

    if($meta_template_id)
        $meta_template = $connect->getRow("SELECT * FROM `app_models_site_page_meta_templates` WHERE `id`=?i",$meta_template_id);
    else
        $meta_template = NULL;

    ob_start();
    ?>
    <div class="modal fade sites-content-modal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><i class="fa fa-times"></i></button>
                    <h4 class="modal-title"><?php if($meta_template) { ?>Редактировать шаблон мета-тегов<?php } else { ?>Добавить шаблон мета-тегов<?php } ?></h4>
                </div>
                <div class="modal-body form-horizontal">
                    <?php if($meta_template || $site_id) { ?>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">Название</label>
                            <div class="col-sm-10">
                                <input type="text" class="form-control" name="name" maxlength="255" value="<?=$meta_template?htmlspecialchars($meta_template['name']):"";?>">
                                <input type="hidden" value="<?=$site_id?$site_id:$meta_template['site_id'];?>" name="site_id">
                                <input type="hidden" value="<?=$meta_template?$meta_template['id']:0;?>" name="id">
                                <div class="input-message-block" data-for="name"></div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-2 control-label">Ключ</label>
                            <div class="col-sm-10">
                                <select class="form-control" name="key">
                                    <?php foreach ($keys as $key => $label) { ?>
                                        <option value="<?=$key;?>"<?php if($meta_template && $meta_template['key'] == $key) { ?> selected<?php } ?>><?=$label;?></option>
                                    <?php } ?>
                                </select>
                                <div class="input-message-block" data-for="block"></div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-2 control-label">Тип страницы</label>
                            <div class="col-sm-10">
                                <select class="form-control" name="type">
                                    <?php foreach ($typesArray as $type => $label) { ?>
                                        <option value="<?=$type;?>"<?php if($meta_template && $meta_template['type'] == $type) { ?> selected<?php } ?>><?=$label;?></option>
                                    <?php } ?>
                                </select>
                                <div class="input-message-block" data-for="type"></div>
                            </div>
                        </div>


                        <div class="form-group">
                            <label class="col-sm-2 control-label">Подтип страницы</label>
                            <div class="col-sm-10">
                                <select class="form-control" name="subtype">
                                    <?php foreach ($subTypesArray as $subType => $label) { ?>
                                        <option value="<?=$subType;?>"<?php if($meta_template && $meta_template['subtype'] == $subType) { ?> selected<?php } ?>><?=$label;?></option>
                                    <?php } ?>
                                </select>
                                <div class="input-message-block" data-for="subtype"></div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-2 control-label">Текст шаблона</label>
                            <div class="col-sm-10">
                                <input type="text" class="form-control" name="value" value="<?=$meta_template?htmlspecialchars($meta_template['value']):"";?>">
                                <div class="input-message-block std-bottom-margin" data-for="value"></div>
                                <div class="info std-bottom-margin">
                                    <div class="std-bottom-margin">
                                        <b>Доступные переменные</b>:
                                    </div>
                                    <div>{{resort.name}} - название объекта</div>
                                    <div>{{resort.full_name}} - полное название объекта</div>
                                    <div>{{resort.description}} - описание объекта</div>
                                    <div>{{resort.type}} - тип объекта</div>
                                    <div>{{resort.name_with_type}} - тип название объекта</div>
                                    <div>{{resort.city.name}} - город объекта</div>
                                    <div>{{resort.region.name}} - регион объекта</div>
                                    <div>{{resort.direction.name}} - направление объекта</div>
                                    <div>{{resort.city.name_genitive}} - название города в родительном падеже</div>
                                    <div>{{resort.region.name_genitive}} - название региона в родительном падеже</div>
                                    <div>{{resort.direction.name_genitive}} - название направления в родительном падеже</div>
                                    <div>{{prev_year}} - предыдующий год</div>
                                    <div>{{current_year}} - текущий год</div>
                                    <div>{{next_year}} - следующий год</div>
                                </div>
                                <div class="info std-bottom-margin">
                                    <i class="fa fa-warning"></i> В случае пустоты значений использованных переменных на какой-либо странице шаблон будет проигнорирован!
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-2 control-label">Активный</label>
                            <div class="col-sm-10">
                                <input type="checkbox" name="status" class="form-control"<?php if($meta_template && $meta_template['status'] == 1) {?> checked<?php } ?>>
                            </div>
                        </div>
                    <?php } ?>
                </div>
                <div class="modal-loader"></div>
                <div class="modal-footer">
                    <button class="btn btn-success btn-sm btn-save-sites-meta-template" onclick="save_sites_meta_template()" id="btn-save-sites-meta-template"><i class="fa fa-check-circle"></i> Сохранить</button>
                </div>
            </div>
        </div>
    </div>
    <?php
    return ob_get_clean();
}


function edit_sites_content($connect) {
  $content_id = isset($_POST['id'])?(int)$_POST['id']:0;
  $copy_mode = isset($_POST['copy_mode'])?(int)$_POST['copy_mode']:0;
  $content = NULL;
  if($content_id)
      $content = $connect->getRow("SELECT `id`, `status`, `published`, `type`, `site_id`, `title`, `title_h1`, `title_h2`, `slider_mode`, `summary`, `snippet_summary`, `body`, `body2`, `head_code`, `pre_body_code`, `post_body_code`, `path`, `redirect_path`, `description`, `keywords`, `weight`, `sort`, `module_object_id`, `module_block`, `second_bg`, `form_action`, `landing_info`, `map_code`, `photogallery_title`, `photogallery_orientation`, `breadcrumb_title`, `direction_id`, `region_id`, `regional_direction_id`, `rss`, `rss_aggregator_link`, `rss_addition`, `rss_aggregation`, `main_page_fix`, `aggregation_by_dates`, `aggregation_date_start`, `aggregation_date_end`, `phone` FROM `sites_contents` WHERE `id` =?i",$content_id);
      $entity = $content;
      $entity['type'] = 'content';
  ob_start();
  if($content) {
      if(!$content['module_object_id'])
          $content['module_object_id'] = NULL;
      $contentTypesRows = $connect->getAll("SELECT * FROM `app_models_site_contenttype` WHERE `status` = 1");

    $regions = [];

    if($content['direction_id']) {
      $regions = $connect->getAll("SELECT `id`, `name` FROM `region` WHERE `id_direction` = ?i", $content['direction_id']);
    }

    $region_directions = [];
    if($content['region_id']) {
      $region_directions = $connect->getAll("SELECT `id`, `name` FROM `direction_object` WHERE `id_reg` = ?i",$content['region_id']);
    }
    ?>
      <div class="modal fade sites-content-modal">
          <div class="modal-dialog">
              <div class="modal-content">
                  <div class="modal-header">
                      <button type="button" class="close" data-dismiss="modal" aria-label="Close"><i class="fa fa-times"></i></button>
                      <h4 class="modal-title"><?php if($copy_mode) { ?>Копирование материала<?php } else { ?>Редактировать материал<?php } ?></h4>
                  </div>
                  <div class="modal-body form-horizontal site-name">
                      <div class="form-group">
                          <label class="col-sm-2 control-label">Заголовок</label>
                          <div class="col-sm-10">
                              <input type="text" class="form-control" name="title" maxlength="255" value="<?=htmlspecialchars($content['title']);?>">
                              <input type="hidden" value="<?=$content['site_id'];?>" name="site_id">
                              <input type="hidden" value="<?=$copy_mode?0:$content['id'];?>" name="content_id">
                              <div class="input-message-block" data-for="title"></div>
                          </div>
                      </div>
                      <div class="form-group<?php if(in_array($content['type'],['redirect']) || ($content['type'] === 'aggregator' && $content['rss'])) { ?> hidden<?php } ?>">
                          <label class="col-sm-2 control-label">Заголовок к крошкам</label>
                          <div class="col-sm-10">
                              <input type="text" class="form-control" name="breadcrumb_title" maxlength="255" value="<?=htmlspecialchars($content['breadcrumb_title']);?>">
                              <div class="input-message-block" data-for="breadcrumb_title"></div>
                          </div>
                      </div>
                      <div class="form-group<?php if(in_array($content['type'],['redirect']) || ($content['type'] === 'aggregator' && $content['rss'])) { ?> hidden<?php } ?>">
                          <label class="col-sm-2 control-label">Заголовок h1</label>
                          <div class="col-sm-10">
                              <input type="text" class="form-control" name="title_h1" maxlength="255" value="<?=htmlspecialchars($content['title_h1']);?>">
                              <div class="input-message-block" data-for="title_h1"></div>
                          </div>
                      </div>
                      <div class="form-group<?php if(in_array($content['type'],['redirect'])) { ?> hidden<?php } ?>">
                          <label class="col-sm-2 control-label">Основная картинка</label>
                          <div class="col-sm-10">
                              <input type="file" class="form-control" name="image" value="<?=htmlspecialchars(json_encode(bounds_to_files($connect,load_bounds($connect,$entity,'image'))));?>">
                              <div class="input-message-block" data-for="image"></div>
                          </div>
                      </div>
                      <div class="form-group">
                          <label class="col-sm-2 control-label">Тип</label>
                          <div class="col-sm-10">
                              <select class="form-control" name="type">
                                <?php foreach ($contentTypesRows as $contentTypesRow) { ?>
                                    <option value="<?=$contentTypesRow['machine_name'];?>"<?php if($content['type'] === $contentTypesRow['machine_name']) {?> selected<?php } ?>><?=$contentTypesRow['name'];?></option>
                                <?php } ?>
                              </select>
                              <div class="input-message-block" data-for="type"></div>
                          </div>
                      </div>
                      <div class="form-group<?php if($content['type'] !== 'aggregator') { ?> hidden<?php } ?>">
                          <label class="col-sm-2 control-label">Тип агрегатора</label>
                          <div class="col-sm-10">
                              <select class="form-control" name="rss">
                                  <option value="0"<?php if(!$content['rss']) { ?> selected<?php } ?>>Страница</option>
                                  <option value="1"<?php if($content['rss']) { ?> selected<?php } ?>>RSS</option>
                              </select>
                          </div>
                      </div>
                      <div class="form-group<?php if($content['type'] !== 'aggregator') { ?> hidden<?php } ?>">
                          <label class="col-sm-2 control-label">Агрегация по датам</label>
                          <div class="col-sm-10">
                              <select class="form-control" name="aggregation_by_dates">
                                  <option value="0"<?php if(!$content['aggregation_by_dates']) { ?> selected<?php } ?>>Нет</option>
                                  <option value="1"<?php if($content['aggregation_by_dates']) { ?> selected<?php } ?>>Да</option>
                              </select>
                          </div>
                      </div>
                      <div class="form-group<?php if($content['type'] !== 'aggregator' || !$content['aggregation_by_dates']) { ?> hidden<?php } ?>">
                          <label class="col-sm-2 control-label">Начальная дата</label>
                          <div class="col-sm-10">
                              <input type="datetime-local" name="aggregation_date_start" class="form-control" value="<?=gmdate("Y-m-d\TH:i",$content['aggregation_date_start']+3600*3);?>">
                          </div>
                      </div>
                      <div class="form-group<?php if($content['type'] !== 'aggregator' || !$content['aggregation_by_dates']) { ?> hidden<?php } ?>">
                          <label class="col-sm-2 control-label">Конечная дата</label>
                          <div class="col-sm-10">
                              <input type="datetime-local" name="aggregation_date_end" class="form-control" value="<?=gmdate("Y-m-d\TH:i",$content['aggregation_date_end']+3600*3);?>">
                          </div>
                      </div>
                      <div class="form-group<?php if(!$content['rss'] || $content['type'] !== 'aggregator') { ?> hidden<?php } ?>">
                          <label class="col-sm-2 control-label">Адрес основного агрегатора</label>
                          <div class="col-sm-10">
                              <input type="text" class="form-control" name="rss_aggregator_link" value="<?=htmlspecialchars($content['rss_aggregator_link']);?>" maxlength="512">
                              <div class="input-message-block" data-for="rss_aggregator_link"></div>
                          </div>
                      </div>
                      <div class="form-group<?php if(!$content['rss'] || $content['type'] !== 'aggregator') { ?> hidden<?php } ?>">
                          <label class="col-sm-2 control-label">Дополнения в RSS</label>
                          <div class="col-sm-10">
                              <textarea class="form-control" name="rss_addition"><?=htmlspecialchars($content['rss_addition'],ENT_QUOTES);?></textarea>
                          </div>
                      </div>
                      <div class="form-group">
                          <label class="col-sm-2 control-label">Адрес страницы</label>
                          <div class="col-sm-10">
                              <input type="text" class="form-control" name="path" value="<?=htmlspecialchars($content['path']);?>" maxlength="512">
                              <div class="input-message-block" data-for="path"></div>
                          </div>
                      </div>
                      <div class="form-group<?php if(!in_array($content['type'],['redirect'])) { ?> hidden<?php } ?>">
                          <label class="col-sm-2 control-label">Адрес редиректа</label>
                          <div class="col-sm-10">
                              <input type="text" class="form-control" name="redirect_path" value="<?=htmlspecialchars($content['redirect_path']);?>" maxlength="512">
                              <div class="input-message-block" data-for="redirect_path"></div>
                          </div>
                      </div>
                      <div class="form-group with-bottom-margin<?php if(!in_array($content['type'],['aggregator'])) { ?> hidden<?php } ?>">
                          <label class="col-sm-2 control-label">Список материалов</label>
                          <div class="col-sm-10">
                              <?php
                              $aggregateTypesIds = bounds_to_ids($connect,load_bounds($connect,$entity,'aggregate_types'));
                              $aggregateTypes = $connect->getAll("SELECT * FROM `app_models_site_contenttype` WHERE `aggregate` = 1 AND `status` = 1");
                              foreach ($aggregateTypes as $aggrI => $aggregateType) {
                              ?>
                              <div class="checkbox-container">
                                  <input type="checkbox" class="form-control" name="aggregate_types" value="<?=$aggregateType['id'];?>" id="aggregate_types_<?=$aggrI;?>"<?php if(in_array($aggregateType['id'],$aggregateTypesIds)) { ?> checked<?php }?>> <label class="control-label" for="aggregate_types_<?=$aggrI;?>"><?=$aggregateType['name'];?></label>
                              </div>

                              <?php } ?>
                              <div class="with-bottom-margin"></div>
                              <div class="input-message-block" data-for="aggregate_types"></div>
                          </div>
                      </div>
                      <div class="form-group<?php if(!in_array($content['type'],['landing','settings', 'news', 'article', 'info', 'advice', 'blog_post'])) { ?> hidden<?php } ?>">
                          <label class="col-sm-2 control-label">Второй заголовок (h2)</label>
                          <div class="col-sm-10">
                              <input type="text" class="form-control" name="title_h2" maxlength="255" value="<?=htmlspecialchars($content['title_h2']);?>">
                              <div class="input-message-block" data-for="title_h2"></div>
                          </div>
                      </div>
                      <div class="form-group<?php if($content['type'] !== 'module') { ?> hidden<?php } ?>">
                          <label class="col-sm-2 control-label">ID объекта </label>
                          <div class="col-sm-10">
                              <input type="number" class="form-control" min="1" name="module_object_id" value="<?=$content['module_object_id'];?>">
                              <div class="input-message-block" data-for="module_object_id"></div>
                          </div>
                      </div>
                      <div class="form-group<?php if($content['type'] !== 'landing') { ?> hidden<?php } ?>">
                          <label class="col-sm-2 control-label">ID объекта для отзывов</label>
                          <div class="col-sm-10">
                              <input type="text" class="form-control" name="reviews_objects" value="<?=implode(", ",bounds_to_ids($connect,load_bounds($connect,$entity,'reviews_objects')));?>">
                              <div class="input-message-block" data-for="reviews_objects"></div>
                          </div>
                      </div>
                      <div class="form-group<?php if($content['type'] !== 'module') { ?> hidden<?php } ?>">
                          <label class="col-sm-2 control-label">Блок модуля</label>
                          <div class="col-sm-10">
                              <select class="form-control" name="module_block">
                                  <option value=""<?php if(is_null($content['module_block'])) { ?> selected<?php } ?>>Выберите блок для отображения...</option>
                                  <option value="rooms"<?php if($content['module_block'] === 'rooms') { ?> selected<?php } ?>>Номера и цены</option>
                                  <option value="desc"<?php if($content['module_block'] === 'desc') { ?> selected<?php } ?>>Описание</option>
                                  <option value="promo"<?php if($content['module_block'] === 'promo') { ?> selected<?php } ?>>Акции</option>
                                  <option value="rating"<?php if($content['module_block'] === 'rating') { ?> selected<?php } ?>>Отзывы</option>
                              </select>
                              <div class="input-message-block" data-for="module_block"></div>
                          </div>
                      </div>
                      <div class="form-group<?php if(!in_array($content['type'],['photogallery','landing','news', 'page','settings', 'article', 'info', 'advice', 'blog_post'])) { ?> hidden<?php } ?>">
                          <label class="col-sm-2 control-label">Фотографии</label>
                          <div class="col-sm-10">
                              <div class="input-message-block" data-for="photogallery"></div>
                              <input type="file" name="photogallery" value="<?=htmlspecialchars(json_encode(bounds_to_files($connect,load_bounds($connect,$entity,'photogallery'))));?>">
                          </div>
                      </div>
                      <div class="form-group<?php if(!in_array($content['type'],['photogallery','landing','news', 'page','settings', 'article', 'info', 'advice', 'blog_post'])) { ?> hidden<?php } ?>">
                          <label class="col-sm-2 control-label">Заголовок к фото</label>
                          <div class="col-sm-10">
                              <div class="input-message-block" data-for="photogallery"></div>
                              <input type="text" class="form-control" name="photogallery_title" value="<?=htmlspecialchars($content['photogallery_title']);?>">
                          </div>
                      </div>
                      <div class="form-group<?php if(!in_array($content['type'],['photogallery','landing','news', 'page','settings', 'article', 'info', 'advice', 'blog_post'])) { ?> hidden<?php } ?>">
                          <label class="col-sm-2 control-label">Ориентация фото</label>
                          <div class="col-sm-10">
                              <select class="form-control" name="photogallery_orientation">
                                  <option value="album"<?php if($content['photogallery_orientation'] === 'album') { ?> selected<?php } ?>>Альбомная</option>
                                  <option value="book"<?php if($content['photogallery_orientation'] === 'book') { ?> selected<?php } ?>>Книжная</option>
                              </select>
                              <div class="input-message-block" data-for="photogallery_orientation"></div>
                          </div>
                      </div>
                      <div class="form-group<?php if($content['type'] !== 'landing') { ?> hidden<?php } ?>">
                          <label class="col-sm-2 control-label">Адрес для формы поиска</label>
                          <div class="col-sm-10">
                              <input type="text" class="form-control" name="form_action" value="<?=htmlspecialchars($content['form_action']);?>" maxlength="512">
                              <div class="input-message-block" data-for="form_action"></div>
                          </div>
                      </div>
                      <div class="form-group<?php if(!in_array($content['type'],['landing','settings'])) { ?> hidden<?php } ?>">
                          <label class="col-sm-2 control-label">Фото слайдера</label>
                          <div class="col-sm-10">
                              <div class="input-message-block" data-for="slider_photos"></div>
                              <input type="file" name="slider_photos" value="<?=htmlspecialchars(json_encode(bounds_to_files($connect,load_bounds($connect,$entity,'slider_photos'))));?>">
                          </div>
                      </div>
                      <div class="form-group<?php if(!in_array($content['type'],['landing','settings'])) { ?> hidden<?php } ?>">
                          <label class="col-sm-2 control-label">Фото слайдера (моб. версия)</label>
                          <div class="col-sm-10">
                              <div class="input-message-block" data-for="slider_photos_mobile"></div>
                              <input type="file" name="slider_photos_mobile" value="<?=htmlspecialchars(json_encode(bounds_to_files($connect,load_bounds($connect,$entity,'slider_photos_mobile'))));?>">
                          </div>
                      </div>
                      <div class="form-group<?php if(!in_array($content['type'],['landing','settings'])) { ?> hidden<?php } ?>">
                          <label class="col-sm-2 control-label">Тип слайдера</label>
                          <div class="col-sm-10">
                              <select class="form-control" name="slider_mode">
                                  <option value="0"<?php if($content['slider_mode'] == 0) { ?> selected<?php } ?>>Стандартный</option>
                                  <option value="1"<?php if($content['slider_mode'] == 1) { ?> selected<?php } ?>>Увеличенный по высоте</option>
                              </select>
                              <div class="input-message-block" data-for="slider_mode"></div>
                          </div>
                      </div>
                      <div class="form-group<?php if(!in_array($content['type'],['landing','settings'])) { ?> hidden<?php } ?>">
                          <label class="col-sm-2 control-label">Фото для фона</label>
                          <div class="col-sm-10">
                              <div class="input-message-block" data-for="page_bg"></div>
                              <input type="file" name="page_bg" value="<?=htmlspecialchars(json_encode(bounds_to_files($connect,load_bounds($connect,$entity,'page_bg'))));?>">
                          </div>
                      </div>
                      <div class="form-group<?php if(!in_array($content['type'],['landing','settings'])) { ?> hidden<?php } ?>">
                          <label class="col-sm-2 control-label">Двухуровневый фон</label>
                          <div class="col-sm-10">
                              <input type="checkbox" name="second_bg" class="form-control"<?php if($content['second_bg'] == 1) {?> checked<?php } ?>>
                          </div>
                      </div>
                      <div class="form-group<?php if(in_array($content['type'],['redirect'])) { ?> hidden<?php } ?>">
                          <label class="col-sm-2 control-label">Мета-описание</label>
                          <div class="col-sm-10">
                              <textarea class="form-control" name="description"><?=$content['description'];?></textarea>
                          </div>
                      </div>
                      <div class="form-group<?php if(in_array($content['type'],['redirect'])) { ?> hidden<?php } ?>">
                          <label class="col-sm-2 control-label">Ключевые слова (через запятую)</label>
                          <div class="col-sm-10">
                              <textarea class="form-control" name="keywords"><?=htmlspecialchars($content['keywords']);?></textarea>
                          </div>
                      </div>
                      <div class="form-group<?php if(in_array($content['type'],['redirect'])) { ?> hidden<?php } ?>">
                          <label class="col-sm-2 control-label">Анонс</label>
                          <div class="col-sm-10">
                              <textarea class="form-control" name="summary"><?=htmlspecialchars($content['summary']);?></textarea>
                          </div>
                      </div>
                      <div class="form-group<?php if(in_array($content['type'],['redirect'])) { ?> hidden<?php } ?>">
                          <label class="col-sm-2 control-label">Анонс для сниппетов</label>
                          <div class="col-sm-10">
                              <textarea class="form-control" name="snippet_summary"><?=htmlspecialchars($content['snippet_summary']);?></textarea>
                          </div>
                      </div>
                      <div class="form-group<?php if(in_array($content['type'],['redirect']) || ($content['type'] === 'aggregator' && $content['rss'])) { ?> hidden<?php } ?>">
                          <label class="col-sm-2 control-label">Содержимое</label>
                          <div class="col-sm-10">
                              <textarea class="form-control resizable-textarea" name="body" id="sites_content_body"><?=htmlspecialchars($content['body']);?></textarea>
                          </div>
                      </div>
                      <div class="form-group<?php if(!in_array($content['type'],['landing','settings', 'news', 'article', 'info', 'advice', 'blog_post'])) { ?> hidden<?php } ?>">
                          <label class="col-sm-2 control-label">Доп. содержимое</label>
                          <div class="col-sm-10">
                              <textarea class="form-control resizable-textarea" name="body2" id="sites_content_body2"><?=htmlspecialchars($content['body2']);?></textarea>
                          </div>
                      </div>
                      <div class="form-group<?php if(!in_array($content['type'],['article','news','info', 'advice', 'blog_post'])) { ?> hidden<?php } ?>">
                          <label class="col-sm-2 control-label">Направление</label>
                          <div class="col-sm-10">
                            <?=get_select_table($connect, "direction_object", "(`id_reg` IS NULL OR `id_reg` = 0) AND `id_country` = 1", $content["direction_id"], "direction_id", 1, "");?>
                          </div>
                      </div>
                      <div class="form-group<?php if(!$content['direction_id'] || !in_array($content['type'],['article','news','info', 'advice', 'blog_post'])) { ?> hidden<?php } ?>">
                          <label class="col-sm-2 control-label">Регион</label>
                          <div class="col-sm-10">
                              <select class="form-control" name="region_id">
                                  <option value="0"<?php if(!$content['region_id']) { ?> selected<?php } ?>>Не выбран</option>
                                <?php foreach ($regions as $region) { ?>
                                    <option value="<?=$region['id'];?>"<?php if($content['region_id'] == $region['id']) { ?> selected<?php } ?>><?=$region['name'];?></option>
                                <?php } ?>
                              </select>
                          </div>
                      </div>
                      <div class="form-group<?php if(!$content['region_id'] || count($region_directions) === 0 || !in_array($content['type'],['article','news','info', 'advice', 'blog_post'])) { ?> hidden<?php } ?>">
                          <label class="col-sm-2 control-label">Рег. направление</label>
                          <div class="col-sm-10">
                              <select class="form-control" name="regional_direction_id">
                                  <option value="0"<?php if(!$content['regional_direction_id']) { ?> selected<?php } ?>>Не выбрано</option>
                                <?php foreach ($region_directions as $region_direction) { ?>
                                    <option value="<?=$region_direction['id'];?>"<?php if($content['regional_direction_id'] == $region_direction['id']) { ?> selected<?php } ?>><?=$region_direction['name'];?></option>
                                <?php } ?>
                              </select>
                          </div>
                      </div>
                      <div class="form-group<?php if(!in_array($content['type'],['article','news','info', 'advice', 'blog_post'])) { ?> hidden<?php } ?>">
                          <label class="col-sm-2 control-label">ID объектов</label>
                          <div class="col-sm-10">
                              <input class="form-control" type="text" name="resorts_ids" value="<?=implode(", ",bounds_to_ids($connect,load_bounds($connect,$entity,'resorts_ids')));?>">
                              <div class="input-message-block" data-for="resorts_ids"></div>
                          </div>
                      </div>
                      <div class="form-group<?php if(in_array($content['type'],['redirect']) || ($content['type'] === 'aggregator' && $content['rss'])) { ?> hidden<?php } ?>">
                          <label class="col-sm-2 control-label">Код карты</label>
                          <div class="col-sm-10">
                              <textarea class="form-control" name="map_code"><?=htmlspecialchars($content['map_code']);?></textarea>
                          </div>
                      </div>
                      <div class="form-group<?php if($content['type'] !== 'landing') { ?> hidden<?php } ?>">
                          <label class="col-sm-2 control-label">Вводный текст</label>
                          <div class="col-sm-10">
                              <textarea class="form-control" name="landing_info"><?=htmlspecialchars($content['landing_info']);?></textarea>
                          </div>
                      </div>
                      <div class="form-group">
                          <label class="col-sm-2 control-label<?php if(in_array($content['type'],['redirect'])) { ?> hidden<?php } ?>">Код в блоке head</label>
                          <div class="col-sm-10">
                              <textarea class="form-control" name="head_code"><?=$content?htmlspecialchars($content['head_code']):"";?></textarea>
                          </div>
                      </div>
                      <div class="form-group">
                          <label class="col-sm-2 control-label<?php if(in_array($content['type'],['redirect'])) { ?> hidden<?php } ?>">Код в начале элемента body</label>
                          <div class="col-sm-10">
                              <textarea class="form-control" name="pre_body_code"><?=$content?htmlspecialchars($content['pre_body_code']):"";?></textarea>
                          </div>
                      </div>
                      <div class="form-group">
                          <label class="col-sm-2 control-label<?php if(in_array($content['type'],['redirect'])) { ?> hidden<?php } ?>">Код в конце элемента body</label>
                          <div class="col-sm-10">
                              <textarea class="form-control" name="post_body_code"><?=$content?htmlspecialchars($content['post_body_code']):"";?></textarea>
                          </div>
                      </div>
                      <div class="form-group">
                          <label class="col-sm-2 control-label<?php if(in_array($content['type'],['redirect'])) { ?> hidden<?php } ?>">Телефон</label>
                          <div class="col-sm-10">
                              <input class="form-control" name="phone" value="<?=$content?htmlspecialchars($content['phone']):"";?>">
                          </div>
                      </div>
                      <div class="form-group">
                          <label class="col-sm-2 control-label">Дата и время публикации</label>
                          <div class="col-sm-10">
                              <input type="datetime-local" name="published" class="form-control" value="<?=gmdate("Y-m-d\TH:i",$content['published']+3600*3);?>">
                          </div>
                      </div>
                      <div class="form-group<?php if(in_array($content['type'],['redirect']) || ($content['type'] === 'aggregator' && $content['rss'])) { ?> hidden<?php } ?>">
                          <label class="col-sm-2 control-label">Вес материала (для Sitemap)</label>
                          <div class="col-sm-10">
                              <input type="number" name="weight" class="form-control" value="<?=$content['weight'];?>">
                              <div class="input-message-block" data-for="weight"></div>
                          </div>
                      </div>
                      <div class="form-group<?php if(in_array($content['type'],['redirect']) || ($content['type'] === 'aggregator' && $content['rss'])) { ?> hidden<?php } ?>">
                          <label class="col-sm-2 control-label">Вес материала (сортировка)</label>
                          <div class="col-sm-10">
                              <input type="number" name="sort" class="form-control" value="<?=$content['sort'];?>">
                              <div class="input-message-block" data-for="sort"></div>
                          </div>
                      </div>
                      <div class="form-group<?php if(!in_array($content['type'],['photogallery','news', 'page','settings', 'article', 'info', 'advice', 'blog_post'])) { ?> hidden<?php } ?>">
                          <label class="col-sm-2 control-label">Разрешить RSS-агрегацию</label>
                          <div class="col-sm-10">
                              <input type="checkbox" name="rss_aggregation" class="form-control"<?php if($content['rss_aggregation'] == 1) {?> checked<?php } ?>>
                          </div>
                      </div>
                      <div class="form-group">
                          <label class="col-sm-2 control-label">Опубликовано</label>
                          <div class="col-sm-10">
                              <input type="checkbox" name="status" class="form-control"<?php if($content['status'] == 1) {?> checked<?php } ?>>
                          </div>
                      </div>
                      <div class="form-group<?php if(!in_array($content['type'],['article','news','info', 'advice', 'blog_post'])) { ?> hidden<?php } ?>">
                          <label class="col-sm-2 control-label">Закрепить на главной</label>
                          <div class="col-sm-10">
                              <input type="checkbox" name="main_page_fix" class="form-control"<?php if($content['main_page_fix'] == 1) {?> checked<?php } ?>>
                          </div>
                      </div>
                  </div>
                  <div class="modal-loader"></div>
                  <div class="modal-footer">
                      <button class="btn btn-success btn-sm btn-save-new-sites-content" onclick="set_sites_content()" id="btn-save-new-sites-content"><i class="fa fa-check-circle"></i> Сохранить</button>
                      </div>
                  </div>
              </div>
          </div>
    <?php
  }
  return ob_get_clean();
}

function remove_bounds($connect,$entity,$boundsName) {
  $connect->query("UPDATE `app_models_site_bound` INNER JOIN `core_models_file_file` ON `app_models_site_bound`.`entity2_id` = `core_models_file_file`.`id` AND `app_models_site_bound`.`entity2_type` = 'file' SET `core_models_file_file`.`usages` = `core_models_file_file`.`usages`-1 WHERE `app_models_site_bound`.`entity1_type`=?s AND `app_models_site_bound`.`entity1_id`=?i AND `app_models_site_bound`.`name`=?s",$entity['type'],$entity['id'],$boundsName);
  $connect->query("DELETE FROM `app_models_site_bound` WHERE `entity1_type`=?s AND `entity1_id`=?i AND `name` =?s",$entity['type'],$entity['id'],$boundsName);
}

function set_bounds($connect,$boundsArray,String $boundsName)
{
    $entity1_types = [
      'site',
      'content',
      'room',
      'treatment_profile',
      'treatment_method'
    ];

    $entity2_types = [
      'file',
      'resort',
      'content_type'
    ];

    $timestamp = gmdate("U");
    $i = 0;
    foreach ($boundsArray as $bound) {
      if(in_array($bound['entity1_type'],$entity1_types) && in_array($bound['entity2_type'],$entity2_types) && $bound['entity1_id'] > 0 && $bound['entity2_id'] > 0) {
          if($bound['entity2_type'] === 'file') {
            $connect->query("UPDATE `core_models_file_file` SET `usages` = `usages`+1 WHERE `id` = ?i",$bound['entity2_id']);
          }

          if($bound['entity2_type'] === 'object') {
              if($connect->getOne("SELECT `id` FROM `object` WHERE `id` = ?i",$bound['entity2_id'])) {
                $connect->query("INSERT INTO `app_models_site_bound` (`created`,`changed`,`status`,`uid`, `sort`, `name`,`entity1_type`,`entity1_id`,`entity2_type`,`entity2_id`, `title`, `description`) VALUES (?i,?i,?i,?i,?i,?s,?s,?i,?s,?i,?s,?s)",$timestamp,$timestamp,1,1,$i,$boundsName,$bound['entity1_type'],$bound['entity1_id'],$bound['entity2_type'],$bound['entity2_id'],$bound['title'],$bound['description']);
              }
          }
          else {
            $connect->query("INSERT INTO `app_models_site_bound` (`created`,`changed`,`status`,`uid`, `sort`, `name`,`entity1_type`,`entity1_id`,`entity2_type`,`entity2_id`, `title`, `description`) VALUES (?i,?i,?i,?i,?i,?s,?s,?i,?s,?i,?s,?s)",$timestamp,$timestamp,1,1,$i,$boundsName,$bound['entity1_type'],$bound['entity1_id'],$bound['entity2_type'],$bound['entity2_id'],$bound['title'],$bound['description']);
          }
      }
      $i++;
    }
}

function load_bounds($connect,$entity,String $boundsName = NULL)
{
    if(!is_null($boundsName))
        return $connect->getAll("SELECT * FROM `app_models_site_bound` WHERE  `status` = 1 AND `entity1_type`=?s AND `entity1_id` = ?i AND `name`=?s ORDER BY `sort` ASC",$entity['type'],$entity['id'],$boundsName);
    else
        return $connect->getAll("SELECT * FROM `app_models_site_bound` WHERE `status` = 1 AND `entity1_type`=?s AND `entity1_id` = ?i ORDER BY `sort` ASC",$entity['type'],$entity['id']);
}

function bounds_to_ids($connect, array $bounds):array
{
    $idsArray = [];

    foreach ($bounds as $bound) {
        $idsArray[] = $bound['entity2_id'];
    }

    return $idsArray;
}

function bounds_to_files($connect,array $bounds):array
{
    $filesAr = [];

    foreach ($bounds as $bound) {
        if($bound['entity2_type'] === 'file' && ($file = $connect->getRow("SELECT * FROM `core_models_file_file` WHERE `id` =?i LIMIT 1",$bound['entity2_id']))) {
          $filesAr[] = [
            'id' => $bound['entity2_id'],
            'title' => $bound['title'],
            'description' => $bound['description'],
            'uri' => $file['uri'],
            'uri_thumbnail' => in_array($file['mime'],['image/png','image/jpeg'])?imageUriStyle($file['uri'],"thumbnail"):(in_array($file['mime'],['image/vnd.microsoft.icon','image/x-icon'])?$file['uri']:""),
            'uri_preview' => in_array($file['mime'],['image/png','image/jpeg'])?imageUriStyle($file['uri'],"preview"):(in_array($file['mime'],['image/vnd.microsoft.icon','image/x-icon'])?$file['uri']:""),
            'uri_large' => in_array($file['mime'],['image/png','image/jpeg'])?imageUriStyle($file['uri'],"large"):(in_array($file['mime'],['image/vnd.microsoft.icon','image/x-icon'])?$file['uri']:""),
            'mime' => $file['mime'],
            'extension' => $file['ext'],
            'cache' => substr(str_shuffle('0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz'), 1, 15)
          ];
        }
    }
    return $filesAr;
}

function ids_to_bounds($connect,$entity, String $name, array $ids, String $entity2_type = 'resort'):array
{
    $boundsAr = [];
    $timestamp = gmdate("U");
    $i = 0;
    foreach ($ids as $id) {
      $boundsAr[] = [
        'created' => $timestamp,
        'changed' => $timestamp,
        'status' => 1,
        'uid' => 1,
        'name' => $name,
        'entity1_type' => $entity['type'],
        'entity1_id' => $entity['id'],
        'entity2_type' => $entity2_type,
        'entity2_id' => $id,
        'title' => "",
        'description' => "",
        'sort' => $i
      ];
      $i++;
    }
    return $boundsAr;
}

function ids_string_to_ids(String $ids_string):array {
    $ids_string_ar = explode(",",$ids_string);

    for($i = 0; $i < count($ids_string_ar); $i++) {
        $ids_string_ar[$i] = (int)trim($ids_string_ar[$i]);
    }

    return $ids_string_ar;
}

function files_to_bounds($connect,$entity,String $name, array $files):array
{
    $boundsAr = [];
    $i = 0;
    $timestamp = gmdate("U");
    foreach ($files as $file) {
        if($fileBase = $connect->getRow("SELECT * FROM `core_models_file_file` WHERE `id` =?i LIMIT 1",$file['id'])) {
            $boundsAr[] = [
            'created' => $timestamp,
            'changed' => $timestamp,
            'status' => 1,
            'uid' => 1,
            'name' => $name,
            'entity1_type' => $entity['type'],
            'entity1_id' => $entity['id'],
            'entity2_type' => 'file',
            'entity2_id' => $fileBase['id'],
            'title' => $file['title'],
            'description' => $file['description'],
            'sort' => $i
          ];
          $i++;
        }
    }
  return $boundsAr;

}

function imageUriStyle(String $uri, String $style) {
    $uriExpl = explode("/",$uri);
    $newUri = "";

    foreach ($uriExpl as $i => $uriItem) {
        if($i > 0 || $uriItem !== '') {

            if($i === count($uriExpl)-1)
                $newUri .= '/'.$style;

            if($i !== 0)
              $newUri .= '/';

            $newUri .= $uriItem;
        }
    }

    return $newUri;
}

function multipart_upload($connect, $customData = NULL) {
  $respAr = [
    'msg' => "Неизвестная ошибка",
    'title' => 'Error',
    'success' => 0
  ];
  try {
    $client = new \GuzzleHttp\Client();
    if(is_null($customData)) {
      $postCopy = $_POST;
    }
    else {
      $postCopy  = [
          'format' => 'jpg',
          'type' => 'image/jpeg',
          'name' => 'temp-file.jpg',
          'partnum' => 0,
          'plength' => 1
      ];
    }
    if(is_null($customData)) {
      $respAr['partnum'] = (int)$_POST['partnum'];
      $respAr['plength'] = (int)$_POST['plength'];
    }
    else {
      $respAr['partnum'] = 0;
      $respAr['plength'] = 1;
    }

    if($respAr['partnum'] == $respAr['plength']-1)
        $postCopy['used'] = 1;


    $multipart = [
      [
        'Content-type' => 'multipart/form-data',
        'name' => 'upload',
        'contents' => is_null($customData)?fopen($_FILES['upload']['tmp_name'],"r"):fopen($customData,"r")
      ]
    ];

    foreach ($postCopy as $postKey => $postItem) {
      $multipart[] = [
        'name' => $postKey,
        'contents' => $postItem
      ];
    }


    $res = $client->request('POST',"https://cdn.tonia.ru/api/files/upload/multipart",[
      'multipart' => $multipart,
      'headers' => [
          'X-Secret-Token' => '8g5bKM1o70O3MqQPsaHNvXTICd5ZSZoB9ZmcpBBh'
      ]
    ]);

    $res = json_decode($res->getBody()->getContents(),true);
    if(array_key_exists('success',$res)) {
      $respAr = $res;
      $respAr['success'] = (int)$respAr['success'];
      if(array_key_exists('loaded',$respAr))
          $respAr['loaded'] = (int)$respAr['loaded'];
      else
          $respAr['loaded'] = 0;

      if($respAr['loaded']) {
        $respAr['uri'] = 'https://cdn.tonia.ru'.$respAr['uri'];


        $connect->query("INSERT INTO `core_models_file_file` (`created`, `changed`, `status`, `uid`, `title`, `description`, `uri`, `mime`, `ext`, `usages`) VALUES (?i,?i,?i,?i,?s,?s,?s,?s,?s,?i)",$respAr['created'],$respAr['changed'],1,(int)$respAr['uid'],'','',$respAr['uri'],$respAr['mime'],$respAr['extension'],0);

        $respAr['id'] = $connect->insertId();

        if(!is_null($customData))
          return [
            'id' => $connect->insertId()
          ];
        $respAr['uri_thumbnail'] = 'https://cdn.tonia.ru'.$respAr['uri_thumbnail'];
        $respAr['uri_preview'] = 'https://cdn.tonia.ru'.$respAr['uri_preview'];
      }

      return json_encode($respAr);
    }
    else
      return json_encode($respAr);
  }
  catch (Exception $e) {
    $respAr['msg'] = "Ошибка соединения: ".$e->getMessage();
    return json_encode($respAr);
  }
}

function edit_site($connect) {
  $id = isset($_POST['id'])?(int)$_POST['id']:0;
  $site = NULL;
  $site_types = [
    'no_objects' => 'Без объектов',
    'objects' => 'С отдельными объектами',
    'global' => 'Региональный'
  ];
  if($id)
    $site = $connect->getRow("SELECT `id`, `status`, `name`, `branding_name`, `branding_slogan`,  `domain`, `main_bg_color`, `main_bg_color2`, `main_bg_color3`, `main_font_color`, `main_font_color2`, `main_link_color`, `head_code`, `pre_body_code`, `post_body_code`, `robots`, `interface_style`, `type`, `direction_id`, `region_id`, `theme` FROM `sites` WHERE `id` =?i",$id);
  ob_start();
  if($site || !$id) {
      $directions = $connect->getAll("SELECT `id`, `name` FROM `direction_object` WHERE (`id_reg` IS NULL OR `id_reg` = 0) AND `id_country` = 1 ORDER BY `name` ASC");
      $regions = [];

      if($site['direction_id'] > 0) {
          $regions = $connect->getAll("SELECT `id`, `name` FROM `region` WHERE `id_direction` = ?i", $site['direction_id']);
      }

      $entity = [
          'id' => $site['id'],
          'type' => 'site'
      ];
    ?>
      <div class="modal fade site-modal">
          <div class="modal-dialog">
              <div class="modal-content">
                  <div class="modal-header">
                      <button type="button" class="close" data-dismiss="modal" aria-label="Close"><i class="fa fa-times"></i></button>
                      <h4 class="modal-title"><?php if($site) { ?>Основная информация сайта<?php } else { ?>Добавление нового сайта<?php } ?></h4>
                      </div>
                  <div class="modal-body form-horizontal site-name">
                      <div class="form-group">
                          <label class="col-sm-4 control-label">Название</label>
                          <div class="col-sm-8">
                              <input type="text" class="form-control" name="name" value="<?=$site?$site['name']:"";?>">
                              <input type="hidden" name="id" value="<?=$id;?>">
                              <div class="input-message-block" data-for="name"></div>
                          </div>
                      </div>
                      <div class="form-group">
                          <label class="col-sm-4 control-label">Название бренда</label>
                          <div class="col-sm-8">
                              <input type="text" class="form-control" name="branding_name" value="<?=$site?$site['branding_name']:"";?>">
                              <div class="input-message-block" data-for="branding_name"></div>
                          </div>
                      </div>
                      <div class="form-group">
                          <label class="col-sm-4 control-label">Слоган бренда</label>
                          <div class="col-sm-8">
                              <input type="text" class="form-control" name="branding_slogan" value="<?=$site?$site['branding_slogan']:"";?>">
                              <div class="input-message-block" data-for="branding_slogan"></div>
                          </div>
                      </div>
                      <div class="form-group">
                          <label class="col-sm-4 control-label">Favicon</label>
                          <div class="col-sm-8">
                              <div class="input-message-block" data-for="favicon"></div>
                              <input type="file" name="favicon" value="<?=htmlspecialchars(json_encode($site?bounds_to_files($connect,load_bounds($connect,$entity,'favicon')):[]));?>">
                          </div>
                      </div>
                      <div class="form-group">
                          <label class="col-sm-4 control-label">Домен</label>
                          <div class="col-sm-8">
                              <input type="text" class="form-control site-domain" name="domain" value="<?=$site?$site['domain']:"";?>">
                              <div class="input-message-block" data-for="domain"></div>
                          </div>
                      </div>
                      <div class="form-group">
                          <label class="col-sm-4 control-label">Тема</label>
                          <div class="col-sm-8">
                              <select class="form-control" name="theme">
                                  <option value="default"<?php if(!$site || $site['theme'] === 'default') { ?> selected<?php } ?>>По умолчанию</option>
                                  <option value="sanrussia"<?php if($site['theme'] === 'sanrussia') { ?> selected<?php } ?>>Санатории России</option>
                                  <option value="simplesite"<?php if($site['theme'] === 'simplesite') { ?> selected<?php } ?>>Для простых сайтов</option>
                              </select>
                          </div>
                      </div>
                      <div class="form-group">
                          <label class="col-sm-4 control-label">Основной цвет интерфейса</label>
                          <div class="col-sm-8">
                              <input type="color" class="form-control site-main-bg-color" name="main-bg-color" value="<?=$site?$site['main_bg_color']:"#ffffff";?>">
                              <div class="input-message-block" data-for="main-bg-color"></div>
                          </div>
                      </div>
                      <div class="form-group">
                          <label class="col-sm-4 control-label">Основной цвет интерфейса 2</label>
                          <div class="col-sm-8">
                              <input type="color" class="form-control site-main-bg-color2" name="main-bg-color2" value="<?=$site?$site['main_bg_color2']:"#356d33";?>">
                              <div class="input-message-block" data-for="main-bg-color2"></div>
                          </div>
                      </div>
                      <div class="form-group">
                          <label class="col-sm-4 control-label">Основной цвет интерфейса 3</label>
                          <div class="col-sm-8">
                              <input type="color" class="form-control site-main-bg-color3" name="main-bg-color3" value="<?=$site?$site['main_bg_color3']:"#ba2328";?>">
                              <div class="input-message-block" data-for="main-bg-color3"></div>
                          </div>
                      </div>
                      <div class="form-group">
                          <label class="col-sm-4 control-label">Основной цвет текста</label>
                          <div class="col-sm-8">
                              <input type="color" class="form-control site-main-font-color" name="main-font-color" value="<?=$site?$site['main_font_color']:"#356d33";?>">
                              <div class="input-message-block" data-for="main-font-color"></div>
                          </div>
                      </div>
                      <div class="form-group">
                          <label class="col-sm-4 control-label">Основной цвет текста 2</label>
                          <div class="col-sm-8">
                              <input type="color" class="form-control site-main-font-color2" name="main-font-color2" value="<?=$site?$site['main_font_color2']:"#ffffff";?>">
                              <div class="input-message-block" data-for="main-font-color2"></div>
                          </div>
                      </div>
                      <div class="form-group">
                          <label class="col-sm-4 control-label">Основной цвет ссылок</label>
                          <div class="col-sm-8">
                              <input type="color" class="form-control site-main-link-color" name="main-link-color" value="<?=$site?$site['main_link_color']:"#356d33";?>">
                              <div class="input-message-block" data-for="main-link-color"></div>
                          </div>
                      </div>
                      <div class="form-group">
                          <label class="col-sm-4 control-label">Стиль интерфейса</label>
                          <div class="col-sm-8">
                              <select class="form-control" name="interface_style">
                                  <option value="1"<?php if($site && $site['interface_style'] == 1) { ?> selected<?php }?>>Строгий</option>
                                  <option value="2"<?php if($site && $site['interface_style'] == 2) { ?> selected<?php }?>>Мягкий</option>
                              </select>
                          </div>
                      </div>
                      <div class="form-group">
                          <label class="col-sm-4 control-label">Код в блоке head</label>
                          <div class="col-sm-8">
                              <textarea class="form-control" name="head_code"><?=$site?htmlspecialchars($site['head_code']):"";?></textarea>
                          </div>
                      </div>
                      <div class="form-group">
                          <label class="col-sm-4 control-label">Код в начале элемента body</label>
                          <div class="col-sm-8">
                              <textarea class="form-control" name="pre_body_code"><?=$site?htmlspecialchars($site['pre_body_code']):"";?></textarea>
                          </div>
                      </div>
                      <div class="form-group">
                          <label class="col-sm-4 control-label">Код в конце элемента body</label>
                          <div class="col-sm-8">
                              <textarea class="form-control" name="post_body_code"><?=$site?htmlspecialchars($site['post_body_code']):"";?></textarea>
                          </div>
                      </div>
                      <div class="form-group">
                          <label class="col-sm-4 control-label">Robots.txt</label>
                          <div class="col-sm-8">
                              <textarea class="form-control" name="robots"><?=$site?htmlspecialchars($site['robots']):"";?></textarea>
                          </div>
                      </div>
                      <div class="form-group">
                          <label class="col-sm-4 control-label">Тип сайта</label>
                          <div class="col-sm-8">
                              <select class="form-control" name="type">
                                  <?php foreach ($site_types as $type_key => $site_type) { ?>
                                    <option value="<?=$type_key;?>"<?php if($site['type'] === $type_key) { ?> selected<?php } ?>><?=$site_type;?></option>
                                  <?php } ?>
                              </select>
                          </div>
                      </div>
                      <div class="form-group<?php if(!$site || $site['type'] !== 'global') { ?> hidden<?php } ?>">
                          <label class="col-sm-4 control-label">Направление</label>
                          <div class="col-sm-8">
                              <select class="form-control direction-selector" name="direction_id">
                                  <option value="0"<?php if($site && $site['direction_id'] == 0) { ?> selected<?php } ?>>Без направления</option>
                                  <?php foreach ($directions as $direction) { ?>
                                    <option value="<?=$direction['id'];?>"<?php if($site && $site['direction_id'] == $direction['id']) { ?> selected<?php } ?>><?=$direction['name'];?></option>
                                  <?php } ?>
                              </select>
                          </div>
                      </div>
                      <div class="form-group<?php if(!$site || $site['type'] !== 'global' || !$site['direction_id']) { ?> hidden<?php } ?>">
                          <label class="col-sm-4 control-label">Регион</label>
                          <div class="col-sm-8">
                              <select class="form-control" name="region_id">
                                  <option value="0"<?php if($site && $site['region_id'] == 0) { ?> selected<?php } ?>>Без региона</option>
                                  <?php foreach ($regions as $region) { ?>
                                      <option value="<?=$region['id'];?>"<?php if($site && $site['region_id'] == $region['id']) { ?> selected<?php } ?>><?=$region['name'];?></option>
                                  <?php } ?>
                              </select>
                          </div>
                      </div>
                      <div class="form-group<?php if(!$site || $site['type'] !== 'objects') { ?> hidden<?php } ?>">
                          <label class="col-sm-4 control-label">ID объектов</label>
                          <div class="col-sm-8">
                              <input class="form-control" type="text" name="resorts_ids" value="<?=implode(", ",bounds_to_ids($connect,load_bounds($connect,$entity,'resorts_ids')));?>">
                              <div class="input-message-block" data-for="main-link-color"></div>
                          </div>
                      </div>
                  </div>
                  <div class="modal-loader"></div>
                  <div class="modal-footer">
                      <button class="btn btn-success btn-sm btn-save-new-site" onclick="save_site()" id="btn-save-new-site"><i class="fa fa-check-circle"></i> Сохранить</button>
                      </div>
                  </div>
              </div>
          </div>
    <?php
  }
  return ob_get_clean();
}

function edit_site_icons($connect) {
  $id = isset($_POST['id'])?(int)$_POST['id']:0;
  $site = NULL;
  if($id)
    $site = $connect->getRow("SELECT `id`, `status`, `name`, `branding_name`, `branding_slogan`,  `domain`, `main_bg_color`, `main_bg_color2`, `main_font_color`, `main_font_color2`, `main_link_color`, `head_code`, `pre_body_code`, `post_body_code`, `robots`, `interface_style`, `type`, `direction_id`, `region_id`, `theme` FROM `sites` WHERE `id` =?i",$id);
  ob_start();
  if($site) {
    $entity = [
      'id' => $site['id'],
      'type' => 'site'
    ];
    ?>
      <div class="modal fade site-icons-modal">
          <div class="modal-dialog">
              <div class="modal-content">
                  <div class="modal-header">
                      <button type="button" class="close" data-dismiss="modal" aria-label="Close"><i class="fa fa-times"></i></button>
                      <h4 class="modal-title"><?php if($site) { ?>Иконки сайта<?php } ?></h4>
                  </div>
                  <div class="modal-body form-horizontal site-name">
                      <div class="form-group">
                          <label class="col-sm-4 control-label">Favicon</label>
                          <div class="col-sm-8">
                              <div class="input-message-block" data-for="favicon"></div>
                              <input type="hidden" name="id" value="<?=$id;?>">
                              <input type="file" name="favicon" value="<?=htmlspecialchars(json_encode($site?bounds_to_files($connect,load_bounds($connect,$entity,'favicon')):[]));?>">
                          </div>
                      </div>
                      <div class="form-group">
                          <label class="col-sm-4 control-label">Логотип</label>
                          <div class="col-sm-8">
                              <div class="input-message-block" data-for="logo"></div>
                              <input type="file" name="logo" value="<?=htmlspecialchars(json_encode($site?bounds_to_files($connect,load_bounds($connect,$entity,'logo')):[]));?>">
                          </div>
                      </div>
                      <div class="form-group">
                          <label class="col-sm-4 control-label">Иконка 16x16</label>
                          <div class="col-sm-8">
                              <div class="input-message-block" data-for="icon_16x16"></div>
                              <input type="file" name="icon_16x16" value="<?=htmlspecialchars(json_encode($site?bounds_to_files($connect,load_bounds($connect,$entity,'icon_16x16')):[]));?>">
                          </div>
                      </div>
                      <div class="form-group">
                          <label class="col-sm-4 control-label">Иконка 32x32</label>
                          <div class="col-sm-8">
                              <div class="input-message-block" data-for="icon_32x32"></div>
                              <input type="file" name="icon_32x32" value="<?=htmlspecialchars(json_encode($site?bounds_to_files($connect,load_bounds($connect,$entity,'icon_32x32')):[]));?>">
                          </div>
                      </div>

                      <div class="form-group">
                          <label class="col-sm-4 control-label">Иконка 57x57 (Apple non-Retina)</label>
                          <div class="col-sm-8">
                              <div class="input-message-block" data-for="icon_apple_57x57"></div>
                              <input type="file" name="icon_apple_57x57" value="<?=htmlspecialchars(json_encode($site?bounds_to_files($connect,load_bounds($connect,$entity,'icon_apple_57x57')):[]));?>">
                          </div>
                      </div>

                      <div class="form-group">
                          <label class="col-sm-4 control-label">Иконка 60x60 (Apple)</label>
                          <div class="col-sm-8">
                              <div class="input-message-block" data-for="icon_apple_60x60"></div>
                              <input type="file" name="icon_apple_60x60" value="<?=htmlspecialchars(json_encode($site?bounds_to_files($connect,load_bounds($connect,$entity,'icon_apple_60x60')):[]));?>">
                          </div>
                      </div>
                      <div class="form-group">
                          <label class="col-sm-4 control-label">Иконка 72x72 (Apple)</label>
                          <div class="col-sm-8">
                              <div class="input-message-block" data-for="icon_apple_72x72"></div>
                              <input type="file" name="icon_apple_72x72" value="<?=htmlspecialchars(json_encode($site?bounds_to_files($connect,load_bounds($connect,$entity,'icon_apple_72x72')):[]));?>">
                          </div>
                      </div>
                      <div class="form-group">
                          <label class="col-sm-4 control-label">Иконка 76x76 (Apple)</label>
                          <div class="col-sm-8">
                              <div class="input-message-block" data-for="icon_apple_76x76"></div>
                              <input type="file" name="icon_apple_76x76" value="<?=htmlspecialchars(json_encode($site?bounds_to_files($connect,load_bounds($connect,$entity,'icon_apple_76x76')):[]));?>">
                          </div>
                      </div>
                      <div class="form-group">
                          <label class="col-sm-4 control-label">Иконка 96x96</label>
                          <div class="col-sm-8">
                              <div class="input-message-block" data-for="icon_96x96"></div>
                              <input type="file" name="icon_96x96" value="<?=htmlspecialchars(json_encode($site?bounds_to_files($connect,load_bounds($connect,$entity,'icon_96x96')):[]));?>">
                          </div>
                      </div>
                      <div class="form-group">
                          <label class="col-sm-4 control-label">Иконка 114x114 (Apple)</label>
                          <div class="col-sm-8">
                              <div class="input-message-block" data-for="icon_apple_114x114"></div>
                              <input type="file" name="icon_apple_114x114" value="<?=htmlspecialchars(json_encode($site?bounds_to_files($connect,load_bounds($connect,$entity,'icon_apple_114x114')):[]));?>">
                          </div>
                      </div>
                      <div class="form-group">
                          <label class="col-sm-4 control-label">Иконка 120x120 (Apple)</label>
                          <div class="col-sm-8">
                              <div class="input-message-block" data-for="icon_apple_120x120"></div>
                              <input type="file" name="icon_apple_120x120" value="<?=htmlspecialchars(json_encode($site?bounds_to_files($connect,load_bounds($connect,$entity,'icon_apple_120x120')):[]));?>">
                          </div>
                      </div>
                      <div class="form-group">
                          <label class="col-sm-4 control-label">Иконка 144x144 (Apple)</label>
                          <div class="col-sm-8">
                              <div class="input-message-block" data-for="icon_apple_144x144"></div>
                              <input type="file" name="icon_apple_144x144" value="<?=htmlspecialchars(json_encode($site?bounds_to_files($connect,load_bounds($connect,$entity,'icon_apple_144x144')):[]));?>">
                          </div>
                      </div>
                      <div class="form-group">
                          <label class="col-sm-4 control-label">Иконка 152x152 (Apple)</label>
                          <div class="col-sm-8">
                              <div class="input-message-block" data-for="icon_apple_152x152"></div>
                              <input type="file" name="icon_apple_152x152" value="<?=htmlspecialchars(json_encode($site?bounds_to_files($connect,load_bounds($connect,$entity,'icon_apple_152x152')):[]));?>">
                          </div>
                      </div>
                      <div class="form-group">
                          <label class="col-sm-4 control-label">Иконка 180x180 (Apple)</label>
                          <div class="col-sm-8">
                              <div class="input-message-block" data-for="icon_apple_180x180"></div>
                              <input type="file" name="icon_apple_180x180" value="<?=htmlspecialchars(json_encode($site?bounds_to_files($connect,load_bounds($connect,$entity,'icon_apple_180x180')):[]));?>">
                          </div>
                      </div>
                      <div class="form-group">
                          <label class="col-sm-4 control-label">Иконка 192x192</label>
                          <div class="col-sm-8">
                              <div class="input-message-block" data-for="icon_192x192"></div>
                              <input type="file" name="icon_192x192" value="<?=htmlspecialchars(json_encode($site?bounds_to_files($connect,load_bounds($connect,$entity,'icon_192x192')):[]));?>">
                          </div>
                      </div>
                  </div>
                  <div class="modal-loader"></div>
                  <div class="modal-footer">
                      <button class="btn btn-success btn-sm btn-save-site-icons" onclick="save_site_icons();" id="btn-save-site-icons"><i class="fa fa-check-circle"></i> Сохранить</button>
                  </div>
              </div>
          </div>
      </div>
    <?php
  }
  return ob_get_clean();
}

function edit_site_tech($connect) {
    $id = isset($_POST['id'])?(int)$_POST['id']:0;
    $site = NULL;
    if($id)
        $site = $connect->getRow("SELECT `id`, `status`, `name`, `branding_name`, `branding_slogan`,  `domain`, `main_bg_color`, `main_bg_color2`, `main_font_color`, `main_font_color2`, `main_link_color`, `head_code`, `pre_body_code`, `post_body_code`, `robots`, `interface_style`, `type`, `direction_id`, `region_id`, `theme`, `glue_css`, `glue_js`, `compress_css`, `compress_js` FROM `sites` WHERE `id` =?i",$id);
    ob_start();
    if($site) {
        ?>
        <div class="modal fade site-icons-modal">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><i class="fa fa-times"></i></button>
                        <h4 class="modal-title"><?php if($site) { ?> Технические настройки сайта<?php } ?></h4>
                    </div>
                    <div class="modal-body form-horizontal site-name">
                        <div class="form-group">
                            <label class="col-sm-4 control-label">Объединение CSS</label>
                            <div class="col-sm-8">
                                <input type="hidden" name="id" value="<?=$id;?>">
                                <input type="checkbox" name="glue_css"<?php if($site['glue_css']) { ?> checked<?php } ?>>
                                <div class="input-message-block" data-for="glue_css"></div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-4 control-label">Сжатие CSS</label>
                            <div class="col-sm-8">
                                <input type="checkbox" name="compress_css"<?php if($site['compress_css']) { ?> checked<?php } ?>>
                                <div class="input-message-block" data-for="compress_css"></div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-4 control-label">Объединение JS</label>
                            <div class="col-sm-8">
                                <input type="checkbox" name="glue_js"<?php if($site['glue_js']) { ?> checked<?php } ?>>
                                <div class="input-message-block" data-for="glue_js"></div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-4 control-label">Сжатие JS</label>
                            <div class="col-sm-8">
                                <input type="checkbox" name="compress_js"<?php if($site['compress_js']) { ?> checked<?php } ?>>
                                <div class="input-message-block" data-for="compress_js"></div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-loader"></div>
                    <div class="modal-footer">
                        <button class="btn btn-success btn-sm btn-save-site-icons" onclick="save_site_tech();" id="btn-save-site-tech"><i class="fa fa-check-circle"></i> Сохранить</button>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
    return ob_get_clean();
}

function get_regions_options($connect) {
    $direction_id = isset($_GET['direction_id'])?(int)$_GET['direction_id']:0;
    $regions = $connect->getAll("SELECT `id`, `name` FROM `region` WHERE `id_direction` = ?i", $direction_id);
    ob_start();
    ?>
    <option value="0">Без региона</option>
    <?php
    foreach ($regions as $region) {
      ?>
      <option value="<?=$region['id'];?>"><?=$region['name'];?></option>
      <?php
    }
    return ob_get_clean();
}

function get_regions_directions_options($connect) {
  $region_id = isset($_GET['region_id'])?(int)$_GET['region_id']:0;
  $regions_directions = $connect->getAll("SELECT `id`, `name` FROM `direction_object` WHERE `id_reg` = ?i", $region_id);
  ob_start();
  ?>
    <option value="0">Не выбрано</option>
  <?php
  foreach ($regions_directions as $region_direction) {
    ?>
      <option value="<?=$region_direction['id'];?>"><?=$region_direction['name'];?></option>
    <?php
  }
  return ob_get_clean();
}

function set_sites_content($connect) {
  $respAr = [
    'success' => 0,
    'title' => '',
    'msg' => ''
  ];

  $title = isset($_POST['title'])?trim($_POST['title']):"";
  $breadcrumb_title = isset($_POST['breadcrumb_title'])?trim($_POST['breadcrumb_title']):"";
  $title_h1 = isset($_POST['title_h1'])?trim($_POST['title_h1']):"";
  $photogallery_title = isset($_POST['photogallery_title'])?trim($_POST['photogallery_title']):"";
  $photogallery_orientation = isset($_POST['photogallery_orientation'])?trim($_POST['photogallery_orientation']):"album";

    $slider_mode = isset($_POST['slider_mode'])?(int)$_POST['slider_mode']:0;


    $title_h2 = isset($_POST['title_h2'])?trim($_POST['title_h2']):"";
  $path = isset($_POST['path'])?explode("?",trim($_POST['path']))[0]:"";
  $redirect_path = isset($_POST['redirect_path'])?explode("?",trim($_POST['redirect_path']))[0]:"";
  $form_action = isset($_POST['form_action'])?trim($_POST['form_action']):"";
  $description = isset($_POST['description'])?trim($_POST['description']):"";
  $body = isset($_POST['body'])?$_POST['body']:"";
  $body2 = isset($_POST['body2'])?$_POST['body2']:"";
   $phone = isset($_POST['phone'])?$_POST['phone']:"";

    $head_code = isset($_POST['head_code'])?$_POST['head_code']:"";
  $pre_body_code = isset($_POST['pre_body_code'])?$_POST['pre_body_code']:"";
  $post_body_code = isset($_POST['post_body_code'])?$_POST['post_body_code']:"";
  $map_code = isset($_POST['map_code'])?$_POST['map_code']:"";
  $landing_info = isset($_POST['landing_info'])?$_POST['landing_info']:"";
  $weight = isset($_POST['weight'])?(float)$_POST['weight']:0;
  $sort = isset($_POST['sort'])?(int)$_POST['sort']:0;
  $connect->query("SET CHARSET utf8");
  $direction_id = isset($_POST['direction_id'])?(int)$_POST['direction_id']:0;
  $region_id = isset($_POST['region_id'])?(int)$_POST['region_id']:0;
  $regional_direction_id = isset($_POST['regional_direction_id'])?(int)$_POST['regional_direction_id']:0;
  $aggregate_types_start = isset($_POST['aggregate_types'])?(array)$_POST['aggregate_types']:[];
  $main_page_fix = isset($_POST['main_page_fix'])?(int)$_POST['main_page_fix']:0;

  $aggregate_types = [];

  if($direction_id < 0) {
      $direction_id = 0;
  }

  if($region_id < 0 || $direction_id === 0) {
      $region_id = 0;
  }

  if($regional_direction_id < 0 || $region_id === 0) {
      $regional_direction_id = 0;
  }

  if($weight < 0)
      $weight = 0;

  if($weight > 1)
      $weight = 1;

  $summary = isset($_POST['summary'])?trim($_POST['summary']):"";
  $snippet_summary = isset($_POST['snippet_summary'])?trim($_POST['snippet_summary']):"";
  $keywords = isset($_POST['keywords'])?trim($_POST['keywords']):"";
  $site_id = isset($_POST['site_id'])?(int)$_POST['site_id']:0;
  $type = isset($_POST['type'])?trim($_POST['type']):"page";
  $rss = isset($_POST['rss'])?(int)$_POST['rss']:0;
  $rss_aggregator_link = isset($_POST['rss_aggregator_link'])?trim($_POST['rss_aggregator_link']):"";
  $rss_addition = isset($_POST['rss_addition'])?trim($_POST['rss_addition']):"";
  $rss_aggregation = isset($_POST['rss_aggregation'])?(int)$_POST['rss_aggregation']:0;
  $aggregation_date_start = (isset($_POST['aggregation_date_start']) && mb_strlen($_POST['aggregation_date_start']) > 0)?(strtotime($_POST['aggregation_date_start'])):0;
  $aggregation_date_end = (isset($_POST['aggregation_date_end']) && mb_strlen($_POST['aggregation_date_end']) > 0)?(strtotime($_POST['aggregation_date_end'])):0;
  $aggregation_by_dates = isset($_POST['aggregation_by_dates'])?(int)$_POST['aggregation_by_dates']:0;

  if(!in_array($aggregation_by_dates,[0,1])) {
      $aggregation_by_dates = 0;
  }

  $typesAr = ['page','news', 'module', 'landing', "photogallery", "settings", 'article', 'info', 'aggregator', 'advice', 'blog_post', 'redirect'];
  $photogallery_orientations = ['album', 'book'];


  if(in_array($type,['redirect'])) {
      $breadcrumb_title = "";
      $body = "";
      $description = "";
      $summary = "";
      $snippet_summary = "";
      $map_code = "";
      $keywords = "";
      $body2 = "";
      $title_h2 = "";
      $title_h1 = "";

      $head_code = '';
      $pre_body_code = '';
      $post_body_code = '';
      $phone = '';

      $contentRedirect = $connect->getRow("SELECT `redirect_path` FROM `sites_contents` WHERE `status` <> 2 AND `type` = 'redirect' AND `path` = ?s",$redirect_path);
      if($contentRedirect) {
          $redirect_path = $contentRedirect['redirect_path'];
      }

  }
  else {
      $redirect_path = "";
  }

  if(!in_array($type,['landing', 'settings', 'news', 'article', 'info', 'advice', 'blog_post', 'page'])) {
      $body2 = "";
  }

  if(!in_array($type,['news','article','info', 'advice', 'blog_post'])) {
      $direction_id = 0;
      $region_id = 0;
      $regional_direction_id = 0;
      $main_page_fix = 0;
  }

  if(!in_array($type,['photogallery','news', 'page','settings', 'article', 'info', 'advice', 'blog_post'])) {
     $rss_aggregation = 0;
  }

    if(!in_array($aggregate_types_start,[0,1]))
        $rss = 0;

  if(in_array($type,['aggregator'])) {
    foreach ($aggregate_types_start as $aggregate_types_start_item) {
      $aggregate_types_start_item = (int)$aggregate_types_start_item;
      if($aggregate_types_start_item > 0) {
          $aggregate_types[] = $aggregate_types_start_item;
      }
    }
    if(!$aggregation_by_dates) {
        $aggregation_date_end = 0;
        $aggregation_date_start = 0;
    }
  }
  else {
      $rss = 0;
      $rss_aggregator_link = "";
      $rss_addition = "";
      $aggregation_by_dates = 0;
      $aggregation_date_end = 0;
      $aggregation_date_start = 0;
  }

  if(!in_array($rss,[0,1]))
      $rss = 0;

  if(!$rss) {
      $rss_aggregator_link = "";
      $rss_addition = "";
  }

  $moduleBlocks = ["rooms","desc","promo","rating"];
  $timestamp = gmdate("U");
  $published = (isset($_POST['published']) && mb_strlen($_POST['published']) > 0)?(strtotime($_POST['published'])):$timestamp;
  $content_id = isset($_POST['content_id'])?(int)$_POST['content_id']:0;
  $status = isset($_POST['status'])?(int)$_POST['status']:0;
  $path_autogenerate = isset($_POST['path_autogenerate'])?(int)$_POST['path_autogenerate']:0;
  $second_bg = isset($_POST['second_bg'])?(int)$_POST['second_bg']:0;
  $module_object_id = isset($_POST['module_object_id'])?(int)$_POST['module_object_id']:0;
  $module_block = isset($_POST['module_block'])?mb_strtolower(trim($_POST['module_block'])):"";

  if($status !== 0 && $status !== 1)
      $status = 0;

  if($path_autogenerate !== 0 && $path_autogenerate !== 1)
      $path_autogenerate = 0;

    if(!in_array($type,['news','article','info', 'advice', 'blog_post'])) {
        $path_autogenerate = 0;
    }
    elseif($path_autogenerate && !$content_id) {
        $pathAr = [
          'news' => '/новости',
          'article' => '/статьи',
          'info'    => '/информация',
            'advice'    => '/советы',
            'blog_post' => '/блог'
        ];
        if(array_key_exists($type,$pathAr))
            $path = $pathAr[$type].'/'.change_text_url($title,'new');
        $i = 0;
        $orPath = $path;
        while($connect->getRow('SELECT `id` FROM `sites_contents` WHERE `path`=?s AND `site_id` = ?i AND `status` <> 2', $path, $site_id)) {
            $i++;
            $path = $orPath.'-'.$i;
        }
    }

    if(!in_array($type,['landing','settings'])) {
        $slider_mode = 0;
    }

    if(!in_array($slider_mode, [0, 1])) {
        $slider_mode = 0;
    }

  if(in_array($type,$typesAr) && in_array($photogallery_orientation,$photogallery_orientations) && (($module_object_id === 0 && $module_block === "" && $type !== 'module') || ($module_object_id > 0 && in_array($module_block,$moduleBlocks) && $type === 'module'))) {
    if($site_id) {
      if($title && $path) {

        if($content_id)
          $oldPath = $connect->getRow("SELECT `id` FROM `sites_contents` WHERE `path`=?s AND `id` <> ?i AND `site_id` = ?i AND `status` <> 2",$path,$content_id,$site_id);
        else
          $oldPath = $connect->getRow("SELECT `id` FROM `sites_contents` WHERE `path`=?s AND `site_id` = ?i AND `status` <> 2",$path,$site_id);

        if($oldPath) {
          $respAr['msg'] = "Указанный адрес уже используется";
          $respAr['msg_field'] = 'path';
        }
        else {
          if(!$module_object_id || $connect->getOne("SELECT `id` FROM `object` WHERE `id` = ?i",$module_object_id)) {
            if($content_id) {
              $respAr['success'] = 1;
              $respAr['msg'] = "Контент успешно обновлен";

              $content = $connect->getRow("SELECT `id`,`path`, `site_id` FROM `sites_contents` WHERE `id` = ?i AND `type` != 'redirect'",$content_id);

              $entity = [
                  'id' => $content_id,
                  'type' => 'content'
              ];

              $boundsArrayImage = [];

              if(!in_array($type,['redirect']))
                  $boundsArrayImage = files_to_bounds($connect,$entity,'image',isset($_POST['image'])?$_POST['image']:[]);


              $boundsArrayPhotogallery = [];

              if(in_array($type,['photogallery','landing','news', 'page', 'settings', 'article', 'info', 'advice', 'blog_post'])) {
                  $boundsArrayPhotogallery = files_to_bounds($connect,$entity,'photogallery',isset($_POST['photogallery'])?$_POST['photogallery']:[]);
              }

              $boundsArraySliderPhotos = [];
              $boundsArraySliderPhotosMobile = [];
              $boundsArrayPageBg = [];
              $boundsArrayReviewsObjects = [];
              $boundsArrayAggregateTypes = [];
              $boundsArrayResortsIds = [];

              if(in_array($type,['landing','settings'])) {
                $boundsArraySliderPhotos = files_to_bounds($connect,$entity,'slider_photos',isset($_POST['slider_photos'])?$_POST['slider_photos']:[]);
                $boundsArraySliderPhotosMobile = files_to_bounds($connect,$entity,'slider_photos_mobile',isset($_POST['slider_photos_mobile'])?$_POST['slider_photos_mobile']:[]);
                $boundsArrayPageBg = files_to_bounds($connect,$entity,'page_bg',isset($_POST['page_bg'])?$_POST['page_bg']:[]);
                $boundsArrayReviewsObjects = ids_to_bounds($connect,$entity,'reviews_objects',isset($_POST['reviews_objects'])?ids_string_to_ids($_POST['reviews_objects']):[]);
              }
              
              if(in_array($type,['aggregator'])) {
                  $boundsArrayAggregateTypes = ids_to_bounds($connect,$entity,'aggregate_types',$aggregate_types,'content_type');
              }

              if(in_array($type, ['news','article','info', 'advice', 'blog_post'])) {
                  $boundsArrayResortsIds = ids_to_bounds($connect, $entity, 'resorts_ids',isset($_POST['resorts_ids'])?ids_string_to_ids($_POST['resorts_ids']):[]);
              }

              remove_bounds($connect,$entity,'image');
              remove_bounds($connect,$entity,'page_bg');
              remove_bounds($connect,$entity,'photogallery');
              remove_bounds($connect,$entity,'slider_photos');
                remove_bounds($connect,$entity,'slider_photos_mobile');
              remove_bounds($connect,$entity,'reviews_objects');
              remove_bounds($connect,$entity,'aggregate_types');
              remove_bounds($connect,$entity, 'resorts_ids');
              set_bounds($connect,$boundsArrayImage,'image');
              set_bounds($connect,$boundsArrayPageBg,'page_bg');
              set_bounds($connect,$boundsArrayPhotogallery,'photogallery');
              set_bounds($connect,$boundsArraySliderPhotos,'slider_photos');
                set_bounds($connect,$boundsArraySliderPhotosMobile,'slider_photos_mobile');
              set_bounds($connect,$boundsArrayReviewsObjects,'reviews_objects');
              set_bounds($connect,$boundsArrayAggregateTypes,'aggregate_types');
              set_bounds($connect,$boundsArrayResortsIds, 'resorts_ids');


              $connect->query("UPDATE `sites_contents` SET `title`=?s, `slider_mode` = ?i, `title_h1`=?s, `title_h2` = ?s, `path`=?s, `redirect_path` = ?s, `description`=?s, `body`=?s, `body2` =?s, `summary`=?s, `snippet_summary`=?s, `keywords`=?s, `type`=?s, `changed`=?i, `published`=?i, `status`=?i, `synchronized`=?i, `weight` = ?s, `sort` = ?i, `module_object_id` = ?i, `module_block` =?s, `second_bg` = ?i, `form_action` = ?s, `map_code` = ?s, `landing_info` = ?s, `breadcrumb_title` = ?s, `photogallery_title` = ?s, `photogallery_orientation` = ?s, `direction_id` = ?i, `region_id` = ?i, `regional_direction_id` = ?i, `rss` = ?i, `rss_aggregator_link` = ?s, `rss_addition` = ?s, `rss_aggregation` = ?i, `main_page_fix` = ?i, `aggregation_by_dates` = ?i, `aggregation_date_start` = ?i, `aggregation_date_end` = ?i, `head_code` = ?s, `pre_body_code` = ?s, `post_body_code` = ?s, `phone` = ?s WHERE `id`=?i",$title, $slider_mode, $title_h1, $title_h2, $path, $redirect_path, $description, $body, $body2,$summary, $snippet_summary,$keywords,$type,$timestamp,$published,$status,0,$weight, $sort,$module_object_id,$module_block,$second_bg, $form_action, $map_code, $landing_info, $breadcrumb_title, $photogallery_title, $photogallery_orientation, $direction_id, $region_id, $regional_direction_id, $rss, $rss_aggregator_link, $rss_addition, $rss_aggregation, $main_page_fix, $aggregation_by_dates, $aggregation_date_start, $aggregation_date_end, $head_code, $pre_body_code, $post_body_code, $phone,$content_id);
              if($content && $content['path'] !== $path && $type !== 'redirect' && !($type === 'aggregator' && $rss)) {
                  $connect->query("INSERT INTO `sites_contents` (`title`, `title_h1`, `title_h2`, `path`, `redirect_path`, `description`, `body`, `body2`, `summary`, `keywords`, `type`, `changed`, `published`, `status`, `synchronized`, `site_id`, `created`, `weight`, `sort`, `module_object_id`, `module_block`, `second_bg`, `form_action`, `map_code`, `landing_info`, `breadcrumb_title`, `photogallery_title`, `photogallery_orientation`, `direction_id`, `region_id`, `regional_direction_id`) VALUES (?s, ?s, ?s, ?s, ?s, ?s, ?s, ?s, ?s, ?s, ?s, ?i, ?i, ?i, ?i, ?i, ?i, ?s, ?i, ?i, ?s, ?i, ?s, ?s, ?s, ?s, ?s, ?s, ?i, ?i, ?i)","Редирект", "", "", $content['path'], $path, "","","","","",'redirect',$timestamp,$published,1,0,$content['site_id'],$timestamp,0.9, 0,0, '', 0, '','', '', '', '', 'album', 0, 0, 0);
                  if($site_id == 38) {
                    $contentPathExpl = explode("/",$content['path']);
                    $newPathExpl = explode("/",$path);
                    if(count($contentPathExpl) > 2 && count($newPathExpl) > 2 && $contentPathExpl[1] === 'объект' && $newPathExpl[1] === 'направления') {
                      $connect->query("INSERT INTO `sites_contents` (`title`, `title_h1`, `title_h2`, `path`, `redirect_path`, `description`, `body`, `body2`, `summary`, `keywords`, `type`, `changed`, `published`, `status`, `synchronized`, `site_id`, `created`, `weight`, `sort`, `module_object_id`, `module_block`, `second_bg`, `form_action`, `map_code`, `landing_info`, `breadcrumb_title`, `photogallery_title`, `photogallery_orientation`, `direction_id`, `region_id`, `regional_direction_id`) VALUES (?s, ?s, ?s, ?s, ?s, ?s, ?s, ?s, ?s, ?s, ?s, ?i, ?i, ?i, ?i, ?i, ?i, ?s, ?i, ?i, ?s, ?i, ?s, ?s, ?s, ?s, ?s, ?s, ?i, ?i, ?i)","Редирект", "", "", $content['path'].'/отзывы', $path.'/отзывы', "","","","","",'redirect',$timestamp,$published,1,0,$content['site_id'],$timestamp,0.9, 0,0, '', 0, '','', '', '', '', 'album', 0, 0, 0);
                      $connect->query("INSERT INTO `sites_contents` (`title`, `title_h1`, `title_h2`, `path`, `redirect_path`, `description`, `body`, `body2`, `summary`, `keywords`, `type`, `changed`, `published`, `status`, `synchronized`, `site_id`, `created`, `weight`, `sort`, `module_object_id`, `module_block`, `second_bg`, `form_action`, `map_code`, `landing_info`, `breadcrumb_title`, `photogallery_title`, `photogallery_orientation`, `direction_id`, `region_id`, `regional_direction_id`) VALUES (?s, ?s, ?s, ?s, ?s, ?s, ?s, ?s, ?s, ?s, ?s, ?i, ?i, ?i, ?i, ?i, ?i, ?s, ?i, ?i, ?s, ?i, ?s, ?s, ?s, ?s, ?s, ?s, ?i, ?i, ?i)","Редирект", "", "", $content['path'].'/акции', $path, "","","","","",'redirect',$timestamp,$published,1,0,$content['site_id'],$timestamp,0.9, 0,0, '', 0, '','', '', '', '', 'album', 0, 0, 0);
                      $connect->query("INSERT INTO `sites_contents` (`title`, `title_h1`, `title_h2`, `path`, `redirect_path`, `description`, `body`, `body2`, `summary`, `keywords`, `type`, `changed`, `published`, `status`, `synchronized`, `site_id`, `created`, `weight`, `sort`, `module_object_id`, `module_block`, `second_bg`, `form_action`, `map_code`, `landing_info`, `breadcrumb_title`, `photogallery_title`, `photogallery_orientation`, `direction_id`, `region_id`, `regional_direction_id`) VALUES (?s, ?s, ?s, ?s, ?s, ?s, ?s, ?s, ?s, ?s, ?s, ?i, ?i, ?i, ?i, ?i, ?i, ?s, ?i, ?i, ?s, ?i, ?s, ?s, ?s, ?s, ?s, ?s, ?i, ?i, ?i)","Редирект", "", "", $content['path'].'/описание', $path, "","","","","",'redirect',$timestamp,$published,1,0,$content['site_id'],$timestamp,0.9, 0,0, '', 0, '','', '', '', '', 'album', 0, 0, 0);
                    }

                  }

                  $connect->query("UPDATE `sites_contents` SET `redirect_path` = ?s, `synchronized` = 0 WHERE `type` = 'redirect' AND `status` <> 2 AND `redirect_path` = ?s",$path, $content['path']);

              }
            }
            else {
              $respAr['success'] = 1;
              $respAr['msg'] = "Контент успешно добавлен";
              $connect->query("INSERT INTO `sites_contents` (`title`, `slider_mode`, `title_h1`, `title_h2`, `path`, `redirect_path`, `description`, `body`, `body2`, `summary`, `snippet_summary`, `keywords`, `type`, `changed`, `published`, `status`, `synchronized`, `site_id`, `created`, `weight`, `sort`, `module_object_id`, `module_block`, `second_bg`, `form_action`, `map_code`, `landing_info`, `breadcrumb_title`, `photogallery_title`, `photogallery_orientation`, `direction_id`, `region_id`, `regional_direction_id`, `rss`, `rss_aggregator_link`, `rss_addition`, `rss_aggregation`, `main_page_fix`, `aggregation_by_dates`, `aggregation_date_start`, `aggregation_date_end`, `head_code`, `pre_body_code`, `post_body_code`, `phone`) VALUES (?s, ?i, ?s, ?s, ?s, ?s, ?s, ?s, ?s, ?s, ?s, ?s, ?s, ?i, ?i, ?i, ?i, ?i, ?i, ?s, ?i, ?i, ?s, ?i, ?s, ?s, ?s, ?s, ?s, ?s, ?i, ?i, ?i, ?i, ?s, ?s, ?i, ?i, ?i, ?i, ?i, ?s, ?s, ?s, ?s)",$title, $slider_mode, $title_h1, $title_h2, $path, $redirect_path, $description,$body,$body2,$summary, $snippet_summary, $keywords,$type,$timestamp,$published,$status,0,$site_id,$timestamp,$weight, $sort,$module_object_id, $module_block, $second_bg, $form_action, $map_code, $landing_info, $breadcrumb_title, $photogallery_title, $photogallery_orientation, $direction_id, $region_id, $regional_direction_id, $rss, $rss_aggregator_link, $rss_addition, $rss_aggregation, $main_page_fix, $aggregation_by_dates, $aggregation_date_start, $aggregation_date_end, $head_code, $pre_body_code, $post_body_code, $phone);

              $entity = [
                'id' => $connect->insertId(),
                'type' => 'content'
              ];

              $boundsArrayImage = files_to_bounds($connect,$entity,'image',isset($_POST['image'])?$_POST['image']:[]);;


              $boundsArrayPhotogallery = [];
              $boundsArrayPageBg = [];
              $boundsArrayReviewsObjects = [];
              $boundsArrayAggregateTypes = [];
              $boundsArrayResortsIds = [];

              if(in_array($type,['aggregator'])) {
                $boundsArrayAggregateTypes = ids_to_bounds($connect,$entity,'aggregate_types',$aggregate_types,'content_type');
              }


              if(in_array($type,['photogallery','landing','news', 'page', 'settings', 'article', 'info', 'advice', 'blog_post'])) {
                $boundsArrayPhotogallery = files_to_bounds($connect,$entity,'photogallery',isset($_POST['photogallery'])?$_POST['photogallery']:[]);
              }

              $boundsArraySliderPhotos = [];
                $boundsArraySliderPhotosMobile = [];

              if(in_array($type,['landing','settings'])) {
                $boundsArraySliderPhotos = files_to_bounds($connect,$entity,'slider_photos',isset($_POST['slider_photos'])?$_POST['slider_photos']:[]);
                  $boundsArraySliderPhotosMobile = files_to_bounds($connect,$entity,'slider_photos_mobile',isset($_POST['slider_photos_mobile'])?$_POST['slider_photos_mobile']:[]);

                  $boundsArrayPageBg = files_to_bounds($connect,$entity,'page_bg',isset($_POST['page_bg'])?$_POST['page_bg']:[]);
                $boundsArrayReviewsObjects = ids_to_bounds($connect,$entity,'reviews_objects',isset($_POST['reviews_objects'])?ids_string_to_ids($_POST['reviews_objects']):[]);
              }

              if(in_array($type, ['news','article','info', 'advice', 'blog_post'])) {
                  $boundsArrayResortsIds = ids_to_bounds($connect, $entity, 'resorts_ids',isset($_POST['resorts_ids'])?ids_string_to_ids($_POST['resorts_ids']):[]);
              }

              set_bounds($connect,$boundsArrayImage,'image');
              set_bounds($connect,$boundsArrayPageBg,'page_bg');
              set_bounds($connect,$boundsArrayPhotogallery,'photogallery');
              set_bounds($connect,$boundsArraySliderPhotos,'slider_photos');
                set_bounds($connect,$boundsArraySliderPhotosMobile,'slider_photos_mobile');

                set_bounds($connect,$boundsArrayReviewsObjects,'reviews_objects');
              set_bounds($connect,$boundsArrayAggregateTypes,'aggregate_types');
              set_bounds($connect,$boundsArrayResortsIds, 'resorts_ids');
            }
          }
          else {
            $respAr['msg'] = "Объект с указанным ID не существует";
            $respAr['msg_field'] = 'module_object_id';
          }
        }
      }
      else {
        $respAr['msg'] = "Некорректный заголовок или адрес страницы";
        $respAr['msg_field'] = 'title';
      }
    }
    else {
      $respAr['msg'] = "Некорректный ID сайта";
      $respAr['msg_field'] = 'title';
    }
  }
  else {
    $respAr['msg'] = "Некорректный типа материала";
    $respAr['msg_field'] = 'type';
  }

  return json_encode($respAr);
}

function sync_site_content($connect, $id):bool {
    $content = $connect->getRow("SELECT `id`, `status`, `published`, `type`, `site_id`, `title`, `title_h1`, `title_h2`, `summary`, `snippet_summary`, `body`, `body2`, `head_code`, `pre_body_code`, `post_body_code`, `path`, `redirect_path`, `description`, `keywords`, `weight`, `sort`, `module_object_id`, `module_block`, `second_bg`, `form_action`, `landing_info`, `map_code`, `breadcrumb_title`, `photogallery_title`, `photogallery_orientation`, `direction_id`, `region_id`, `regional_direction_id`, `rss`, `rss_aggregator_link`, `rss_addition`, `rss_aggregation`, `main_page_fix`, `aggregation_by_dates`, `aggregation_date_start`, `aggregation_date_end`, `phone`, `slider_mode` FROM `sites_contents` WHERE `id` =?i",$id);
    if($content) {
        try {
          $client = new \GuzzleHttp\Client();
          $content["token"] = '7db0d2680968f87e33dd3db9a4b5db38d373ba8a9f42ca7dc97d6f14711efaa4';
          $content["body"] = base64_encode($content["body"]);
          $res = $client->request('POST',"https://sites.tonia.ru/api/content/set/".$content['id'],[
            'form_params' => $content
          ]);

          $res = json_decode($res->getBody(),true);
          if(array_key_exists('success',$res)) {
            $success = (bool)(int)$res['success'];
            if($success) {
                $connect->query("UPDATE `sites_contents` SET `synchronized` = '1' WHERE `id` = ?i",$id);
            }
            return $success;
          }
          else
              return false;
        }
        catch (Exception $e) {
            return false;
        }
    }
    else return false;
}

function sync_files($connect) {
    $ret = true;
    while ($files = $connect->getAll("SELECT * FROM `core_models_file_file` WHERE `synchronized` = 0 LIMIT 0, 10")) {
      try {
        $client = new \GuzzleHttp\Client();
        $data = [];
        $data["token"] = '7db0d2680968f87e33dd3db9a4b5db38d373ba8a9f42ca7dc97d6f14711efaa4';
        $data["files_list"] = $files;
        $res = $client->request('POST',"https://sites.tonia.ru/api/files/set",[
          'form_params' => $data
        ]);

        $res = json_decode($res->getBody(),true);
        if(array_key_exists('success',$res)) {
          $success = (bool)(int)$res['success'];
          if($success) {
              foreach ($files as $file) {
                $connect->query("UPDATE `core_models_file_file` SET `synchronized` = '1' WHERE `id` = ?i", $file['id']);
              }
              $ret = $success;
          }
          else
              return false;
        }
        else
          return false;
      }
      catch (Exception $e) {
        return false;
      }
    }
    return $ret;
}

function sync_bounds($connect,$entity) {
    $bounds = $connect->getAll("SELECT * FROM `app_models_site_bound` WHERE `entity1_type` =?s AND `entity1_id` =?i ORDER BY `sort` ASC",$entity['type'],$entity['id']);
  try {
    $client = new \GuzzleHttp\Client();
    $data = [];
    $data["token"] = '7db0d2680968f87e33dd3db9a4b5db38d373ba8a9f42ca7dc97d6f14711efaa4';
    $data["bounds"] = $bounds;
    $res = $client->request('POST',"https://sites.tonia.ru/api/".$entity['type']."/".$entity['id']."/bounds/set",[
      'form_params' => $data
    ]);

    $res = json_decode($res->getBody(),true);
    if(array_key_exists('success',$res)) {
      return (bool)(int)$res['success'];
    }
    else
      return false;
  }
  catch (Exception $e) {
    return false;
  }
}

function sync_site($connect) {
    $respAr = [
      'title' => '',
      'msg' => '',
      'success' => 1
    ];
    $site_id = isset($_POST['site_id'])?(int)$_POST['site_id']:0;

    $contentTypes = $connect->getAll("SELECT * FROM `app_models_site_contenttype` WHERE `synchronized` = 0");

      foreach ($contentTypes as $contentType) {
        try {
          $client = new \GuzzleHttp\Client();
          $contentType["token"] = '7db0d2680968f87e33dd3db9a4b5db38d373ba8a9f42ca7dc97d6f14711efaa4';
          $res = $client->request('POST',"https://sites.tonia.ru/api/content/type/set/".$contentType['id'],[
            'form_params' => $contentType
          ]);

          $res = json_decode($res->getBody(),true);
          if(array_key_exists('success',$res)) {
            $respAr['success'] = $res['success'];
            $respAr['msg'] = $res['msg'];
            if($respAr['success']) {
                $connect->query("UPDATE `app_models_site_contenttype` SET `synchronized` = 1 WHERE `id` = ?i",$contentType['id']);
            }
            else {
                $respAr['msg'] = "Что-то пошло не так...";
                break;
            }
          }
          else {
            $respAr['success'] = 0;
            $respAr['msg'] = "Что-то пошло не так...";
            break;
          }
        }
        catch (Exception $e) {
          $respAr['success'] = 0;
          $respAr['msg'] = "Что-то пошло не так: ".$e->getMessage();
          break;
        }
      }


    if($respAr['success'])  {
      if($site_id) {
        $contents = $connect->getAll("SELECT `id` FROM `sites_contents` WHERE `site_id` = ?i AND `synchronized` = 0", $site_id);
        $sites = $connect->getAll("SELECT * FROM `sites` WHERE `id` = ?i LIMIT 1",$site_id);
      }
      else {
        $contents = $connect->getAll("SELECT `id` FROM `sites_contents` WHERE `synchronized` = 0");
        $sites = $connect->getAll("SELECT * FROM `sites`");
      }
      $respAr['success'] = 1;

      foreach ($contents as $content) {
        if(!sync_site_content($connect,$content['id'])) {
          $respAr['success'] = 0;
          $respAr['msg'] = "Что-то пошло не так...";
        }
        elseif (!sync_bounds($connect,[
          'type' => 'content',
          'id' => $content['id']
        ])) {
          $respAr['success'] = 0;
          $respAr['msg'] = "Что-то пошло не так...";
        }
      }

      if($respAr['success']) {
        foreach ($sites as $site) {
          try {
            $client = new \GuzzleHttp\Client();
            $site["token"] = '7db0d2680968f87e33dd3db9a4b5db38d373ba8a9f42ca7dc97d6f14711efaa4';
            $res = $client->request('POST',"https://sites.tonia.ru/api/site/set/".$site['id'],[
              'form_params' => $site
            ]);

            $res = json_decode($res->getBody(),true);
            if(array_key_exists('success',$res)) {
              $respAr['success'] = $res['success'];
              $respAr['msg'] = $res['msg'];
              if(!sync_bounds($connect,['type' => 'site', 'id' => $site['id']])) {
                throw new Exception("Bounds sync error");
              }
            }
            else {
              $respAr['success'] = 0;
              $respAr['msg'] = "Что-то пошло не так...";
              break;
            }
          }
          catch (Exception $e) {
            $respAr['success'] = 0;
            $respAr['msg'] = "Что-то пошло не так: ".$e->getMessage();
            break;
          }

          if($respAr['success']) {
            $addresses = $connect->getAll("SELECT * FROM `app_models_site_address` WHERE `site_id` = ?i", $site['id']);
            $res = $client->request('POST',"https://sites.tonia.ru/api/site/".$site['id']."/addresses/set",[
              'form_params' => [
                'addresses' => $addresses,
                'token' => '7db0d2680968f87e33dd3db9a4b5db38d373ba8a9f42ca7dc97d6f14711efaa4'
              ]
            ]);

            $res = json_decode($res->getBody(),true);
            if(array_key_exists('success',$res)) {
              $respAr['success'] = $res['success'];
              $respAr['msg'] = $res['msg'];
              if(!$respAr['success']) {
                break;
              }
            }
            else {
              $respAr['success'] = 0;
              $respAr['msg'] = "Что-то пошло не так...";
              break;
            }
          }

          if($respAr['success']) {
            $menu_items = $connect->getAll("SELECT * FROM `app_models_site_menu_item` WHERE `site_id` = ?i", $site['id']);
            $res = $client->request('POST',"https://sites.tonia.ru/api/site/".$site['id']."/menu/items/set",[
              'form_params' => [
                'menu_items' => $menu_items,
                'token' => '7db0d2680968f87e33dd3db9a4b5db38d373ba8a9f42ca7dc97d6f14711efaa4'
              ]
            ]);

            $res = json_decode($res->getBody(),true);
            if(array_key_exists('success',$res)) {
              $respAr['success'] = $res['success'];
              $respAr['msg'] = $res['msg'];
              if(!$respAr['success']) {
                break;
              }
            }
            else {
              $respAr['success'] = 0;
              $respAr['msg'] = "Что-то пошло не так...";
              break;
            }
          }

          if($respAr['success']) {
            $phones = $connect->getAll("SELECT * FROM `app_models_site_phone` WHERE `site_id` = ?i", $site['id']);
            $res = $client->request('POST',"https://sites.tonia.ru/api/site/".$site['id']."/phones/set",[
              'form_params' => [
                'phones' => $phones,
                'token' => '7db0d2680968f87e33dd3db9a4b5db38d373ba8a9f42ca7dc97d6f14711efaa4'
              ]
            ]);

            $res = json_decode($res->getBody(),true);
            if(array_key_exists('success',$res)) {
              $respAr['success'] = $res['success'];
              $respAr['msg'] = $res['msg'];
              if(!$respAr['success']) {
                break;
              }
            }
            else {
              $respAr['success'] = 0;
              $respAr['msg'] = "Что-то пошло не так...";
              break;
            }
          }

            if($respAr['success']) {
                $meta_templates = $connect->getAll("SELECT * FROM `app_models_site_page_meta_templates` WHERE `site_id` = ?i", $site['id']);

                foreach ($meta_templates as $meta_template) {
                    $res = $client->request('POST',"https://sites.tonia.ru/api/meta-templates/set/" . $meta_template['id'],[
                        'form_params' => [
                            'id' => $meta_template['id'],
                            'name' => $meta_template['name'],
                            'key' => $meta_template['key'],
                            'type' => $meta_template['type'],
                            'subtype' => $meta_template['subtype'],
                            'value' => $meta_template['value'],
                            'status' => $meta_template['status'],
                            'uid' => $meta_template['uid'],
                            'created' => $meta_template['created'],
                            'changed' => $meta_template['changed'],
                            'site_id' => $meta_template['site_id'],
                            'token' => '7db0d2680968f87e33dd3db9a4b5db38d373ba8a9f42ca7dc97d6f14711efaa4'
                        ]
                    ]);

                    $res = json_decode($res->getBody(),true);
                    if(array_key_exists('success',$res)) {
                        $respAr['success'] = $res['success'];
                        $respAr['msg'] = $res['msg'];
                        if(!$respAr['success']) {
                            break;
                        }
                    }
                    else {
                        $respAr['success'] = 0;
                        $respAr['msg'] = "Что-то пошло не так...";
                        break;
                    }
                }
            }


        }

        if(!sync_files($connect)) {
          $respAr['success'] = 0;
          $respAr['msg'] = "Что-то пошло не так...";
        }
      }
    }

    return json_encode($respAr);
}

function check_status_news($connect){
	$id = $_POST["id"];
	$check = 0;
	$active = $connect->getOne("SELECT active FROM news WHERE id=?i", $id);
	if($active == 0){
		$connect->query("UPDATE news SET active=1 WHERE id=?i", $id);
		$check = 1;
	}else
		$connect->query("UPDATE news SET active=0 WHERE id=?i", $id);
	return json_encode($check);
}

function edit_news($connect){
	$id = $_POST["id"];
	$row = $connect->getRow("SELECT DATE_FORMAT(date, '%d.%m.%Y') as post, title, text, url, website, image, description FROM news WHERE id=?i", $id);
	$data = array();
	$data["date"] = $row["post"];
	$data["title"] = $row["title"];
	$data["text"] = $row["text"];
	$data["url"] = $row["url"];
	$data["image"] = $row["image"];
	$data["description"] = $row["description"];
	if(!$row["image"])
		$data["image"] = "";
	$data["website"] = $connect->getOne("SELECT url FROM st_website WHERE id=?i", $row["website"]);
	return json_encode($data);
}

function update_news($connect){
	$id = $_POST["id"];
	$title = $_POST["title"];
	$text = $_POST["text"];
	$image = $_POST["image"];
	$desc = $_POST["desc"];
	$connect->query("UPDATE news SET title=?s, text=?s, image=?s, description=?s WHERE id=?i", $title, $text, $image, $desc, $id);
}

function upload_news_website($connect){
	$website = $_POST["id"];
	$url = $connect->getOne("SELECT url FROM st_website WHERE id=?i", $website);
	if($url == "romashkino.com")
		$url = "romashkino.com";
	if($url == "санаторий-дубки.рф")
		$url = "курорт-ундоры.рф";
	if($url == "санаторий-ленина.рф")
		$url = "санаторий-ундоры.рф";
	if($url == "саната-тревел.рф")
		$url = "саната-тревел.рф";
	$request = idn_to_ascii($url)."/core/update.website.php";
	$data = $connect->getAll("SELECT title, text, url, image, DATE_FORMAT(date, '%d.%m.%Y') as date_post, description FROM news WHERE website=?i AND active=1 ORDER BY date DESC", $website);

	foreach($data as $index => $row) {
    $data[$index]["url"] = mb_strtolower($row["url"], "UTF-8");
	}

	$params = array(
		"password" => "jgnbvbpfwbz",
		"update" => "news",
		"data" => json_encode($data)
	);

	$result = request_to_url($request, $params);
	return $result;
}

function upload_images_website($connect){
	global $directory;
	$website = $_POST["id"];
	$url = $connect->getOne("SELECT url FROM st_website WHERE id=?i", $website);
	if($url == "romashkino.com")
		$url = "romashkino.com";
	if($url == "санаторий-дубки.рф")
		$url = "курорт-ундоры.рф";
	if($url == "санаторий-ленина.рф")
		$url = "санаторий-ундоры.рф";
	if($url == "саната-тревел.рф")
		$url = "саната-тревел.рф";
	$request = idn_to_ascii($url)."/core/update.website.php";
	$folder = $directory."/temp/news/".$website;
	$open = opendir($folder);
	$images = array();
	while($image = readdir($open)){
		if(($image != ".") AND ($image != "..") AND ($image)){
			$new = array();
			$new["name"] = $image;
			$new["code"] = base64_encode(file_get_contents($folder."/".$image));
			$images[] = $new;
			unlink($folder."/".$image);
		}
	}
	$params = array(
		"password" => "jgnbvbpfwbz",
		"update" => "images",
		"data" => json_encode($images)
	);
	return request_to_url($request, $params);
}

function upload_price_website($connect){
	$website = $_POST["id"];
	$row = $connect->getRow("SELECT url, id_reg FROM st_website WHERE id=?i", $website);
	$url = $row["url"];
	$region = $row["id_reg"];
	$request = idn_to_ascii($url)."/core/update.website.php";
	$array = array();
	$data = $connect->getAll("SELECT id, active FROM object WHERE id_reg=?i", $region);
	foreach($data as $row){
		$id = $row["id"];
		$array[$id] = "";
		if($row["active"] == 0){
			$value = get_prices_object($connect, $id);
			if($value["min"] > 0)
				$array[$id] = $value["min"];
		}
	}
	$params = array(
		"password" => "jgnbvbpfwbz",
		"update" => "price",
		"data" => json_encode($array)
	);
	return request_to_url($request, $params);
}

?>
