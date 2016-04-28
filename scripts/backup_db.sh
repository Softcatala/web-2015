#!/bin/bash

# Database credentials
user="root"
host="localhost"
db_name="web2016_softcatala_org"

# Path and date
backup_path="/var/www/backup/bd"
date=$(date +"%d-%b-%Y-%T")
umask 177

# Dump database into SQL file
mysqldump --user=$user --host=$host $db_name > $backup_path/$db_name-$date.sql

# Delete files older than 30 days
find $backup_path/* -mtime +30 -exec rm {} \;
