#!/bin/bash
####################################
#
# Symlink any cronjobs ensuring they are executable
#
####################################


echo 'Insert and update crons'

cd $htdocs/cron/


echo $htdocs/cron/
echo $appid

stat -t $htdocs/cron/cron-* >/dev/null 2>&1 && chmod +x $htdocs/cron/cron-*
stat -t $htdocs/cron/*.sh >/dev/null 2>&1 && chmod +x $htdocs/cron/*.sh
test ! -e $htdocs/cron/cron-daily.sh || ln $htdocs/cron/cron-daily.sh /etc/cron.daily/$appid -sf
test ! -e $htdocs/cron/cron-hourly.sh || ln $htdocs/cron/cron-hourly.sh /etc/cron.hourly/$appid -sf
test ! -e $htdocs/cron/cron-weekly.sh || ln $htdocs/cron/cron-weekly.sh /etc/cron.weekly/$appid -sf

