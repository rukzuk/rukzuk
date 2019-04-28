#!/bin/sh
set -e
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

if [ ! -e  ${CMS_SQLITE_DB} ]; then
    # init system with a admin user
    sudo -E -u www-data ${INSTANCE_PATH}/environment/cli --action initSystem --docroot=${INSTANCE_PATH}/htdocs --templatepath=${CMS_PATH} --params='{"email": "rukzuk@example.com", "lastname": "Super", "firstname": "User", "gender": "", "language": "en"}'
    # default password: admin123
    sudo -E -u www-data sqlite3 ${CMS_SQLITE_DB} "UPDATE user SET password='pbkdf2_sha256\$12000\$0GdFwXIujjGUk3PrJ1rZ1gd5WATYf/JG\$1Rclyh5R7st7vqKthqQT462S65pj8mn9';"
fi
sudo -E -u www-data ${INSTANCE_PATH}/environment/cli --action="updateSystem" --templatepath "${CMS_PATH}" --docroot "${INSTANCE_PATH}/htdocs"
sudo -E -u www-data ${INSTANCE_PATH}/environment/cli --action="updateData" --templatepath "${CMS_PATH}" --docroot "${INSTANCE_PATH}/htdocs"
