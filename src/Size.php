<?php

declare(strict_types=1);

namespace Dllobell\NanoId;

use InvalidArgumentException;

/**
 * @internal
 */
final readonly class Size
{
    public function __construct(public int $value)
    {
        if ($this->value <= 0) {
            throw new InvalidArgumentException('Size must be a positive integer.');
        }
    }
}
