<?php

declare(strict_types=1);

namespace Sst\SurveyLibBundle\Service;

use Sst\SurveyLibBundle\Interfaces\Entity\AnswerInterface;
use Sst\SurveyLibBundle\Interfaces\Entity\ContainerInterface;
use Sst\SurveyLibBundle\Interfaces\Entity\ElementUsageInterface;
use Sst\SurveyLibBundle\Interfaces\Entity\SurveyResponseInterface;
use Sst\SurveyLibBundle\Interfaces\Service\AstToJavascriptServiceInterface;
use Sst\SurveyLibBundle\Interfaces\Service\DisplayConditionServiceInterface;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\ExpressionLanguage\Node\Node;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class DisplayConditionService implements DisplayConditionServiceInterface
{
    //For now, we only support 'answers' and 'availableAnswerKeys', but we could add more in the future by add more items to this array
    protected array $conditionDataCollectors = [
        'answers' => 'getAnswers',
        'availableAnswerKeys' => 'getAvailableAnswerKeys',
    ];

    public function __construct(
        protected AstToJavascriptServiceInterface $getJavascriptFromAstService,
    ) {
    }

    public function itemVisible(ElementUsageInterface|ContainerInterface $displayItem, SurveyResponseInterface $surveyResponse): bool
    {
        $displayCondition = $displayItem->getDisplayCondition();
        if (empty($displayCondition)) {
            return true;
        }

        $ast = $this->getAbstractSyntaxTree($displayItem);
        if ($ast === null) {
            return true;
        }
        return ((bool)$ast->evaluate([], $this->getConditionData($surveyResponse)));
    }

    public function getPhpCondition(ElementUsageInterface|ContainerInterface $displayItem): string
    {
        $displayCondition = $displayItem->getDisplayCondition();
        if (empty($displayCondition)) {
            return 'true';
        }
        $expressionLanguage = new ExpressionLanguage();
        return $expressionLanguage->compile($displayCondition, array_keys($this->conditionDataCollectors));
    }

    public function getJavascriptCondition(ElementUsageInterface|ContainerInterface $displayItem): string
    {
        $displayCondition = $displayItem->getDisplayCondition();
        if (empty($displayCondition)) {
            return 'true';
        }
        $ast = $this->getAbstractSyntaxTree($displayItem);
        if ($ast === null) {
            return 'true';
        }
        return $this->getJavascriptFromAstService->translateAstToJavascript($ast);
    }

    public function getConditionData(SurveyResponseInterface $surveyResponse): array
    {
        $result = [];
        foreach ($this->conditionDataCollectors as $key => $method) {
            $result[$key] = $this->{$method}($surveyResponse);
        }

        return $result;
    }

    protected function getAvailableAnswerKeys(SurveyResponseInterface $surveyResponse): array
    {
        return array_keys($this->getAnswers($surveyResponse));
    }

    protected function getAnswers(SurveyResponseInterface $surveyResponse): array
    {
        $answers = [];
        foreach ($surveyResponse->getAnswers() as $answer) {
            $answers[$answer->getElementUsage()?->getCode()] = $this->normalizeAnswer($answer);
        }
        return $answers;
    }

    protected function getAbstractSyntaxTree(ElementUsageInterface|ContainerInterface $displayItem): ?Node
    {
        $displayCondition = $displayItem->getDisplayCondition();
        if (empty($displayCondition)) {
            return null;
        }
        $expressionLanguage = new ExpressionLanguage();
        return $expressionLanguage->parse(
            $displayCondition,
            array_keys($this->conditionDataCollectors),
        )->getNodes();
    }

    protected function normalizeAnswer(AnswerInterface $object): array
    {
        $normalizers = [new ObjectNormalizer()];
        $serializer = new Serializer($normalizers, []);
        return $serializer->normalize($object, null, [AbstractNormalizer::IGNORED_ATTRIBUTES => ['surveyResponse', 'elementUsage']]);
    }
}
