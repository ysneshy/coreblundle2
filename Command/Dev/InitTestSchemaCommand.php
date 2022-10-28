<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Command\Dev;

use Claroline\MigrationBundle\Migrator\Migrator;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Doctrine\Bundle\DoctrineBundle\Command\CreateDatabaseDoctrineCommand;

class InitTestSchemaCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('claroline:init_test_schema')
            ->setDescription('Creates a test database with full app schema (without data)');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if ($input->getOption('env') !== 'test') {
            throw new \Exception(
                'This command is only available in the test environment (--env=test)'
            );
        }

        $this->createDatabase();
        $this->createSchema($output);
    }

    private function createDatabase()
    {
        $command = new CreateDatabaseDoctrineCommand();
        $command->setContainer($this->getContainer());
        $code = $command->run(new ArrayInput(array()), new NullOutput());

        if ($code !== 0) {
            throw new \Exception(
                'Database cannot be created (existing database must be dropped first)'
            );
        }
    }

    private function createSchema(OutputInterface $output)
    {
        $migrator = $this->getContainer()->get('claroline.migration.manager');
        $migrator->setLogger(
            function ($message) use ($output) {
                $output->writeln($message);
            }
        );

        foreach ($this->getContainer()->get('kernel')->getBundles() as $bundle) {
            if (count($migrator->getBundleStatus($bundle)[Migrator::STATUS_AVAILABLE]) > 1) {
                $migrator->upgradeBundle($bundle, Migrator::VERSION_FARTHEST);
            }
        }
    }
}
