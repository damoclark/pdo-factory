# pdo-factory


## Synopsis

PHP Script
```php
<?php
  $pdoFactory = new PdoFactory('etc/databases.ini');

  $pdo = $pdoFactory->createPDO('server1');
  
  $pdo->prepare('select * from table1');
  
  ...
```

Database configuration file `etc/databases.ini`
```ini
[server1]
driver=pgsql
host=localhost
dbname=db
username=user
password='secret password'
```

## Overview

pdo-factory allows you to store your database connection information, including
username and passwords in files that are separate to your codebase.  Being in 
separate files, they can be omitted from your version control, and different
connection information can be used depending on whether you are working in
development, testing or production.

The configuration format is simple, and directly relates to the configuration
options supported by the PDO driver itself.  You can also specify [PDO
Attributes](http://php.net/manual/en/pdo.setattribute.php) that can be assigned to the connection.

The library is also framework agnostic, meaning you can implement it in to any
project, big or small.

## Installation

To install, just do the usual:

```bash
$ composer require pdo-factory/pdo-factory

```


## Usage

The following explains the API and how to use the library.

### Constructor

```php
public function __construct(string $source = 'etc/pdofactory.ini')
```

The constructor takes a single argument being the path to the configuration
file that stores all the connection information.  If a relative path is
provided, then the library will attempt to locate the file relative
to the path of the script itself, and if it is not found, move repeatedly
up the directory tree until it finds a matching config file.  For example
if the path specified is `etc/database.ini`, and the path of the PHP script is
`/www/project/www/index.php`, then pdo-factory will search using the
following precendence until it finds a matching file:

1. `/www/project/www/etc/database.ini`
2. `/www/project/etc/database.ini`
3. `/www/etc/database.ini`
4. `/etc/database.ini`

This can be useful if you wish to have specific config files per project that 
can override more general config files.  It also means if you place your
project's code in different spots, you don't have to change paths to your
config files (i.e. they can be relative within your project).

A common pattern would be to create an `etc` directory under your project
directory and put your pdo-factory configuration files there.  Then call
pdo-factory's constructor as so:

```php
$pdoFactory = new PdoFactory('etc/database.ini');
```

If you don't provide a path at all:

```php
$pdoFactory = new PdoFactory();
```

Then the default path is `etc/pdofactory.ini`

### Configuration File Format

The configuration parameters available in the config file vary according to the 
PDO driver that you are using.  For convenience, the following links to the
specific parameters for all the PDO supported database drivers:

1. [Postgresql](http://php.net/manual/en/ref.pdo-pgsql.connection.php)
2. [MariaDB/Mysql](http://php.net/manual/en/ref.pdo-mysql.connection.php)
3. [Sqlite](http://php.net/manual/en/ref.pdo-sqlite.connection.php)
4. [Oracle](http://php.net/manual/en/ref.pdo-oci.connection.php)
5. [MSSQL (SQLSRV)](http://php.net/manual/en/ref.pdo-sqlsrv.connection.php)
6. [MSSQL (DBLIB)](http://php.net/manual/en/ref.pdo-dblib.connection.php)

To specify the parameters, you have a choice of encoding.  Presently, Microsoft
INI file format and JSON format is supported.  The library is modular so you
can add your own encoding formats, such as YAML, .env and whatever else
suits your project.

#### INI File

Here is an example config file using the INI format for a Postgresql Database:

```ini
[server1]
driver=pgsql
host=localhost
dbname=db
username=user
password=pass
attribute[PDO::ATTR_TIMEOUT]=30
attribute[PDO::ATTR_ERRMODE]=PDO::ERRMODE_EXCEPTION
```

#### JSON File

Here is the same example config file instead using JSON format:

```json
{
  "server1": {
    "driver": "pgsql",
    "host": "localhost",
    "dbname": "db",
    "username": "user",
    "password": "pass",
    "attribute": {
      "PDO::ATTR_TIMEOUT": 30,
      "PDO::ATTR_ERRMODE": "PDO::ERRMODE_EXCEPTION"
    }
  }
}
```


## Contributions

Contributions are most welcome via pull requests on Github.  The following is an
existing list of missing features, and most of which are quite trivial to add.

### TODOs

1. Add driver support for
  * Oracle
  * Mysql/MariaDB
  * MSSQL (DBLIB)
2. Add encoding support for
  * YAML
  * .env
3. Further improve unit testing  


## Licence

Copyright (c) 2018 Damien Clark

Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

   http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
See the License for the specific language governing permissions and
limitations under the License.

