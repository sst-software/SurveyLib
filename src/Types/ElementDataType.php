<?php

declare(strict_types=1);

namespace Sst\SurveyLibBundle\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\JsonType;
use ReflectionNamedType;
use ReflectionUnionType;
use Sst\SurveyLibBundle\Interfaces\Entity\ElementData\CustomElementDataInterface;
use Sst\SurveyLibBundle\Interfaces\Entity\ElementData\MultipleChoiceGridQuestionElementDataInterface;
use Sst\SurveyLibBundle\Interfaces\Entity\ElementData\MultipleChoiceQuestionElementDataInterface;

class ElementDataType extends JsonType
{
    public const ELEMENT_DATA_TYPE = 'elementData';

    /**
     * {@inheritDoc}
     */
    public function convertToPHPValue($value, AbstractPlatform $platform): mixed
    {
        $parentResult = parent::convertToPHPValue($value, $platform);
        if ($parentResult === null) {
            return null;
        }
        return $this->convertRawDbDataArrayToElementDataObject($parentResult['className'], $parentResult['value'], $parentResult['extraData'] ?? []);
    }

    /**
     * {@inheritDoc}
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        if ($value === null) {
            return parent::convertToDatabaseValue(null, $platform);
        }
        $extendedValue = ['className' => get_class($value), 'value' => $value, 'extraData' => $this->getExtraData($value)];
        return parent::convertToDatabaseValue($extendedValue, $platform);
    }

    protected function getExtraData(mixed $value): array
    {
        $result = [];
        if ($value instanceof CustomElementDataInterface) {
            return ['elementDataType' => get_class($value->getElementData())];
        }
        if (($value instanceof MultipleChoiceQuestionElementDataInterface || $value instanceof MultipleChoiceGridQuestionElementDataInterface) && count($value->getAnswerOptions()) > 0) {
            $result['answerOptionsType'] = get_class($value->getAnswerOptions()[0]);
        }
        if ($value instanceof MultipleChoiceGridQuestionElementDataInterface && count($value->getQuestions()) > 0) {
            $result['questionsType'] = get_class($value->getQuestions()[array_key_first($value->getQuestions())]);
        }
        return $result;
    }

    public function getName(): string
    {
        return self::ELEMENT_DATA_TYPE;
    }

    /**
     * {@inheritDoc}
     */
    protected function convertRawDbDataArrayToElementDataObject(string $className, array $dbData, array $extraData)
    {
        $class = new $className();
        $methods = get_class_methods($class);
        foreach ($methods as $method) {
            preg_match(' /^(set)(.*?)$/i', $method, $results);
            $pre = $results[1] ?? '';
            $keyInDbData = $results[2] ?? '';
            $keyInDbData = strtolower(substr($keyInDbData, 0, 1)) . substr($keyInDbData, 1);
            if ($pre === 'set' && array_key_exists($keyInDbData, $dbData)) {
                $this->fillField($keyInDbData, $dbData[$keyInDbData], $extraData, $class, $method);
            }
        }
        return $class;
    }

    protected function fillField(string $keyInDbData, mixed $dbDataValue, array $extraData, mixed $class, string $method): void
    {
        if ($class instanceof CustomElementDataInterface && $keyInDbData === 'elementData') {
            $this->convertRawDbDataToCustomElementData($class, $method, $dbDataValue, $extraData['elementDataType']);
            return;
        }
        if (($class instanceof MultipleChoiceQuestionElementDataInterface || $class instanceof MultipleChoiceGridQuestionElementDataInterface) && $keyInDbData === 'answerOptions') {
            $this->convertRawDbDataToMultipleChoiceElementData($class, $method, $dbDataValue, $extraData['answerOptionsType'] ?? '');
            return;
        }
        if ($class instanceof MultipleChoiceGridQuestionElementDataInterface && $keyInDbData === 'questions') {
            $this->convertRawDbDataToMultipleChoiceElementData($class, $method, $dbDataValue, $extraData['questionsType'] ?? '');
            return;
        }

        $dbDataValue = $this->convertDbValueToBackedEnum($class, $method, $dbDataValue);

        $class->$method($dbDataValue);
    }

    private function convertDbValueToBackedEnum(mixed $class, string $method, mixed $dbDataValue): mixed
    {
        if ($dbDataValue === null) {
            return $dbDataValue;
        }

        foreach ((new \ReflectionClass($class))->getMethod($method)->getParameters() as $reflectionParameter) {
            $type = $reflectionParameter->getType();
            $typeNames = [];
            if ($type instanceof ReflectionNamedType) {
                $typeNames[] = $type->getName();
            }
            if ($type instanceof ReflectionUnionType) {
                foreach ($type->getTypes() as $subType) {
                    $typeNames[] = $subType->getName();
                }
            }

            foreach ($typeNames as $typeName) {
                if (enum_exists($typeName)) {
                    return $typeName::tryFrom($dbDataValue);
                }
            }
        }
        return $dbDataValue;
    }

    protected function convertRawDbDataToCustomElementData(mixed &$class, string $method, array $rawData, string $elementDataType): void
    {
        $class->$method($this->convertRawDbDataArrayToElementDataObject($elementDataType, $rawData, []));
    }

    protected function convertRawDbDataToMultipleChoiceElementData(mixed &$class, string $method, array $rawData, string $answerOptionsType): void
    {
        if (empty($answerOptionsType)) {
            return;
        }

        $answerOptions = [];
        foreach ($rawData as $answerOption) {
            $answerOptions[] = $this->convertRawDbDataArrayToElementDataObject($answerOptionsType, $answerOption, []);
        }
        $class->$method($answerOptions);
    }
}
