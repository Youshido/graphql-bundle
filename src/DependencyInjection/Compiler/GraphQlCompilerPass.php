<?php

namespace Youshido\GraphQLBundle\src\DependencyInjection\Compiler;

use Exception;
use RuntimeException;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Youshido\GraphQLBundle\src\Security\Voter\BlacklistVoter;
use Youshido\GraphQLBundle\src\Security\Voter\WhitelistVoter;

/**
 * Date: 25.08.16
 *
 * @author Portey Vasil <portey@gmail.com>
 */
class GraphQlCompilerPass implements CompilerPassInterface
{
    /**
     * You can modify the container here before it is dumped to PHP code.
     *
     *
     * @throws Exception
     */
    public function process(ContainerBuilder $container): void
    {
        if ($loggerAlias = $container->getParameter('graphql.logger')) {
            if (strpos($loggerAlias, '@') === 0) {
                $loggerAlias = substr($loggerAlias, 1);
            }

            if (!$container->has($loggerAlias)) {
                throw new RuntimeException(sprintf('Logger "%s" not found', $loggerAlias));
            }

            $container->getDefinition('graphql.processor')->addMethodCall('setLogger', [new Reference($loggerAlias)]);
        }

        if ($maxComplexity = $container->getParameter('graphql.max_complexity')) {
            $container->getDefinition('graphql.processor')->addMethodCall('setMaxComplexity', [$maxComplexity]);
        }

        $this->processSecurityGuard($container);
    }

    /**
     * @throws Exception
     */
    private function processSecurityGuard(ContainerBuilder $container): void
    {
        $guardConfig = $container->getParameter('graphql.security.guard_config');
        $whiteList = $container->getParameter('graphql.security.white_list');
        $blackList = $container->getParameter('graphql.security.black_list');

        if ((!$guardConfig['field'] && !$guardConfig['operation']) && ($whiteList || $blackList)) {
            if ($whiteList && $blackList) {
                throw new RuntimeException('Configuration error: Only one white or black list allowed');
            }

            $this->addListVoter($container, BlacklistVoter::class, $blackList);
            $this->addListVoter($container, WhitelistVoter::class, $whiteList);
        }
    }

    /**
     * @param                  $voterClass
     *
     * @throws Exception
     */
    private function addListVoter(ContainerBuilder $container, ?string $voterClass, array $list): void
    {
        if ($list !== []) {
            $container
                ->getDefinition('graphql.security.voter')
                ->setClass($voterClass)
                ->addMethodCall('setEnabled', [true])
                ->addMethodCall('setList', [$list]);

            $container->setParameter('graphql.security.guard_config', [
                'operation' => true,
                'field' => false,
            ]);
        }
    }
}
