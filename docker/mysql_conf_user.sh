#!/usr/bin/env bash
set -e

# This requires the following env vars: 
##CMS_DB_TYPE=mysql
##CMS_MYSQL_USER=rukzuk
##CMS_MYSQL_PASSWORD=rukzuk
##CMS_MYSQL_DB=rukzuk

# check if server is already configured
if [ -f /var/lib/mysql/rz-mysql-root.pw ]; then
	exit 0 # end here
fi

# wait for mysql to be up
echo "wait for mysql."
while ! mysqladmin ping -h localhost --silent; do
    printf "."
    sleep 1
done
echo "ok"

# get or create password
MYSQL_ROOT_PW=${MYSQL_ROOT_PW:-$(cat /var/lib/mysql/rz-mysql-root.pw 2>/dev/null)} || \
MYSQL_ROOT_PW=$(od -An -N8 -x /dev/random | head -1 | tr -d ' ');

# Default query to lock down access and clean up
echo "mysql: set root password"
MYSQL_INIT="DELETE from mysql.user WHERE User = 'root'; GRANT ALL on *.* to 'root'@'localhost' identified by '${MYSQL_ROOT_PW}' with grant option; FLUSH PRIVILEGES;"
# non localhost: GRANT ALL on *.* to 'root'@'${PRIVATE_IP:-${PUBLIC_IP}}' identified by '${MYSQL_ROOT_PW}' with grant option;
mysql -h localhost -uroot -e"${MYSQL_INIT}"

# app user
echo "mysql: create ${CMS_MYSQL_USER} user and ${CMS_MYSQL_DB} db"
MYSQL_CREATE_DB="CREATE DATABASE ${CMS_MYSQL_DB}; CREATE USER '${CMS_MYSQL_USER}' IDENTIFIED BY '${CMS_MYSQL_PASSWORD}'; GRANT ALL ON ${CMS_MYSQL_DB}.* TO '${CMS_MYSQL_USER}'@localhost IDENTIFIED BY '${CMS_MYSQL_PASSWORD}'; FLUSH PRIVILEGES;"
mysql -h localhost -uroot -p${MYSQL_ROOT_PW} -e"${MYSQL_CREATE_DB}"

# update root pw file
echo ${MYSQL_ROOT_PW} > /var/lib/mysql/rz-mysql-root.pw

# set my.cnf for root user with proper password
cat /etc/root-my-cnf.tpl | \
    sed "s/##MYSQL_ROOT_PW##/${MYSQL_ROOT_PW}/" \
    > /root/.my.cnf
