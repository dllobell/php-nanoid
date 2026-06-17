<?php

declare(strict_types=1);

namespace Dllobell\NanoId;

use InvalidArgumentException;

/**
 * @internal
 */
final readonly class Alphabet
{
    private const int MIN_LENGTH = 2;

    private const int MAX_LENGTH = 256;

    /**
     * @var list<string>
     */
    public array $characters;

    public int $length;

    public int $safeByteCutoff;

    /**
     * @throws InvalidArgumentException
     */
    public function __construct(string $value)
    {
        $this->validateEncoding($value);

        $this->characters = mb_str_split($value, encoding: 'UTF-8');
        $this->length = count($this->characters);

        $this->validateLength();
        $this->validateNoDuplicates();

        $this->safeByteCutoff = 256 - (256 % $this->length);
    }

    private function validateEncoding(string $value): void
    {
        if (!mb_check_encoding($value, encoding: 'UTF-8')) {
            throw new InvalidArgumentException('Alphabet must be valid UTF-8.');
        }
    }

    private function validateLength(): void
    {
        if ($this->length < self::MIN_LENGTH) {
            throw new InvalidArgumentException(
                sprintf('Alphabet must contain at least %d characters.', self::MIN_LENGTH),
            );
        }

        if ($this->length > self::MAX_LENGTH) {
            throw new InvalidArgumentException(
                sprintf('Alphabet must not exceed %d characters.', self::MAX_LENGTH),
            );
        }
    }

    private function validateNoDuplicates(): void
    {
        if ($this->length !== count(array_unique($this->characters, SORT_STRING))) {
            throw new InvalidArgumentException('Alphabet must not contain duplicate characters.');
        }
    }
}
