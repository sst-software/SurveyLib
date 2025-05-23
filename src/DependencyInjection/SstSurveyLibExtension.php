<?php

declare(strict_types=1);

namespace Sst\SurveyLibBundle\DependencyInjection;

use Doctrine\DBAL\Types\Type;
use Sst\SurveyLibBundle\Interfaces\Service\AddAnswerServiceInterface;
use Sst\SurveyLibBundle\Interfaces\Service\AstToJavascriptServiceInterface;
use Sst\SurveyLibBundle\Interfaces\Service\CreateSurveyResponseServiceInterface;
use Sst\SurveyLibBundle\Interfaces\Service\DisplayConditionServiceInterface;
use Sst\SurveyLibBundle\Interfaces\Service\NextElementServiceInterface;
use Sst\SurveyLibBundle\Interfaces\Service\ValidateAnswerServiceInterface;
use Sst\SurveyLibBundle\Types\ElementDataType;
use Sst\SurveyLibBundle\Types\ElementOverrideType;
use Sst\SurveyLibBundle\Types\RawAnswerType;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Extension\Extension;

class SstSurveyLibExtension extends Extension implements PrependExtensionInterface
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $processor = new Processor();
        $processor->processConfiguration($configuration, $configs);
    }

    public function prepend(ContainerBuilder $container): void
    {
        $configs = $container->getExtensionConfig('sst_survey_lib');
        $processor = new Processor();
        $configs = $processor->processConfiguration(new Configuration(), $configs);

        $container->autowire(CreateSurveyResponseServiceInterface::class, $configs['services']['createSurveyResponseService']);
        $container->autowire(DisplayConditionServiceInterface::class, $configs['services']['displayConditionService']);
        $container->autowire(AstToJavascriptServiceInterface::class, $configs['services']['astToJavascriptService']);
        $container->autowire(NextElementServiceInterface::class, $configs['services']['nextElementService']);
        $container->autowire(AddAnswerServiceInterface::class, $configs['services']['addAnswerService']);
        $container->autowire(ValidateAnswerServiceInterface::class, $configs['services']['validateAnswerService']);

        if (Type::hasType(ElementDataType::ELEMENT_DATA_TYPE)) {
            Type::overrideType(ElementDataType::ELEMENT_DATA_TYPE, $configs['typeMappings']['elementData']);
        } else {
            Type::addType(ElementDataType::ELEMENT_DATA_TYPE, $configs['typeMappings']['elementData']);
        }

        if (Type::hasType(RawAnswerType::RAW_ANSWER_DATA_TYPE)) {
            Type::overrideType(RawAnswerType::RAW_ANSWER_DATA_TYPE, $configs['typeMappings']['rawAnswer']);
        } else {
            Type::addType(RawAnswerType::RAW_ANSWER_DATA_TYPE, $configs['typeMappings']['rawAnswer']);
        }

        if (Type::hasType(ElementOverrideType::ELEMENT_OVERRIDE_TYPE)) {
            Type::overrideType(ElementOverrideType::ELEMENT_OVERRIDE_TYPE, $configs['typeMappings']['elementOverride']);
        } else {
            Type::addType(ElementOverrideType::ELEMENT_OVERRIDE_TYPE, $configs['typeMappings']['elementOverride']);
        }
        $container->loadFromExtension('doctrine', [
            'dbal' => [
                'types' => [
                    ElementDataType::ELEMENT_DATA_TYPE => $configs['typeMappings']['elementData'],
                    RawAnswerType::RAW_ANSWER_DATA_TYPE => $configs['typeMappings']['rawAnswer'],
                    ElementOverrideType::ELEMENT_OVERRIDE_TYPE => $configs['typeMappings']['elementOverride'],
                ],
            ],
        ]);
    }
}
