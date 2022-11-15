#!/bin/bash
mysqldump -u root -h 10.10.10.10 -p1qaz@WSX site001_CRM1 > /home/ArhivSQL/`date +%Y%m%d-%H%M%S-%N`.sql
