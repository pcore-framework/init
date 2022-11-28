<?php

declare(strict_types=1);

namespace PCore\Init\Routing\Attributes;

use Attribute;
use PCore\HttpMessage\Contracts\RequestMethodInterface;

/**
 * Class PostMapping
 * @package PCore\Init\Routing\Attributes
 * @github https://github.com/pcore-framework/init
 */
#[Attribute(Attribute::TARGET_METHOD)]
class PostMapping extends RequestMapping
{

    /**
     * @var array
     */
    public array $methods = [RequestMethodInterface::METHOD_POST];

}
