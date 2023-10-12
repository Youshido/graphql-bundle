<?php
/**
 * Date: 9/12/16
 *
 * @author Portey Vasil <portey@gmail.com>
 */

namespace Youshido\GraphQLBundle\src\Security\Voter;


use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Youshido\GraphQL\Parser\Ast\Query;

class WhitelistVoter extends AbstractListVoter
{

    /**
     * Perform a single access check operation on a given attribute, subject and token.
     *
     * @param string $attribute
     * @param mixed $subject
     *
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token): bool
    {
        /** @var $subject Query */
        return $this->isLoggedInUser($token) || $this->inList($subject->getName());
    }
}
