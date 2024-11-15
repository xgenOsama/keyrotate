```shell
mkdir mysql-ssl
cd mysql-ssl
# Create CA certificate
openssl genrsa 2048 > ca-key.pem
openssl req -new -x509 -nodes -days 3650 -key ca-key.pem -out ca-cert.pem -subj "/CN=MySQL-CA"

# Create server key and certificate
openssl req -newkey rsa:2048 -days 3650 -nodes -keyout server-key.pem -out server-req.pem -subj "/CN=MySQL-Server"
openssl x509 -req -in server-req.pem -days 3650 -CA ca-cert.pem -CAkey ca-key.pem -set_serial 01 -out server-cert.pem

# Create client key and certificate (optional)
openssl req -newkey rsa:2048 -days 3650 -nodes -keyout client-key.pem -out client-req.pem -subj "/CN=MySQL-Client"
openssl x509 -req -in client-req.pem -days 3650 -CA ca-cert.pem -CAkey ca-key.pem -set_serial 01 -out client-cert.pem

# Set permissions
chmod 600 *.pem
cd ../
mkdir mysql_data

# Access the MySQL container:
docker exec -it mysql-secure mysql -u root -p

SHOW VARIABLES LIKE 'default_authentication_plugin';
SHOW VARIABLES LIKE 'require_secure_transport';
mysql> SHOW VARIABLES LIKE 'require_secure_transport';
+--------------------------+-------+
| Variable_name            | Value |
+--------------------------+-------+
| require_secure_transport | ON    |
+--------------------------+-------+
1 row in set (0.00 sec)
SHOW VARIABLES LIKE 'ssl%';
mysql> SHOW VARIABLES LIKE 'ssl%';
+---------------------------+--------------------------------+
| Variable_name             | Value                          |
+---------------------------+--------------------------------+
| ssl_ca                    | /etc/mysql/ssl/ca-cert.pem     |
| ssl_capath                |                                |
| ssl_cert                  | /etc/mysql/ssl/server-cert.pem |
| ssl_cipher                |                                |
| ssl_crl                   |                                |
| ssl_crlpath               |                                |
| ssl_fips_mode             | OFF                            |
| ssl_key                   | /etc/mysql/ssl/server-key.pem  |
| ssl_session_cache_mode    | ON                             |
| ssl_session_cache_timeout | 300                            |
+---------------------------+--------------------------------+
10 rows in set (0.00 sec)
```
# in .env file 
DB_SSL_CA=./mysql-ssl/ca-cert.pem
DB_SSL_CERT=./mysql-ssl/client-cert.pem
DB_SSL_KEY=./mysql-ssl/client-key.pem
# in config/database.php
```php
'mysql' => [
    'driver' => 'mysql',
    'host' => env('DB_HOST', '127.0.0.1'),
    'port' => env('DB_PORT', '3306'),
    'database' => env('DB_DATABASE', 'forge'),
    'username' => env('DB_USERNAME', 'forge'),
    'password' => env('DB_PASSWORD', ''),
    'charset' => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
    'prefix' => '',
    'strict' => true,
    'engine' => null,
    'options' => extension_loaded('pdo_mysql') ? array_filter([
        PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false,
        PDO::MYSQL_ATTR_SSL_CA => env('DB_SSL_CA'),
        PDO::MYSQL_ATTR_SSL_CERT => env('DB_SSL_CERT'),
        PDO::MYSQL_ATTR_SSL_KEY => env('DB_SSL_KEY'),
    ]) : [],
],
php artisan config:clear
php artisan config:cache
php artisan cache:clear
```
# if there is an issue with the connection like
```
 SQLSTATE[HY000] [1045] Access denied for user 'keyrotate'@'172.20.0.1' (using password: YES) (Connection: mysql, SQL: select table_name as name, (data_length + index_length) as size, table_comment as comment, engine as engine, table_collation as collation from information_schema.tables where table_schema = 'keyrotate' and table_type in ('BASE TABLE', 'SYSTEM VERSIONED') order by table_name)
```
```
docker exec -it mysql-secure mysql -u root -p
SELECT User, Host FROM mysql.user WHERE User = 'keyrotate';
CREATE USER 'keyrotate'@'%' IDENTIFIED BY 'keyrotate_password';
GRANT ALL PRIVILEGES ON keyrotate.* TO 'keyrotate'@'%';
FLUSH PRIVILEGES;
SHOW GRANTS FOR 'keyrotate'@'%';
```
# you will get the error if you didn't setup secure connection config in database config file methioned above
```
SQLSTATE[HY000] [3159] Connections using insecure transport are prohibited while --require_secure_transport=ON. (Connection: mysql, SQL: select table_name as `name`, (data_length + index_length) as `size`, table_comment as `comment`, engine as `engine`, table_collation as `collation` from information_schema.tables where table_schema = 'keyrotate' and table_type in ('BASE TABLE', 'SYSTEM VERSIONED') order by table_name)
```
# test connection to mysql using mysql comand
```
mysql -u keyrotate -p --host=127.0.0.1 --port=3307 \
    --ssl-ca=/Users/n1x/Code/keyrotate/mysql_ssl_single/mysql-ssl/ca-cert.pem \
    --ssl-cert=/Users/n1x/Code/keyrotate/mysql_ssl_single/mysql-ssl/client-cert.pem \
    --ssl-key=/Users/n1x/Code/keyrotate/mysql_ssl_single/mysql-ssl/client-key.pem
```
