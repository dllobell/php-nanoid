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

    public int $length;

    public bool $isPowerOfTwo;

    public function __construct(public string $value)
    {
        $this->length = strlen($value);

        $this->validateLength();
        $this->validateNoDuplicates();

        $this->isPowerOfTwo = ($this->length & ($this->length - 1)) === 0;
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
        if ($this->length !== count(array_unique(str_split($this->value)))) {
            throw new InvalidArgumentException('Alphabet must not contain duplicate characters.');
        }
    }
}
