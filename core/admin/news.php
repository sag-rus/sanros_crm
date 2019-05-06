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
              <td><a href="//<?=idn_to_utf8($site['domain']);?>" target="_blank"><?=idn_to_utf8($site['domain']);?></a></td>
              <td>
                  <button class="btn btn-default btn-sm" onclick="show_sites_contents_list(<?=$site['id'];?>);">Материалы</button>
                  <button class="btn btn-default btn-sm" onclick="show_sites_addresses_list(<?=$site['id'];?>);">Адреса</button>
                  <button class="btn btn-default btn-sm" onclick="show_sites_menu_items_list(<?=$site['id'];?>);">Элементы меню</button>
                  <button class="btn btn-default btn-sm" onclick="show_sites_phones_list(<?=$site['id'];?>);">Телефоны</button>
                  <?php if($id_rights > 5)  { ?>
                      <button class="btn btn-default btn-sm"><i class="fa fa-trash-o"></i></button>
                  <?php } ?>
                  <?php if($id_rights > 4 || $session_login == 62)  { ?>
                      <button class="btn btn-default btn-sm" onclick="edit_site(<?=$site['id'];?>);"><i class="fa fa-pencil"></i></button>
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
  $content_types = [
    'landing' => 'Лэндинг',
    'photogallery' => 'Фотогалерея',
    'news' => 'Новость',
    'page' => 'Страница',
    'module' => 'Модуль бронирования',
    'settings' => 'Настройки'
  ];
  $site_id = isset($_POST['site_id'])?(int)$_POST['site_id']:0;
  $site = NULL;
  if($site_id) {
    $site = $connect->getRow("SELECT `id`, `name`, `domain` FROM `sites` WHERE `id`=?i",$site_id);
    if($site)
        $sites_contents = $connect->getAll("SELECT id, title, published, synchronized, type, status FROM `sites_contents` WHERE `site_id`=?i ORDER BY id ASC", $site_id);
    else
        $sites_contents = [];
  }
  else
      $sites_contents = $connect->getAll("SELECT id, title, published, synchronized, type, status FROM `sites_contents` ORDER BY id ASC");

  ob_start();
  ?>
    <div class="panel panel-default">
        <div class="panel-heading"><i class="fa fa-list"></i> Материалы<?php if($site) { ?> сайта «<?=$site['name'];?>»<?php } ?> <button class="btn btn-success btn-sm btn-sites-sync" onclick="sync_site(<?=($site?$site['id']:0);?>)">Синхронизировать</button> <button class="btn btn-default btn-sm" onclick="show_sites_list();">К списку сайтов</button></div>
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
                        <td>
                            <?php if($id_rights > 5) { ?>
                                <button class="btn btn-default btn-sm"><i class="fa fa-trash-o"></i></button>
                            <?php } ?>
                            <button class="btn btn-default btn-sm" onclick="edit_sites_content(<?=$sites_content['id'];?>)"><i class="fa fa-pencil"></i></button>
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
        'sanrussia'
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

                $connect->query("UPDATE `sites` SET `name` = ?s, `branding_name` = ?s, `branding_slogan` = ?s, `domain` = ?s, `main_bg_color` = ?s, `main_bg_color2` = ?s, `main_font_color` = ?s, `main_font_color2` = ?s, `main_link_color` = ?s, `head_code` =?s, `pre_body_code` =?s, `post_body_code` =?s, `robots` = ?s, `interface_style` = ?i, `type` = ?s, `direction_id` = ?i, `region_id` = ?i, `theme` = ?s WHERE `id`=?i",$siteName,$branding_name,$branding_slogan,$siteDomain,$main_bg_color,$main_bg_color2,$main_font_color,$main_font_color2,$main_link_color,$head_code, $pre_body_code, $post_body_code,$robots,$interface_style, $type, $direction_id, $region_id, $theme, $id);
            }
            else {
                $connect->query("INSERT INTO `sites` (`status`,`created`,`changed`,`name`, `branding_name`, `branding_slogan`, `domain`,`main_bg_color`,`main_bg_color2`,`main_font_color`,`main_font_color2`,`main_link_color`,`head_code`, `pre_body_code`, `post_body_code`, `robots`, `interface_style`, `type`, `direction_id`, `region_id`, `theme`) VALUES (1, ?i, ?i, ?s, ?s, ?s, ?s, ?s, ?s, ?s, ?s, ?s, ?s, ?s, ?s, ?s, ?i, ?s, ?i, ?i, ?s)", $datetime, $datetime, $siteName, $branding_name, $branding_slogan, $siteDomain,$main_bg_color,$main_bg_color2,$main_font_color,$main_font_color2,$main_link_color,$head_code, $pre_body_code, $post_body_code, $robots, $interface_style, $type, $direction_id, $region_id, $theme);

                $entity = [
                    'id' => $connect->insertId(),
                    'type' => 'site'
                ];

                $boundsArrayFavicon = files_to_bounds($connect,$entity,'favicon',isset($_POST['favicon'])?$_POST['favicon']:[]);
                set_bounds($connect,$boundsArrayFavicon,'favicon');
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

function edit_sites_content($connect) {
  $content_id = isset($_POST['id'])?(int)$_POST['id']:0;
  $content = NULL;
  if($content_id)
      $content = $connect->getRow("SELECT `id`, `status`, `published`, `type`, `site_id`, `title`, `title_h2`, `summary`, `body`, `body2`, `path`, `description`, `keywords`, `weight`, `module_object_id`, `module_block`, `second_bg`, `form_action`, `landing_info`, `map_code`, `photogallery_title`, `photogallery_orientation`, `breadcrumb_title` FROM `sites_contents` WHERE `id` =?i",$content_id);
      $entity = $content;
      $entity['type'] = 'content';
  ob_start();
  if($content) {
      if(!$content['module_object_id'])
          $content['module_object_id'] = NULL;
    ?>
      <div class="modal fade sites-content-modal">
          <div class="modal-dialog">
              <div class="modal-content">
                  <div class="modal-header">
                      <button type="button" class="close" data-dismiss="modal" aria-label="Close"><i class="fa fa-times"></i></button>
                      <h4 class="modal-title">Редактировать материал</h4>
                  </div>
                  <div class="modal-body form-horizontal site-name">
                      <div class="form-group">
                          <label class="col-sm-2 control-label">Заголовок</label>
                          <div class="col-sm-10">
                              <input type="text" class="form-control" name="title" maxlength="255" value="<?=htmlspecialchars($content['title']);?>">
                              <input type="hidden" value="<?=$content['site_id'];?>" name="site_id">
                              <input type="hidden" value="<?=$content['id'];?>" name="content_id">
                              <div class="input-message-block" data-for="title"></div>
                          </div>
                      </div>
                      <div class="form-group">
                          <label class="col-sm-2 control-label">Заголовок к крошкам</label>
                          <div class="col-sm-10">
                              <input type="text" class="form-control" name="breadcrumb_title" maxlength="255" value="<?=htmlspecialchars($content['breadcrumb_title']);?>">
                              <div class="input-message-block" data-for="breadcrumb_title"></div>
                          </div>
                      </div>
                      <div class="form-group">
                          <label class="col-sm-2 control-label">Основная картинка</label>
                          <div class="col-sm-10">
                              <input type="file" class="form-control" name="image" value="<?=htmlspecialchars(json_encode((object)bounds_to_files($connect,load_bounds($connect,$entity,'image'))));?>">
                              <div class="input-message-block" data-for="image"></div>
                          </div>
                      </div>
                      <div class="form-group">
                          <label class="col-sm-2 control-label">Адрес страницы</label>
                          <div class="col-sm-10">
                              <input type="text" class="form-control" name="path" value="<?=htmlspecialchars($content['path']);?>" maxlength="512">
                              <div class="input-message-block" data-for="path"></div>
                          </div>
                      </div>
                      <div class="form-group">
                          <label class="col-sm-2 control-label">Тип</label>
                          <div class="col-sm-10">
                              <select class="form-control" name="type">
                                  <option value="landing"<?php if($content['type'] === 'landing') {?> selected<?php } ?>>Лэндинг</option>
                                  <option value="page"<?php if($content['type'] === 'page') {?> selected<?php } ?>>Страница</option>
                                  <option value="news"<?php if($content['type'] === 'news') {?> selected<?php } ?>>Новость</option>
                                  <option value="photogallery"<?php if($content['type'] === 'photogallery') {?> selected<?php } ?>>Фотогалерея</option>
                                  <option value="module"<?php if($content['type'] === 'module') {?> selected<?php } ?>>Модуль бронирования</option>
                                  <option value="settings"<?php if($content['type'] === 'settings') {?> selected<?php } ?>>Настройки</option>
                              </select>
                              <div class="input-message-block" data-for="type"></div>
                          </div>
                      </div>
                      <div class="form-group<?php if(!in_array($content['type'],['landing','settings'])) { ?> hidden<?php } ?>">
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
                      <div class="form-group<?php if(!in_array($content['type'],['photogallery','landing','news', 'page','settings'])) { ?> hidden<?php } ?>">
                          <label class="col-sm-2 control-label">Фотографии</label>
                          <div class="col-sm-10">
                              <div class="input-message-block" data-for="photogallery"></div>
                              <input type="file" name="photogallery" value="<?=htmlspecialchars(json_encode((object)bounds_to_files($connect,load_bounds($connect,$entity,'photogallery'))));?>">
                          </div>
                      </div>
                      <div class="form-group<?php if(!in_array($content['type'],['photogallery','landing','news', 'page','settings'])) { ?> hidden<?php } ?>">
                          <label class="col-sm-2 control-label">Заголовок к фото</label>
                          <div class="col-sm-10">
                              <div class="input-message-block" data-for="photogallery"></div>
                              <input type="text" class="form-control" name="photogallery_title" value="<?=htmlspecialchars($content['photogallery_title']);?>">
                          </div>
                      </div>
                      <div class="form-group<?php if(!in_array($content['type'],['photogallery','landing','news', 'page','settings'])) { ?> hidden<?php } ?>">
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
                              <input type="file" name="slider_photos" value="<?=htmlspecialchars(json_encode((object)bounds_to_files($connect,load_bounds($connect,$entity,'slider_photos'))));?>">
                          </div>
                      </div>
                      <div class="form-group<?php if(!in_array($content['type'],['landing','settings'])) { ?> hidden<?php } ?>">
                          <label class="col-sm-2 control-label">Фото для фона</label>
                          <div class="col-sm-10">
                              <div class="input-message-block" data-for="page_bg"></div>
                              <input type="file" name="page_bg" value="<?=htmlspecialchars(json_encode((object)bounds_to_files($connect,load_bounds($connect,$entity,'page_bg'))));?>">
                          </div>
                      </div>
                      <div class="form-group<?php if(!in_array($content['type'],['landing','settings'])) { ?> hidden<?php } ?>">
                          <label class="col-sm-2 control-label">Двухуровневый фон</label>
                          <div class="col-sm-10">
                              <input type="checkbox" name="second_bg" class="form-control"<?php if($content['second_bg'] == 1) {?> checked<?php } ?>>
                          </div>
                      </div>
                      <div class="form-group">
                          <label class="col-sm-2 control-label">Мета-описание</label>
                          <div class="col-sm-10">
                              <textarea class="form-control" name="description"><?=$content['description'];?></textarea>
                          </div>
                      </div>
                      <div class="form-group">
                          <label class="col-sm-2 control-label">Ключевые слова (через запятую)</label>
                          <div class="col-sm-10">
                              <textarea class="form-control" name="keywords"><?=htmlspecialchars($content['keywords']);?></textarea>
                          </div>
                      </div>
                      <div class="form-group">
                          <label class="col-sm-2 control-label">Анонс</label>
                          <div class="col-sm-10">
                              <textarea class="form-control" name="summary"><?=htmlspecialchars($content['summary']);?></textarea>
                          </div>
                      </div>
                      <div class="form-group">
                          <label class="col-sm-2 control-label">Содержимое</label>
                          <div class="col-sm-10">
                              <textarea class="form-control resizable-textarea" name="body" id="sites_content_body"><?=htmlspecialchars($content['body']);?></textarea>
                          </div>
                      </div>
                      <div class="form-group<?php if(!in_array($content['type'],['landing','settings'])) { ?> hidden<?php } ?>">
                          <label class="col-sm-2 control-label">Доп. содержимое</label>
                          <div class="col-sm-10">
                              <textarea class="form-control resizable-textarea" name="body2" id="sites_content_body2"><?=htmlspecialchars($content['body2']);?></textarea>
                          </div>
                      </div>
                      <div class="form-group">
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
                          <label class="col-sm-2 control-label">Дата и время публикации</label>
                          <div class="col-sm-10">
                              <input type="datetime-local" name="published" class="form-control" value="<?=date("Y-m-d\TH:i",$content['published']+3600*3);?>">
                          </div>
                      </div>
                      <div class="form-group">
                          <label class="col-sm-2 control-label">Вес материала (для Sitemap)</label>
                          <div class="col-sm-10">
                              <input type="number" name="weight" class="form-control" value="<?=$content['weight'];?>">
                              <div class="input-message-block" data-for="weight"></div>
                          </div>
                      </div>
                      <div class="form-group">
                          <label class="col-sm-2 control-label">Опубликовано</label>
                          <div class="col-sm-10">
                              <input type="checkbox" name="status" class="form-control"<?php if($content['status'] == 1) {?> checked<?php } ?>>
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
      'room'
    ];

    $entity2_types = [
      'file',
      'object'
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
            'fid' => $bound['entity2_id'],
            'title' => $bound['title'],
            'description' => $bound['description'],
            'uri' => $file['uri'],
            'uri_thumbnail' => in_array($file['mime'],['image/png','image/jpeg'])?imageUriStyle($file['uri'],"thumbnail"):(in_array($file['mime'],['image/vnd.microsoft.icon','image/x-icon'])?$file['uri']:""),
            'uri_preview' => in_array($file['mime'],['image/png','image/jpeg'])?imageUriStyle($file['uri'],"preview"):(in_array($file['mime'],['image/vnd.microsoft.icon','image/x-icon'])?$file['uri']:""),
            'uri_large' => in_array($file['mime'],['image/png','image/jpeg'])?imageUriStyle($file['uri'],"large"):(in_array($file['mime'],['image/vnd.microsoft.icon','image/x-icon'])?$file['uri']:""),
            'mime' => $file['mime'],
            'ext' => $file['ext'],
            'cache' => substr(str_shuffle('0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz'), 1, 15)
          ];
        }
    }
    return $filesAr;
}

function ids_to_bounds($connect,$entity, String $name, array $ids):array
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
        'entity2_type' => 'resort',
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
        if($fileBase = $connect->getRow("SELECT * FROM `core_models_file_file` WHERE `id` =?i LIMIT 1",$file['fid'])) {
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

function multipart_upload($connect) {
  $respAr = [
    'msg' => "Неизвестная ошибка",
    'title' => 'Error',
    'success' => 0
  ];
  try {
    $client = new \GuzzleHttp\Client();
    $postCopy = $_POST;
    $postCopy["token"] = '7db0d2680968f87e33dd3db9a4b5db38d373ba8a9f42ca7dc97d6f14711efaa4';
    $respAr['partnum'] = (int)$_POST['partnum'];
    $respAr['plength'] = (int)$_POST['plength'];

    if($respAr['partnum'] == $respAr['plength']-1)
        $postCopy['used'] = 1;


    $multipart = [
      [
        'Content-type' => 'multipart/form-data',
        'name' => 'upload',
        'contents' => fopen($_FILES['upload']['tmp_name'],"r")
      ]
    ];

    foreach ($postCopy as $postKey => $postItem) {
      $multipart[] = [
        'name' => $postKey,
        'contents' => $postItem
      ];
    }


    $res = $client->request('POST',"https://cdn.tonia.ru/api/files/upload/multipart",[
      'multipart' => $multipart
    ]);

    $res = json_decode($res->getBody(),true);
    if(array_key_exists('success',$res)) {
      $respAr = $res;
      $respAr['success'] = (int)$respAr['success'];
      if(array_key_exists('loaded',$respAr))
          $respAr['loaded'] = (int)$respAr['loaded'];
      else
          $respAr['loaded'] = 0;

      if($respAr['loaded']) {
        $respAr['uri'] = 'https://cdn.tonia.ru'.$respAr['uri'];
        $connect->query("INSERT INTO `core_models_file_file` (`id`, `created`, `changed`, `status`, `uid`, `title`, `description`, `uri`, `mime`, `ext`, `usages`) VALUES (?i,?i,?i,?i,?i,?s,?s,?s,?s,?s,?i)",$respAr['fid'],$respAr['created'],$respAr['changed'],1,$respAr['uid'],'','',$respAr['uri'],$respAr['mime'],$respAr['ext'],0);
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
    $site = $connect->getRow("SELECT `id`, `status`, `name`, `branding_name`, `branding_slogan`,  `domain`, `main_bg_color`, `main_bg_color2`, `main_font_color`, `main_font_color2`, `main_link_color`, `head_code`, `pre_body_code`, `post_body_code`, `robots`, `interface_style`, `type`, `direction_id`, `region_id`, `theme` FROM `sites` WHERE `id` =?i",$id);
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
                              <input type="file" name="favicon" value="<?=htmlspecialchars(json_encode($site?(object)bounds_to_files($connect,load_bounds($connect,$entity,'favicon')):[]));?>">
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
  $photogallery_title = isset($_POST['photogallery_title'])?trim($_POST['photogallery_title']):"";
  $photogallery_orientation = isset($_POST['photogallery_orientation'])?trim($_POST['photogallery_orientation']):"album";
  $title_h2 = isset($_POST['title_h2'])?trim($_POST['title_h2']):"";
  $path = isset($_POST['path'])?trim($_POST['path']):"";
  $form_action = isset($_POST['form_action'])?trim($_POST['form_action']):"";
  $description = isset($_POST['description'])?trim($_POST['description']):"";
  $body = isset($_POST['body'])?$_POST['body']:"";
  $body2 = isset($_POST['body2'])?$_POST['body2']:"";
  $map_code = isset($_POST['map_code'])?$_POST['map_code']:"";
  $landing_info = isset($_POST['landing_info'])?$_POST['landing_info']:"";
  $weight = isset($_POST['weight'])?(float)$_POST['weight']:0;
  $connect->query("SET CHARSET utf8");

  if($weight < 0)
      $weight = 0;

  if($weight > 1)
      $weight = 1;

  $summary = isset($_POST['summary'])?trim($_POST['summary']):"";
  $keywords = isset($_POST['keywords'])?trim($_POST['keywords']):"";
  $site_id = isset($_POST['site_id'])?(int)$_POST['site_id']:0;
  $type = isset($_POST['type'])?trim($_POST['type']):"page";

  $typesAr = ['page','news', 'module', 'landing', "photogallery", "settings"];
  $photogallery_orientations = ['album', 'book'];

  if(!in_array($type,['landing', 'settings'])) {
      $body2 = "";
  }

  $moduleBlocks = ["rooms","desc","promo","rating"];
  $timestamp = gmdate("U");
  $published = isset($_POST['published'])?(strtotime($_POST['published'])-3600*3):$timestamp;
  $content_id = isset($_POST['content_id'])?(int)$_POST['content_id']:0;
  $status = isset($_POST['status'])?(int)$_POST['status']:0;
  $second_bg = isset($_POST['second_bg'])?(int)$_POST['second_bg']:0;
  $module_object_id = isset($_POST['module_object_id'])?(int)$_POST['module_object_id']:0;
  $module_block = isset($_POST['module_block'])?mb_strtolower(trim($_POST['module_block'])):"";

  if($status !== 0 && $status !== 1)
      $status = 0;

  if(in_array($type,$typesAr) && in_array($photogallery_orientation,$photogallery_orientations) && (($module_object_id === 0 && $module_block === "" && $type !== 'module') || ($module_object_id > 0 && in_array($module_block,$moduleBlocks) && $type === 'module'))) {
    if($site_id) {
      if($title && $path) {

        if($content_id)
          $oldPath = $connect->getRow("SELECT `id` FROM `sites_contents` WHERE `path`=?s AND `id` <> ?i AND `site_id` = ?i",$path,$content_id,$site_id);
        else
          $oldPath = $connect->getRow("SELECT `id` FROM `sites_contents` WHERE `path`=?s AND `site_id` = ?i",$path,$site_id);

        if($oldPath) {
          $respAr['msg'] = "Указанный адрес уже используется";
          $respAr['msg_field'] = 'path';
        }
        else {
          if(!$module_object_id || $connect->getOne("SELECT `id` FROM `object` WHERE `id` = ?i",$module_object_id)) {
            if($content_id) {
              $respAr['success'] = 1;
              $respAr['msg'] = "Контент успешно обновлен";

              $entity = [
                  'id' => $content_id,
                  'type' => 'content'
              ];

              $boundsArrayImage = files_to_bounds($connect,$entity,'image',isset($_POST['image'])?$_POST['image']:[]);


              $boundsArrayPhotogallery = [];

              if(in_array($type,['photogallery','landing','news', 'page', 'settings'])) {
                  $boundsArrayPhotogallery = files_to_bounds($connect,$entity,'photogallery',isset($_POST['photogallery'])?$_POST['photogallery']:[]);
              }

              $boundsArraySliderPhotos = [];
              $boundsArrayPageBg = [];
              $boundsArrayReviewsObjects = [];

              if(in_array($type,['landing','settings'])) {
                $boundsArraySliderPhotos = files_to_bounds($connect,$entity,'slider_photos',isset($_POST['slider_photos'])?$_POST['slider_photos']:[]);
                $boundsArrayPageBg = files_to_bounds($connect,$entity,'page_bg',isset($_POST['page_bg'])?$_POST['page_bg']:[]);
                $boundsArrayReviewsObjects = ids_to_bounds($connect,$entity,'reviews_objects',isset($_POST['reviews_objects'])?ids_string_to_ids($_POST['reviews_objects']):[]);
              }

              remove_bounds($connect,$entity,'image');
              remove_bounds($connect,$entity,'page_bg');
              remove_bounds($connect,$entity,'photogallery');
              remove_bounds($connect,$entity,'slider_photos');
              remove_bounds($connect,$entity,'reviews_objects');
              set_bounds($connect,$boundsArrayImage,'image');
              set_bounds($connect,$boundsArrayPageBg,'page_bg');
              set_bounds($connect,$boundsArrayPhotogallery,'photogallery');
              set_bounds($connect,$boundsArraySliderPhotos,'slider_photos');
              set_bounds($connect,$boundsArrayReviewsObjects,'reviews_objects');


              $connect->query("UPDATE `sites_contents` SET `title`=?s, `title_h2` = ?s, `path`=?s, `description`=?s, `body`=?s, `body2` =?s, `summary`=?s, `keywords`=?s, `type`=?s, `changed`=?i, `published`=?i, `status`=?i, `synchronized`=?i, `weight` = ?s, `module_object_id` = ?i, `module_block` =?s, `second_bg` = ?i, `form_action` = ?s, `map_code` = ?s, `landing_info` = ?s, `breadcrumb_title` = ?s, `photogallery_title` = ?s, `photogallery_orientation` = ?s WHERE `id`=?i",$title, $title_h2, $path,$description,$body, $body2,$summary,$keywords,$type,$timestamp,$published,$status,0,$weight,$module_object_id,$module_block,$second_bg, $form_action, $map_code, $landing_info, $breadcrumb_title, $photogallery_title, $photogallery_orientation, $content_id);
            }
            else {
              $respAr['success'] = 1;
              $respAr['msg'] = "Контент успешно добавлен";
              $connect->query("INSERT INTO `sites_contents` (`title`, `title_h2`, `path`, `description`, `body`, `body2`, `summary`, `keywords`, `type`, `changed`, `published`, `status`, `synchronized`, `site_id`, `created`, `weight`,`module_object_id`, `module_block`, `second_bg`, `form_action`, `map_code`, `landing_info`, `breadcrumb_title`, `photogallery_title`, `photogallery_orientation`) VALUES (?s, ?s, ?s, ?s, ?s, ?s, ?s, ?s, ?s, ?i, ?i, ?i, ?i, ?i, ?i, ?s, ?i, ?s, ?i, ?s, ?s, ?s, ?s, ?s, ?s)",$title, $title_h2, $path,$description,$body,$body2,$summary,$keywords,$type,$timestamp,$published,$status,0,$site_id,$timestamp,$weight,$module_object_id, $module_block, $second_bg, $form_action, $map_code, $landing_info, $breadcrumb_title, $photogallery_title, $photogallery_orientation);

              $entity = [
                'id' => $connect->insertId(),
                'type' => 'content'
              ];

              $boundsArrayImage = files_to_bounds($connect,$entity,'image',isset($_POST['image'])?$_POST['image']:[]);;


              $boundsArrayPhotogallery = [];
              $boundsArrayPageBg = [];
              $boundsArrayReviewsObjects = [];


              if(in_array($type,['photogallery','landing','news', 'page', 'settings'])) {
                $boundsArrayPhotogallery = files_to_bounds($connect,$entity,'photogallery',isset($_POST['photogallery'])?$_POST['photogallery']:[]);
              }

              $boundsArraySliderPhotos = [];

              if(in_array($type,['landing','settings'])) {
                $boundsArraySliderPhotos = files_to_bounds($connect,$entity,'slider_photos',isset($_POST['slider_photos'])?$_POST['slider_photos']:[]);
                $boundsArrayPageBg = files_to_bounds($connect,$entity,'page_bg',isset($_POST['page_bg'])?$_POST['page_bg']:[]);
                $boundsArrayReviewsObjects = ids_to_bounds($connect,$entity,'reviews_objects',isset($_POST['reviews_objects'])?ids_string_to_ids($_POST['reviews_objects']):[]);
              }

              set_bounds($connect,$boundsArrayImage,'image');
              set_bounds($connect,$boundsArrayPageBg,'page_bg');
              set_bounds($connect,$boundsArrayPhotogallery,'photogallery');
              set_bounds($connect,$boundsArraySliderPhotos,'slider_photos');
              set_bounds($connect,$boundsArrayReviewsObjects,'reviews_objects');

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
    $content = $connect->getRow("SELECT `id`, `status`, `published`, `type`, `site_id`, `title`, `title_h2`, `summary`, `body`, `body2`, `path`, `description`, `keywords`, `weight`, `module_object_id`, `module_block`, `second_bg`, `form_action`, `landing_info`, `map_code`, `breadcrumb_title`, `photogallery_title`, `photogallery_orientation` FROM `sites_contents` WHERE `id` =?i",$id);
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
      'success' => 0
    ];
    $site_id = isset($_POST['site_id'])?(int)$_POST['site_id']:0;
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

      }

      if(!sync_files($connect)) {
        $respAr['success'] = 0;
        $respAr['msg'] = "Что-то пошло не так...";
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
