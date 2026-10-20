#!/bin/sh
set -eu
[ "$(id -u)" = 0 ]
root=$(CDPATH= cd -- "$(dirname -- "$0")/.." && pwd)
install -o root -g root -m 0644 "$root/deploy/price-live-catalogue.cron" /etc/cron.d/price-live-catalogue-crm
install -o root -g root -m 0644 "$root/deploy/price-live-catalogue.logrotate" /etc/logrotate.d/price-live-catalogue-crm
