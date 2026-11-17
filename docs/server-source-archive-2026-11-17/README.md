# Архив серверных исходников CRM от 17.11.2026

Пять патчей сохраняют отличия дополнительных файлов от версий в истории master. Все восстановленные тексты используют LF. В двух тестовых скриптах реальные идентификаторы заявки и сопоставлений Bnovo заменены примерами.

| Серверный файл | База для восстановления | Патч |
|---|---|---|
| `core/admin/5` | `4372279:core/admin/rating.php` | `5.patch` |
| `core/price_tonia_live.php.orig` | `3c9e2b4:core/price_tonia_live.php` | `price_tonia_live.php.orig.patch` |
| `core/reckoning.php.orig` | `9d7437c:core/reckoning.php` | `reckoning.php.orig.patch` |
| `core/cron/rating-test.php` | `92b8236:core/cron/rating.php` | `rating-test.php.patch` |
| `bnovo_save_matches_test.php` | `bf462edf:bnovo_save_matches.php` | `bnovo_save_matches_test.php.patch` |

Пример восстановления панели PRICE во временном каталоге из корня репозитория:

```sh
archive_dir="$PWD/docs/server-source-archive-2026-11-17"
restore_dir=$(mktemp -d)
mkdir -p "$restore_dir/core"
git show 3c9e2b4:core/price_tonia_live.php > "$restore_dir/core/price_tonia_live.php.orig"
(cd "$restore_dir" && git apply --unidiff-zero "$archive_dir/price_tonia_live.php.orig.patch")
```

Три дополнительные копии сохранены побайтно в истории master. Команда `git cat-file blob <хеш>` возвращает полный файл.

| Серверный файл | Хеш объекта Git |
|---|---|
| `core/admin/.php` | `188db31d6e1f5c9b47f229d73c3238066c5478b3` |
| `core/sync/check-cabinet1.php` | `cf956993ef4a3a7fc5f34aefac583e849a006052` |
| `modules/module-admin1.js` | `fab27c9c1fec9d9ac2b7f30646ef583df11b671d` |
