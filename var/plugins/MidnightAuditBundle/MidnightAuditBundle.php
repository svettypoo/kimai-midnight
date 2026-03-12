<?php

namespace KimaiPlugin\MidnightAuditBundle;

use App\Plugin\PluginInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class MidnightAuditBundle extends Bundle implements PluginInterface
{
    public function getPath(): string
    {
        return __DIR__;
    }
}
