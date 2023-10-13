<?php

namespace Youshido\GraphQLBundle;

use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\EventDispatcher\DependencyInjection\RegisterListenersPass;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;
use Youshido\GraphQL\Schema\AbstractSchema;
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

    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        // load an XML, PHP or Yaml file
        $container->import('../config/services.xml');

        $preparedHeaders = [];
        $headers = $config['response']['headers'] ?: $this->getDefaultHeaders();
        foreach ($headers as $header) {
            $preparedHeaders[$header['name']] = $header['value'];
        }

        $container->parameters()->set('graphql.response.headers', $preparedHeaders);
        $container->parameters()->set('graphql.schema_class', $config['schema_class']);
        $container->parameters()->set('graphql.schema_service', $config['schema_service']);
        $container->parameters()->set('graphql.logger', $config['logger']);
        $container->parameters()->set('graphql.max_complexity', $config['max_complexity']);
        $container->parameters()->set('graphql.response.json_pretty', $config['response']['json_pretty']);

        $container->parameters()->set('graphql.security.guard_config', [
            'field' => $config['security']['guard']['field'],
            'operation' => $config['security']['guard']['operation']
        ]);

        $container->parameters()->set('graphql.security.black_list', $config['security']['black_list']);
        $container->parameters()->set('graphql.security.white_list', $config['security']['white_list']);

        if ($config['graphql']) {
            $container->services()
                ->get('graphql.schema')
                ->class(AbstractSchema::class);
        }
    }

    private function getDefaultHeaders(): array
    {
        return [
            ['name' => 'Access-Control-Allow-Origin', 'value' => '*'],
            ['name' => 'Access-Control-Allow-Headers', 'value' => 'Content-Type'],
        ];
    }

    public function getPath(): string
    {
        return dirname(__DIR__);
    }
}