ResourceBundle
===============

[![Build Status](https://github.com/FiveLab/ResourceBundle/workflows/Testing/badge.svg?branch=master)](https://github.com/FiveLab/ResourceBundle/actions)

Integrate the Resource library with Symfony applications.

Requirements
------------

* PHP 7.4 or higher
* Symfony 4.4 or higher

Installation
------------

Add ResourceBundle in your composer.json:

````json
{
    "require": {
        "fivelab/resource-bundle": "~2.0"
    }
}
````

Now tell composer to download the library by running the command:

```bash
$ php composer.phar update fivelab/resource-bundle
```

License
-------

This library is under the MIT license. See the complete license in library

```
LICENSE
```

Development
-----------

For easy development you can use our `Dockerfile`:

```shell script
docker build -t fivelab-resource-bundle .
docker run -it -v $(pwd):/code fivelab-resource-bundle bash
```

After run docker container, please install vendors:

```shell script
composer install
```

Reporting an issue or a feature request
---------------------------------------

Issues and feature requests are tracked in the [Github issue tracker](https://github.com/FiveLab/ResourceBundle/issues).

Contributors:
-------------

Thanks to [everyone participating](https://github.com/FiveLab/ResourceBundle/graphs/contributors) in the development of this Resource library!
