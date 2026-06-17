<?php

declare(strict_types=1);

namespace Dllobell\NanoId;

use Dllobell\NanoId\RandomBytesGenerator\NativeRandomBytesGenerator;
use BackedEnum;
use InvalidArgumentException;

final readonly class NanoIdGenerator
{
    private const string DEFAULT_ALPHABET = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ_abcdefghijklmnopqrstuvwxyz-';

    private const int DEFAULT_SIZE = 21;

    private function __construct(
        private Alphabet $alphabet,
        private Size $defaultSize,
        private RandomBytesGenerator $randomBytesGenerator,
    ) {}

    /**
     * @throws InvalidArgumentException
     */
    public static function create(
        AlphabetProvider | BackedEnum | string | null $alphabet = null,
        ?int $defaultSize = null,
        ?RandomBytesGenerator $randomBytesGenerator = null,
    ): self {
        return new self(
            new Alphabet(self::parseAlphabet($alphabet)),
            new Size($defaultSize ?? self::DEFAULT_SIZE),
            $randomBytesGenerator ?? new NativeRandomBytesGenerator(),
        );
    }

    /**
     * @throws InvalidArgumentException
     */
    private static function parseAlphabet(AlphabetProvider | BackedEnum | string | null $alphabet): string
    {
        if ($alphabet === null) {
            return self::DEFAULT_ALPHABET;
        }

        if ($alphabet instanceof AlphabetProvider) {
            return $alphabet->alphabet();
        }

        if ($alphabet instanceof BackedEnum) {
            $value = $alphabet->value;

            if (!is_string($value)) {
                throw new InvalidArgumentException('Alphabet backed enum must be string-backed.');
            }

            return $value;
        }

        return $alphabet;
    }

    /**
     * @throws InvalidArgumentException
     */
    public function generate(?int $size = null): string
    {
        $size = $size !== null ? new Size($size) : $this->defaultSize;

        return $this->alphabet->safeByteCutoff === 256
            ? $this->generateForPowerOfTwoLengthAlphabet($size)
            : $this->generateForNonPowerOfTwoLengthAlphabet($size);
    }

    private function generateForPowerOfTwoLengthAlphabet(Size $size): string
    {
        $mask = $this->alphabet->length - 1;

        $bytes = $this->randomBytesGenerator->generate($size->value);
        for ($i = $size->value - 1, $id = ''; $i >= 0; $i--) {
            $index = $bytes[$i] & $mask;

            $id .= $this->alphabet->characters[$index];
        }

        return $id;
    }

    private function generateForNonPowerOfTwoLengthAlphabet(Size $size): string
    {
        $step = (int) ceil((1.6 * 256 * $size->value) / $this->alphabet->safeByteCutoff);
        $id = '';
        $idLength = 0;
        while (true) {
            $bytes = $this->randomBytesGenerator->generate($step);

            for ($i = $step - 1; $i >= 0; $i--) {
                $byte = $bytes[$i];
                if ($byte < $this->alphabet->safeByteCutoff) {
                    $id .= $this->alphabet->characters[$byte % $this->alphabet->length];
                    $idLength++;
                    if ($idLength === $size->value) {
                        return $id;
                    }
                }
            }
        }
    }
}
