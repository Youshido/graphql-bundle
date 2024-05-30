<?php
/**
 * Date: 9/12/16
 *
 * @author Portey Vasil <portey@gmail.com>
 */

namespace Youshido\GraphQLBundle\Security\Voter;


use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Youshido\GraphQLBundle\Security\Manager\SecurityManagerInterface;

abstract class AbstractListVoter extends Voter
{

    /** @var string[] */
    private array $list = [];

    private bool $enabled = false;

    /**
     * @return string[]
     */
    public function getList(): array
    {
        return $this->list;
    }

    public function setList(array $list): void
    {
        $this->list = $list;
    }

    public function setEnabled($enabled): void
    {
        $this->enabled = $enabled;
    }

    protected function supports($attribute, $subject): bool
    {
        return $this->enabled && $attribute == SecurityManagerInterface::RESOLVE_ROOT_OPERATION_ATTRIBUTE;
    }

    protected function isLoggedInUser(TokenInterface $token): bool
    {
        return is_object($token->getUser());
    }

    protected function inList($query): bool
    {
        return in_array($query, $this->list);
    }
}