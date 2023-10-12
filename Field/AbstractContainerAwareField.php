<?php

/**
 * Date: 23.05.16
 *
 * @author Portey Vasil <portey@gmail.com>
 */

namespace Youshido\GraphQLBundle\Field;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Youshido\GraphQL\Field\AbstractField as BaseAbstractField;


abstract class AbstractContainerAwareField extends BaseAbstractField implements ContainerAwareInterface
{

    use ContainerAwareTrait;
}