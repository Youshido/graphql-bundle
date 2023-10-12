<?php

namespace Youshido\GraphQLBundle\Security\Manager;

use Youshido\GraphQL\Execution\ResolveInfo;
use Youshido\GraphQL\Parser\Ast\Query;

/**
 * Date: 29.08.16
 *
 * @author Portey Vasil <portey@gmail.com>
 */
interface SecurityManagerInterface
{

    const RESOLVE_ROOT_OPERATION_ATTRIBUTE = 'RESOLVE_ROOT_OPERATION';
    
    const RESOLVE_FIELD_ATTRIBUTE = 'RESOLVE_FIELD';

    /**
     * @param $attribute string
     *
     * @return bool
     */
    public function isSecurityEnabledFor($attribute);

    /**
     * @return bool
     */
    public function isGrantedToFieldResolve(ResolveInfo $resolveInfo);

    /**
     * @return bool
     */
    public function isGrantedToOperationResolve(Query $query);

    /**
     *
     * @return mixed
     * @throw \Exception
     */
    public function createNewFieldAccessDeniedException(ResolveInfo $resolveInfo);

    /**
     *
     * @return mixed
     * @throw \Exception
     */
    public function createNewOperationAccessDeniedException(Query $query);
}