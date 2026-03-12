<?php

namespace KimaiPlugin\MidnightTaskBundle;

use App\Plugin\PluginInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class MidnightTaskBundle extends Bundle implements PluginInterface
{
    public function getPath(): string
    {
        return __DIR__;
    }
}
