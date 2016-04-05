Repository
==========
Don't use your [Eloquent model]() directly into your controllers. Instead create a repository and inject it, so your controllers can be tested better.

![Repository](cover.png)

<p align="center">
  <a href="https://styleci.io/repos/42290942">
    <img src="https://styleci.io/repos/42290942/shield" alt="StyleCI Status" />
  </a>
  <a href="https://circleci.com/gh/znck/repository">
    <img src="https://circleci.com/gh/znck/repository.svg?style=svg" alt="Build Status" />
  </a>
  <a href="https://coveralls.io/github/znck/repository?branch=master">
    <img src="https://coveralls.io/repos/github/znck/repository/badge.svg?branch=master&style=flat-square" alt="Coverage Status" />
  </a>
  <a href="LICENSE">
    <img src="https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square" alt="Software License" />
  </a>
  <a href="https://packagist.org/packages/znck/repository">
    <img src="https://img.shields.io/packagist/v/znck/repository.svg?style=flat-square" alt="Packagist" />
  </a>
  <a href="https://github.com/znck/repostory/releases">
    <img src="https://img.shields.io/github/release/znck/repository.svg?style=flat-square" alt="Latest Version" />
  </a>

  <a href="https://github.com/znck/repository/issues">
    <img src="https://img.shields.io/github/issues/znck/repository.svg?style=flat-square" alt="Issues" />
  </a>
</p>

> ## Development Milestones
- [x] Build a robust framework for repository implementation.
- [x] Build a strong test suite.
- [ ] Build a collection of common criterion.

## Installation

Either [PHP](https://php.net) 7.0+ is required.

To get the latest version of Repository, simply require the project using [Composer](https://getcomposer.org):

```bash
$ composer require znck/repository
```

Instead, you may of course manually update your require block and run `composer update` if you so choose:

```json
{
    "require": {
        "znck/repository": "^2.2"
    }
}
```

Once `Repository` is installed, you have to register its service provider. Open `config/app.php` and add `Znck\Repository\RepositoryServiceProvider::class` to `providers` key. Your `config/app.php` should look like this.

```php
<?php return [
  // ...
  'providers' => [
    // ....
    Znck\Repository\RepositoryServiceProvider::class,
  ]
  // ...
];
```

## Usage
> TODO: Add usage docs.

`Repository` provides command (`php artisan make:repository`) to create a new repository class.

## Change log

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Testing

``` bash
$ composer test
```

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) and [CONDUCT](CONDUCT.md) for details.

## Security

If you discover any security related issues, please email hi@znck.me instead of using the issue tracker.

## Credits

- [Rahul Kadyan][link-author]
- [All Contributors][link-contributors]

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.

[link-author]: https://github.com/znck
[link-contributors]: ../../contributors
