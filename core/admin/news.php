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
    global $id_rights;
	$sites = $connect->getAll("SELECT id, name, url FROM sites ORDER BY id ASC");
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
						URL
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
              <td><?=$site['url'];?></td>
              <td>
                  <button class="btn btn-default btn-sm" onclick="show_sites_contents_list(<?=$site['id'];?>)">Материалы</button>
                  <?php if($id_rights > 5)  { ?>
                      <button class="btn btn-default btn-sm"><i class="fa fa-trash-o"></i></button>
                      <button class="btn btn-default btn-sm"><i class="fa fa-pencil"></i></button>
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
			<button type="button" class="btn btn-primary btn-sm" onclick="add_new_site()"><i class="fa fa-plus-circle"></i> Добавить сайт</button>
		</div>
	</div>
	<?php
	return ob_get_clean();
}

function show_sites_contents_list($connect) {
  global $id_rights;
  $site_id = isset($_POST['site_id'])?(int)$_POST['site_id']:0;
  $site = NULL;
  if($site_id) {
    $site = $connect->getRow("SELECT `id`, `name`, `url` FROM `sites` WHERE `id`=?i",$site_id);
    if($site)
        $sites_contents = $connect->getAll("SELECT id, title, published, synchronized FROM `sites_contents` WHERE `site_id`=?i ORDER BY id ASC", $site_id);
    else
        $sites_contents = [];
  }
  else
      $sites_contents = $connect->getAll("SELECT id, title, published, synchronized FROM `sites_contents` ORDER BY id ASC");

  ob_start();
  ?>
    <div class="panel panel-default">
        <div class="panel-heading"><i class="fa fa-list"></i> Материалы<?php if($site) { ?> сайта «<?=$site['name'];?>»<?php } ?> <button class="btn btn-success btn-sm btn-sites-sync" onclick="sync_site(<?=($site?$site['id']:0);?>)">Синхронизировать</button></div>
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

function add_new_site($connect) {
    $respAr = [
      'success' => 0,
      'title' => '',
      'msg' => ''
    ];

    $siteName = isset($_POST['name'])?trim($_POST['name']):"";
    $siteUrl = isset($_POST['url'])?mb_strtolower(trim($_POST['url'])):"";

    if($siteName && $siteUrl) {
        $datetime = gmdate("U");
        $oldsite = $connect->getRow("SELECT `name`,`url` FROM `sites` WHERE `name`=?s OR `url`=?s LIMIT 1",$siteName,$siteUrl);
        if(!$oldsite) {
            $respAr['success'] = 1;
            $connect->query("INSERT INTO `sites` (`status`,`created`,`changed`,`name`,`url`) VALUES (1,?i,?i,?s,?s)", $datetime, $datetime, $siteName, $siteUrl);
        }
        else {
            if($oldsite['name'] === $siteName) {
              $respAr['msg'] = 'Сайт с таким названием уже есть';
              $respAr['msg_field'] = 'name';
            }
            elseif ($oldsite['url'] === $siteUrl) {
              $respAr['msg'] = 'Сайт с таким URL уже есть';
              $respAr['msg_field'] = 'url';
            }
        }
    }

    return json_encode($respAr);
}

function edit_sites_content($connect) {
  $content_id = isset($_POST['id'])?(int)$_POST['id']:0;
  $content = NULL;
  if($content_id)
      $content = $connect->getRow("SELECT `id`, `status`, `published`, `type`, `site_id`, `title`, `summary`, `body`, `path`, `description`, `keywords`, `image` FROM `sites_contents` WHERE `id` =?i",$content_id);
  ob_start();
  if($content) {
    ?>
      <div class="modal fade">
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
                              <input type="text" class="form-control" name="title" maxlength="255" value="<?=$content['title'];?>">
                              <input type="hidden" value="<?=$content['site_id'];?>" name="site_id">
                              <input type="hidden" value="<?=$content['id'];?>" name="content_id">
                              <div class="input-message-block" data-for="title"></div>
                          </div>
                      </div>
                      <div class="form-group">
                          <label class="col-sm-2 control-label">URL картинки</label>
                          <div class="col-sm-10">
                              <input type="text" class="form-control" name="image" value="<?=$content['image'];?>">
                              <div class="input-message-block" data-for="image"></div>
                          </div>
                      </div>
                      <div class="form-group">
                          <label class="col-sm-2 control-label">Адрес страницы</label>
                          <div class="col-sm-10">
                              <input type="text" class="form-control" name="path" value="<?=$content['path'];?>">
                              <div class="input-message-block" data-for="path"></div>
                          </div>
                      </div>
                      <div class="form-group">
                          <label class="col-sm-2 control-label">Тип</label>
                          <div class="col-sm-10">
                              <select class="form-control" name="type">
                                  <option value="page"<?php if($content['type'] === 'page') {?> selected<?php } ?>>Страница</option>
                                  <option value="news"<?php if($content['type'] === 'news') {?> selected<?php } ?>>Новость</option>
                              </select>
                              <div class="input-message-block" data-for="type"></div>
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
                              <textarea class="form-control" name="keywords"><?=$content['keywords'];?></textarea>
                          </div>
                      </div>
                      <div class="form-group">
                          <label class="col-sm-2 control-label">Анонс</label>
                          <div class="col-sm-10">
                              <textarea class="form-control" name="summary"><?=$content['summary'];?></textarea>
                          </div>
                      </div>
                      <div class="form-group">
                          <label class="col-sm-2 control-label">Содержимое</label>
                          <div class="col-sm-10">
                              <textarea class="form-control resizable-textarea" name="body" id="sites_content_body"><?=$content['body'];?></textarea>
                              </div>
                          </div>
                      <div class="form-group">
                          <label class="col-sm-2 control-label">Дата и время публикации</label>
                          <div class="col-sm-10">
                              <input type="datetime-local" name="published" class="form-control" value="<?=date("Y-m-d\TH:i",$content['published']+3600*3);?>">
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

function set_sites_content($connect) {
  $respAr = [
    'success' => 0,
    'title' => '',
    'msg' => ''
  ];

  $title = isset($_POST['title'])?trim($_POST['title']):"";
  $path = isset($_POST['path'])?trim($_POST['path']):"";
  $description = isset($_POST['description'])?trim($_POST['description']):"";
  $body = isset($_POST['body'])?$_POST['body']:"";
  $connect->query("SET CHARSET utf8");
  ///$body = $body;
  $summary = isset($_POST['summary'])?trim($_POST['summary']):"";
  $keywords = isset($_POST['keywords'])?trim($_POST['keywords']):"";
  $site_id = isset($_POST['site_id'])?(int)$_POST['site_id']:0;
  $image = isset($_POST['image'])?trim($_POST['image']):"";
  $type = isset($_POST['type'])?trim($_POST['type']):"page";
  $typesAr = ['page','news'];
  $timestamp = gmdate("U");
  $published = isset($_POST['published'])?(strtotime($_POST['published'])-3600*3):$timestamp;
  $content_id = isset($_POST['content_id'])?(int)$_POST['content_id']:0;
  $status = isset($_POST['status'])?(int)$_POST['status']:0;

  if($status !== 0 && $status !== 1)
      $status = 0;

  if(in_array($type,$typesAr)) {
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
          if($content_id) {
            $respAr['success'] = 1;
            $respAr['msg'] = "Контент успешно обновлен";
            $connect->query("UPDATE `sites_contents` SET `title`=?s, `path`=?s, `description`=?s, `body`=?s, `summary`=?s, `keywords`=?s, `image`=?s, `type`=?s, `changed`=?i, `published`=?i, `status`=?i, `synchronized`=?i WHERE `id`=?i",$title,$path,$description,$body,$summary,$keywords,$image,$type,$timestamp,$published,$status,0,$content_id);
          }
          else {
            $respAr['success'] = 1;
            $respAr['msg'] = "Контент успешно добавлен";
            $connect->query("INSERT INTO `sites_contents` (`title`, `path`, `description`, `body`, `summary`, `keywords`, `image`, `type`, `changed`, `published`, `status`, `synchronized`, `site_id`, `created`) VALUES (?s,?s,?s,?s,?s,?s,?s,?s,?i,?i,?i,?i,?i,?i)",$title,$path,$description,$body,$summary,$keywords,$image,$type,$timestamp,$published,$status,0,$site_id,$timestamp);
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
    $content = $connect->getRow("SELECT `id` AS `source_id`, `status`, `published`, `type`, `site_id`, `title`, `summary`, `body`, `path`, `description`, `keywords`, `image` FROM `sites_contents` WHERE `id` =?i",$id);
    if($content) {
        try {
          $client = new \GuzzleHttp\Client();
          $content["token"] = '7db0d2680968f87e33dd3db9a4b5db38d373ba8a9f42ca7dc97d6f14711efaa4';
          $res = $client->request('POST',"https://sites.tonia.ru/api/content/set/".$content['source_id'],[
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

function sync_site($connect) {
    $respAr = [
      'title' => '',
      'msg' => '',
      'success' => 0
    ];
    $site_id = isset($_POST['site_id'])?(int)$_POST['site_id']:0;
    if($site_id)
        $contents = $connect->getAll("SELECT `id` FROM `sites_contents` WHERE `site_id` = ?i AND `synchronized` = 0",$site_id);
    else
        $contents = $connect->getAll("SELECT `id` FROM `sites_contents` WHERE `synchronized` = 0");

    $respAr['success'] = 1;

    foreach ($contents as $content) {
        if(!sync_site_content($connect,$content['id'])) {
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
