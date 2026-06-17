<?php

declare(strict_types=1);

namespace Dllobell\NanoId;

use InvalidArgumentException;

/**
 * @internal
 */
final readonly class Size
{
    /**
     * @var positive-int
     */
    public int $value;

    /**
     * @throws InvalidArgumentException
     */
    public function __construct(int $value)
    {
        if ($value <= 0) {
            throw new InvalidArgumentException('Size must be a positive integer.');
        }

        $this->value = $value;
    }
}
