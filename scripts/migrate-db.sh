#!/bin/bash

#!/usr/local/bin/bash
# DB Backup script
# DIR="${BASH_SOURCE%/*}"
# echo -e $PWD



#NORMAL MODE: ./mysql_deploy.sh -s local -t jeroen-dev


# SETTINGS
DATE=$(date +"%Y%m%d_%H%M%S")

# COLORS
RESTORE='\033[0m'

RED='\033[00;31m'
GREEN='\033[00;32m'
YELLOW='\033[00;33m'
BLUE='\033[00;34m'
PURPLE='\033[00;35m'
CYAN='\033[00;36m'
LIGHTGRAY='\033[00;37m'

LRED='\033[01;31m'
LGREEN='\033[01;32m'
LYELLOW='\033[01;33m'
LBLUE='\033[01;34m'
LPURPLE='\033[01;35m'
LCYAN='\033[01;36m'
WHITE='\033[01;37m'

GRAY='\033[01;30m'



# PROFILES -------------------------------------------------------
# { s:source, t:target}

# while getopts -s:t: option
# do
# 	case "${option}"
#  		in
#           	-s) 
# 				echo "s"
# 				;;
# 			t) 
# 				echo "t"
# 				;;
#  	esac
# done

while [[ $# > 1 ]]
do
key="$1"

case $key in
    --source-host)
    SOURCE_HOST="$2"
    shift # past argument
    ;;
    --target-host)
    TARGET_HOST="$2"
    shift # past argument
    ;;
    --source-user)
    SOURCE_USER="$2"
    shift # past argument
    ;;
    --target-user)
    TARGET_USER="$2"
    shift # past argument
    ;;
    --target-root-folder)
    TARGET_ROOT_FOLDER="$2"
    shift # past argument
    ;;
    --source-wp-db-prefix)
    SOURCE_WP_DB_PREFIX="$2"
    shift # past argument
    ;;
    --target-wp-db-prefix)
    TARGET_WP_DB_PREFIX="$2"
    shift # past argument
    ;;
    --source-wp-folder)
    SOURCE_WP_FOLDER="$2"
    shift # past argument
    ;;
    --target-wp-folder)
    TARGET_WP_FOLDER="$2"
    shift # past argument
    ;;
    --db-host)
    DB_HOST="$2"
    shift # past argument
    ;;
    --db-port)
    DB_PORT="$2"
    shift # past argument
    ;;
    --db-user)
    DB_USER="$2"
    shift # past argument
    ;;
    --db-password)
    DB_PASSWORD="$2"
    shift # past argument
    ;;
    --db-name)
    DB_NAME="$2"
    shift # past argument
    ;;
    --db-target-host)
    DB_TARGET_HOST="$2"
    shift # past argument
    ;;
    --db-target-user)
    DB_TARGET_USER="$2"
    shift # past argument
    ;;
    --db-target-password)
    DB_TARGET_PASSWORD="$2"
    shift # past argument
    ;;
    --db-target-name)
    DB_TARGET_NAME="$2"
    shift # past argument
    ;;
    -l|--lib)
    LIBPATH="$2"
    shift # past argument
    ;;
    --default)
    DEFAULT=YES
    ;;
    *)
            # unknown option
    ;;
esac
shift # past argument or value
done
echo SOURCE HOST     = "${SOURCE_HOST}"
echo TARGET HOST    = "${TARGET_HOST}"
echo SOURCE WP PREFIX = "${SOURCE_WP_DB_PREFIX}"
echo TARGET WP PREFIX = "${TARGET_WP_DB_PREFIX}"
echo SOURCE WP FOLDER = "${SOURCE_WP_FOLDER}"
echo TARGET WP FOLDER = "${TARGET_WP_FOLDER}"
echo DB HOST     = "${DB_HOST}"
echo DB PORT     = "${DB_PORT}"
echo DB NAME     = "${DB_NAME}"
echo DB USER     = "${DB_USER}"
echo DB PASSWORD     = "${DB_PASSWORD}"

DB_FOLDER="data/"
DB_FILENAME_RAW="db-mig_${DB_NAME}_${DATE}.sql"
DB_FILENAME_TMP="db-mig_${DB_NAME}_${DATE}_tmp.sql"
DB_FILENAME_MIGRATED="db-mig_${DB_NAME}_${DATE}_migrated.sql"


# # Set the filenames
# SOURCE_SERVER_FILE="server_${SOURCE}.sh"
# TARGET_SERVER_FILE="server_${TARGET}.sh"


# # Reads the config file and puts the content in the associative array
# read_config_file() {

# 	source $2
# 	# project
# 	server_cfgs[$1_PROJECT_FOLDER]=${PROJECT_FOLDER}
# 	server_cfgs[$1_DEPLOY_FOLDER]=${DEPLOY_FOLDER}

# 	# server
# 	server_cfgs[$1_SERVER_DOMAIN]=${SERVER_DOMAIN}
# 	server_cfgs[$1_SERVER_FOLDER]=${SERVER_FOLDER}
# 	server_cfgs[$1_SERVER_HOST]=${SERVER_HOST}
# 	server_cfgs[$1_SERVER_USER]=${SERVER_USER}

# 	# db
# 	server_cfgs[$1_DB_HOST]=${DB_HOST}
# 	server_cfgs[$1_DB_NAME]=${DB_NAME}
# 	server_cfgs[$1_DB_USER]=${DB_USER}
# 	server_cfgs[$1_DB_PASSWORD]=${DB_PASSWORD}

# 	# wordpress
# 	server_cfgs[$1_WP_PREFIX]=${WP_PREFIX}

# }

# # declare associative array
# declare -A server_cfgs

# # import server details
# read_config_file SRC $SOURCE_SERVER_FILE
# read_config_file TRG $TARGET_SERVER_FILE





# MYSQL PART ------------------------------------------------------------------------------------- #

# create directory structure if needed
# DB_FOLDER="${server_cfgs[SRC_DEPLOY_FOLDER]}db/"
# mkdir -p ${DB_FOLDER}


# # MYSQL DUMP --- 
echo -e "${CYAN}Starting ${SOURCE} mysql dump${GRAY}"

# # do backup
#mysqldump -h${server_cfgs[SRC_DB_HOST]} -u${server_cfgs[SRC_DB_USER]} -p${server_cfgs[SRC_DB_PASSWORD]} ${server_cfgs[SRC_DB_NAME]} > "${DB_FOLDER}${DATE}-${SOURCE}-dump.sql"
mysqldump --compatible=mysql4 -h${DB_PORT} -h${DB_HOST} -u${DB_USER} -p${DB_PASSWORD} ${DB_NAME} > "${DB_FOLDER}${DB_FILENAME_RAW}"

if [ $? == 0 ] 
	then
		echo -e "${DB_FILENAME_RAW}"
		echo -e "\n${LGREEN}Dump of table:${DB_NAME} completed successfully.\n\n${RESTORE}"
	else 
		echo -e "\n${LRED}Dump of table:${DB_NAME} failed.${RESTORE}\n\n"
		exit
fi


# # MYSQL MIGRATE --- 
echo -e "${CYAN}Migrating mysqldump:\"${SOURCE_HOST}\" to \"${TARGET_HOST}\".${GRAY}"

# Replace table prefix
#sed -e s,\`${SOURCE_WP_DB_PREFIX},\`${TARGET_WP_DB_PREFIX},g "${DB_FOLDER}${DB_FILENAME_RAW}" > "${DB_FOLDER}${DB_FILENAME_TMP}"
#mv "${DB_FOLDER}${DB_FILENAME_TMP}" "${DB_FOLDER}${DB_FILENAME_MIGRATED}"

# Replace site options
LC_CTYPE=C && LANG=C && sed -e "s,'siteurl'\,'[^']*','siteurl'\,'http://${TARGET_HOST}${TARGET_WP_FOLDER}',g;s,'home'\,'[^']*','home'\,'http://${TARGET_HOST}${TARGET_WP_FOLDER}',g" "${DB_FOLDER}${DB_FILENAME_RAW}" > "${DB_FOLDER}${DB_FILENAME_TMP}"
mv "${DB_FOLDER}${DB_FILENAME_TMP}" "${DB_FOLDER}${DB_FILENAME_MIGRATED}"

# # Replace usermeta key prefix
# sed -e s,\'${server_cfgs[SRC_WP_PREFIX]},\'${server_cfgs[TRG_WP_PREFIX]},g "${DB_FOLDER}${DB_FILENAME_MIGRATED}" > "${DB_FOLDER}${DB_FILENAME_TMP}"
# mv "${DB_FOLDER}${DB_FILENAME_TMP}" "${DB_FOLDER}${DB_FILENAME_MIGRATED}"

# Replace main domain + folder
#sed -e s,${SOURCE_HOST}${SOURCE_WP_FOLDER},${TARGET_HOST}${TARGET_WP_FOLDER},g "${DB_FOLDER}${DB_FILENAME_MIGRATED}" > "${DB_FOLDER}${DB_FILENAME_TMP}"
#mv "${DB_FOLDER}${DB_FILENAME_TMP}" "${DB_FOLDER}${DB_FILENAME_MIGRATED}"

# Replace domain 
#sed -e s,${SOURCE_HOST},${TARGET_HOST},g "${DB_FOLDER}${DB_FILENAME_MIGRATED}" > "${DB_FOLDER}${DB_FILENAME_TMP}"
#mv "${DB_FOLDER}${DB_FILENAME_TMP}" "${DB_FOLDER}${DB_FILENAME_MIGRATED}"

# Replace last instances of the '/folder
#sed -e s,\'${SOURCE_WP_FOLDER},\'${TARGET_WP_FOLDER},g "${DB_FOLDER}${DB_FILENAME_MIGRATED}" > "${DB_FOLDER}${DB_FILENAME_TMP}"
#mv "${DB_FOLDER}${DB_FILENAME_TMP}" "${DB_FOLDER}${DB_FILENAME_MIGRATED}"


MIGRATE_STATE=$?

if [ $MIGRATE_STATE == 0 ] 
	then
		echo -e "\n${LGREEN}Migration of the .sql completed.\n\n${RESTORE}"
	else 
		echo -e "\n${LRED}Migration of the .sql failed.${RESTORE}\n\n"
		exit
fi




# read -p "Ready to deploy the database to the remote server ? (y/n) : " answer
# case $answer in
#     [Yy]* ) ;;
#     [Nn]* ) echo -e "\n\n${LRED}Okay, let's exit...\n\n${RESTORE}"; exit;;
#     * ) echo "Please answer y or n."; exit;;
# esac




echo -e "${CYAN}Copying migrated database to remote environment. ${GRAY}"

rsync -av --progress "${DB_FOLDER}${DB_FILENAME_MIGRATED}" ${TARGET_USER}@${TARGET_HOST}:"${TARGET_ROOT_FOLDER}/${DB_FILENAME_MIGRATED}"

COPY_STATE=$?

if [ $COPY_STATE == 0 ] 
	then
		echo -e "\n${LGREEN}File copied succesfully.\n\n${RESTORE}"
	else 
		echo -e "\n${LRED}File copy failed.${RESTORE}\n\n"
		exit
fi

ssh -t -t ${TARGET_USER}@${TARGET_HOST} bash -c "'
	mysql -u${DB_TARGET_USER} -p${DB_TARGET_PASSWORD} -h${DB_TARGET_HOST} ${DB_TARGET_NAME} < ${TARGET_ROOT_FOLDER}/${DB_FILENAME_MIGRATED}
'"
	#mysqldump -u${DB_TARGET_USER} -p${DB_TARGET_PASSWORD} -h${DB_TARGET_HOST} ${DB_TARGET_NAME} > ${TARGET_ROOT_FOLDER}/db-bck_${DATE}.sql && mysqldump -u${DB_TARGET_USER} -p${DB_TARGET_PASSWORD} -h${DB_TARGET_HOST} ${DB_TARGET_NAME} > ${TARGET_ROOT_FOLDER}/db-bck_${DATE}.sql
#
# ssh -t ${TARGET_USER}@${TARGET_HOST} bash -c "'
# 	mysqldump -u${DB_TARGET_USER} -p${DB_TARGET_PASSWORD} -h${DB_TARGET_HOST} ${DB_TARGET_NAME} > ${TARGET_ROOT_FOLDER}/db-bck_${DATE}.sql
# 	mysql -u${DB_TARGET_USER} -p${DB_TARGET_PASSWORD} -h${DB_TARGET_HOST} ${DB_TARGET_NAME} < ${TARGET_ROOT_FOLDER}/${DB_FILENAME_MIGRATED}
	
# '"
#mysql -userver_db -p7jlnY1sg -hlocalhost server_daphnalaurens < /home/server/public_html/clients/daphnalaurens/site/
