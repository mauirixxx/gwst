# gwst - Guild Wars Stats Tracking

Guild Wars stat tracking
The idea behind this is to track multiple characters individual stats as well as account stats. Hopefully this will be easier then dealing with multiple spreadsheats per character, or editing a public wiki.

Installation instructions are available in [INSTALL.md](INSTALL.md).

## Requirements

GWST is operating-system agnostic and does not require Apache specifically. The current code requires:

- **PHP 8.3 or newer** with the **MySQLi** extension enabled
- A web server capable of serving PHP applications, such as Apache or nginx with PHP-FPM
- **MariaDB 10.11 or newer**, or a compatible MySQL server
- A modern web browser

The current production installation runs on PHP 8.3 and MariaDB 10.11. PostgreSQL is not currently supported; database access is written for MySQL/MariaDB using MySQLi.

See [INSTALL.md](INSTALL.md) for setup instructions.

Currently, you can:
1. Register an account, with a properly salted & hashed password
2. Change said password
3. Change your e-mail
4. Track ALL of your Guild Wars accounts
5. Track ALL of your characters, per account
6. Auto tracks the "Kind of a Big Deal" title
7. Deleting a Guild Wars account deletes ALL account wide titles and ALL characters and there respective titles.

Immediate to do:
1. ~~Auto track all of the "Legendary" character titles~~
2. ~~Figure out how to reset a forgotten password~~
3. ~~E-mail birthday reminders~~
4. ~~Create a default admin user~~
5. Upload a picture of your character

Future to do:
1. ~~Track what was collected via the free treasure scattered around Elona (Nightfall)~~
2. ~~Send out a reminder 31 days later to go collect the free treasure again~~
3. ~~???~~
4. ~~Profit!!~~
5. ~~Use said profits to find someone that can make this go from functional but fugly to functional but pretty.~~
