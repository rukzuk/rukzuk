#!/usr/bin/env bash
#set -e

# This requires the following env vars: 
##CMS_DB_TYPE=mysql
##CMS_MYSQL_USER=rukzuk
##CMS_MYSQL_PASSWORD=rukzuk
##CMS_MYSQL_DB=rukzuk

### or

##CMS_DB_TYPE=sqlite
##CMS_SQLITE_DB=/path/to/db.file

cat /etc/msmtprc.tpl | \
    sed "s/##HOST##/${SMTP_HOST}/" | \
    sed "s/##FROM##/${SMTP_FROM}/" | \
    sed "s/##USER##/${SMTP_USER}/" | \
    sed "s/##PASSWORD##/${SMTP_PASSWORD}/" \
    > /etc/msmtprc

# remove php sessions
find /var/lib/php/ -name "sess_*" -print -delete

# create cms data directory
if [ ! -e  "${INSTANCE_PATH}/htdocs/cms" ]; then
  mkdir -p "${INSTANCE_PATH}/htdocs/cms"
fi
chown -R www-data:www-data "${INSTANCE_PATH}/htdocs/cms"

CMS_DO_INIT="no"
if [ "a$CMS_DB_TYPE"  == "amysql" ]; then
    echo "rukzuk_init: wait for mysql"
    while ! mysqladmin ping -h localhost --silent; do
        printf "."
        sleep 1
    done
    echo "rukzuk_init: mysql is up"
    CMS_SET_USER_PW_CMD="sudo -E -u www-data mysql -h localhost -u ${CMS_MYSQL_USER} -p${CMS_MYSQL_PASSWORD} -D ${CMS_MYSQL_DB} -e "
    # check if db exists and has content
    if ! ${CMS_SET_USER_PW_CMD} "SHOW TABLES;" | grep "website" >/dev/null 2>&1; then 
        CMS_DO_INIT="yes";
    fi
else
    if [ ! -e  ${CMS_SQLITE_DB} ]; then
        CMS_DO_INIT="yes";
    fi
    CMS_SET_USER_PW_CMD="sudo -E -u www-data sqlite3 ${CMS_SQLITE_DB} "
fi


if [  "x${CMS_DO_INIT}" == "xyes" ]; then
    echo "rukzuk_init: call cli:initSystem"
    # init system with a admin user
    sudo -E -u www-data ${INSTANCE_PATH}/environment/cli --action initSystem --docroot=${INSTANCE_PATH}/htdocs --templatepath=${CMS_PATH} --params='{"email": "rukzuk@example.com", "lastname": "Super", "firstname": "User", "gender": "", "language": "en"}'
    # default password: admin123
    ${CMS_SET_USER_PW_CMD} "UPDATE user SET password='pbkdf2_sha256\$12000\$0GdFwXIujjGUk3PrJ1rZ1gd5WATYf/JG\$1Rclyh5R7st7vqKthqQT462S65pj8mn9';"
fi


echo "rukzuk_init: call cli:updateSystem"
sudo -E -u www-data ${INSTANCE_PATH}/environment/cli --action="updateSystem" --templatepath "${CMS_PATH}" --docroot "${INSTANCE_PATH}/htdocs"
echo "rukzuk_init: call cli:updateData"
sudo -E -u www-data ${INSTANCE_PATH}/environment/cli --action="updateData" --templatepath "${CMS_PATH}" --docroot "${INSTANCE_PATH}/htdocs"
