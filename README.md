# Allegro

Symfony bundles autoloader.

[![License](https://poser.pugx.org/carlosv2/allegro/license)](https://packagist.org/packages/carlosv2/allegro)
[![Build Status](https://travis-ci.org/carlosV2/Allegro.svg?branch=master)](https://travis-ci.org/carlosV2/Allegro)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/bba642c5-4e7e-48b1-97b1-b0423c3cf329/mini.png)](https://insight.sensiolabs.com/projects/bba642c5-4e7e-48b1-97b1-b0423c3cf329)

## Why

How many times have you required a composer package for your Symfony project
and then you have needed to add it into AppKernel?

For some projects, there is not an straight correlation but for most of them,
the only reason to require them is to add the bundle from within to a Symfony
project.

For those cases, this project tries to minimise the work required to add the
third party bundle by allowing it to be autoloaded.


## Usage

Using Allegro is as easy as injecting it into the AppKernel file of your
project. For example:

```php

use carlosV2\Allegro\Allegro;
use Symfony\Component\HttpKernel\Kernel;

class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = [
            // Any bundle that you want to have manually added
        ];
        Allegro::appendTo($bundles);

        if (in_array($this->getEnvironment(), ['dev', 'test'], true)) {
            // Any bundle that you want to have manually added for tests
            Allegro::appendDevsTo($bundles);
        }

        return $bundles;
    }

    ...
}

```

That's it. Next time the AppKernel is read, Allegro will append any extra
bundle automatically.


## How

Allegro works by inspecting the composer packages for an specific configuration.
If found, it reads and processes it so, making a bundle compatible with Allegro
is as easy as providing the right configuration into its composer file.

This is the configuration that Allegro looks for on each `composer.json` file of
each required package:

```json
{
    "extra": {
        "symfony": {
            "bundles": ["Namespace\\BundleClass"]
        }
    }
}
```

Of course, you can set as many bundle classes as you need inside the array. Additionally,
the root package (that means, the `composer.json` file of the Symfony project using
Allegro) can also have this configuration in order to autoload the bundles.

Allegro assumes that packages inside `require` key are used for production while
those inside `require-dev` are used only for development.


## Install

In order to have Allegro you first need to require it through composer:

```bash
$ composer require carlosv2/allegro
```
