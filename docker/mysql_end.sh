#!/usr/bin/env bash
set -e

#kill $(cat /var/lib/mysql/rz-mysql-init.pid)
echo "mysql_end: shutdown mysql"
mysqladmin -hlocalhost -uroot -p$(cat /var/lib/mysql/rz-mysql-root.pw) shutdown
rm /var/lib/mysql/rz-mysql-init.pid
