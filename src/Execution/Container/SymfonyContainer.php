<?php
/**
 * This file is a part of PhpStorm project.
 *
 * @author Alexandr Viniychuk <a@viniychuk.com>
 * created: 9/23/16 10:08 PM
 */

namespace Youshido\GraphQLBundle\Execution\Container;

use RuntimeException;
use UnitEnum;
use Youshido\GraphQL\Execution\Container\ContainerInterface;
use function func_num_args;

class SymfonyContainer implements ContainerInterface
{
    protected \Symfony\Component\DependencyInjection\ContainerInterface $container;

    public function setContainer(\Symfony\Component\DependencyInjection\ContainerInterface $container = null): void
    {
        if (1 > func_num_args()) {
            trigger_deprecation('symfony/dependency-injection', '6.2', 'Calling "%s::%s()" without any arguments is deprecated, pass null explicitly instead.', __CLASS__, __FUNCTION__);
        }

        $this->container = $container;
    }

    public function get($id)
    {
        return $this->container->get($id);
    }

    public function set($id, $value)
    {
        $this->container->set($id, $value);
        return $this;
    }

    public function remove($id): void
    {
        throw new RuntimeException('Remove method is not available for Symfony container');
    }

    public function has($id)
    {
        return $this->container->has($id);
    }

    public function initialized($id): bool
    {
        return $this->container->initialized($id);
    }

    public function setParameter($name, $value): static
    {
        $this->container->setParameter($name, $value);
        return $this;
    }

    public function getParameter($name): UnitEnum|float|int|bool|array|string|null
    {
        return $this->container->getParameter($name);
    }

    public function hasParameter($name): bool
    {
        return $this->container->hasParameter($name);
    }

    /**
     * Exists temporarily for ContainerAwareField that is to be removed in 1.5
     * @return \Symfony\Component\DependencyInjection\ContainerInterface|null
     */
    public function getSymfonyContainer(): ?\Symfony\Component\DependencyInjection\ContainerInterface
    {
        return $this->container;
    }
}