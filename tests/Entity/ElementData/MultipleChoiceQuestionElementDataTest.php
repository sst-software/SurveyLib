<?php

declare(strict_types=1);

namespace Sst\SurveyLibBundle\Tests\Entity\ElementData;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Sst\SurveyLibBundle\Entity\ElementData\MultipleChoiceQuestionElementData;
use Sst\SurveyLibBundle\Entity\ElementData\SubItems\MultipleChoiceQuestionAnswerOption;
use stdClass;

class MultipleChoiceQuestionElementDataTest extends TestCase
{
    public function testSetAnswerOptionsValid(): void
    {
        $elementData = new MultipleChoiceQuestionElementData();
        $result = $elementData->setAnswerOptions([new MultipleChoiceQuestionAnswerOption(), new MultipleChoiceQuestionAnswerOption()]);
        $this->assertEquals($elementData, $result);
    }

    public function testSetAnswerOptionsInvalid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $elementData = new MultipleChoiceQuestionElementData();
        $elementData->setAnswerOptions([new stdClass(), new MultipleChoiceQuestionAnswerOption()]);
    }
}
