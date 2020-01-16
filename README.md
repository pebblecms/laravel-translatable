# Laravel Translatable

Make Eloquent model attributes translatables using Translations table

[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)
[![Laravel 6.x](https://img.shields.io/badge/Laravel-6.x-orange.svg)](http://laravel.com)

This package contains a trait to make Eloquent attributes translatable. Translations are stored in Translations database table.

Once the trait is installed on the model you can do these things:

```php
$customer = new Customer; // An Eloquent model
$customer
   ->setTranslation('greeting', 'en', 'Hello')
   ->setTranslation('greeting', 'es', 'Hola')
   ->save();
   
$customer->greeting; // Returns 'Hello' given that the current app locale is 'en'
$customer->getTranslation('greeting', 'es'); // returns 'Hola'

app()->setLocale('es');

$customer->greeting; // Returns 'Hola'
```

## Installation

You can install the package via composer:

``` bash
composer require pebble/laravel-translatable
```

If you want to change the default model or the default tables names, you could publish the config file:
``` bash
php artisan vendor:publish --provider="Pebble\Translatable\TranslatableServiceProvider" --tag=config --force
```

You must publish the migration file to create polymorphic and main translations tables:
``` bash
php artisan vendor:publish --provider="Pebble\Translatable\TranslatableServiceProvider" --tag=migrations --force
```

## Making a model translatable

The required steps to make a model translatable are:

- First, you need to add the `Pebble\Translatable\Traits\Translatable`-trait.
- Next, you should create a public static property `$translatable` which holds an array with all the names of attributes you wish to make translatable.
- You have to create a field in the migration of your model type `string` and `nullable`.

Here's an example of a prepared model:

``` php
use Illuminate\Database\Eloquent\Model;
use Pebble\Translatable\Traits\Translatable;

class NewsItem extends Model
{
    use Translatable;
       
    protected $fillable = ['greeting'];
    public static $translatable = ['greeting'];
}
```