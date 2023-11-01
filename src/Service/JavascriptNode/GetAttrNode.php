<?php

declare(strict_types=1);

namespace Sst\SurveyLibBundle\Service\JavascriptNode;

class GetAttrNode extends Node
{
    public const PROPERTY_CALL = 1;
    public const METHOD_CALL = 2;
    public const ARRAY_CALL = 3;

    public function __construct(Node $node, Node $attribute, ArrayNode $arguments, int $type)
    {
        parent::__construct(
            ['node' => $node, 'attribute' => $attribute, 'arguments' => $arguments],
            ['type' => $type, 'is_null_coalesce' => false, 'is_short_circuited' => false],
        );
    }

    public function compile(Compiler $compiler)
    {
        $nullSafe = $this->nodes['attribute'] instanceof ConstantNode && $this->nodes['attribute']->isNullSafe;
        switch ($this->attributes['type']) {
            case self::PROPERTY_CALL:
                $compiler
                    ->compile($this->nodes['node'])
                    ->raw($nullSafe ? '?.' : '.')
                    ->raw($this->nodes['attribute']->attributes['value'])
                ;
                break;

            case self::METHOD_CALL:
                $compiler
                    ->compile($this->nodes['node'])
                    ->raw($nullSafe ? '?.' : '.')
                    ->raw($this->nodes['attribute']->attributes['value'])
                    ->raw('(')
                    ->compile($this->nodes['arguments'])
                    ->raw(')')
                ;
                break;

            case self::ARRAY_CALL:
                $compiler
                    ->compile($this->nodes['node'])
                    ->raw('[')
                    ->compile($this->nodes['attribute'])
                    ->raw(']')
                ;
                break;
        }
    }
}
