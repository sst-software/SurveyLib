<?php

declare(strict_types=1);

namespace Sst\SurveyLibBundle\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\JsonType;
use ReflectionNamedType;
use ReflectionUnionType;
use Sst\SurveyLibBundle\Interfaces\Entity\ElementData\CustomElementDataInterface;
use Sst\SurveyLibBundle\Interfaces\Entity\ElementData\MultipleChoiceQuestionElementDataInterface;

class ElementDataType extends JsonType
{
    public const ELEMENT_DATA_TYPE = 'elementData';

    public function convertToPHPValue($value, AbstractPlatform $platform): mixed
    {
        $parentResult = parent::convertToPHPValue($value, $platform);
        if ($parentResult === null) {
            return null;
        }
        return $this->convertRawDbDataArrayToElementDataObject($parentResult['className'], $parentResult['value'], $parentResult['extraData'] ?? []);
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform): mixed
    {
        if ($value === null) {
            return parent::convertToDatabaseValue(null, $platform);
        }
        $extendedValue = ['className' => get_class($value), 'value' => $value, 'extraData' => $this->getExtraData($value)];
        return parent::convertToDatabaseValue($extendedValue, $platform);
    }

    protected function getExtraData($value): array
    {
        if ($value instanceof CustomElementDataInterface) {
            return ['elementDataType' => get_class($value->getElementData())];
        }
        if ($value instanceof MultipleChoiceQuestionElementDataInterface && count($value->getAnswerOptions()) > 0) {
            return ['answerOptionsType' => get_class($value->getAnswerOptions()[0])];
        }
        return [];
    }

    public function getName(): string
    {
        return self::ELEMENT_DATA_TYPE;
    }

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

    protected function fillField(string $keyInDbData, $dbDataValue, array $extraData, $class, string $method): void
    {
        if ($class instanceof CustomElementDataInterface && $keyInDbData === 'elementData') {
            $this->convertRawDbDataToCustomElementData($class, $method, $dbDataValue, $extraData['elementDataType']);
            return;
        }
        if ($class instanceof MultipleChoiceQuestionElementDataInterface && $keyInDbData === 'answerOptions') {
            $this->convertRawDbDataToMultipleChoiceElementData($class, $method, $dbDataValue, $extraData['answerOptionsType'] ?? '');
            return;
        }

        $dbDataValue = $this->convertDbValueToBackedEnum($class, $method, $dbDataValue);

        $class->$method($dbDataValue);
    }

    private function convertDbValueToBackedEnum($class, string $method, $dbDataValue): mixed
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
