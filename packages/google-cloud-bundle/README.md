# Shopsys Google Cloud Bundle

[![Downloads](https://img.shields.io/packagist/dt/shopsys/google-cloud-bundle.svg)](https://packagist.org/packages/shopsys/google-cloud-bundle)

This bundle is used to allow [Shopsys Platform](https://www.shopsys-framework.com) integration with Google Cloud.

This repository is maintained by [shopsys/shopsys] monorepo, information about changes is in [monorepo CHANGELOG.md](https://github.com/shopsys/shopsys/blob/master/CHANGELOG.md).

## Installation
The plugin is a Symfony bundle and is installed in the same way:

### Download
First, you download the package using [Composer](https://getcomposer.org/):
```
composer require shopsys/google-cloud-bundle
```

### Register
For the bundle to be loaded in your application you need to register it in the `app/AppKernel.php` file of your project:
```php
// ...
class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = [
            // ...
            new Shopsys\GoogleCloudBundle\ShopsysGoogleCloudBundle(),
            // ...
        ];

        // ...

        return $bundles;
    }

    // ...
}
```

## Responsibilities
* Bundle allows you to use client and adapter for Google Cloud Storage Bucket

## Contributing
Thank you for your contributions to Shopsys Google Cloud Bundle package.
Together we are making Shopsys Platform better.

This repository is READ-ONLY.
If you want to [report issues](https://github.com/shopsys/shopsys/issues/new) and/or send [pull requests](https://github.com/shopsys/shopsys/compare),
please use the main [Shopsys repository](https://github.com/shopsys/shopsys).

Please, check our [Contribution Guide](https://github.com/shopsys/shopsys/blob/master/CONTRIBUTING.md) before contributing.

## Support
What to do when you are in troubles or need some help?
The best way is to join our [Slack](https://join.slack.com/t/shopsysframework/shared_invite/zt-11wx9au4g-e5pXei73UJydHRQ7nVApAQ).

If you want to [report issues](https://github.com/shopsys/shopsys/issues/new), please use the main [Shopsys repository](https://github.com/shopsys/shopsys).

[shopsys/shopsys]:(https://github.com/shopsys/shopsys)
