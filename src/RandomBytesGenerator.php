<?php

declare(strict_types=1);

namespace Dllobell\NanoId;

interface RandomBytesGenerator
{
    /**
     * @param positive-int $size
     *
     * @return list<int>
     */
    public function generate(int $size): array;
}
