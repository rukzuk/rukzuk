#!/bin/bash

set -e
export LANG=C

echo "import-data.sh: This script imports backups of rukzuk."
#echo "Usage: $0 < backup.tar"
#echo "Usage: xzcat backup.tar.xz | $0"

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
    echo "import-data.sh: OK, DB seems to be empty"
else
    echo "import-data.sh: WARN: MySQL DB ${CMS_MYSQL_DB} does exist and has tables. Please clear DB manually before proceed."
    echo "import-data.sh: Use this command to delete all tables in the current DB. WARNING YOU WILL LOOSE THE DATA!"
    echo "import-data.sh: mysqldump --no-data --add-drop-table ${CMS_MYSQL_DB} | grep ^DROP | mysql -v ${CMS_MYSQL_DB}"
    exit 1
fi

# check for files
#if [ "$(find "${INSTANCE_PATH}/htdocs/cms" -mindepth 1 -print -quit 2>/dev/null)" == "" ]; then
#    echo "import-data.sh: No CMS files found. OK"
#else
#    echo "import-data.sh: WARN: Please remove rukzuk files in '${INSTANCE_PATH}/htdocs/cms'"
#    echo "import-data.sh: rm -rf ${INSTANCE_PATH}/htdocs/cms/*"
#    exit 2
#fi

# do import
TMPDIR="$(mktemp -d ${IMPORT_TMP_DIR}/rzimport.XXXXXXXXXX)"

# extract tar from stdin to temp folder
tar -C ${TMPDIR} -xf -

if [ "a$CMS_DB_TYPE"  == "amysql" ]; then
    # load sql dump into db
    echo "import-data.sh: load sql dump into db"
    ${CMD_MYSQL_CONFED} < ${TMPDIR}/dump.sql
    # fix colorscheme (for php7)
    ${CMD_MYSQL_CONFED}  -e "update rukzuk.website SET colorscheme = '[]' WHERE colorscheme IS NULL OR colorscheme = ' '; update rukzuk.website SET publish = '{}' WHERE publish IS NULL OR publish = ' '; "
fi

# extrat data.tar to correct path
echo "import-data.sh: extract data"
tar -C "${INSTANCE_PATH}/htdocs/cms" --strip-components=3 -xf $TMPDIR/data.tar ./htdocs/cms
# NOTE: --strip-components=3 is due to the point ./htdocs/cms (3 components)

# convert meta.json owner to local db user
echo "import-data.sh: extract meta.json"
tar -C "${TMPDIR}/" -xf $TMPDIR/data.tar ./meta.json || true # do not fail if we can't find a meta.json
if [ -s "$TMPDIR/meta.json" ]; then
    php /opt/rukzuk-tools/import-metajson.php $TMPDIR/meta.json
fi

# fix rights
echo "import-data.sh: fix rights"
chown -R www-data:www-data "${INSTANCE_PATH}/htdocs/cms"

# remove temp folder
rm -rf ${TMPDIR}

exit 0 # end

