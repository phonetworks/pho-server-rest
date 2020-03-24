<p align="center">
  <img width="375" height="150" src="https://github.com/phonetworks/commons-php/raw/master/.github/cover-smaller.png">
</p>

# pho-server-rest

An event-driven non-blocking REST API for the Phở Kernel.

Pho-Server-Rest does not rely on a third party HTTP Server such as [NGINX](https://nginx.org/en/) or [Apache HTTPD](https://httpd.apache.org/). But it is recommended that you run it behind a proxy server for static assets and caching.

## Requirements

* PHP 7.2+
* [Composer](https://getcomposer.org/)
* [Git](https://git-scm.com/)

## Installation

The recommended way to install pho-server-rest is through git. MacOS and most UNIX operating system come with git equipped.

```git clone https://github.com/phonetworks/pho-server-rest/```

> If you are on Windows or don't have git preinstalled, you may download and install git from https://git-scm.com/, 
> or just download the pho-server-rest zip tarball from https://github.com/phonetworks/pho-server-rest/archive/master.zip 
> and extract.

Once the REST Server is installed, you need a Phở Kernel to run it. You can install the standard Phở Kernel simply by typing:

```sh
git submodule init
git submodule update
```

Alternatively you may create your own kernel with a [custom recipe](https://github.com/pho-recipes), and copy/paste it under the kernel directory. For instructions on how to create a custom kernel, check out the [README.md](https://github.com/phonetworks/pho-kernel/blob/master/README.md) file of [pho-kernel](https://github.com/phonetworks/pho-kernel/).

Once the kernel is set up, you should install the dependencies using Composer as follows:

```composer install```

Do not forget to install such dependencies for the submodule kernel as well. You would need to copy the composer.json from one of the [presets](https://github.com/phonetworks/pho-kernel/tree/master/presets) which determine the structure of the graph you are to build.

```sh
cd kernel
cp presets/basic ./composer.json # any preset is fine
composer install
cd ..
```

To run the server, you must execute the [run.php](https://github.com/phonetworks/pho-server-rest/tree/master/run.php) file. Alternatively, you may call the server programmatically:

```php
<?php
require "vendor/autoload.php";
include("PATH/TO/YOUR/KERNEL");
$server = new \Pho\Server\Rest\Server($kernel);
$server->port(8080);
$server->serve();
```

## Usage

```php
/**
 * Returns the routes object to manipulate the behaviour of the server
 * @return self
 */
$server->routes();

/**
 * Returns all existing routes
 * @return self
 */
$server->routes()->all();

/**
 * Adds a new route:
 * @param string $method HTTP Method, like GET, PUT, DELETE
 * @param string $path HTTP Path like /some_path
 * @param callable $func Function to call with arguments; $request (\Psr\Http\Message\ServerRequestInterface), $response (\React\Http\Response)
 * @return void
 */
$server->routes()->add("GET", "/path/{arg:[0-9]+}", function($request, $response, $arg) {

});

/**
 * Select no route
 * @return self
 */
$server->routes()->none()

/**
 * Select all routes, minus the defined ones.
 * @return self
 */
$server->routes()->all()->but(...)

/**
 * Select only the defined ones.
 */
$server->routes()->only(...)

/**
 * Only admins can access the selected routes
 * The opposite is: `unlock()`
 * @return void
 */
$server->routes()->only(...)->lock()

/**
 * No one can access the selected routes
 * The opposite is: `enable()`
 * @return void
 */
$server->routes()->only(...)->disable()

```

## Sessions

```php
Session::depend();
Session:destroy();
Session:begin();
```

## Unit Testing

In order to run tests, make sure the REST server is running. For that, you'll need to type in `php run.php` while in the root folder, ensuring the kernel submodule was initialized and set up with the right recipe, and environment variables are properly entered.

Once the server is up and running, you may run the unit tests by typing `vendor/bin/phpunit`, assuming that you have already installed the dependencies.

## FAQ

**How do I change the port?**

By default, pho-server-rest is designed to serve through port 1337. You may change it from the file [run.php](https://github.com/phonetworks/pho-server-rest/blob/master/run.php) by changing the line that corresponds to ```$server->port(1337);```

**How can I use Pho Server as part of a real-time application?**

Phở Server uses [React's core reactor event loop](https://github.com/reactphp/event-loop), and can work with any application that uses the same infrastructure. To achieve that, one has to construct the Phở server object with the custom event loop as the second argument; ```$server = new \Pho\Server\Rest\Server($kernel, $custom_loop);```

Once the server object is initialized, you can start the event loop separately using the `serve()` call by passing the `false` argument as follows: ```$server->serve(false);```. This starts the server in a non-blocking way, so you can initialize the loop at a different point and time of your application's lifetime. 

## License

MIT, see [LICENSE](https://github.com/phonetworks/pho-microkernel/blob/master/LICENSE).
