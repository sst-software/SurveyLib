<?php

declare(strict_types=1);

namespace Sst\SurveyLibBundle\Tests\Entity\Traits;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Sst\SurveyLibBundle\Entity\ElementData\CustomElementData;
use Sst\SurveyLibBundle\Entity\ElementData\ElementData;
use Sst\SurveyLibBundle\Entity\ElementData\MultipleChoiceGridQuestionElementData;
use Sst\SurveyLibBundle\Entity\ElementData\MultipleChoiceQuestionElementData;
use Sst\SurveyLibBundle\Entity\ElementData\NumberQuestionElementData;
use Sst\SurveyLibBundle\Entity\ElementData\ScaleQuestionElementData;
use Sst\SurveyLibBundle\Entity\ElementData\TextQuestionElementData;
use Sst\SurveyLibBundle\Enums\ElementType;
use Sst\SurveyLibBundle\Interfaces\Entity\ElementData\ElementDataInterface;
use Sst\SurveyLibBundle\Tests\TestEntities\DummyElement;

class ElementTraitTest extends TestCase
{
    /**
     * @dataProvider getElementDataItems
     */
    public function testSetElementData(ElementType $type, ElementDataInterface $elementData): void
    {
        $element = new DummyElement();
        $element->setType($type);
        $result = $element->setElementData($elementData);
        $this->assertEquals($element, $result);
        $this->assertEquals($elementData, $element->getElementData());
    }

    public static function getElementDataItems(): iterable
    {
        yield [ElementType::TEXT, new TextQuestionElementData()];
        yield [ElementType::NUMBER, new NumberQuestionElementData()];
        yield [ElementType::MULTIPLE_CHOICE, new MultipleChoiceQuestionElementData()];
        yield [ElementType::MULTIPLE_CHOICE_GRID, new MultipleChoiceGridQuestionElementData()];
        yield [ElementType::SCALE, new ScaleQuestionElementData()];
        yield [ElementType::INFO, new ElementData()];
        yield [ElementType::CUSTOM, new CustomElementData()];
    }

    public function testSetElementDataInvalid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $element = new DummyElement();
        $element->setType(ElementType::TEXT);
        $element->setElementData(new ScaleQuestionElementData());
    }

    public function testTypeChange(): void
    {
        $element = new DummyElement();
        $element->setType(ElementType::TEXT);
        $element->setElementData(new TextQuestionElementData());
        $element->setType(ElementType::SCALE);
        $this->assertNull($element->getElementData());
    }
}
