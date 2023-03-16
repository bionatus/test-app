#!/bin/bash
set -o allexport; source  /var/www/app/bluon-api/.env; set +o allexport
echo "Generating local mysql backup"
mysqldump -h $DB_HOST -P $DB_PORT -u $DB_USERNAME -p$DB_PASSWORD $DB_DATABASE --set-gtid-purged=OFF > local.sql
echo "Generating prod backup"
mysqldump -u prod bluon_api_prod  --set-gtid-purged=OFF > prod.sql
sed  -i '1i set sql_require_primary_key = off;' prod.sql
echo "Dropping current database"
mysql -P $DB_PORT -h $DB_HOST -u $DB_USERNAME -p$DB_PASSWORD -e "DROP DATABASE ${DB_DATABASE};CREATE DATABASE ${DB_DATABASE}"
echo "Loading prod database"
mysql -P $DB_PORT -h $DB_HOST -u $DB_USERNAME -p$DB_PASSWORD $DB_DATABASE < prod.sql

echo "Sync DO spaces"
rclone sync -v spaces-sfo3:api-prod spaces-sfo3:$DO_SPACES_BUCKET
echo "Set public permissions to DO space"
s3cmd setacl s3://$DO_SPACES_BUCKET/ --acl-public --recursive

echo "Sync Storage from prod"
rsync -avhH --progress bluon-prod:/var/www/app/bluon-api/storage/app/public/* /var/www/app/bluon-api/storage/app/public
