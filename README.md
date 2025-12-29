# Nano ID

<p>
<a href="https://packagist.org/packages/dllobell/nanoid"><img src="https://img.shields.io/packagist/dt/dllobell/nanoid" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/dllobell/nanoid"><img src="https://img.shields.io/packagist/v/dllobell/nanoid" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/dllobell/nanoid"><img src="https://img.shields.io/packagist/l/dllobell/nanoid" alt="License"></a>
<a href="https://php.net"><img src="https://img.shields.io/badge/PHP-8.4+-777BB4?logo=php" alt="PHP Minimum Version"></a>
</p>

Lightweight, framework-agnostic Nano ID generation for PHP.

Nano ID is a tiny, secure, URL-friendly, unique string ID generator, ported from the original Javascript project [ai/nanoid](https://github.com/ai/nanoid).

## Requirements

- PHP 8.4 or higher

## Installation

Install via [Composer](https://getcomposer.org):

```bash
composer require dllobell/nanoid
```

## Usage

### Quick start

```php
use Dllobell\NanoId\NanoIdGenerator;

$generator = NanoIdGenerator::create();

$id = $generator->generate(); // "Xy7z_9A-mK2jLp4qR5sTv"
```

### Custom size

You can specify the size of the ID when generating it:

```php
$id = $generator->generate(10); // "aB3dE5fG7h"
```

Or set a default size when creating the generator:

```php
$generator = NanoIdGenerator::create(defaultSize: 10);

$id = $generator->generate(); // "kL9mN8oP7q"
```

### Custom alphabet

You can specify a custom alphabet when creating the generator:

```php
$generator = NanoIdGenerator::create(alphabet: '0123456789');

$id = $generator->generate(); // "857201493620581749302"
```

> **Note:** Alphabets must contain between 2 and 256 unique characters. Duplicate characters are not allowed.

#### Using the `AlphabetValue` interface

You can pass an object implementing the `AlphabetValue` interface. This is particularly useful when using enums to create custom alphabet sets:

```php
use Dllobell\NanoId\AlphabetValue;
use Dllobell\NanoId\NanoIdGenerator;

enum MyAlphabets: string implements AlphabetValue
{
    case Uppercase = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    case Lowercase = 'abcdefghijklmnopqrstuvwxyz';
    case Numeric = '0123456789';

    public function value(): string
    {
        return $this->value;
    }
}

$generator = NanoIdGenerator::create(alphabet: MyAlphabets::Numeric);
```

#### Automatic generation

If you want to use a set of predefined alphabets or manage your own via `composer.json`, you can use the [Nano ID Composer Plugin](https://github.com/dllobell/php-nanoid-plugin):

```bash
composer require dllobell/nanoid-plugin
```

This plugin automatically generates an `Alphabets` enum that implements the `AlphabetValue` interface whenever you run `composer install`, `update`, or `dump-autoload`.

You can also trigger the generation manually using the following command:

```bash
composer nanoid:generate-alphabets
```

Then use it in your code:

```php
use Dllobell\NanoId\Alphabets;
use Dllobell\NanoId\NanoIdGenerator;

$generator = NanoIdGenerator::create(alphabet: Alphabets::Uppercase);
```


By default, it includes the following set of common alphabets:

| Name | Value |
| --- | --- |
| `Default` | `0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ_abcdefghijklmnopqrstuvwxyz-` |
| `Uppercase` | `ABCDEFGHIJKLMNOPQRSTUVWXYZ` |
| `Lowercase` | `abcdefghijklmnopqrstuvwxyz` |
| `Numeric` | `0123456789` |
| `Alphanumeric` | `0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz` |
| `HexadecimalUppercase` | `0123456789ABCDEF` |
| `HexadecimalLowercase` | `0123456789abcdef` |
| `NoLookalikes` | `346789ABCDEFGHJKLMNPQRTUVWXYabcdefghijkmnpqrtwxyz` |

You can also define your own custom alphabets in your `composer.json` file:

```json
{
    "extra": {
        "nanoid": {
            "alphabets": {
                "Vowels": "aeiou"
            }
        }
    }
}
```

You can then use it in your code using the same `Alphabets` enum:

```php
use Dllobell\NanoId\Alphabets;
use Dllobell\NanoId\NanoIdGenerator;

$generator = NanoIdGenerator::create(alphabet: Alphabets::Vowels);
```

### Custom Random Bytes Generator

By default, the `NativeRandomBytesGenerator` is used for generating random bytes, you can inject a custom random bytes generator by implementing the `RandomBytesGenerator` interface:

```php
use Dllobell\NanoId\RandomBytesGenerator;

final readonly class MyRandomBytesGenerator implements RandomBytesGenerator
{
    public function generate(int $size): array
    {
        // Return an array of random integers
        return [/* ... */];
    }
}

$generator = NanoIdGenerator::create(randomBytesGenerator: new MyRandomBytesGenerator());
```

### Deterministic generation

The built-in `NativeRandomBytesGenerator` accepts a custom `Engine` for deterministic/seeded generation, useful for testing:

```php
use Dllobell\NanoId\NanoIdGenerator;
use Dllobell\NanoId\RandomBytesGenerator\NativeRandomBytesGenerator;
use Random\Engine\Mt19937;

$generator = NanoIdGenerator::create(
    randomBytesGenerator: new NativeRandomBytesGenerator(
        engine: new Mt19937(seed: 42),
    ),
);

$id = $generator->generate(); // Always produces the same ID for the same seed
```

## Credits

The Nano ID generation algorithm is ported from the [ai/nanoid](https://github.com/ai/nanoid) project.

## License

The MIT License (MIT). See the [license file](LICENSE) for more information.
