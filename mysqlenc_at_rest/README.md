```shell
mkdir -p mysql-data mysql-keyring
# mysql to enable enc at rest you should config mysql config to use plugin
[mysqld]
early-plugin-load=keyring_file.so
# to check config is enabled 
mysql> show plugins;

docker-compose up -d
# for all tables to use encryption at rest you should alter table and set ENCRYPTION='Y'   
ALTER TABLE old_table ENCRYPTION = 'y';
DB::statement("ALTER TABLE users ENCRYPTION='Y'");
# for the newly created tables you should put
CREATE TABLE new_table (
  ...
) ENGINE=InnoDB ENCRYPTION='Y'

# We can find out if tables are encrypted or not using the INFORMATION_SCHEMA database:
mysql> SELECT NAME, ENCRYPTION FROM INFORMATION_SCHEMA.INNODB_TABLESPACES
+-------------------------------------------------+------------+
| NAME                                            | ENCRYPTION |
+-------------------------------------------------+------------+
| mysql                                           | N          |
| db/users                                        | Y          |
+-------------------------------------------------+------------+

mysql> show variables like 'keyring_file_data';

+-------------------+--------------------------------+
| Variable_name     | Value                          |
+-------------------+--------------------------------+
| keyring_file_data | /var/lib/mysql-keyring/keyring |
+-------------------+--------------------------------+
```

# Securing master key
```shell
# The master key is used for encrypting and decrypting table data on disk. It is generated automatically upon first usage and is stored in a password-protected file on the disk:
mysql> show variables like 'keyring_file_data';
+-------------------+--------------------------------+
| Variable_name     | Value                          |
+-------------------+--------------------------------+
| keyring_file_data | /var/lib/mysql-keyring/keyring |
+-------------------+--------------------------------+
# A good practice is to backup this file to protected external storage.
# if this file gone /var/lib/mysql-keyring/keyring you won't be able to restore your data 
# Another thing to do periodically - is to rotate the master key with the following query:
mysql > ALTER INSTANCE ROTATE INNODB MASTER KEY; #  this will generate a new master key and re-encrypt all data automatically.
```
