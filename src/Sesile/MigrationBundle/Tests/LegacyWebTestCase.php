<?php


namespace Sesile\MigrationBundle\Tests;


use Symfony\Bundle\FrameworkBundle\Console\Application;
use Sesile\MainBundle\Tests\Tools\SesileWebTestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Finder\Finder;

/**
 * Class LegacyWebTestCase
 * @package Sesile\MigrationBundle\Tests
 */
class LegacyWebTestCase extends SesileWebTestCase
{
    /**
     *
     */
    protected function resetLegacyTestDatabase()
    {
        $application = new Application($this->getContainer()->get('kernel'));
        $application->setAutoExit(false);
        $command = $application->find('test:database:legacy:create');
        $arguments = array(
            'command' => 'test:database:legacy:create',
            '--env' => 'test',
            '--quiet' => true,
        );
        $commandInput = new ArrayInput($arguments);
        $output = new NullOutput();
        $command->run($commandInput, $output);
    }

    /**
     * execute sql files located in the Sql folder
     *
     * @param array $fixtures
     * @return bool
     */
    public function loadLegacyFixtures(array $fixtures = [])
    {
        try {
            // Bundle to manage file and directories
            $finder = new Finder();
            $finder->in(__DIR__.'/Sql/');
            $finder->files();
            $finder->sortByName();
            if ($finder->count() == 0) {
                return false;
            }
            foreach ($finder as $file) {
                print "Importing: {$file->getBasename()} ".PHP_EOL;

                $sql = $file->getContents();
                $connection = $this->getContainer()->get('doctrine.dbal.legacy_connection');
                $connection->exec($sql);  // Execute native SQL
            }
        } catch (\Exception $e) {
            return false;
        }

        return true;
    }

    /**
     * @param $sql
     *
     * @return bool
     */
    public function loadCustomFixture($sql)
    {
        try {
            $connection = $this->getContainer()->get('doctrine.dbal.legacy_connection');
            $connection->exec($sql);
        } catch (\Exception $e) {
            return false;
        }

        return true;
    }
}
