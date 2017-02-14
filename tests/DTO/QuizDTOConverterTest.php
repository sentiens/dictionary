<?php

namespace Tests\AppBundle\DTO;

use AppBundle\DTO\Quiz as QuizDTO;
use AppBundle\DTO\QuizDTOConverter;
use AppBundle\Entity\Quiz;
use AppBundle\Entity\QuizStatus;
use AppBundle\Entity\Word;
use Doctrine\Common\Collections\ArrayCollection;

class QuizDTOConverterTest extends \PHPUnit_Framework_TestCase {

    public function test_converting_quiz_without_question() {

        $quiz = $this->createMock(Quiz::class);
        $quiz->expects($this->once())->method('getId')->willReturn(1);
        $quiz->expects($this->once())->method('getStatus')->willReturn(QuizStatus::NoQuestion());
        $quiz->expects($this->once())->method('getScore')->willReturn(3);
        $quiz->expects($this->once())->method('getName')->willReturn('Morpheus');
        $quiz->expects($this->once())->method('getMistakesAvailable')->willReturn(3);
        $quiz->expects($this->exactly(2))->method('hasUnansweredQuestion')->willReturn(false);


        $converter = new QuizDTOConverter();

        $dto = $converter->convert($quiz);

        $this->assertInstanceOf(QuizDTO::class, $dto);
        $this->assertEquals(1, $dto->id);
        $this->assertEquals(QuizStatus::NoQuestion()->toNative(), $dto->status);
        $this->assertEquals(3, $dto->score);
        $this->assertEquals('Morpheus', $dto->name);
        $this->assertEquals(3, $dto->mistakesAvailable);
        $this->assertNull($dto->question);
        $this->assertNull($dto->answers);
    }

    public function test_converting_quiz_with_question() {

        $drafts = [
            [1, 'apple', true],
            [2, 'banana', false],
            [3, 'orange', true],
            [4, 'juice', false]
        ];
        $answers = new ArrayCollection();
        foreach ($drafts as $draft) {
            list($id, $word) = $draft;

            $answers->add($answer = $this->createMock(Word::class));
            $answer->expects($this->once())->method('getId')->willReturn($id);
            $answer->expects($this->once())->method('getWord')->willReturn($word);
        }

        $question = $this->createMock(Word::class);
        $question->expects($this->once())->method('getWord')->willReturn('яблоко');

        $quiz = $this->createMock(Quiz::class);
        $quiz->expects($this->once())->method('getId')->willReturn(1);
        $quiz->expects($this->once())->method('getStatus')->willReturn(QuizStatus::UnansweredQuestion());
        $quiz->expects($this->once())->method('getScore')->willReturn(3);
        $quiz->expects($this->once())->method('getName')->willReturn('Morpheus');
        $quiz->expects($this->once())->method('getMistakesAvailable')->willReturn(3);
        $quiz->expects($this->exactly(2))->method('hasUnansweredQuestion')->willReturn(true);
        $quiz->expects($this->once())->method('getQuestion')->willReturn($question);
        $quiz->expects($this->once())->method('getAnswers')->willReturn($answers);
        $quiz->expects($this->exactly(4))->method('getIsFailedAnswer')->withConsecutive($answers[0], $answers[1],
            $answers[2], $answers[3])->willReturnCallback(function ($answer) use ($answers, $drafts) {

            return $drafts[$answers->indexOf($answer)][2];
        });

        $converter = new QuizDTOConverter();

        $dto = $converter->convert($quiz);

        $this->assertInstanceOf(QuizDTO::class, $dto);
        $this->assertEquals(1, $dto->id);
        $this->assertEquals(QuizStatus::UnansweredQuestion()->toNative(), $dto->status);
        $this->assertEquals(3, $dto->score);
        $this->assertEquals('Morpheus', $dto->name);
        $this->assertEquals(3, $dto->mistakesAvailable);
        $this->assertEquals('яблоко', $dto->question);

    }
}
