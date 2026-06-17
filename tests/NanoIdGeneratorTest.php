<?php

declare(strict_types=1);

use Dllobell\NanoId\AlphabetValue;
use Dllobell\NanoId\NanoIdGenerator;
use Dllobell\NanoId\RandomBytesGenerator;
use Dllobell\NanoId\RandomBytesGenerator\NativeRandomBytesGenerator;
use Random\Engine\Mt19937;

use function Pest\Faker\fake;

function cyrillicAlphabet(int $count): string
{
    return implode('', array_map(static fn (int $i): string => mb_chr(0x0430 + $i, encoding: 'UTF-8'), range(0, $count - 1)));
}

enum AlphabetEnum: string implements AlphabetValue
{
    case Numeric = '0123456789';

    public function value(): string
    {
        return $this->value;
    }
}

covers(NanoIdGenerator::class);

describe('NanoIdGenerator', function (): void {
    it('generates an ID with default parameters', function (): void {
        $generator = NanoIdGenerator::create();

        $id = $generator->generate();

        expect($id)->toHaveLength(21)->toMatch('/^[0-9A-Za-z_-]+$/');
    });

    it('generates an ID with a custom size', function (): void {
        $generator = NanoIdGenerator::create();

        expect($generator->generate(10))->toHaveLength(10);
    });

    it('generates an ID with a custom default size', function (): void {
        $generator = NanoIdGenerator::create(defaultSize: 30);

        expect($generator->generate())->toHaveLength(30);
    });

    it('generates an ID with a custom alphabet', function (): void {
        $alphabet = 'abc';
        $generator = NanoIdGenerator::create(alphabet: $alphabet);

        $id = $generator->generate();

        expect($id)->toHaveLength(21)->toMatch('/^[abc]+$/');
    });

    it('generates an ID from an AlphabetValue enum', function (): void {
        $generator = NanoIdGenerator::create(alphabet: AlphabetEnum::Numeric);

        $id = $generator->generate(10);

        expect($id)->toHaveLength(10)->toMatch('/^[0-9]+$/');
    });

    it('generates an ID from an AlphabetValue class', function (): void {
        $alphabet = new class implements AlphabetValue
        {
            public function value(): string
            {
                return '0123456789';
            }
        };

        $generator = NanoIdGenerator::create(alphabet: $alphabet);

        $id = $generator->generate(10);

        expect($id)->toHaveLength(10)->toMatch('/^[0-9]+$/');
    });

    it('uses custom RandomBytesGenerator', function (): void {
        $randomBytesGenerator = new class implements RandomBytesGenerator
        {
            public function generate(int $size): array
            {
                return array_fill(0, $size, 0);
            }
        };

        $generator = NanoIdGenerator::create(randomBytesGenerator: $randomBytesGenerator);

        expect($generator->generate())->toBe(str_repeat('0', 21));
    });

    it('processes every byte in a rejection sampling batch including index zero', function (): void {
        $randomBytesGenerator = new class implements RandomBytesGenerator
        {
            public function generate(int $size): array
            {
                return array_merge([0], array_fill(1, $size - 1, 255));
            }
        };

        $generator = NanoIdGenerator::create(
            alphabet: 'abc',
            randomBytesGenerator: $randomBytesGenerator,
        );

        expect($generator->generate(1))->toBe('a');
    });

    it('throws an exception for non-positive size', function (): void {
        $generator = NanoIdGenerator::create();

        expect(fn () => $generator->generate(0))->toThrow(InvalidArgumentException::class);
        expect(fn () => $generator->generate(fake()->numberBetween(PHP_INT_MIN, -1)))->toThrow(InvalidArgumentException::class);
    });

    it('throws an exception for non-positive default size', function (): void {
        expect(fn () => NanoIdGenerator::create(defaultSize: 0))
            ->toThrow(InvalidArgumentException::class, 'Size must be a positive integer.')
        ;
        expect(fn () => NanoIdGenerator::create(defaultSize: fake()->numberBetween(PHP_INT_MIN, -1)))
            ->toThrow(InvalidArgumentException::class, 'Size must be a positive integer.')
        ;
    });

    it('throws an exception for an empty alphabet', function (): void {
        expect(fn () => NanoIdGenerator::create(alphabet: ''))
            ->toThrow(InvalidArgumentException::class, 'Alphabet must contain at least 2 characters.')
        ;
    });

    it('throws an exception for an alphabet with less than 2 characters', function (): void {
        expect(fn () => NanoIdGenerator::create(alphabet: 'a'))
            ->toThrow(InvalidArgumentException::class, 'Alphabet must contain at least 2 characters.')
        ;
    });

    it('throws an exception for alphabet with more than 256 characters', function (): void {
        $alphabet = str_repeat('a', fake()->numberBetween(257, 300));

        expect(fn () => NanoIdGenerator::create(alphabet: $alphabet))
            ->toThrow(InvalidArgumentException::class, 'Alphabet must not exceed 256 characters.')
        ;
    });

    it('throws an exception for alphabet with duplicate characters', function (): void {
        expect(fn () => NanoIdGenerator::create(alphabet: 'aab'))
            ->toThrow(InvalidArgumentException::class, 'Alphabet must not contain duplicate characters.')
        ;
    });

    it('generates an ID with a Unicode alphabet', function (): void {
        $alphabet = '0123456789абвгдеё';
        $generator = NanoIdGenerator::create(alphabet: $alphabet);

        $id = $generator->generate(5);

        expect($id)->toHaveLength(5);
        expect(mb_str_split($id, encoding: 'UTF-8'))->each->toBeIn(mb_str_split($alphabet, encoding: 'UTF-8'));
    });

    it('accepts an alphabet with up to 256 Unicode characters', function (): void {
        $generator = NanoIdGenerator::create(alphabet: cyrillicAlphabet(200));

        expect($generator->generate(10))->toHaveLength(10);
    });

    it('throws an exception for alphabet with more than 256 Unicode characters', function (): void {
        expect(fn () => NanoIdGenerator::create(alphabet: cyrillicAlphabet(257)))
            ->toThrow(InvalidArgumentException::class, 'Alphabet must not exceed 256 characters.')
        ;
    });

    it('throws an exception for alphabet with duplicate Unicode characters', function (): void {
        expect(fn () => NanoIdGenerator::create(alphabet: 'абвгдеёа'))
            ->toThrow(InvalidArgumentException::class, 'Alphabet must not contain duplicate characters.')
        ;
    });

    it('throws an exception for invalid UTF-8 alphabet', function (): void {
        expect(fn () => NanoIdGenerator::create(alphabet: "\xC3\x28"))
            ->toThrow(InvalidArgumentException::class, 'Alphabet must be valid UTF-8.')
        ;
    });

    it('generates matching ids for golden examples', function (int $seed, string $expectedId): void {
        $generator = NanoIdGenerator::create(
            randomBytesGenerator: new NativeRandomBytesGenerator(
                engine: new Mt19937(seed: $seed),
            ),
        );

        expect($generator->generate())->toBe($expectedId);
    })->with([
        'seed 0' => [0, 'wQEo3OXC0sMbqN4fkC-Ah'],
        'seed 42' => [42, '7wZpfjqLEoY3SBfyoVXSb'],
        'seed 123456' => [123456, 'g2jEtagCmsvHn3HQfW1O1'],
        'seed 987654321' => [987654321, 'XrvLl3HdbsEg79NPIIkT7'],
        'seed 2147483647' => [2147483647, 'dMNcPZP2CdQyT_Qkf_q7k'],
    ]);

    it('generates matching ids for non-power-of-two alphabet golden examples', function (int $seed, string $expectedId): void {
        $generator = NanoIdGenerator::create(
            alphabet: 'abc',
            randomBytesGenerator: new NativeRandomBytesGenerator(
                engine: new Mt19937(seed: $seed),
            ),
        );

        expect($generator->generate())->toBe($expectedId);
    })->with([
        'seed 0' => [0, 'caabcbbcbaaabcbacbaaa'],
        'seed 42' => [42, 'baccccacacbaccbabbbcc'],
        'seed 123456' => [123456, 'abbbcbbaccacbaaabcbbc'],
    ]);

    it('generates matching ids for Unicode alphabet golden examples', function (int $seed, string $expectedId): void {
        $generator = NanoIdGenerator::create(
            alphabet: '0123456789абвгдеё',
            randomBytesGenerator: new NativeRandomBytesGenerator(
                engine: new Mt19937(seed: $seed),
            ),
        );

        expect($generator->generate())->toBe($expectedId);
    })->with([
        'seed 0' => [0, 'б9в86136ё8е9дг1а9ёвё0'],
        'seed 42' => [42, 'а0ёёа30д41в0830д64в7г'],
        'seed 123456' => [123456, '16ввг98761а671е4658д4'],
    ]);
});
