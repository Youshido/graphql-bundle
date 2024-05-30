<?php

namespace Youshido\GraphQLBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Config\Resource\DirectoryResource;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\DependencyInjection\ContainerInterface;

class GraphQLConfigureCommand extends Command
{
    const PROJECT_NAMESPACE = 'App';

    private ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct();
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('graphql:configure')
            ->setDescription('Generates GraphQL Schema class')
            ->addOption('composer');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $isComposerCall = $input->getOption('composer');

        $rootDir    = $this->container->getParameter('kernel.root_dir');
        $configFile = $rootDir . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'config/packages/graphql.yml';

        $className       = 'Schema';
        $schemaNamespace = self::PROJECT_NAMESPACE . '\\GraphQL';
        $graphqlPath     = rtrim($rootDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'GraphQL';
        $classPath       = $graphqlPath . DIRECTORY_SEPARATOR . $className . '.php';

        $inputHelper = $this->getHelper('question');
        if (file_exists($classPath)) {
            if (!$isComposerCall) {
                $output->writeln(sprintf('Schema class %s was found.', $schemaNamespace . '\\' . $className));
            }
        } else {
            $question = new ConfirmationQuestion(sprintf('Confirm creating class at %s ? [Y/n]', $schemaNamespace . '\\' . $className), true);
            if (!$inputHelper->ask($input, $output, $question)) {
                return;
            }

            if (!is_dir($graphqlPath)) {
                mkdir($graphqlPath, 0777, true);
            }
            file_put_contents($classPath, $this->getSchemaClassTemplate($schemaNamespace, $className));

            $output->writeln('Schema file has been created at');
            $output->writeln($classPath . "\n");

            if (!file_exists($configFile)) {
                $question = new ConfirmationQuestion(sprintf('Config file not found (look at %s). Create it? [Y/n]', $configFile), true);
                if (!$inputHelper->ask($input, $output, $question)) {
                    return;
                }

                touch($configFile);
            }

            $originalConfigData = file_get_contents($configFile);
            if (strpos($originalConfigData, 'graphql') === false) {
                $projectNameSpace = self::PROJECT_NAMESPACE;
                $configData       = <<<CONFIG
graphql:
    schema_class: "{$projectNameSpace}\\\\GraphQL\\\\{$className}"

CONFIG;
                file_put_contents($configFile, $configData . $originalConfigData);
            }
        }
        if (!$this->graphQLRouteExists()) {
            $question = new ConfirmationQuestion('Confirm adding GraphQL route? [Y/n]', true);
            $resource = $this->getMainRouteConfig();
            if ($resource && $inputHelper->ask($input, $output, $question)) {
                $routeConfigData = <<<CONFIG

graphql:
    resource: "@GraphQLBundle/Controller/"
CONFIG;
                file_put_contents($resource, $routeConfigData, FILE_APPEND);
                $output->writeln('Config was added to ' . $resource);
            }
        } else {
            if (!$isComposerCall) {
                $output->writeln('GraphQL default route was found.');
            }
        }
    }

    /**
     * @return null|string
     *
     * @throws \Exception
     */
    protected function getMainRouteConfig()
    {
        $routerResources = $this->getContainer()->get('router')->getRouteCollection()->getResources();
        foreach ($routerResources as $resource) {
            /** @var FileResource|DirectoryResource $resource */
            if (method_exists($resource, 'getResource') && substr($resource->getResource(), -11) == 'routes.yaml') {
                return $resource->getResource();
            }
        }

        return null;
    }

    /**
     * @return bool
     * @throws \Exception
     */
    protected function graphQLRouteExists()
    {
        $routerResources = $this->getContainer()->get('router')->getRouteCollection()->getResources();
        foreach ($routerResources as $resource) {
            /** @var FileResource|DirectoryResource $resource */
            if (method_exists($resource, 'getResource') && strpos($resource->getResource(), 'GraphQLController.php') !== false) {
                return true;
            }
        }

        return false;
    }

    protected function generateRoutes()
    {

    }

    protected function getSchemaClassTemplate($nameSpace, $className = 'Schema')
    {
        $tpl = <<<TEXT
<?php
/**
 * This class was automatically generated by GraphQL Schema generator
 */

namespace $nameSpace;

use Youshido\GraphQL\Schema\AbstractSchema;
use Youshido\GraphQL\Config\Schema\SchemaConfig;
use Youshido\GraphQL\Type\Scalar\StringType;

class $className extends AbstractSchema
{
    public function build(SchemaConfig \$config)
    {
        \$config->getQuery()->addFields([
            'hello' => [
                'type'    => new StringType(),
                'args'    => [
                    'name' => [
                        'type' => new StringType(),
                        'defaultValue' => 'Stranger'
                    ]
                ],
                'resolve' => function (\$context, \$args) {
                    return 'Hello ' . \$args['name'];
                }
            ]
        ]);
    }

}


TEXT;

        return $tpl;
    }
}
