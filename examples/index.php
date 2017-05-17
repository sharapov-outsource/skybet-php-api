<?php
/**
 * Skybet API PHP implementation.
 *
 * (c) Alexander Sharapov <alexander@sharapov.biz>
 * http://sharapov.biz/
 *
 */

ini_set('display_errors', 1);

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