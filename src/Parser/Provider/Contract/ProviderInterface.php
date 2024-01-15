<?php

namespace App\Parser\Provider\Contract;

interface ProviderInterface
{
    public function iteratePages(): \Generator;
}