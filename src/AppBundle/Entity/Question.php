<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use AppBundle\Entity\Exception as DomainExceptions;

/**
 * Question
 *
 * @ORM\Table(name="question")
 * @ORM\Entity()
 */
class Question {

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var Quiz
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Quiz", inversedBy="questions")
     */
    private $quiz;

    /**
     * @var Word
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Word", cascade={"persist"})
     */
    private $question;

    /**
     * @var Word
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Word", cascade={"persist"})
     */
    private $rightAnswer;

    /**
     * @var Word
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Word", cascade={"persist"})
     */
    private $answer1;

    /**
     * @var Word
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Word", cascade={"persist"})
     */
    private $answer2;

    /**
     * @var Word
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Word", cascade={"persist"})
     */
    private $answer3;

    /**
     * @var Word
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Word", cascade={"persist"})
     */
    private $answer4;

    /**
     * @var ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\Word", cascade={"persist", "remove"})
     * @ORM\JoinTable(
     *     name="failed_answers",
     *     joinColumns={@ORM\JoinColumn(name="question_id", referencedColumnName="id")},
     *     inverseJoinColumns={@ORM\JoinColumn(name="word_id", referencedColumnName="id")}
     * )
     */
    private $failedAnswers;

    public function __construct() {

        $this->wrongAnswers = new ArrayCollection();
        $this->failedAnswers = new ArrayCollection();
    }

    /**
     * @param Quiz $quiz
     * @param Word $question
     * @param Word $rightAnswer
     * @param array $wrongAnswers
     *
     * @return Question
     * @throws DomainExceptions\LogicException
     */
    public static function ask(Quiz $quiz, Word $question, Word $rightAnswer, array $wrongAnswers) : Question {

        if ($question->getLang() === $rightAnswer->getLang()) {
            throw new DomainExceptions\LogicException('Question and answer are of the same language.');
        }

        if (count($wrongAnswers) !== 3) {
            throw new DomainExceptions\LogicException('There are should be 3 wrong answers.');
        }

        foreach ($wrongAnswers as $wrongAnswer) {
            if ($rightAnswer->getLang() !== $wrongAnswer->getLang()) {
                throw new DomainExceptions\LogicException('Wrong answer should be of the same language as right answer.');
            }

            if (!$wrongAnswer instanceof Word) {
                throw new DomainExceptions\LogicException('Wrong answers should be instances of Word class.');
            }
        }

        $q = new Question();

        $q->quiz = $quiz;
        $q->question = $question;
        $q->rightAnswer = $rightAnswer;

        $answers = array_merge($wrongAnswers, [$rightAnswer]);

        shuffle($answers);

        list($q->answer1, $q->answer2, $q->answer3, $q->answer4) = $answers;

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
     * @return Word
     */
    public function getQuestion() : Word {

        return $this->question;
    }

    /**
     * @return Word
     */
    public function getRightAnswer() : Word {

        return $this->rightAnswer;
    }

    /**
     * @return ArrayCollection
     */
    public function getAnswers() {

        return new ArrayCollection(array_map(function ($i) {

            $prop = 'answer' . $i;
            return $this->$prop;
        }, [1, 2, 3, 4]));
    }

    public function isFailedAnswer(Word $word) : bool {

        return $this->failedAnswers->contains($word);
    }

    /**
     * @param Word $word
     *
     * @throws DomainExceptions\AnswerDoesNotBelongToTheQuestionException
     * @throws DomainExceptions\RepeatingAnswerException
     * @throws DomainExceptions\WrongAnswerException
     */
    public function answer(Word $word) {

        if ($this->hasAnswer($word)) {
            if ($this->rightAnswer === $word) {
                return;
            } else {
                if ($this->failedAnswers->contains($word)) {
                    throw new DomainExceptions\RepeatingAnswerException;
                } else {
                    $this->failedAnswers->add($word);
                    throw new DomainExceptions\WrongAnswerException;
                }
            }
        } else {
            throw new DomainExceptions\AnswerDoesNotBelongToTheQuestionException;
        }
    }

    private function hasAnswer(Word $answer) : bool {

        foreach ([1, 2, 3, 4] as $i) {
            $prop = 'answer' . $i;
            if ($this->$prop === $answer) {
                return true;
            }
        }

        return false;
    }
}

