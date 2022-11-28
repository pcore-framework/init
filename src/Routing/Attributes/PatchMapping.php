<?php

declare(strict_types=1);

namespace PCore\Init\Routing\Attributes;

use Attribute;
use PCore\HttpMessage\Contracts\RequestMethodInterface;

/**
 * Class PatchMapping
 * @package PCore\Init\Routing\Attributes
 * @github https://github.com/pcore-framework/init
 */
#[Attribute(Attribute::TARGET_METHOD)]
class PatchMapping extends RequestMapping
{

    /**
     * @var array|string[]
     */
    public array $methods = [RequestMethodInterface::METHOD_PATCH];

}
