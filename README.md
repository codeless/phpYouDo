With phpYouDo (PYD), the development of Database Driven WebApps (DDWA) with PHP gets standardized, clearly arranged and last but not least: mainly outsourced to the client.

**When to use phpYouDo?**
* The focus of your application is on the database
* You plan to integrate multiple developers with different skills (PHP Dev, Database Dev, Design Dev)
* Your fellow developers are PHP beginners but might be Database professionals
* Your business logic is in the database
* You need an administrative gui &mdash; quick!
* You want no "coded application", but a transparent one

[Demo](http://www.codeless.at/phpyoudo)

[Intro Documentation](http://www.codeless.at/phpyoudo/doc_intro)


About
=====

DDWA's in general consist of these parts: HTML-Templates, PHP-Scripts and JavaScripts. Most often, these parts are tightly woven into each other, which makes it tricky for the designer to edit the HTML. Additionally, the PHP-Programmer has a hard time extracting SQL-queries for specific inspections.

phpYouDo tries to solve these problems by separating the PHP-Scripts from the SQL-Queries and the HTML-Templates. SQL-Queries are outsourced to configuration files and HTML-Templates are combined with JavaScript to display the selected records.

An application in phpYouDo consists of one or more reports. Reports in turn consists of one or more database queries. Each query can get visualized with an individual HTML or PHP template.

Check out the [online demo](http://www.codeless.at/phpyoudo)!


Features
========

* SQL and HTML separation from PHP code
* Conditional execution of SQL queries
* Easy integration of user-input from GET (:), POST (#) and SESSION ($)
* Easily integration of JavaScript template engines
* Load removal from the server when using JavaScript
* Can manage multiple applications
* Lightweight library: phpYouDo as a library consists of only one file; additional scripts and files (JavaScript and CSS) are integrated via Content Delivery Networks (CDN)


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

__Q: How can SQL statements be debugged?__

A: The execution of each section inside a report can be logged by adding the key _log_ with a value of 1 or 2: 1 means a full log, while 2 logs only the time spent on the execution of the query. The log-entries are published in your PHP error-logfile.

Example report:

~~~
;...

[get\_required]
; Only gets executed when the lorem parameter is set:
sql="SELECT * FROM lipsum WHERE lorem=:lorem*"
log=1

;...
~~~

Example log-entries:

~~~
[26-May-2014 14:02:42 UTC] PYD example_01_intro\get\get_required GET params: application,report,lorem
[26-May-2014 14:02:42 UTC] PYD example_01_intro\get\get_required POST params:
[26-May-2014 14:02:42 UTC] PYD example_01_intro\get\get_required SQL params: get_lorem
[26-May-2014 14:02:42 UTC] PYD example_01_intro\get\get_required SQL param values: 3
[26-May-2014 14:02:42 UTC] PYD example_01_intro\get\get_required Obligatory params: get_lorem
[26-May-2014 14:02:42 UTC] PYD example_01_intro\get\get_required Seconds needed for binding params and executing query: 0.00011682510375977
~~~


Best practices
==============

Debugging SQL-statements with MySQL
-----------------------------------

Make use of the [General Query Log](https://dev.mysql.com/doc/refman/5.1/en/query-log.html).


Alternatives to phpYouDo
========================

* [Dadabik](http://www.dadabik.org/)
* [DbToRia](http://www.dbtoria.org/)
* [PHPLens](http://phplens.com/)
* [SynApp2](http://www.synapp2.org/)
* [VFront](http://www.vfront.org/)


Glossary and abbreviations
==========================

PYD: phpYouDo

DDWA: Database Driven Webapp

Application: A set of reports

Report: A set of queries

Subreport: A report within a report

Query: An SQL statement


License
=======

This work is licensed under the Creative Commons Attribution 4.0 International License. To view a copy of this license, visit http://creativecommons.org/licenses/by/4.0/.
