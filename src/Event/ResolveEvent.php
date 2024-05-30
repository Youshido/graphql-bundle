<?php

namespace Youshido\GraphQLBundle\Event;

use Symfony\Component\EventDispatcher\GenericEvent;
use Youshido\GraphQL\Field\FieldInterface;

class ResolveEvent extends GenericEvent
{
    private readonly FieldInterface $field;

    private readonly array $astFields;

    private mixed $resolvedValue;

    /**
     * Constructor.
     *
     * @param mixed|null $resolvedValue
     */
    public function __construct(FieldInterface $field, array $astFields, mixed $resolvedValue = null)
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
    public function getField(): FieldInterface
    {
        return $this->field;
    }

    /**
     * Returns the AST fields.
     *
     * @return array
     */
    public function getAstFields(): array
    {
        return $this->astFields;
    }

    /**
     * Returns the resolved value.
     *
     * @return mixed|null
     */
    public function getResolvedValue(): mixed
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