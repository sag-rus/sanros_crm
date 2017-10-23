#!/bin/bash

DIR="/mnt/media/www/html/CRM/core/sync/file"
LOCK_FILE="${DIR}/kill.txt"

while [ 1 ]
do

	if test -f $LOCK_FILE;
		then

			rsync -crlOt ${DIR}/rsync/answer/ rsync://tonia.ru/sync-answer
			rsync -crlOt rsync://tonia.ru/sync-request ${DIR}/rsync/request

		else

			exit 0

		fi

done
