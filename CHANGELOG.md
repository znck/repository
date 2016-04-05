# Change-log

All Notable changes to `repository` will be documented in this file.

## [Unpublished]

## [2.2.0] - 2016-04-04

### Added
- **Boot Traits:** When repository is created, if any trait have `boot{ClassNameOfTrait}` function it will be called after repository is created.
- **HTTP Mode:** with `enableHttpMode` method, the repository can be configured to throw 404 error if resource is not in database. This works with `find` and `findBy` methods.
- **Self Instance:** state of repository is defined by criteria applied to it, if you need a repository in initial state call `self`.

### Removed
- Method `model` is deprecated and will be removed from 3.0.0+ versions. Instead of `model` method use `protected $modelClass` property to define model class.

[Unpublished]: https://github.com/znck/plug/compare/v2.2.0...HEAD
[2.2.0]: https://github.com/znck/plug/compare/v2.2.0...v2.1
