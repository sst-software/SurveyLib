<?php

declare(strict_types=1);

namespace Sst\SurveyLibBundle\DependencyInjection\Compiler;

use Sst\SurveyLibBundle\DependencyInjection\Configuration;
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

class DoctrineResolveTargetEntityPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container): void
    {
        $definition = $container->findDefinition('doctrine.orm.listeners.resolve_target_entity');

        $configs = $container->getExtensionConfig('sst_survey_lib');
        $processor = new Processor();
        $configs = $processor->processConfiguration(new Configuration(), $configs);

        foreach ($this->resolveOrmTargetEntities($container, $configs) as $interface => $class) {
            $definition->addMethodCall('addResolveTargetEntity', [
                $interface,
                $class,
                [],
            ]);
        }

        $definition->addTag('doctrine.event_subscriber', ['connection' => 'default']);
    }

    protected function resolveOrmTargetEntities(ContainerBuilder $container, array $configs): array
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
