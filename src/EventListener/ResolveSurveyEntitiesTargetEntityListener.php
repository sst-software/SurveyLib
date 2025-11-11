<?php

declare(strict_types=1);

namespace Sst\SurveyLibBundle\EventListener;

use Doctrine\ORM\Tools\ResolveTargetEntityListener;

class ResolveSurveyEntitiesTargetEntityListener extends ResolveTargetEntityListener
{
    public function __construct(
        array $mapping,
    ) {
        foreach ($mapping as $interface => $class) {
            $this->addResolveTargetEntity($interface, $class, []);
        }
    }
}
