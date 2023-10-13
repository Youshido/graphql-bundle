<?php

namespace Youshido\GraphQLBundle;

use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\EventDispatcher\DependencyInjection\RegisterListenersPass;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;
use Youshido\GraphQLBundle\DependencyInjection\Compiler\GraphQlCompilerPass;

class GraphQLBundle extends AbstractBundle
{
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container->addCompilerPass(new GraphQlCompilerPass());
        $container->addCompilerPass(
            new RegisterListenersPass(
                'graphql.event_dispatcher',
                'graphql.event_listener',
                'graphql.event_subscriber'
            ),
            PassConfig::TYPE_BEFORE_REMOVING
        );
    }

    public function getPath(): string
    {
        return dirname(__DIR__);
    }
}