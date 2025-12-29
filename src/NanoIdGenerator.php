<?php

declare(strict_types=1);

namespace Dllobell\NanoId;

use Dllobell\NanoId\RandomBytesGenerator\NativeRandomBytesGenerator;

final readonly class NanoIdGenerator
{
    private const string DEFAULT_ALPHABET = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ_abcdefghijklmnopqrstuvwxyz-';

    private const int DEFAULT_SIZE = 21;

    private function __construct(
        private Alphabet $alphabet,
        private Size $defaultSize,
        private RandomBytesGenerator $randomBytesGenerator,
    ) {}

    public static function create(
        AlphabetValue | string | null $alphabet = null,
        ?int $defaultSize = null,
        ?RandomBytesGenerator $randomBytesGenerator = null,
    ): self {
        return new self(
            new Alphabet(self::parseAlphabetValue($alphabet)),
            new Size($defaultSize ?? self::DEFAULT_SIZE),
            $randomBytesGenerator ?? new NativeRandomBytesGenerator(),
        );
    }

    private static function parseAlphabetValue(AlphabetValue | string | null $alphabet): string
    {
        if ($alphabet === null) {
            return self::DEFAULT_ALPHABET;
        }

        if ($alphabet instanceof AlphabetValue) {
            return $alphabet->value();
        }

        return $alphabet;
    }

    public function generate(?int $size = null): string
    {
        $size = $size !== null ? new Size($size) : $this->defaultSize;

        return $this->alphabet->isPowerOfTwo
            ? $this->generateForPowerOfTwoLengthAlphabet($size)
            : $this->generateForNonPowerOfTwoLengthAlphabet($size);
    }

    private function generateForPowerOfTwoLengthAlphabet(Size $size): string
    {
        $mask = $this->alphabet->length - 1;

        $bytes = $this->randomBytesGenerator->generate($size->value);
        for ($i = 0, $id = ''; $i < $size->value; $i++) {
            $index = $bytes[$i] & $mask;

            $id .= $this->alphabet->value[$index];
        }

        return $id;
    }

    private function generateForNonPowerOfTwoLengthAlphabet(Size $size): string
    {
        $mask = (2 << (int) (log($this->alphabet->length - 1) / M_LN2)) - 1;

        $step = (int) ceil((1.6 * $mask * $size->value) / $this->alphabet->length);
        $id = '';
        while (true) {
            $bytes = $this->randomBytesGenerator->generate($step);

            for ($i = $step - 1; $i > 0; $i--) {
                $byte = $bytes[$i] & $mask;
                if ($byte < $this->alphabet->length) {
                    $id .= $this->alphabet->value[$byte];
                    if (strlen($id) === $size->value) {
                        return $id;
                    }
                }
            }
        }
    }
}
