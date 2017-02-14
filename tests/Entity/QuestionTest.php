<?php

namespace Tests\AppBundle\Entity;

use AppBundle\Entity\Exception as DomainExceptions;
use AppBundle\Entity\Question;
use AppBundle\Entity\Quiz;
use AppBundle\Entity\Word;
use Doctrine\Common\Collections\ArrayCollection;

class QuestionTest extends \PHPUnit_Framework_TestCase {

    public function test_ask() {
        $wordQuestion = Word::recordEnglishWord('apple');
        $rightAnswer = Word::recordRussianWord('яблоко');
        $wrongAnswers = [
            Word::recordRussianWord('куст'),
            Word::recordRussianWord('дерево'),
            Word::recordRussianWord('карандаш'),
        ];

        $question = Question::ask(new Quiz(), $wordQuestion, $rightAnswer, $wrongAnswers);

        $this->assertEquals($wordQuestion, $question->getQuestion());
        $this->assertEquals($rightAnswer, $question->getRightAnswer());
    }

    public function test_it_throws_exception_if_question_and_answer_are_of_the_same_lang() {

        $this->expectException(DomainExceptions\LogicException::class);
        $this->expectExceptionMessage('Question and answer are of the same language.');
        Question::ask(new Quiz(), Word::recordRussianWord('пирог'),
            Word::recordRussianWord('пирожок'), []);
    }

    public function test_it_throws_exception_if_do_not_provide_exactly_3_wrong_answers() {

        $this->expectException(DomainExceptions\LogicException::class);
        $this->expectExceptionMessage('There are should be 3 wrong answers.');
        Question::ask(new Quiz(), Word::recordRussianWord('пирог'),
            Word::recordEnglishWord('cake'), []);
    }

    public function test_it_throws_exception_if_the_wrong_answer_is_not_of_the_same_lang_with_the_right_answer() {

        $this->expectException(DomainExceptions\LogicException::class);
        $this->expectExceptionMessage('Wrong answer should be of the same language as right answer.');
        Question::ask(new Quiz(), Word::recordRussianWord('пирог'),
            Word::recordEnglishWord('cake'), [
                Word::recordEnglishWord('cheese'),
                Word::recordEnglishWord('bottle'),
                Word::recordRussianWord('каскадер'),
            ]);
    }

    public function test_it_wins_if_right_answer_provided() {

        $answer = Word::recordEnglishWord('cake');

        $question = Question::ask(new Quiz(), Word::recordRussianWord('пирог'), $answer, [
            Word::recordEnglishWord('cheese'),
            Word::recordEnglishWord('bottle'),
            Word::recordEnglishWord('candy'),
        ]);

        $question->answer($answer);

        return $question;
    }


    public function test_answer_throws_exceptions() {

        $answer = Word::recordEnglishWord('cake');
        $wrongAnswers = [
            Word::recordEnglishWord('cheese'),
            Word::recordEnglishWord('bottle'),
            Word::recordEnglishWord('candy')
        ];

        $question = Question::ask(new Quiz(), Word::recordRussianWord('пирог'), $answer,
            $wrongAnswers);

        try {
            $question->answer($wrongAnswers[0]);
            $this->fail('Exception was not thrown.');
        } catch (DomainExceptions\WrongAnswerException $e) {
            $this->assertTrue($question->isFailedAnswer($wrongAnswers[0]));
        }

        try {
            $question->answer($wrongAnswers[0]);
            $this->fail('Exception was not thrown.');
        } catch (DomainExceptions\RepeatingAnswerException $e) {
        }

        try {
            $question->answer($wrongAnswers[1]);
            $this->fail('Exception was not thrown.');
        } catch (DomainExceptions\WrongAnswerException $e) {
        }

        try {
            $question->answer(Word::recordRussianWord('тест'));
            $this->fail('Exception was not thrown.');
        } catch (DomainExceptions\AnswerDoesNotBelongToTheQuestionException $e) {
        }

        try {
            $question->answer($wrongAnswers[2]);
            $this->fail('Exception was not thrown.');
        } catch (DomainExceptions\WrongAnswerException $e) {
        }
    }

    public function test_getAnswers() {

        $answer = Word::recordEnglishWord('cake');
        $wrongAnswers = [
            Word::recordEnglishWord('cheese'),
            Word::recordEnglishWord('bottle'),
            Word::recordEnglishWord('candy'),
        ];

        $question = Question::ask(new Quiz(), Word::recordRussianWord('пирог'), $answer,
            $wrongAnswers);

        $answers = $question->getAnswers();
        $this->assertInstanceOf(ArrayCollection::class, $answers);
        $this->assertCount(4, $answers);
    }
}