# Pho-Server-REST

An event-driven non-blocking REST API for the Pho Kernel.

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

## Kernel

Once the REST Server is installed, you need a Pho Kernel to run it. You can install the standard Pho Kernel simply by typing:

```
git submodule init
git submodule update
```

Alternatively you may create your own kernel with a [custom recipe](https://github.com/pho-recipes), and copy/paste it under the kernel directory. For instructions on how to create a custom kernel, check out the [README.md](https://github.com/phonetworks/pho-kernel/blob/master/README.md) file of [pho-kernel](https://github.com/phonetworks/pho-kernel/).

## License

MIT, see [LICENSE](https://github.com/phonetworks/pho-microkernel/blob/master/LICENSE).
