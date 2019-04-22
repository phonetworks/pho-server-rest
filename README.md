# Pho-Server-REST

An event-driven non-blocking REST API for the Phở Kernel.

Pho-Server-Rest does not rely on a third party HTTP Server such as [NGINX](https://nginx.org/en/) or [Apache HTTPD](https://httpd.apache.org/). But it is recommended that you run it behind a proxy server for static assets and caching.

## Requirements

* PHP 7.1+
* [Composer](https://getcomposer.org/)
* [Git](https://git-scm.com/)

## Installation

The recommended way to install pho-server-rest is through git. MacOS and most UNIX operating system come with git equipped.

```git clone https://github.com/phonetworks/pho-server-rest/```

> If you are on Windows or don't have git preinstalled, you may download and install git from https://git-scm.com/, 
> or just download the pho-server-rest zip tarball from https://github.com/phonetworks/pho-server-rest/archive/master.zip 
> and extract.

Once the REST Server is installed, you need a Pho Kernel to run it. You can install the standard Pho Kernel simply by typing:

```
git submodule init
git submodule update
```

Alternatively you may create your own kernel with a [custom recipe](https://github.com/pho-recipes), and copy/paste it under the kernel directory. For instructions on how to create a custom kernel, check out the [README.md](https://github.com/phonetworks/pho-kernel/blob/master/README.md) file of [pho-kernel](https://github.com/phonetworks/pho-kernel/).

Once the kernel is set up, you should install the dependencies using Composer as follows:

```composer install```

## FAQ

**How do I change the port?**

By default, pho-server-rest is designed to serve through port 1337. You may change it from the file [run.php](https://github.com/phonetworks/pho-server-rest/blob/master/run.php) by changing the line that corresponds to ```$server->setPort(1337);```

## License

MIT, see [LICENSE](https://github.com/phonetworks/pho-microkernel/blob/master/LICENSE).
