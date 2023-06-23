<?php
/**
 * This file is part of the Networking package.
 *
 * (c) net working AG <info@networking.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare(strict_types=1);
namespace Networking\InitCmsBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;

#[AsCommand(name: 'networking:initcms:install', description: 'Install the Networking Init cms: create update schema, load fixtures, create super user, dump assetic resources')]
class InstallCommand extends Command
{

    /**
     * configuration for the command.
     */
    protected function configure(): void
    {
        $this
            ->addArgument('username', InputArgument::REQUIRED, 'username of the to be created super user')
            ->addArgument('email', InputArgument::REQUIRED, 'the email address of the to be created super user')
            ->addArgument('password',  InputArgument::REQUIRED, 'password of the to be created super user')
            ->addOption('drop', '', InputOption::VALUE_NONE, 'If set: drop the existing db schema')
            ->addOption('no-fixtures', '', InputOption::VALUE_NONE, 'If set: don\'t load fixtures')
            ->addOption('use-acl', '', InputOption::VALUE_OPTIONAL, 'If set: use acl', false)
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->setupData($input, $output);
        $this->createAdminUser($input, $output);

        return 0;
    }

    /**
     * @param $input
     * @param $output
     * @throws \Exception
     */
    private function setupData(InputInterface $input, OutputInterface $output): int
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
     * @throws \Exception
     */
    private function createAdminUser(InputInterface $input, OutputInterface $output): int
    {
        $arguments = [];

        $arguments['command'] = 'sonata:user:create';

        $arguments['username'] = $input->getArgument('username');
        $arguments['email'] = $input->getArgument('email');
        $arguments['password'] = $input->getArgument('password');

        $command = $this->getApplication()->find('sonata:user:create');

        $arguments['--super-admin'] = true;

        $input = new ArrayInput($arguments);

        return $command->run($input, $output);
    }
}
