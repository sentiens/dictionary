<?php

namespace AppBundle\Entity;

use AppBundle\Value\Username;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use AppBundle\Entity\Exception as DomainExceptions;

/**
 * Quiz
 *
 * @ORM\Table(name="quiz")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\QuizRepository")
 */
class Quiz {

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var QuizStatus
     *
     * @ORM\Embedded(class="AppBundle\Entity\QuizStatus")
     */
    private $status;

    /**
     * @var int
     *
     * @ORM\Column(name="score", type="integer")
     */
    private $score;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=10)
     */
    private $name;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Question", mappedBy="quiz", cascade={"persist", "remove"})
     */
    private $questions;

    /**
     * @var Question
     *
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\Question", cascade={"persist", "remove"})
     */
    private $unansweredQuestion;

    /**
     * @var integer
     *
     * @ORM\Column(name="lives", type="smallint")
     */
    private $mistakesAvailable;

    public function __construct() {

        $this->questions = new ArrayCollection();
    }

    /**
     * @param Username $name
     *
     * @return Quiz
     * @throws DomainExceptions\QuizAlreadyHasAQuestionException
     * @throws DomainExceptions\QuizIsEndedException
     * @throws DomainExceptions\UsedWordException
     */
    public static function begin(Username $name) : Quiz {

        $q = new Quiz();

        $q->name = $name;
        $q->score = 0;
        $q->mistakesAvailable = 3;
        $q->status = QuizStatus::NoQuestion();

        return $q;
    }

    /**
     * Get id
     * @codeCoverageIgnore
     * @return int
     */
    public function getId() {

        return $this->id;
    }

    /**
     * @deprecated Only for testing purposes
     */
    public function _setId($id) {

        $this->id = $id;
    }

    /**
     * @return QuizStatus
     */
    public function getStatus() : QuizStatus {

        return $this->status;
    }

    /**
     * @return int
     */
    public function getScore() : int {

        return $this->score;
    }

    /**
     * @return int
     */
    public function getMistakesAvailable() : int {

        return $this->mistakesAvailable;
    }

    /**
     * @return string
     */
    public function getName() : string {

        return $this->name;
    }

    /**
     * @return ArrayCollection
     */
    public function getQuestions() {

        return $this->questions;
    }

    /**
     * @return Word
     */
    public function getQuestion() {

        $this->shouldHaveUnansweredQuestion();

        return $this->unansweredQuestion->getQuestion();
    }

    private function shouldHaveUnansweredQuestion() {

        if (!$this->hasUnansweredQuestion()) {
            throw new DomainExceptions\QuizHasNoQuestionToAnswerException;
        }
    }

    /**
     * @return bool
     */
    public function hasUnansweredQuestion() : bool {

        return $this->status->equalTo(QuizStatus::UnansweredQuestion());
    }

    public function getIsFailedAnswer(Word $word) {

        $this->shouldHaveUnansweredQuestion();

        return $this->unansweredQuestion->isFailedAnswer($word);
    }

    public function getAnswers() {

        $this->shouldHaveUnansweredQuestion();

        return $this->unansweredQuestion->getAnswers();
    }

    /**
     * @return bool
     */
    public function isEnded() : bool {

        return $this->status->equalTo(QuizStatus::Ended());
    }

    /**
     * @return ArrayCollection
     */
    public function getUsedWords() {

        return $this->questions->map(function (Question $r) {

            return $r->getQuestion();
        });
    }

    /**
     * @param Word $question
     * @param Word $rightAnswer
     * @param array $wrongAnswers
     *
     * @return $this
     * @throws DomainExceptions\QuizAlreadyHasAQuestionException
     * @throws DomainExceptions\QuizIsEndedException
     * @throws DomainExceptions\UsedWordException
     */
    public function askNewQuestion(Word $question, Word $rightAnswer, array $wrongAnswers) {

        if ($this->isEnded()) {
            throw new DomainExceptions\QuizIsEndedException;
        }

        $this->shouldNotHaveUnansweredQuestion();

        if ($this->getUsedWords()->contains($question)) {
            throw new DomainExceptions\UsedWordException;
        }

        $this->setQuestion(Question::ask($this, $question, $rightAnswer, $wrongAnswers));

        return $this;
    }

    /**
     * @param Word $word
     *
     * @return $this
     * @throws DomainExceptions\AnswerDoesNotBelongToTheQuestionException
     * @throws DomainExceptions\QuizHasNoQuestionToAnswerException
     * @throws DomainExceptions\RepeatingAnswerException
     * @throws DomainExceptions\WrongAnswerException
     */
    public function answer(Word $word) {

        $this->shouldHaveUnansweredQuestion();

        try {
            $this->unansweredQuestion->answer($word);
            $this->score++;
            $this->removeQuestion();
        } catch (DomainExceptions\WrongAnswerException $e) {
            $this->mistakeHappened();
            throw $e;
        }

        return $this;
    }

    /**
     * @throws DomainExceptions\QuizAlreadyHasAQuestionException
     */
    private function shouldNotHaveUnansweredQuestion() {

        if ($this->hasUnansweredQuestion()) {
            throw new DomainExceptions\QuizAlreadyHasAQuestionException;
        }
    }

    /**
     * @param Question $question
     *
     * @return $this
     */
    private function setQuestion(Question $question) {

        $this->questions->add($this->unansweredQuestion = $question);

        $this->status = QuizStatus::UnansweredQuestion();

        return $this;
    }

    /**
     * @return $this
     */
    private function removeQuestion() {

        $this->unansweredQuestion = null;
        $this->status = QuizStatus::NoQuestion();

        return $this;
    }

    private function mistakeHappened() {

        $this->mistakesAvailable--;
        if ($this->mistakesAvailable <= 0) {
            $this->removeQuestion();
            $this->end();
        }

        return $this;
    }

    /**
     * @return $this
     * @throws DomainExceptions\QuizAlreadyHasAQuestionException
     */
    public function end() {

        $this->removeQuestion();
        $this->status = QuizStatus::Ended();
        return $this;
    }
}
