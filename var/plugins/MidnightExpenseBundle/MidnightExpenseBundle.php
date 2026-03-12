<?php

namespace KimaiPlugin\MidnightExpenseBundle;

use App\Plugin\PluginInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class MidnightExpenseBundle extends Bundle implements PluginInterface
{
    public function getName(): string
    {
        return 'MidnightExpenseBundle';
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
