<?php
require_once __DIR__.'/../../vendor/autoload.php';
$directory = dirname(__FILE__)."/../..";

include_once($directory."/config.php");
$conf = new JConfig;
$sync = $conf->sync_base;
include_once($directory."/core/functions.php");
include_once($directory."/core/lib/Mysql.Class.php");
include_once $directory."/core/upload/price.php";
include_once $directory."/core/upload/default.php";
include_once($directory."/core/admin/news.php");
include_once($directory."/core/upload/sync-objects-api.php");
$connect = connect_to_MySQL_directory();

$objects = $connect->getAll("SELECT `object`.`id` AS `id`, `object`.`name` AS `name`, `object`.`url_name` AS `url_name`, `object`.`id_reg` AS `region_id`, `object`.`region_direction_id` AS `region_direction_id`, `object`.`direction` AS `direction`, `object`.`active` AS `active`, `object`.`note` AS `note`, `object`.`type` AS `type`, `object`.`full_name` AS `full_name`, `object`.`address` AS `address`, `object`.`telephone` AS `telephone`, `object`.`email` AS `email`, `object`.`id_profile` AS `id_profile`, `object`.`id_methods` AS `id_methods`, `object`.`id_infa` AS `id_infa`, `object`.`check_places` AS `check_places`, `object`.`default_price_type` AS `default_price_type`, `object`.`description` AS `description`, (`object`.`image` IS NOT NULL) AS `has_thumbnail`, `object`.`image` AS `image_cont`, `type_object`.`name` AS `type_name` FROM `object` LEFT JOIN `type_object` ON `object`.`type` = `type_object`.`id` AND `object`.`type` IS NOT NULL AND `object`.`id_reg` > 0");

foreach ($objects as $object) {
  if(mb_strlen($object['url_name']) > 0) {
    $uri = '/объект/' . $object['url_name'];
    $content = $connect->getRow("SELECT id FROM `sites_contents` WHERE `path` = ?s AND `type` = 'settings' AND `site_id` = 38",$uri);
    if(mb_strlen($object['image_cont']) > 10) {
      $timestamp = gmdate("U");
      file_put_contents($directory.'/temp/object_file.tmp',base64_decode($object['image_cont']));
      $imageRes = multipart_upload($connect,$directory.'/temp/object_file.tmp');
      if($content) {
        $content_id = $content['id'];
      }
      else {
        if(is_null($object['type_name'])) {
          $object['type_name'] = 'Санаторий';
        }
        $connect->query("INSERT INTO `sites_contents` (`status`,`created`, `changed`, `published`, `synchronized`, `type`, `site_id`, `title`, `title_h1`, `title_h2`,`summary`,`body`,`body2`,`path`,`description`,`keywords`,`weight`,`module_object_id`,`module_block`,`second_bg`,`form_action`,`landing_info`,`map_code`,`photogallery_title`,`photogallery_orientation`,`breadcrumb_title`) VALUES (1,".$timestamp.",".$timestamp.",".$timestamp.",0,'settings',38,?s,'','','','','',?s,'','',0.90,0,'',0,'','','','','album',?s)",$object["type_name"]." «".$object["name"]."»",$uri,$object["type_name"]." «".$object["name"]."»");
        $content_id = $connect->insertId();
      }

      if(is_array($imageRes) && array_key_exists('id',$imageRes) && $imageRes['id'] > 0 && $content_id > 0) {
        $connect->query("DELETE FROM `app_models_site_bound` WHERE `entity1_type` = 'content' AND `entity1_id` = ?i AND `name` = 'image'",$content_id);
        $connect->query("INSERT INTO `app_models_site_bound` (`created`, `changed`,`status`,`uid`,`sort`,`name`,`entity1_type`,`entity1_id`,`entity2_type`,`entity2_id`,`title`,`description`) VALUES (".$timestamp.",".$timestamp.",1,1,0,'image','content',?i,'file',?i,'','')",$content_id,$imageRes['id']);
        if($connect->insertId()) {
          $connect->query("UPDATE `core_models_file_file` SET `usages` = `usages`+1 WHERE `id` = ?i",$imageRes['id']);
        }
        else {
          echo 'Что-то пошло не так...';
          break;
        }
      }

    }
  }
}