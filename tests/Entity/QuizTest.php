<?php

namespace Tests\AppBundle\Entity;

use AppBundle\Entity\Exception as DomainExceptions;
use AppBundle\Entity\Quiz;
use AppBundle\Entity\QuizStatus;
use AppBundle\Entity\Word;
use AppBundle\Value\Username;
use Doctrine\Common\Collections\ArrayCollection;

class QuizTest extends \PHPUnit_Framework_TestCase {

    public function test_it_begins() {

        $name = new Username('username');
        $quiz = Quiz::begin($name);

        $this->assertEquals($name, $quiz->getName());
        $this->assertTrue($quiz->getStatus()->equalTo(QuizStatus::NoQuestion()));
        $this->assertEquals(0, $quiz->getScore());
        $this->assertEquals(3, $quiz->getMistakesAvailable());
        $this->assertFalse($quiz->hasUnansweredQuestion());
        $this->assertInstanceOf(ArrayCollection::class, $quiz->getQuestions());

        return $quiz;
    }

    /**
     * @depends test_it_begins
     */
    public function test_impossible_to_ask_new_question_if_there_is_unanswered_question(Quiz $quiz) {

        $quiz->askNewQuestion(Word::recordRussianWord('волк'),
            Word::recordEnglishWord('wolf'), [
                Word::recordEnglishWord('sky'),
                Word::recordEnglishWord('tree'),
                Word::recordEnglishWord('water')
            ]);
        $this->expectException(DomainExceptions\QuizAlreadyHasAQuestionException::class);
        $quiz->askNewQuestion(Word::recordRussianWord('волк'),
            Word::recordEnglishWord('wolf'), [
                Word::recordEnglishWord('sky'),
                Word::recordEnglishWord('tree'),
                Word::recordEnglishWord('water')
            ]);
    }

    public function test_quiz_end() {

        $quiz = new Quiz();

        $quiz->end();

        $this->assertTrue($quiz->getStatus()->equalTo(QuizStatus::Ended()));

        return $quiz;
    }

    /**
     * @depends test_quiz_end
     */
    public function test_impossible_to_ask_new_question_if_quiz_is_ended(Quiz $quiz) {

        $this->expectException(DomainExceptions\QuizIsEndedException::class);
        $quiz->askNewQuestion(Word::recordRussianWord('волк'),
            Word::recordEnglishWord('wolf'), [
                Word::recordEnglishWord('sky'),
                Word::recordEnglishWord('tree'),
                Word::recordEnglishWord('water')
            ]);
    }

    public function test_impossible_to_answer_on_quiz_without_question() {

        $quiz = Quiz::begin(new Username('Neo'));
        $this->expectException(DomainExceptions\QuizHasNoQuestionToAnswerException::class);
        $quiz->answer(Word::recordRussianWord('test'));
    }

    public function test_it_increases_score_on_right_answer() {

        $quiz = Quiz::begin(new Username('Neo'));

        $questionWord = Word::recordRussianWord('волк');
        $answer = Word::recordEnglishWord('wolf');

        $quiz->askNewQuestion($questionWord, $answer, [
            Word::recordEnglishWord('sky'),
            Word::recordEnglishWord('tree'),
            Word::recordEnglishWord('water')
        ]);

        $this->assertEquals(0, $quiz->getScore());
        $quiz->answer($answer);
        $this->assertEquals(1, $quiz->getScore());
        $this->assertTrue(QuizStatus::NoQuestion()->equalTo($quiz->getStatus()));
        

        return [$quiz, $questionWord, $answer];
    }

    /**
     * @depends test_it_increases_score_on_right_answer
     */
    public function test_impossible_to_ask_new_question_with_used_word(array $data) {

        list($quiz, $questionWord, $answer) = $data;

        try {
            $quiz->askNewQuestion($questionWord, $answer, [
                Word::recordEnglishWord('sky'),
                Word::recordEnglishWord('tree'),
                Word::recordEnglishWord('water')
            ]);
            $this->fail('Exception was not thrown.');
        } catch (DomainExceptions\UsedWordException $e) {

        }

        return $quiz;
    }

    /**
     * @depends test_impossible_to_ask_new_question_with_used_word
     */
    public function test_it_saves_previous_questions(Quiz $quiz)
    {
        $questionWord = Word::recordRussianWord('свобода');
        $answer = Word::recordEnglishWord('freedom');

        $quiz->askNewQuestion($questionWord, $answer, [
            Word::recordEnglishWord('sky'),
            Word::recordEnglishWord('tree'),
            Word::recordEnglishWord('water')
        ]);

        $this->assertCount(2, $quiz->getQuestions());
    }

    public function test_after_3_wrong_answers_quiz_will_end() {

        $wrongAnswers = [
            Word::recordRussianWord('аблоко'),
            Word::recordRussianWord('человек'),
            Word::recordRussianWord('змея'),
        ];

        $quiz = Quiz::begin(new Username('Neo'));

        $quiz->askNewQuestion(Word::recordEnglishWord('cake'),
            Word::recordRussianWord('торт'), $wrongAnswers);

        try {
            $quiz->answer($wrongAnswers[0]);
            $this->fail('Exception was not thrown.');
        } catch (DomainExceptions\WrongAnswerException $e) {
        }

        $this->assertEquals(2, $quiz->getMistakesAvailable());

        try {
            $quiz->answer($wrongAnswers[1]);
            $this->fail('Exception was not thrown.');
        } catch (DomainExceptions\WrongAnswerException $e) {
        }

        $this->assertEquals(1, $quiz->getMistakesAvailable());

        try {
            $quiz->answer($wrongAnswers[2]);
            $this->fail('Exception not thrown.');
        } catch (DomainExceptions\WrongAnswerException $e) {
        }

        $this->assertEquals(0, $quiz->getMistakesAvailable());
        $this->assertTrue($quiz->isEnded());
    }
}