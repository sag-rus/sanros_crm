<?php

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
                  <button class="btn btn-default btn-sm"><i class="fa fa-trash-o"></i></button>
                  <button class="btn btn-default btn-sm"><i class="fa fa-pencil"></i></button>
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
  $site_id = isset($_POST['site_id'])?(int)$_POST['site_id']:0;
  $site = NULL;
  if($site_id) {
    $site = $connect->getRow("SELECT `id`, `name`, `url` FROM `sites` WHERE `id`=?i",$site_id);
    if($site)
        $sites_contents = $connect->getAll("SELECT id, title, published FROM `sites_contents` WHERE `site_id`=?i ORDER BY id ASC", $site_id);
    else
        $sites_contents = [];
  }
  else
      $sites_contents = $connect->getAll("SELECT id, title, published FROM `sites_contents` ORDER BY id ASC");

  ob_start();
  ?>
    <div class="panel panel-default">
        <div class="panel-heading"><i class="fa fa-list"></i> Материалы<?php if($site) { ?>сайта «<?=$site['name'];?>»<?php } ?></div>
        <div class="panel-body">
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
                    <tr>
                        <td><?=$sites_content['id'];?></td>
                        <td><?=$sites_content['title'];?></td>
                        <td>
                            <button class="btn btn-default btn-sm"><i class="fa fa-trash-o"></i></button>
                            <button class="btn btn-default btn-sm"><i class="fa fa-pencil"></i></button>
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
