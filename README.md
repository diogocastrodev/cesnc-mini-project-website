# CESNC Mini Project Website

This project was made in the ambit of the Cryptography and Communications Security class.

## Necessities

Apache2, PHP, MySQL

Some of the installations can be found in [this page](https://castrooo.notion.site/mini-projeto-cripto).

## Importing

### Importing SQL

The SQL can be found [here](sql/sql.sql) just copy the whole file and paste it in the MySQL CLI or phpMyAdmin SQL.

In case of need it's possible to import like this to the CLI:

```bash
mysql -u root -p < sql/sql.sql
```

### Change MySQL Connection Information

The MySQL Information can be found [here](php/mysql_connection.php). Change it according to your mysql user, password, host.

## Usage

### Users

There's 2 pre-created users:

```bash
(EMAIL)             - (PASSWORD)
admin@cesnc.com     - admin
joao@cesnc.com      - 123
```

## Upgrades for the Future

* Send attachments
* Store attachments in the samba filesystem
* Download attachments
