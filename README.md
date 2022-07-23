# Corviz - Database Layer

Corviz database layer provides a simple yet powerful interface to run your database operations.

We use [Hydrahon](https://clancats.io/hydrahon/master/) query builder as it's base components, extending it with a Model.
It means that all operations included in their library will be avaliable for your models as well.

## Installation

```
composer require corviz/database-layer
```

## Features

- Simple to use query builder
- Database interface (binding, native queries, transactions, db function execution, etc...)
- Base model that features mutators, accessors and CRUD operations...
- Mass objects creation

And more coming soon!

### Have a taste:

Example 1 - Fetch active users:
```php
$users = User::query()->where('active', true)->get();

foreach ($users as $user) {
    echo $user->id, ' - ', $user->email;
}
```

Example 2 - Create and save a contact:

```php
$contact = new Contact();
$contact->name =  'John';
$contact->phone = '+1 (999) 999-9999';
$contact->insert(); 
```

Example 3 - Create messages in the messages table:
```php
Message::create([
    [
        'message' => 'This is an warning message',
        'level' => 'warning'
    ],
    [
        'message' => 'This is an info message',
        'level' => 'info'
    ]
]);
```

[See complete documentation...](https://corviz.github.io/database-layer/)
