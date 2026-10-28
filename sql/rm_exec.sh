#!/bin/bash
mysql -u tapimon -ptapimonpass tapimon < /docker-entrypoint-initdb.d/rm_init.sql
mysql -u tapimon -ptapimonpass tapimon -e 'SHOW TABLES LIKE "rm_%"'
