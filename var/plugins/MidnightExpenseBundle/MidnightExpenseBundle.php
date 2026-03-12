<?php

namespace KimaiPlugin\MidnightExpenseBundle;

use App\Plugin\PluginInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class MidnightExpenseBundle extends Bundle implements PluginInterface
{
    public function getPath(): string
    {
        return __DIR__;
    }
}
