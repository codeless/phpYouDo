With phpYouDo (PYD), the development of Database Driven WebApps (DDWA) gets standardized, clearly arranged and last but not least: mainly outsourced to the client.

[Online Demo](http://www.codeless.at/phpyoudo)


Requirements
============

Server: PHP with PDO
Client: A JavaScript enabled browser


FAQ
===

__Q: Can PYD handle multiple databases in one application?__
A: Yes, see the examples.

__Q: What sorts of databases PYD can handle?__
A: Only relational ones. PYD uses [PHP Data Objects](http://php.net/pdo) to access databases.

__Q: The examples use SQLite databases only. How to use MySQL?__
A: Example db.ini.php for MySQL:

~~~
type=mysql
host=localhost
database=mydb
username=myuser
password=mypwd
~~~


Best practices
==============

Debugging SQL-statements with MySQL
-----------------------------------

Make use of the [General Query Log](https://dev.mysql.com/doc/refman/5.1/en/query-log.html).


Alternatives to PYD
===================

* [Dadabik](http://www.dadabik.org/)
* [PHPLens](http://phplens.com/)
* [SynApp2](http://www.synapp2.org/)


Glossary and abbreviations
==========================

PYD
  ~ phpYouDo
DDWA
  ~ Database Driven Webapp
Application
  ~ A set of reports
Report
  ~ A set of queries
Subreport
  ~ A report within a report
Query
  ~ An SQL statement


License
=======

This work is licensed under the Creative Commons Attribution 4.0 International License. To view a copy of this license, visit http://creativecommons.org/licenses/by/4.0/.
