<?php

namespace Youshido\GraphQLBundle\DependencyInjection;

use Exception;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class GraphQLExtension extends Extension
{
    /**
     * {@inheritdoc}
     * @throws Exception
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $preparedHeaders = [];
        $headers = $config['response']['headers'] ?: $this->getDefaultHeaders();
        foreach ($headers as $header) {
            $preparedHeaders[$header['name']] = $header['value'];
        }

        $container->setParameter('graphql.response.headers', $preparedHeaders);
        $container->setParameter('graphql.schema_class', $config['schema_class']);
        $container->setParameter('graphql.schema_service', $config['schema_service']);
        $container->setParameter('graphql.logger', $config['logger']);
        $container->setParameter('graphql.max_complexity', $config['max_complexity']);
        $container->setParameter('graphql.response.json_pretty', $config['response']['json_pretty']);

        $container->setParameter('graphql.security.guard_config', [
            'field' => $config['security']['guard']['field'],
            'operation' => $config['security']['guard']['operation']
        ]);

        $container->setParameter('graphql.security.black_list', $config['security']['black_list']);
        $container->setParameter('graphql.security.white_list', $config['security']['white_list']);


        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__ . '/../../config'));
        $loader->load('services.xml');
    }

    private function getDefaultHeaders(): array
    {
        return [
            ['name' => 'Access-Control-Allow-Origin', 'value' => '*'],
            ['name' => 'Access-Control-Allow-Headers', 'value' => 'Content-Type'],
        ];
    }

    public function getAlias(): string
    {
        return "graphql";
    }
}