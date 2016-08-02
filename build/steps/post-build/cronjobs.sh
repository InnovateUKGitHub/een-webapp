#!/bin/bash
####################################
#
# Symlink any cronjobs ensuring they are executable
#
####################################

cd $htdocs/cron/

stat -t $htdocs/cron/cron-* >/dev/null 2>&1 && chmod +x $htdocs/cron/cron-*
stat -t $htdocs/cron/*.php >/dev/null 2>&1 && chmod +x $htdocs/cron/*.php
test ! -e $htdocs/cron/cron-daily || ln $htdocs/cron/cron-daily /etc/cron.daily/$appid -sf
test ! -e $htdocs/cron/cron-hourly || ln $htdocs/cron/cron-hourly /etc/cron.hourly/$appid -sf
test ! -e $htdocs/cron/cron-weekly || ln $htdocs/cron/cron-weekly /etc/cron.weekly/$appid -sf
