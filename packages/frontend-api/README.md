# Shopsys Frontend API

[![Downloads](https://img.shields.io/packagist/dt/shopsys/frontend-api.svg)](https://packagist.org/packages/shopsys/frontend-api)

This bundle for [Shopsys Platform](https://www.shopsys.com) adds frontend API using [overblog/GraphQLBundle](https://github.com/overblog/GraphQLBundle).
The bundle is dedicated for projects based on Shopsys Platform (i.e. created from [`shopsys/project-base`](https://github.com/shopsys/project-base)) exclusively.
This repository is maintained by [shopsys/shopsys] monorepo, information about changes is in [monorepo CHANGELOG.md](https://github.com/shopsys/shopsys/blob/master/CHANGELOG.md).

## Documentation
[Documentation](https://docs.shopsys.com/en/latest/frontend-api/introduction-to-frontend-api/) can be found in Shopsys Platform Knowledge Base.

## Installation
The plugin is a Symfony bundle and is installed in the same way:

### Download
First, you download the package using [Composer](https://getcomposer.org/):
```
composer require shopsys/frontend-api
```

### Register
For the bundle to be loaded in your application you need to register it and the required `Overblog\GraphQLBundle` in `registerBundles()` method in the `app/AppKernel.php` file of your project:

```diff
+ new Shopsys\FrontendApiBundle\ShopsysFrontendApiBundle(),
+ new Overblog\GraphQLBundle\OverblogGraphQLBundle(),
```

and for easier development register GraphiQLBundle for development environment

``` diff
  if ($this->getEnvironment() === EnvironmentType::DEVELOPMENT) {
+     $bundles[] = new Overblog\GraphiQLBundle\OverblogGraphiQLBundle();
```

## Configuration
Detailed information about [configuring the package](https://docs.shopsys.com/en/latest/frontend-api/) can be found in Shopsys Platform Knowledge Base.

## Contributing
Thank you for your contributions to Shopsys Frontend API package.
Together we are making Shopsys Platform better.

This repository is READ-ONLY.
If you want to [report issues](https://github.com/shopsys/shopsys/issues/new) and/or send [pull requests](https://github.com/shopsys/shopsys/compare),
please use the main [Shopsys repository](https://github.com/shopsys/shopsys).

Please, check our [Contribution Guide](https://github.com/shopsys/shopsys/blob/master/CONTRIBUTING.md) before contributing.

## Support
What to do when you are in troubles or need some help?
The best way is to join our [Slack](https://join.slack.com/t/shopsysframework/shared_invite/zt-11wx9au4g-e5pXei73UJydHRQ7nVApAQ).

If you want to [report issues](https://github.com/shopsys/shopsys/issues/new), please use the main [Shopsys repository](https://github.com/shopsys/shopsys).

[shopsys/shopsys]: (https://github.com/shopsys/shopsys)
