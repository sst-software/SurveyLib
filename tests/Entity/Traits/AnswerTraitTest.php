<?php

declare(strict_types=1);

namespace Sst\SurveyLibBundle\Tests\Service;

use PHPUnit\Framework\TestCase;
use Sst\SurveyLibBundle\Tests\TestEntities\DummyAnswer;

class AnswerTraitTest extends TestCase
{
    public function testSetAnswer(): void
    {
        $answer = (new DummyAnswer())
            ->setAnswer('answer');

        $this->assertEquals('answer', $answer->getAnswer());
    }

    public function testSkipClearsAnswer(): void
    {
        $answer = (new DummyAnswer())
            ->setAnswer('answer');
        $answer->setSkipped(true);

        $this->assertNull($answer->getAnswer());
    }

    public function testAnswerResetsSkip(): void
    {
        $answer = (new DummyAnswer())
            ->setSkipped(true);
        $answer->setAnswer('answer');

        $this->assertFalse($answer->isSkipped());
    }

    public function testNonSkipDoesNotClearAnswer(): void
    {
        $answer = (new DummyAnswer())
            ->setAnswer('answer');
        $answer->setSkipped(false);

        $this->assertEquals('answer', $answer->getAnswer());
    }
}
