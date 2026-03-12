<?php

namespace KimaiPlugin\MidnightBudgetBundle;

use App\Plugin\PluginInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class MidnightBudgetBundle extends Bundle implements PluginInterface
{
    public function getPath(): string
    {
        return __DIR__;
    }
}
