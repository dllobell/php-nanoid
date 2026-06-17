<?php

declare(strict_types=1);

namespace Dllobell\NanoId;

interface AlphabetProvider
{
    public function alphabet(): string;
}
