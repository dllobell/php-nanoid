<?php

declare(strict_types=1);

use Dllobell\NanoId\NanoIdGenerator;
use Dllobell\NanoId\RandomBytesGenerator;
use Dllobell\NanoId\RandomBytesGenerator\NativeRandomBytesGenerator;
use Random\Engine\Mt19937;

use function Pest\Faker\fake;

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

    it('throws an exception for non-positive size', function (): void {
        $generator = NanoIdGenerator::create();

        expect(fn () => $generator->generate(0))->toThrow(InvalidArgumentException::class);
        expect(fn () => $generator->generate(fake()->numberBetween(PHP_INT_MIN, -1)))->toThrow(InvalidArgumentException::class);
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

    it('generates matching ids for golden examples', function (int $seed, string $expectedId): void {
        $generator = NanoIdGenerator::create(
            randomBytesGenerator: new NativeRandomBytesGenerator(
                engine: new Mt19937(seed: $seed),
            ),
        );

        expect($generator->generate())->toBe($expectedId);
    })->with([
        'seed 0' => [0, 'hA-Ckf4NqbMs0CXO3oEQw'],
        'seed 42' => [42, 'bSXVoyfBS3YoELqjfpZw7'],
        'seed 123456' => [123456, '1O1WfQH3nHvsmCgatEj2g'],
        'seed 987654321' => [987654321, '7TkIIPN97gEsbdH3lLvrX'],
        'seed 2147483647' => [2147483647, 'k7q_fkQ_TyQdC2PZPcNMd'],
    ]);
});
