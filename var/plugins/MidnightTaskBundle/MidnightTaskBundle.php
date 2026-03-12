<?php

namespace KimaiPlugin\MidnightTaskBundle;

use App\Plugin\PluginInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class MidnightTaskBundle extends Bundle implements PluginInterface
{
    public function getName(): string
    {
        return 'MidnightTaskBundle';
    }

    public function getPath(): string
    {
        return __DIR__;
    }

    public function build(ContainerBuilder $container): void
    {
        parent::build($container);
    }
}
