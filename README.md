# WP Dev Remote Request

The "WP Dev Remote Request" package provide the function `wp_dev_remote_request_get()` which allow us to send the HTTP requests and store the response into the transient.

So, If we resend the request then it serve the data from the transient.

If transient get expired, then it trigger the live request and store the data into the transient and return the response.

Internally use the wp_remote_*() functions to send the remote requests.

## Syntax

```php
wp_dev_remote_request_get( string / array() );
```

Below are some quick examples:

```php
// Example 1:
$response = wp_dev_remote_request_get( 'https://maheshwaghmare.com/wp-json/wp/v2/posts/' );

// Example 2:
$response = wp_dev_remote_request_get( 'https://maheshwaghmare.com/wp-json/wp/v2/posts/?_fields=title' );

// Example 3:
$response = wp_dev_remote_request_get( 'https://maheshwaghmare.com/wp-json/wp/v2/posts/?per_page=5&_fields=title' );

// Example 4:
$response = wp_dev_remote_request_get( array(
    'url' => 'https://maheshwaghmare.com/wp-json/wp/v2/posts/',
) );

// Example 5:
$response = wp_dev_remote_request_get( array(
    'url' => 'https://maheshwaghmare.com/wp-json/wp/v2/posts/?_fields=title',
) );

// Example 6:
$response = wp_dev_remote_request_get( array(
    'url' => 'https://maheshwaghmare.com/wp-json/wp/v2/posts/',
    'query_args' => array(
        'per_page' => 5,
        '_fields' => 'title',
    )
) );
```

## Parameters

Below is the list of default parameters:

```php
'url' => '',
'query_args' => array(),
'remote_args' => array(
    'timeout' => 60,
),
'expiration' => MONTH_IN_SECONDS,
'force' => false,
```

## How it works?

The package provide the function `wp_dev_remote_request_get()` to send the HTTP request.

```php
// "First" request for URL: https://maheshwaghmare.com/wp-json/wp/v2/posts/
$response = wp_dev_remote_request_get( 'https://maheshwaghmare.com/wp-json/wp/v2/posts/' );
var_dump( $response );
// array(
// 		'success' => true,
// 		'message' => 'Response from live site.',
// 		'data' => array(
//			...
// 		),
// )

// "SECOND" request for URL: https://maheshwaghmare.com/wp-json/wp/v2/posts/
$response = wp_dev_remote_request_get( 'https://maheshwaghmare.com/wp-json/wp/v2/posts/' );
var_dump( $response );
// array(
// 		'success' => true,
// 		'message' => 'Response from transient.',
// 		'data' => array(
//			...
// 		),
// )

// "Third" request for URL: https://maheshwaghmare.com/wp-json/wp/v2/posts/
$response = wp_dev_remote_request_get( 'https://maheshwaghmare.com/wp-json/wp/v2/posts/' );
var_dump( $response );
// array(
// 		'success' => true,
// 		'message' => 'Response from transient.',
// 		'data' => array(
//			...
// 		),
// )
```

Here, We can see the live HTTP request send only for the first time and for next request it return the cached response from the transient.

In above example we have pass the `https://maheshwaghmare.com/wp-json/wp/v2/posts` as parameter into the function `wp_dev_remote_request_get()`;

Let's see another example with additional parameters.

## How to request considered as unique request?

The remote request data is stored into the transient.

So, While storing the data into the transient it create a unique transient key with the help of the HTTP request URL.

E.g.

```
wp_dev_remote_request_get( 'https://maheshwaghmare.com/wp-json/wp/v2/posts/' ); // Unique request.
wp_dev_remote_request_get( 'https://maheshwaghmare.com/wp-json/wp/v2/posts/?per_page=10' ); // Unique request.
wp_dev_remote_request_get( 'https://maheshwaghmare.com/wp-json/wp/v2/posts/?per_page=5' ); // Unique request.
wp_dev_remote_request_get( 'https://maheshwaghmare.com/wp-json/wp/v2/posts/?per_page=5&_field=title' ); // Unique request.
wp_dev_remote_request_get( 'https://maheshwaghmare.com/wp-json/wp/v2/posts/?_field=title&per_page=5' ); // Still unique request.
```

## Install

## Install with Composer

Install the package with composer using below command:

```sh
composer require maheshwaghmare/wp-dev-remote-request
```

After installing the package simply use function `wp_dev_remote_request_get()`.

E.g.

```php
// Load files.
require_once 'vendor/autoload.php';

// "First" request for URL: https://maheshwaghmare.com/wp-json/wp/v2/posts/
$response = wp_dev_remote_request_get( 'https://maheshwaghmare.com/wp-json/wp/v2/posts/' );
var_dump( $response );
// array(
// 		'success' => true,
// 		'message' => 'Response from live site.',
// 		'data' => array(
//			...
// 		),
// )

// "SECOND" request for URL: https://maheshwaghmare.com/wp-json/wp/v2/posts/
$response = wp_dev_remote_request_get( 'https://maheshwaghmare.com/wp-json/wp/v2/posts/' );
var_dump( $response );
// array(
// 		'success' => true,
// 		'message' => 'Response from transient.',
// 		'data' => array(
//			...
// 		),
// )

// "Third" request for URL: https://maheshwaghmare.com/wp-json/wp/v2/posts/
$response = wp_dev_remote_request_get( 'https://maheshwaghmare.com/wp-json/wp/v2/posts/' );
var_dump( $response );
// array(
// 		'success' => true,
// 		'message' => 'Response from transient.',
// 		'data' => array(
//			...
// 		),
// )
```

## Debugging

If you enable the debug log and then try to send the request the you can see the logs into the `debug.log` file.

Below is the log of above code:

```log
[21-Jan-2021 13:42:48 UTC] REQUEST URL: https://maheshwaghmare.com/wp-json/wp/v2/posts/
[21-Jan-2021 13:42:48 UTC] ARGS: {"url":"https:\/\/maheshwaghmare.com\/wp-json\/wp\/v2\/posts\/","query_args":[],...
[21-Jan-2021 13:42:48 UTC] TRANSIENT_KEY: wp-dev-remote-request-bd2bab32e19a4d142e99051fcda7f4e7
[21-Jan-2021 13:42:52 UTC] RESULT: (Live) [{"id":37012,"date":"2021-01-18T23:33:10","date_gmt":...
[21-Jan-2021 13:42:53 UTC] MESSAGE: Response from live site.
[21-Jan-2021 13:42:53 UTC] DURATION: 5 seconds
[21-Jan-2021 13:42:53 UTC] REQUEST URL: https://maheshwaghmare.com/wp-json/wp/v2/posts/
[21-Jan-2021 13:42:53 UTC] ARGS: {"url":"https:\/\/maheshwaghmare.com\/wp-json\/wp\/v2\/posts\/","query_args":[],...
[21-Jan-2021 13:42:53 UTC] TRANSIENT_KEY: wp-dev-remote-request-bd2bab32e19a4d142e99051fcda7f4e7
[21-Jan-2021 13:42:53 UTC] RESULT: (Cached) [{"id":37012,"date":"2021-01-18T23:33:10","date_gmt":...
[21-Jan-2021 13:42:53 UTC] MESSAGE: Response from transient.
[21-Jan-2021 13:42:53 UTC] DURATION: 1 second
[21-Jan-2021 13:42:53 UTC] REQUEST URL: https://maheshwaghmare.com/wp-json/wp/v2/posts/
[21-Jan-2021 13:42:53 UTC] ARGS: {"url":"https:\/\/maheshwaghmare.com\/wp-json\/wp\/v2\/posts\/","query_args":[],"remote_args":{"timeout":60},"expiration":2592000,"force":false,"start_time":1611236573,"end_time":1611236573,"duration":0}
[21-Jan-2021 13:42:53 UTC] TRANSIENT_KEY: wp-dev-remote-request-bd2bab32e19a4d142e99051fcda7f4e7
[21-Jan-2021 13:42:53 UTC] RESULT: (Cached) [{"id":37012,"date":"2021-01-18T23:33:10","date_gmt":...
[21-Jan-2021 13:42:53 UTC] MESSAGE: Response from transient.
[21-Jan-2021 13:42:53 UTC] DURATION: 1 second
```

Here, We can see the **first** request takes the **5 seconds** because it send the live request.

But, The **second** and **third** request takes the **1 second** because it get the response from the transient.
