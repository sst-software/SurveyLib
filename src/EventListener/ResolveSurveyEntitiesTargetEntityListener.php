<?php

declare(strict_types=1);

namespace Sst\SurveyLibBundle\EventListener;

use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Tools\ResolveTargetEntityListener;

class ResolveSurveyEntitiesTargetEntityListener extends ResolveTargetEntityListener
{
    public function __construct(
        protected readonly array $mapping,
    ) {
        foreach ($mapping as $interface => $class) {
            $this->addResolveTargetEntity($interface, $class, []);
        }
    }

    public function loadClassMetadata(LoadClassMetadataEventArgs $args): void
    {
        parent::loadClassMetadata($args);

        // Actively set the metadata for the interface, as was done until Doctrine ORM 3.5.2, but removed in version 3.5.3
        // https://github.com/doctrine/orm/pull/12174/files
        $cm = $args->getClassMetadata();
        foreach ($this->mapping as $interface => $class) {
            if ($class === $cm->getName()) {
                $args->getEntityManager()->getMetadataFactory()->setMetadataFor($interface, $cm);
            }
        }
    }
}
