#!/bin/bash

set -e
export LANG=C

echo "This script imports backups of rukzuk."
echo "Usage: $0 < backup.tar"
echo "Usage: xzcat backup.tar.xz | $0"

# check for db 
CMS_DO_INIT="no"
if [ "a$CMS_DB_TYPE"  == "amysql" ]; then
    # check if db exists and has content
    CMD_MYSQL_CONFED="sudo -E -u www-data mysql -h localhost -u ${CMS_MYSQL_USER} -p${CMS_MYSQL_PASSWORD} -D ${CMS_MYSQL_DB} "
    if ! ${CMD_MYSQL_CONFED} -e "SHOW TABLES;" | grep "website" >/dev/null 2>&1; then
        CMS_DO_INIT="yes";
    fi
else
    echo "WARN: sqlite import only works form other sqlite sites."
    CMS_DO_INIT="yes"
fi

if [  "x${CMS_DO_INIT}" == "xyes" ]; then
    echo "DB seems to be empty"
else
    echo "WARN: MySQL DB ${CMS_MYSQL_DB} does exist and has tables. Please clear DB manually before proceed."
    echo "Use this command to delete all tables in the current DB. WARNING YOU WILL LOOSE THE DATA!"
    echo "mysqldump --no-data --add-drop-table ${CMS_MYSQL_DB} | grep ^DROP | mysql -v ${CMS_MYSQL_DB}"
    exit 1
fi

# check for files
if [ "$(find "${INSTANCE_PATH}/htdocs" -mindepth 1 -print -quit 2>/dev/null)" == "" ]; then
    echo "No CMS files found. OK"
else
    echo "WARN: Please remove rukzuk files in '${INSTANCE_PATH}'"
    echo "rm -rf ${INSTANCE_PATH}/htdocs/*"
    echo "OR"
    echo "mkdir ~/cms-files-bak && mv ${INSTANCE_PATH}/htdocs/* ~/cms-files-bak"
    exit 2
fi

# do import
TMPDIR="$(mktemp -d ${IMPORT_TMP_DIR}/rzimport.XXXXXXXXXX)"

# extract tar from stdin to temp folder
${CMD_TAR} -C ${TMPDIR} -xf -

if [ "a$CMS_DB_TYPE"  == "amysql" ]; then
    # load sql dump into db
    ${CMD_MYSQL_CONFED} < ${TMPDIR}/dump.sql
fi

# extrat data.tar to correct path
${CMD_TAR} -C ${INSTANCE_PATH} -xf $TMPDIR/data.tar

# remove temp folder
rm -rf ${TMPDIR}

exit 0 # end

