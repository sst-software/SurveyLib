<?php

declare(strict_types=1);

namespace Sst\SurveyLibBundle\Tests\Entity\Traits;

use PHPUnit\Framework\TestCase;
use Sst\SurveyLibBundle\Entity\ElementData\TextQuestionElementData;
use Sst\SurveyLibBundle\Entity\ElementOverride;
use Sst\SurveyLibBundle\Enums\ElementType;
use Sst\SurveyLibBundle\Tests\TestEntities\DummyContainer;
use Sst\SurveyLibBundle\Tests\TestEntities\DummyElement;
use Sst\SurveyLibBundle\Tests\TestEntities\DummyElementUsage;
use Sst\SurveyLibBundle\Tests\TestEntities\DummySurvey;

class ElementUsageTraitTest extends TestCase
{
    public function testElementCanNotBeAdded(): void
    {
        $survey = new DummySurvey();
        $container = new DummyContainer();

        $survey->addContainer($container);

        $childContainer1 = new DummyContainer();
        $container->addChildContainer($childContainer1);

        $elementUsage1 = new DummyElementUsage();
        $childContainer1->addElementUsage($elementUsage1);

        $element = new DummyElement();
        $elementUsage1->setElement($element);

        $childContainer2 = new DummyContainer();
        $container->addChildContainer($childContainer2);

        $elementUsage2 = new DummyElementUsage();
        $childContainer2->addElementUsage($elementUsage2);
        $result = $elementUsage2->elementCanBeAdded($element);

        $this->assertFalse($result);
    }

    public function testElementCanBeAdded(): void
    {
        $survey = new DummySurvey();
        $container = new DummyContainer();

        $survey->addContainer($container);

        $childContainer1 = new DummyContainer();
        $container->addChildContainer($childContainer1);

        $elementUsage1 = new DummyElementUsage();
        $childContainer1->addElementUsage($elementUsage1);

        $element = new DummyElement();
        $elementUsage1->setElement($element);

        $childContainer2 = new DummyContainer();
        $container->addChildContainer($childContainer2);

        $elementUsage2 = new DummyElementUsage();
        $childContainer2->addElementUsage($elementUsage2);
        $result = $elementUsage2->elementCanBeAdded(new DummyElement());

        $this->assertTrue($result);
    }

    public function testElementWithOverrides(): void
    {
        $element = new DummyElement();
        $element->setType(ElementType::TEXT);
        $element->setTitle('Original');
        $element->setText('Original text');
        $element->setElementData((new TextQuestionElementData())->setDefaultAnswer('Original answer'));

        $elementUsage = new DummyElementUsage();
        $elementUsage->setElement($element);
        $elementUsage->setCode((string)rand(0, 1000));

        $override = new ElementOverride();
        $override->setTitle('Override!!');
        $override->setElementData(null);

        $elementUsage->setElementOverride($override);

        $this->assertEquals('Override!!', $elementUsage->getElementWithOverrides()->getTitle());
        $this->assertEquals('Original text', $elementUsage->getElementWithOverrides()->getText());
        $this->assertNull($elementUsage->getElementWithOverrides()->getElementData());
    }
}
