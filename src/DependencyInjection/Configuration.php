<?php

declare(strict_types=1);

namespace Sst\SurveyLibBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('sst_survey_lib');
        $treeBuilder->getRootNode()
            ->children()
                ->arrayNode('services')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('createSurveyResponseService')->defaultValue('Sst\SurveyLibBundle\Service\CreateSurveyResponseService')->end()
                        ->scalarNode('displayConditionService')->defaultValue('Sst\SurveyLibBundle\Service\DisplayConditionService')->end()
                        ->scalarNode('astToJavascriptService')->defaultValue('Sst\SurveyLibBundle\Service\AstToJavascriptService')->end()
                        ->scalarNode('nextElementService')->defaultValue('Sst\SurveyLibBundle\Service\NextElementService')->end()
                        ->scalarNode('addAnswerService')->defaultValue('Sst\SurveyLibBundle\Service\AddAnswerService')->end()
                        ->scalarNode('validateAnswerService')->defaultValue('Sst\SurveyLibBundle\Service\ValidateAnswerService')->end()
                    ->end()
                ->end()
                ->arrayNode('entities')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('survey')->defaultValue('App\Entity\Survey\Survey')->end()
                        ->scalarNode('container')->defaultValue('App\Entity\Survey\Container')->end()
                        ->scalarNode('elementUsage')->defaultValue('App\Entity\Survey\ElementUsage')->end()
                        ->scalarNode('element')->defaultValue('App\Entity\Survey\Element')->end()
                        ->scalarNode('elementOverride')->defaultValue('Sst\SurveyLibBundle\Entity\ElementOverride')->end()
                        ->scalarNode('surveyResponse')->defaultValue('App\Entity\Survey\SurveyResponse')->end()
                        ->scalarNode('answer')->defaultValue('App\Entity\Survey\Answer')->end()
                    ->end()
                ->end()
                ->arrayNode('typeMappings')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('elementData')->defaultValue('Sst\SurveyLibBundle\Types\ElementDataType')->end()
                        ->scalarNode('rawAnswer')->defaultValue('Sst\SurveyLibBundle\Types\RawAnswerType')->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
