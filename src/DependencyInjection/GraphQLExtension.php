<?php

namespace Youshido\GraphQLBundle\DependencyInjection;

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
    private array $config = [];

    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $this->config = $this->processConfiguration($configuration, $configs);

        $preparedHeaders = [];
        $headers = $this->config['response']['headers'] ? $this->config['response']['headers'] : $this->getDefaultHeaders();
        foreach ($headers as $header) {
            $preparedHeaders[$header['name']] = $header['value'];
        }

        $container->setParameter('graphql.response.headers', $preparedHeaders);
        $container->setParameter('graphql.schema_class', $this->config['schema_class']);
        $container->setParameter('graphql.schema_service', $this->config['schema_service']);
        $container->setParameter('graphql.logger', $this->config['logger']);
        $container->setParameter('graphql.max_complexity', $this->config['max_complexity']);
        $container->setParameter('graphql.response.json_pretty', $this->config['response']['json_pretty']);

        $container->setParameter('graphql.security.guard_config', [
            'field' => $this->config['security']['guard']['field'],
            'operation' => $this->config['security']['guard']['operation']
        ]);

        $container->setParameter('graphql.security.black_list', $this->config['security']['black_list']);
        $container->setParameter('graphql.security.white_list', $this->config['security']['white_list']);


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
