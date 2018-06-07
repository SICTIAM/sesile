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
    protected function resetDatabase()
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
     * @throws \Doctrine\DBAL\DBALException
     * @throws \Exception
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
            throw new \Exception(sprintf('An Error occured during the Legacy Sql fixtures. %s', $e->getMessage()));
        }
    }
}
