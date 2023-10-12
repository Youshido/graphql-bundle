<?php

namespace Youshido\GraphQLBundle\src\Event;

use Symfony\Component\EventDispatcher\GenericEvent;
use Youshido\GraphQL\Field\FieldInterface;
use Youshido\GraphQL\Parser\Ast\Field;

class ResolveEvent extends GenericEvent
{
    /**
     * @var Field
     */
    private readonly \Youshido\GraphQL\Field\FieldInterface $field;

    private readonly array $astFields;

    /** @var mixed|null */
    private $resolvedValue;

    /**
     * Constructor.
     *
     * @param mixed|null $resolvedValue
     */
    public function __construct(FieldInterface $field, array $astFields, $resolvedValue = null)
    {
        $this->field = $field;
        $this->astFields = $astFields;
        $this->resolvedValue = $resolvedValue;
        parent::__construct('ResolveEvent', [$field, $astFields, $resolvedValue]);
    }

    /**
     * Returns the field.
     *
     * @return FieldInterface
     */
    public function getField()
    {
        return $this->field;
    }

    /**
     * Returns the AST fields.
     *
     * @return array
     */
    public function getAstFields()
    {
        return $this->astFields;
    }

    /**
     * Returns the resolved value.
     *
     * @return mixed|null
     */
    public function getResolvedValue()
    {
        return $this->resolvedValue;
    }

    /**
     * Allows the event listener to manipulate the resolved value.
     *
     * @param $resolvedValue
     */
    public function setResolvedValue($resolvedValue): void
    {
        $this->resolvedValue = $resolvedValue;
    }
}

