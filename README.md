# SimpleQuery

A PHP class `SimpleQuery` designed for building SQL queries and interacting with a database using PDO.

## Table of Contents

- [Introduction](#introduction)
- [Features](#features)
- [Installation](#installation)
- [Usage](#usage)
  - [Creating an Instance](#creating-an-instance)
  - [Setting and Getting Data](#setting-and-getting-data)
  - [Executing Queries](#executing-queries)
  - [CRUD Operations](#crud-operations)
- [Contributing](#contributing)
- [License](#license)

## Introduction

The `SimpleQuery` class provides a fluent interface for constructing SQL queries dynamically. It supports basic CRUD operations (Create, Read, Update, Delete) and facilitates parameter binding, making database interactions secure and efficient.

## Features

- Fluent interface for building SQL SELECT, INSERT, UPDATE, and DELETE queries.
- Parameter binding to prevent SQL injection attacks.
- Supports fetching results as arrays or objects.
- Easy setup and integration with existing PDO connections.
- Handles table joins, WHERE conditions, ORDER BY clauses, LIMIT, and OFFSET.

## Installation

To use the `SimpleQuery` class, ensure you have PHP 8.1+ and PDO extension installed. Simply include or autoload the class file in your project:

```composer
composer require celiovmjr/SimpleQuery;
```

## Usage

### Creating an Instance

Create a new instance of `SimpleQuery` by passing an initialized PDO connection:

```php
use Builder\Application\SimpleQuery;

// Assuming $connection is your PDO connection
class User extends SimpleQuery
{
    protected string $table = 'users'; // Ignore if your table name is the same as the class (plural form)
    protected string $primaryKey = 'id'; // Ignore if your primary key is 'id'
    protected array $required = ['name', 'username', 'email', 'sector']; // Required fields
    protected array $safe = []; // Typically fields with default values

    public function __construct()
    {
        parent::__construct($connection);
    }
}

$user = new User();
```

### Setting and Getting Data

You can set data to be used in queries using magic methods (`__set`, `__get`, `__isset`, `__unset`), array conversion methods (`fromArray`, `toArray`), or object conversion methods (`fromObject`, `toObject`):

```php
$user->fromArray(['name' => 'John Doe', 'age' => 30]);
```

### Executing Queries

Build and execute queries using fluent methods:

```php
$results = $user->select()->where('age > :age', ['age' => 25])->fetch(true);
```

### CRUD Operations

Perform CRUD operations easily:

```php
// Create
$user->fromArray([
    'name' => 'Jane Doe',
    'age' => 28
]);
$user->save();

// Read
$user->select()->where('id = :id', ['id' => 1])->fetch();

// Update
$user->name = 'New Name';
$user->save();

// Delete
$user->delete(1);
```

## Contributing

Contributions are welcome! Feel free to open issues or pull requests for any improvements or fixes.

## License

This project is licensed under the MIT License - see the LICENSE file for details.
