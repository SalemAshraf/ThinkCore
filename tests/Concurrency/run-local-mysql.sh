#!/usr/bin/env bash
set -euo pipefail
WORK="${WORK:-/tmp/thinkcore-mysql-qualification}"
APP_DIR="${APP_DIR:?Set APP_DIR to a dedicated local ThinkCore checkout}"
PHP_BIN="${PHP_BIN:-php}"
export WORK APP_DIR
if [ -f "$APP_DIR/bootstrap/cache/config.php" ]; then echo "Refusing cached config; use a dedicated uncached local checkout"; exit 1; fi
mkdir -p "$WORK"
cd "$WORK"
if [ ! -x mysql/bin/mysqld ]; then
 curl --fail --location --retry 2 --output mysql.tar.xz https://downloads.mysql.com/archives/get/p/23/file/mysql-8.0.39-linux-glibc2.17-x86_64-minimal.tar.xz
 tar xf mysql.tar.xz
 mv mysql-8.0.39-linux-glibc2.17-x86_64-minimal mysql
fi
if [ ! -f libaio/usr/lib/x86_64-linux-gnu/libaio.so.1 ]; then
 curl --fail --location --output libaio.deb https://archive.ubuntu.com/ubuntu/pool/main/liba/libaio/libaio1_0.3.112-13build1_amd64.deb
 mkdir -p libaio
 dpkg-deb -x libaio.deb libaio
fi
export LD_LIBRARY_PATH="$WORK/libaio/usr/lib/x86_64-linux-gnu:${LD_LIBRARY_PATH:-}"
if [ ! -d data/mysql ]; then
 mysql/bin/mysqld --no-defaults --initialize-insecure --datadir="$WORK/data" --user="$(whoami)"
fi
rm -f "$WORK/mysql.sock" "$WORK/mysql.sock.lock"
mysql/bin/mysqld --no-defaults --datadir="$WORK/data" --socket="$WORK/mysql.sock" --port=3317 --bind-address=127.0.0.1 --mysqlx=0 --log-error="$WORK/server.log" &
MPID=$!
trap 'kill "$MPID" 2>/dev/null || true; wait "$MPID" 2>/dev/null || true' EXIT
export DB_CONNECTION=mysql DB_HOST=127.0.0.1 DB_PORT=3317 DB_SOCKET="$WORK/mysql.sock" DB_DATABASE=thinkcore_fictional_qualification DB_USERNAME=root DB_PASSWORD= SESSION_DRIVER=array CACHE_STORE=array MAIL_MAILER=log
for i in $(seq 1 90); do
 if "$PHP_BIN" -r 'try {new PDO("mysql:unix_socket=".getenv("DB_SOCKET"),"root","");exit(0);}catch(Throwable $e){exit(1);}'; then break; fi
 sleep 1
done
cd "$APP_DIR"
"$PHP_BIN" -r '$p=new PDO("mysql:unix_socket=".getenv("DB_SOCKET"),"root","");$p->exec("CREATE DATABASE IF NOT EXISTS thinkcore_fictional_qualification");'
"$PHP_BIN" artisan migrate --force
"$PHP_BIN" "$APP_DIR/tests/Concurrency/taxonomy.php"
"$PHP_BIN" "$APP_DIR/tests/Concurrency/outbox.php"
