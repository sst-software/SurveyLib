<?php

declare(strict_types=1);

namespace Sst\SurveyLibBundle\EventListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Events;

class RegisterInterfacesListener implements EventSubscriber
{
    public function __construct(
        protected readonly array $mapping,
    ) {
    }

    public function loadClassMetadata(LoadClassMetadataEventArgs $args): void
    {
        // Actively set the metadata for the interface, as was done until Doctrine ORM 3.5.2, but removed in version 3.5.3
        // https://github.com/doctrine/orm/pull/12174/files
        $cm = $args->getClassMetadata();
        foreach ($this->mapping as $interface => $class) {
            if ($class === $cm->getName()) {
                $args->getEntityManager()->getMetadataFactory()->setMetadataFor($interface, $cm);
            }
        }
    }

    public function getSubscribedEvents(): array
    {
        return [
            Events::loadClassMetadata,
        ];
    }
}
