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

use Sonata\UserBundle\Model\UserManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

#[AsCommand(name: 'networking:initcms:install', description: 'Install the Networking Init cms: create update schema, load fixtures, create super user, dump assetic resources')]
class InstallCommand extends Command
{
    public function __construct(
        private readonly UserManagerInterface $userManager,
        ?string $name = null,
    ) {
        parent::__construct($name);
    }

    /**
     * configuration for the command.
     */
    protected function configure(): void
    {
        $this
            ->addArgument('username', InputArgument::OPTIONAL, 'username of the to be created super user')
            ->addArgument('email', InputArgument::OPTIONAL, 'the email address of the to be created super user')
            ->addArgument('password', InputArgument::OPTIONAL, 'password of the to be created super user')
            ->addOption('drop', '', InputOption::VALUE_NONE, 'If set: drop the existing db schema')
            ->addOption('no-fixtures', '', InputOption::VALUE_NONE, 'If set: don\'t load fixtures')
            ->addOption('use-acl', '', InputOption::VALUE_OPTIONAL, 'If set: the ACL tables will be populated. Set to true if using ACL', false)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $helper = $this->getHelper('question');

        if (!$input->getArgument('username')) {
            $question = new Question('Please enter the username of the super user: ', false);
            $question->setValidator(function ($value) {
                if (!$value || '' == trim($value)) {
                    throw new \Exception('The username can not be empty');
                }

                return $value;
            });
            $question->setMaxAttempts(3);

            $username = $helper->ask($input, $output, $question);
            $input->setArgument('username', $username);
        }

        if (!$input->getArgument('email') || !filter_var($input->getArgument('email'), FILTER_VALIDATE_EMAIL)) {
            $question = new Question('Please enter the email address of the super user: ', false);
            $question->setValidator(function ($value) {
                if (!$value || '' == trim($value)) {
                    throw new \Exception('The email address can not be empty');
                }

                if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    throw new \Exception('The email address is not valid');
                }

                return $value;
            });
            $question->setMaxAttempts(3);

            $email = $helper->ask($input, $output, $question);
            $input->setArgument('email', $email);
        }

        if (!$input->getArgument('password')) {
            $question = new Question('Please enter the password of the super user: ', false);
            $question->setValidator(function ($value) {
                if (!$value || '' == trim($value)) {
                    throw new \Exception('The password can not be empty');
                }

                return $value;
            });
            $question->setMaxAttempts(3);

            $password = $helper->ask($input, $output, $question);
            $input->setArgument('password', $password);
        }

        $result = $this->setupData($input, $output);

        if (Command::INVALID === $result) {
            $output->writeln('<comment>User cancelled action</comment>');

            return $result;
        }
        $this->createAdminUser($input, $output);

        return Command::SUCCESS;
    }

    /**
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

        $noInteraction = $input->getOption('no-interaction');

        $input = new ArrayInput($arguments);
        $input->setInteractive(!$noInteraction);

        return $command->run($input, $output);
    }

    /**
     * @throws \Exception
     */
    private function createAdminUser(InputInterface $input, OutputInterface $output): int
    {
        $user = $this->userManager->findUserByUsername($input->getArgument('username'));

        if ($user) {
            $output->writeln(sprintf('<comment>Admin user %s already exists</comment>', $input->getArgument('username')));

            return Command::SUCCESS;
        }

        $command = $this->getApplication()->find('sonata:user:create');

        $arguments = [
            'username' => $input->getArgument('username'),
            'email' => $input->getArgument('email'),
            'password' => $input->getArgument('password'),
            '--super-admin' => true,
        ];

        $input = new ArrayInput($arguments);

        return $command->run($input, $output);
    }
}
