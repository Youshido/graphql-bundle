<?php

/**
 * Date: 23.05.16
 *
 * @author Portey Vasil <portey@gmail.com>
 */

namespace Youshido\GraphQLBundle\Config\Rule;

use Youshido\GraphQL\Type\TypeService;
use Youshido\GraphQL\Validator\ConfigValidator\Rules\TypeValidationRule as BaseTypeValidationRule;

class TypeValidationRule extends BaseTypeValidationRule
{
    public function validate($data, $ruleInfo): bool
    {
        if (!is_string($ruleInfo)) {
            return false;
        }

        if (($ruleInfo == TypeService::TYPE_CALLABLE) && (
                is_callable($data) ||
                (is_array($data) && count($data) == 2 && str_starts_with((string)$data[0], '@')))
        ) {
            return true;
        }

        return parent::validate($data, $ruleInfo);
    }
}