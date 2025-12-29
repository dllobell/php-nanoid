<?php

declare(strict_types=1);

namespace Dllobell\NanoId\RandomBytesGenerator;

use Dllobell\NanoId\RandomBytesGenerator;
use Random\Engine;
use Random\Randomizer;
use RuntimeException;

final readonly class NativeRandomBytesGenerator implements RandomBytesGenerator
{
    private Randomizer $randomizer;

    public function __construct(?Engine $engine = null)
    {
        $this->randomizer = new Randomizer($engine);
    }

    public function generate(int $size): array
    {
        /** @var array<int, int>|false */
        $bytes = unpack('C*', $this->randomizer->getBytes($size));
        if ($bytes === false) {
            throw new RuntimeException('Failed to convert string to byte array');
        }

        return array_values($bytes);
    }
}
