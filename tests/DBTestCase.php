<?php

namespace Tests;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Console\Input\ArrayInput;

abstract class DBTestCase extends KernelTestCase {

    const ENVIRONMENT = 'test';
    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var Application
     */
    private $console;

    protected function setUp()
    {
        static::bootKernel([
            'environment' => self::ENVIRONMENT
        ]);

        $this->console = new Application(static::$kernel);
        $this->console->setAutoExit(false);

        $this->em = static::$kernel->getContainer()
            ->get('doctrine')
            ->getManager();

        $this->runConsole('doctrine:database:create', [ '-n' => true ]);
        $this->runConsole('doctrine:schema:drop', [ '--force' => true ]);
        $this->runConsole('doctrine:schema:create');
    }

    protected function tearDown()
    {
        parent::tearDown();

        $this->em->close();
        $this->em = null; // avoid memory leaks
        $this->console = null;
    }

    private function runConsole($command, array $options = array())
    {
        $options['--env'] = self::ENVIRONMENT;
        $options['-q'] = null;

        $input = new ArrayInput(array_merge($options, array('command' => $command)));
        $result = $this->console->run($input);

        if (0 != $result) {
            throw new \RuntimeException(sprintf('Something has gone wrong, got return code %d for command %s', $result, $command));
        }

        return $result;
    }
}