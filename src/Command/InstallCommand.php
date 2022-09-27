<?php
/**
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
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;

#[AsCommand(
    name: 'networking:initcms:install',
    description: 'Install the Networking Init cms: create update schema, load fixtures, create super user, dump assetic resources',
)]
class InstallCommand extends Command
{
    protected static $defaultName = 'networking:initcms:install';

    protected static $defaultDescription = 'Install the Networking Init cms: create update schema, load fixtures, create super user, dump assetic resources';

    /**
     * configuration for the command.
     */
    protected function configure()
    {
        $this
            ->addOption('drop', '', InputOption::VALUE_NONE, 'If set: drop the existing db schema')
            ->addOption('no-fixtures', '', InputOption::VALUE_NONE, 'If set: don\'t load fixtures')
            ->addOption('username', '', InputOption::VALUE_REQUIRED, 'username of the to be created super user')
            ->addOption('email', '', InputOption::VALUE_REQUIRED, 'the email address of the to be created super user')
            ->addOption('password', '', InputOption::VALUE_REQUIRED, 'password of the to be created super user')
            ->addOption('use-acl', '', InputOption::VALUE_NONE, 'If set: use acl')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->setupData($input, $output);
        $this->createAdminUser($input, $output);
    }

    /**
     * @param $input
     * @param $output
     * @return int
     * @throws \Exception
     */
    private function setupData(InputInterface $input, OutputInterface $output)
    {
        $command = $this->getApplication()->find('networking:initcms:data-setup');
        $arguments = [
            'command' => 'networking:initcms:data-setup',
            '--drop' => $input->getOption('drop'),
            '--no-fixtures' => $input->getOption('no-fixtures'),
            '--use-acl' => $input->getOption('use-acl'),
        ];

        $input = new ArrayInput($arguments);

        return $command->run($input, $output);
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws \Exception
     */
    private function createAdminUser(InputInterface $input, OutputInterface $output)
    {
        $arguments = [];

        $arguments['command'] = 'fos:user:create';

        if ($input->getOption('username')) {
            $arguments['username'] = $input->getOption('username');
        }
        if ($input->getOption('email')) {
            $arguments['email'] = $input->getOption('email');
        }
        if ($input->getOption('password')) {
            $arguments['password'] = $input->getOption('password');
        }
        $command = $this->getApplication()->find('fos:user:create');

        $arguments['--super-admin'] = true;

        $input = new ArrayInput($arguments);

        return $command->run($input, $output);
    }
}
