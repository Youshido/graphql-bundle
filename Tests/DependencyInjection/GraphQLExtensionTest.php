<?php

namespace Youshido\GraphQLBundle\Tests\DependencyInjection;


use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Compiler\ResolveDefinitionTemplatesPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Youshido\GraphQLBundle\DependencyInjection\Compiler\GraphQlCompilerPass;
use Youshido\GraphQLBundle\DependencyInjection\GraphQLExtension;

class GraphQLExtensionTest extends \PHPUnit_Framework_TestCase
{
    public function testDefaultConfigIsUsed(): void
    {
        $container = $this->loadContainerFromFile('empty', 'yml');

        $this->assertNull($container->getParameter('graphql.schema_class'));
        $this->assertEquals(null, $container->getParameter('graphql.max_complexity'));
        $this->assertEquals(null, $container->getParameter('graphql.logger'));
        $this->assertEmpty($container->getParameter('graphql.security.white_list'));
        $this->assertEmpty($container->getParameter('graphql.security.black_list'));
        $this->assertEquals([
            'field' => false,
            'operation' => false,
        ],
            $container->getParameter('graphql.security.guard_config')
        );

        $this->assertTrue($container->getParameter('graphql.response.json_pretty'));
        $this->assertEquals([
            'Access-Control-Allow-Origin' => '*',
            'Access-Control-Allow-Headers' => 'Content-Type',
        ],
            $container->getParameter('graphql.response.headers')
        );
    }

    public function testDefaultCanBeOverridden(): void
    {
        $container = $this->loadContainerFromFile('full', 'yml');
        $this->assertEquals('AppBundle\GraphQL\Schema', $container->getParameter('graphql.schema_class'));
        $this->assertEquals(10, $container->getParameter('graphql.max_complexity'));
        $this->assertEquals('@logger', $container->getParameter('graphql.logger'));

        $this->assertEquals(['hello'], $container->getParameter('graphql.security.black_list'));
        $this->assertEquals(['world'], $container->getParameter('graphql.security.white_list'));
        $this->assertEquals([
            'field' => true,
            'operation' => true,
        ],
            $container->getParameter('graphql.security.guard_config')
        );

        $this->assertFalse($container->getParameter('graphql.response.json_pretty'));
        $this->assertEquals([
            'X-Powered-By' => 'GraphQL',
        ],
            $container->getParameter('graphql.response.headers')
        );

    }

    /**
     * @throws \Exception
     */
    private function loadContainerFromFile(string $file, string $type, array $services = array(), $skipEnvVars = false)
    {
        $container = new ContainerBuilder();
        if ($skipEnvVars && !method_exists($container, 'resolveEnvPlaceholders')) {
            $this->markTestSkipped('Runtime environment variables has been introduced in the Dependency Injection version 3.2.');
        }

        $container->setParameter('kernel.debug', false);
        $container->setParameter('kernel.cache_dir', '/tmp');
        foreach ($services as $id => $service) {
            $container->set($id, $service);
        }

        $container->registerExtension(new GraphQLExtension());
        $locator = new FileLocator(__DIR__ . '/Fixtures/config/' . $type);

        switch ($type) {
            case 'xml':
                $loader = new XmlFileLoader($container, $locator);
                break;
            case 'yml':
                $loader = new YamlFileLoader($container, $locator);
                break;
            case 'php':
                $loader = new PhpFileLoader($container, $locator);
                break;
            default:
                throw new \InvalidArgumentException('Invalid file type');
        }

        $loader->load($file . '.' . $type);
        $container->getCompilerPassConfig()->setOptimizationPasses(array(
            new GraphQlCompilerPass(),
        ));
        $container->getCompilerPassConfig()->setRemovingPasses(array());
        $container->compile();
        return $container;
    }
}