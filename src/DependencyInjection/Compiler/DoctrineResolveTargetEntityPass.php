<?php

declare(strict_types=1);

namespace Sst\SurveyLibBundle\DependencyInjection\Compiler;

use Sst\SurveyLibBundle\DependencyInjection\Configuration;
use Sst\SurveyLibBundle\EventListener\ResolveSurveyEntitiesTargetEntityListener;
use Sst\SurveyLibBundle\Interfaces\Entity\AnswerInterface;
use Sst\SurveyLibBundle\Interfaces\Entity\ContainerInterface;
use Sst\SurveyLibBundle\Interfaces\Entity\ElementInterface;
use Sst\SurveyLibBundle\Interfaces\Entity\ElementOverrideInterface;
use Sst\SurveyLibBundle\Interfaces\Entity\ElementUsageInterface;
use Sst\SurveyLibBundle\Interfaces\Entity\SurveyInterface;
use Sst\SurveyLibBundle\Interfaces\Entity\SurveyResponseInterface;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class DoctrineResolveTargetEntityPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container): void
    {
        $definition = (new Definition(ResolveSurveyEntitiesTargetEntityListener::class))
            ->addTag('doctrine.event_listener', ['event' => 'loadClassMetadata'])
            ->addArgument($this->resolveOrmTargetEntities($this->getBundleConfig($container)))
        ;
        $container->setDefinition('sst_survey_lib_bundle_survey_entities_target_entity_listener', $definition);
    }

    protected function getBundleConfig(ContainerBuilder $container): array
    {
        $configs = $container->getExtensionConfig('sst_survey_lib');
        return (new Processor())->processConfiguration(new Configuration(), $configs);
    }

    protected function resolveOrmTargetEntities(array $configs): array
    {
        $surveyEntity = $configs['entities']['survey'];
        $containerEntity = $configs['entities']['container'];
        $elementUsageEntity = $configs['entities']['elementUsage'];
        $elementEntity = $configs['entities']['element'];
        $elementOverride = $configs['entities']['elementOverride'];
        $surveyResponseEntity = $configs['entities']['surveyResponse'];
        $answerEntity = $configs['entities']['answer'];

        $targetEntities = [];
        if (class_exists($surveyEntity)) {
            $targetEntities[SurveyInterface::class] = $surveyEntity;
        }
        if (class_exists($containerEntity)) {
            $targetEntities[ContainerInterface::class] = $containerEntity;
        }
        if (class_exists($elementUsageEntity)) {
            $targetEntities[ElementUsageInterface::class] = $elementUsageEntity;
        }
        if (class_exists($elementEntity)) {
            $targetEntities[ElementInterface::class] = $elementEntity;
        }
        if (class_exists($surveyResponseEntity)) {
            $targetEntities[SurveyResponseInterface::class] = $surveyResponseEntity;
        }
        if (class_exists($answerEntity)) {
            $targetEntities[AnswerInterface::class] = $answerEntity;
        }
        if (class_exists($elementOverride)) {
            $targetEntities[ElementOverrideInterface::class] = $elementOverride;
        }

        return $targetEntities;
    }
}
