Skybet PHP API
======================

This is a simple PHP implementation for [Skybet API](http://skybet.com/).

Installation
------------

You can either get the files from GIT or you can install the library via [Composer](getcomposer.org). To use Composer, simply add the following to your `composer.json` file.

```json
{
    "require": {
        "sharapov/skybet-php-api": "dev-master"
    }
}
```

How to use it?
--------------

To initialize the API, you'll need to pass an array with your `application user`.

```php
require_once "../vendor/autoload.php";

$api = new \Sharapov\SkybetPHP\SkybetAPI( [
                                            'api_user'  => 'test',
                                          ] );

// Retrieve available event classes
$response = $api->classes()->get();

// Retrieve events list on certain event class
$response = $api->football()->get();

// Retrieve event document by event id
$response = $api->event('20739612');

// Retrieve market for event by id
 $response = $api->event('20739612', true)->market('86571269')->get();

print '<pre>';
print_r( $response );
print '</pre>';
```