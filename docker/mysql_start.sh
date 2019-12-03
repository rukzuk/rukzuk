#!/usr/bin/env bash
set -e

# check if server is already configured (dir needs to be empty)
if [ -n "$(find /var/lib/mysql/ -prune -empty 2>/dev/null)" ]; then
    # init mysql data and root user (no pw) (debian/ubuntu does this for us at installation, but we remove it)
    mysql_install_db --user=mysql
fi

# start mysql server
echo "mysql_start: start mysql server"
nohup mysqld_safe --console --user=mysql &
MYSQL_PID=$!

echo ${MYSQL_PID} > /var/lib/mysql/rz-mysql-init.pid
