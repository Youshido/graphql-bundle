<?php
/**
 * Date: 29.08.16
 *
 * @author Portey Vasil <portey@gmail.com>
 */

namespace Youshido\GraphQLBundle\src\Security\Manager;

use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Youshido\GraphQL\Execution\ResolveInfo;
use Youshido\GraphQL\Parser\Ast\Query;

class DefaultSecurityManager implements SecurityManagerInterface
{
    /** @var bool */
    private mixed $fieldSecurityEnabled;

    /** @var bool */
    private mixed $rootOperationSecurityEnabled;

    private readonly AuthorizationCheckerInterface $authorizationChecker;

    public function __construct(AuthorizationCheckerInterface $authorizationChecker, array $guardConfig = [])
    {
        $this->authorizationChecker = $authorizationChecker;
        $this->fieldSecurityEnabled = isset($guardConfig['field']) ? $guardConfig['field'] : false;
        $this->rootOperationSecurityEnabled = isset($guardConfig['operation']) ? $guardConfig['operation'] : false;
    }

    /**
     * @param string $attribute
     *
     * @return bool
     */
    public function isSecurityEnabledFor($attribute)
    {
        if (SecurityManagerInterface::RESOLVE_FIELD_ATTRIBUTE == $attribute) {
            return $this->fieldSecurityEnabled;
        } elseif (SecurityManagerInterface::RESOLVE_ROOT_OPERATION_ATTRIBUTE == $attribute) {
            return $this->rootOperationSecurityEnabled;
        }

        return false;
    }

    /**
     * @param boolean $fieldSecurityEnabled
     */
    public function setFieldSecurityEnabled(mixed $fieldSecurityEnabled): void
    {
        $this->fieldSecurityEnabled = $fieldSecurityEnabled;
    }

    /**
     * @param boolean $rootOperationSecurityEnabled
     */
    public function setRooOperationSecurityEnabled(mixed $rootOperationSecurityEnabled): void
    {
        $this->rootOperationSecurityEnabled = $rootOperationSecurityEnabled;
    }

    
    public function isGrantedToOperationResolve(Query $query): bool
    {
        return $this->authorizationChecker->isGranted(SecurityManagerInterface::RESOLVE_ROOT_OPERATION_ATTRIBUTE, $query);
    }

    
    public function isGrantedToFieldResolve(ResolveInfo $resolveInfo): bool
    {
        return $this->authorizationChecker->isGranted(SecurityManagerInterface::RESOLVE_FIELD_ATTRIBUTE, $resolveInfo);
    }

    /**
     *
     * @return mixed
     * @throw \Exception
     */
    public function createNewFieldAccessDeniedException(ResolveInfo $resolveInfo): \Symfony\Component\Security\Core\Exception\AccessDeniedException
    {
        return new AccessDeniedException();
    }

    /**
     *
     * @return mixed
     * @throw \Exception
     */
    public function createNewOperationAccessDeniedException(Query $query): \Symfony\Component\Security\Core\Exception\AccessDeniedException
    {
        return new AccessDeniedException();
    }
}