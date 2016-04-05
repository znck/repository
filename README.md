Repository
==========
Don't use your Eloquent models directly into your controllers, instead create a repository.

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
- [ ] Create documentation/wiki.
- [ ] Build a collection of common criterion.

## Installation

[PHP](https://php.net) 7.0+ is required.

To get the latest version of Repository, simply require the project using [Composer](https://getcomposer.org):

```bash
$ composer require znck/repository
```

Instead, you may of course manually update your require block and run `composer update` if you so choose:

```json
{
    "require": {
        "znck/repository": "^0.2"
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
`Repository` provides command (`php artisan make:repository`) to create a new repository class.

- Create a new repository
  ```bash
  $ php artisan make:repository UserRepository
  ```
- Add logic for `create`, `update` and `delete` method in `app/Repositories/UserRepository.php` file.
- Now you can inject this repository in your controllers.
  ```php
  ...
  class UserController extends Controller {
    public function register(UserRepository $repository, Request $request) {
      $user = $repository->create($request->all());
      if (!$user) {
        // Throw validation error or something.
      }
      return response('', 202);
    }
    
    public function index(UserRepository $repository) {
      return $repository->all();
      // return $repository->paginate(20);
    }
    
    public function show(UserRepository $repository, $id) {
      return $repository->enableHttpMode()->find($id); // This will throw 404 exception if not found.
    }
  }
  ```
- TODO: Add wiki.

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
