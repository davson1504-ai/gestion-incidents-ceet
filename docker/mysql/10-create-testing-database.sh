#!/usr/bin/env bash
set -e

mysql --protocol=socket -uroot -p"${MYSQL_ROOT_PASSWORD}" <<-EOSQL
CREATE DATABASE IF NOT EXISTS testing CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
GRANT ALL PRIVILEGES ON testing.* TO '${MYSQL_USER}'@'%';
FLUSH PRIVILEGES;
EOSQL
