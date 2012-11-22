<?php

/*
 * This file is part of the Networking package.
 *
 * (c) net working AG <info@networking.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Networking\InitCmsBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\ArrayInput;

class InstallCommand extends Command
{
    /**
     * configuration for the command
     */
    protected function configure()
    {
        $this->setName('networking:cms:install')
                ->setDescription('Install the Networiking Init cms');
    }

    /**
     * @param  \Symfony\Component\Console\Input\InputInterface   $input
     * @param  \Symfony\Component\Console\Output\OutputInterface $output
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->createDB($output);
        $this->loadFixtures($output);
        $this->createAdminUser($output);
        $this->dumpAssetic($output);
    }

    /**
     * @param  \Symfony\Component\Console\Output\OutputInterface $output
     * @return int|string
     */
    private function createDB(OutputInterface $output)
    {
        $command = $this->getApplication()->find('doctrine:schema:update');

        $arguments = array(
            'command' => 'doctrine:schema:update',
            '--force' => true,
        );

        $input = new ArrayInput($arguments);

        return $command->run($input, $output);
    }

    /**
     * @param  \Symfony\Component\Console\Output\OutputInterface $output
     * @return int|string
     */
    private function dumpAssetic(OutputInterface $output)
    {

        $command = $this->getApplication()->find('assetic:dump');

        $arguments = array(
            'command' => 'assetic:dump',
            '--env' => 'prod',
            '--no-debug' => true
        );

        $input = new ArrayInput($arguments);

        return $command->run($input, $output);
    }

    /**
     * @param  \Symfony\Component\Console\Output\OutputInterface $output
     * @return int|string
     */
    private function createAdminUser(OutputInterface $output)
    {
        $command = $this->getApplication()->find('fos:user:create');

        $arguments = array(
            'command' => 'fos:user:create',
            '--super-admin' => true
        );

        $input = new ArrayInput($arguments);

        return $command->run($input, $output);
    }

    /**
     * @param  \Symfony\Component\Console\Output\OutputInterface $output
     * @return int|string
     */
    private function loadFixtures(OutputInterface $output)
    {
        $command = $this->getApplication()->find('doctrine:fixtures:load');

        $arguments = array(
            'command' => 'doctrine:fixtures:load',
            '--fixtures' => __DIR__ . '/../Fixtures'
        );

        $input = new ArrayInput($arguments);

        return $command->run($input, $output);
    }
}
