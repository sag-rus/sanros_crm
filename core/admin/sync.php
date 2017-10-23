<?php 
	function sync_reboot() {
		$directory = dirname(__FILE__)."/../..";
		define("_FOLDERSITE_", $directory);
		$message = array();
		if(file_put_contents($directory . "/core/sync/file/time.txt", "")) {
			$message[] = 'Не удалось очистить файл time.txt';
		}

		$kill = $directory . "/core/sync/file/kill.txt";

		if(unlink($kill)) {
			sleep(20);

			$handler = fopen($kill, 'w+');
			fclose($handler);

			if(file_exists($kill)) {
				chmod($kill, 0777);
				$message[] = 'Синхронизация ЛК успешно перезапущена';
				?>
					<div class="alert alert-success" role="alert"><?=implode(', ', $message)?></div>
				<?php
			} else {
				$message[] = 'Не удалось перезапустить синхронизацию ЛК';
				?>
					<div class="alert alert-danger" role="alert"><?=implode(', ', $message)?></div>
				<?php
			}
		} else {
			$error = error_get_last(); 
			$message[] = 'Не удалось остановить синхронизацию ЛК<br />
					Ошибка:' . $error['message'];
			?>
				<div class="alert alert-danger" role="alert"><?=implode(', ', $message)?></div>
			<?php
		}
	}
?>