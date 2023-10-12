<?php

namespace Youshido\GraphQLBundle\src;

use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\EventDispatcher\DependencyInjection\RegisterListenersPass;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;
use Youshido\GraphQLBundle\src\DependencyInjection\Compiler\GraphQlCompilerPass;
use Youshido\GraphQLBundle\src\DependencyInjection\GraphQLExtension;

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


    public function getContainerExtension(): ?ExtensionInterface
    {
        if (null === $this->extension) {
            $this->extension = new GraphQLExtension();
        }

        return $this->extension;
    }

    public function getPath(): string
    {
        return dirname(__DIR__);
    }
}