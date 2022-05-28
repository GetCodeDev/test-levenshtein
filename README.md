
[<img src="https://github-ads.s3.eu-central-1.amazonaws.com/support-ukraine.svg?t=1" />](https://supportukrainenow.org)

# This is my package test-levenshtein

[![Latest Version on Packagist](https://img.shields.io/packagist/v/getcodedev/test-levenshtein.svg?style=flat-square)](https://packagist.org/packages/getcodedev/test-levenshtein)
[![GitHub Tests Action Status](https://img.shields.io/github/workflow/status/getcodedev/test-levenshtein/run-tests?label=tests)](https://github.com/getcodedev/test-levenshtein/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/workflow/status/getcodedev/test-levenshtein/Check%20&%20fix%20styling?label=code%20style)](https://github.com/getcodedev/test-levenshtein/actions?query=workflow%3A"Check+%26+fix+styling"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/getcodedev/test-levenshtein.svg?style=flat-square)](https://packagist.org/packages/getcodedev/test-levenshtein)

This is where your description should go. Limit it to a paragraph or two. Consider adding a small example.

## Installation

You can install the package via composer:

```bash
composer require getcodedev/test-levenshtein
```

You can publish and run the migrations with:

```bash
php artisan vendor:publish --tag="test-levenshtein-migrations"
php artisan migrate
```

## Usage

```php
$duplicates = check_duplicates(
    model: User::class,
    search: [
        'first_name' => 'John',
        'home' => [
            'address' => "bbbbb, rrr BB, 77777",
        ],
    ],
    concat_search_columns: [
        'home' => [
            'address' => 'homes.street, homes.city homes.state, homes.zip',
        ],
    ],
    priority_columns: [
        'homes.address',
        'users.first_name',
    ],
    with_similarity_min_common: 50,
    limit: 10
);
```

## Testing

```bash

# 1. clone this package

# 2. Then need to set configs for mysql connection in phpunit.xml
cp phpunit.xml.dist phpunit.xml

# 3. Run tests
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](https://github.com/spatie/.github/blob/main/CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Roman Nebesnuy](https://github.com/GetCodeDev)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
