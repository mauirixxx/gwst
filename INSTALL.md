# GWTTT Installation

GWTTT (Guild Wars Titles & Treasures Tracker) is a PHP application backed by MariaDB/MySQL. These instructions assume you already have a working web server with PHP and MariaDB/MySQL available.

## 1. Install the application

Clone the repository into the directory you want to serve:

```bash
git clone https://github.com/mauirixxx/gwttt.git gwttt
cd gwttt
```

Alternatively, download and extract a release/archive into a directory of your choice.

Install the PHP dependencies with Composer:

```bash
composer install --no-dev --optimize-autoloader
```

Composer installs PHPMailer and other future PHP dependencies into the local `vendor/` directory. The `vendor/` directory is intentionally not committed to Git.

## 2. Create the database and database user

Open MariaDB/MySQL as an administrative user and create a database and a dedicated GWTTT user. Replace the example password with a strong password of your own.

```sql
CREATE DATABASE gwttt;

CREATE USER 'gwtttuser'@'localhost'
  IDENTIFIED BY 'PUT_A_STRONG_PASSWORD_HERE';

GRANT ALL PRIVILEGES ON gwttt.* TO 'gwtttuser'@'localhost';
```

GWTTT does not need to connect to the database as `root`.

## 3. Initialize the database

From the GWTTT application directory, import the consolidated database initialization file:

```bash
mariadb -u gwtttuser -p gwttt < sql-init/gwttt-initialization.sql
```

This single initialization file creates the complete GWTTT schema and loads the static/reference catalog data required by the application, including Guild Wars professions, titles and title ranks, miniature catalog/group data, tonics, and treasure-tracking reference data. It does not create application users, Guild Wars accounts or characters, title progress, treasure history, miniature inventory, or tonic inventory.

You can verify the tables were created with:

```bash
mariadb -u gwtttuser -p gwttt -e 'SHOW TABLES;'
```

## 4. Configure the database connection and local secrets

Copy the sample configuration:

```bash
cp connect.php-sample connect.php
```

Edit `connect.php` and set the values for your installation:

```php
define ("DATABASE_HOST", "localhost");
define ("DATABASE_USER", "gwtttuser");
define ("DATABASE_PASS", "your-database-password");
define ("DATABASE_NAME", "gwttt");

define ("GWST_SMTP_PASSWORD", "your-smtp-password");
```

`GWST_SMTP_PASSWORD` retains its existing internal configuration name for compatibility; the project itself is now named GWTTT.

The SMTP password is only needed if outgoing e-mail is enabled. Non-secret SMTP settings such as host, port, username, From address, and encryption are configured from the GWTTT Administration panel.

The real `connect.php` is intentionally ignored by Git. Do not commit database or SMTP credentials to the repository.

Optionally verify the PHP syntax after editing it:

```bash
php -l connect.php
```

## 5. Open GWTTT and register the first user

Browse to the URL where you installed GWTTT and create an account.

On a fresh database, the **first successfully registered GWTTT user is automatically granted administrator access**. No default administrator username or password is shipped with GWTTT. All later registrations are normal non-administrator accounts.

After registration, log in with the account you just created.

## 6. Add your first Guild Wars account

Open **Manage Accounts & Characters**.

For the first Guild Wars account, GWTTT suggests the e-mail address used to register your GWTTT account. You may keep it, replace it with the actual Guild Wars account e-mail, or replace it with an alias.

The first Guild Wars account you add is automatically selected as your preferred account. You can then begin adding characters and tracking title progress, treasures, miniatures, and sixth-year everlasting tonics.

## Updating an existing Git installation

For an installation cloned from GitHub:

```bash
git pull --ff-only
composer install --no-dev --optimize-autoloader
```

Because `connect.php` is ignored, pulling application updates will not overwrite your local database or SMTP credentials.

If an existing clone still points to the repository's former `gwst` name, update its Git remote once:

```bash
git remote set-url origin https://github.com/mauirixxx/gwttt.git
```

SSH-based clones can instead use:

```bash
git remote set-url origin git@github.com:mauirixxx/gwttt.git
```
