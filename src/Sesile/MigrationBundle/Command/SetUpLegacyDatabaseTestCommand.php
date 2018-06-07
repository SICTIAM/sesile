<?php

namespace Sesile\MigrationBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class SetUpLegacyDatabaseTestCommand
 * @package Sesile\MigrationBundle\Command
 */
class SetUpLegacyDatabaseTestCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('test:database:legacy:create')
            ->setDescription('Create the Legacy Database for testing');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $env = $input->getParameterOption(array('--env', '-e'));
        if ($env !== 'test') {
            $output->writeln('Sorry this command is only for Test Environment. Please use --env=test');

            return false;
        }
        try {
            $this->dropDatabase($output);
            $this->createDatabase($output);
            $legacyConnection = $this->getContainer()->get('doctrine')->getConnection('legacy');
            $databaseName = $legacyConnection->getDatabase();
            $sqlFilePath = __DIR__.'/legacy.sql';
            $legacySQL = str_replace('%%database_name%%', $databaseName, file_get_contents($sqlFilePath));
            if (!is_file($sqlFilePath)) {
                return $output->writeln('Sorry no legacy.sql file found');
            }
            $legacyConnection->exec($legacySQL);
        } catch (\Exception $e) {
            return $output->writeln(sprintf('ERROR ! Could not create Test legacy Database. %s', $e->getMessage()));
        }
        return $output->writeln('Test legacy Database successfully created with schema');

    }

    /**
     * @param OutputInterface $output
     */
    protected function dropDatabase(OutputInterface $output)
    {
        $command = $this->getApplication()->find('doctrine:database:drop');
        $arguments = array(
            'command' => 'doctrine:database:drop',
            '--connection' => 'legacy',
            '--env' => 'test',
            '--force' => true,
            '--quiet' => true,
            '--if-exists' => true,
        );
        $commandInput = new ArrayInput($arguments);
        $command->run($commandInput, $output);
    }

    /**
     * @param OutputInterface $output
     */
    protected function createDatabase(OutputInterface $output)
    {
        $command = $this->getApplication()->find('doctrine:database:create');
        $arguments = array(
            'command' => 'doctrine:database:create',
            '--connection' => 'legacy',
            '--env' => 'test',
            '--quiet' => true,
            '--if-not-exists' => true,
        );
        $commandInput = new ArrayInput($arguments);
        $command->run($commandInput, $output);
    }
}
