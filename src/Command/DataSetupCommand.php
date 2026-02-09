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

use Doctrine\Persistence\ManagerRegistry;
use Networking\InitCmsBundle\Helper\PageHelper;
use Networking\InitCmsBundle\Model\PageManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

#[AsCommand(name: 'networking:initcms:data-setup', description: 'create and update db schema and append fixtures')]
class DataSetupCommand extends Command
{
    protected bool $proceed = true;

    /**
     * DataSetupCommand constructor.
     */
    public function __construct(
        protected ManagerRegistry $registry,
        protected PageManagerInterface $pageManager,
        protected PageHelper $pageHelper,
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
            ->addOption(
                'drop',
                '',
                InputOption::VALUE_NONE,
                'If set: drop the existing db schema'
            )
            ->addOption(
                'no-fixtures',
                '',
                InputOption::VALUE_NONE,
                'If set: don\'t load fixtures'
            )
            ->addOption(
                'use-acl',
                '',
                InputOption::VALUE_OPTIONAL,
                'If set: use acl',
                false
            );
    }

    protected function execute(
        InputInterface $input,
        OutputInterface $output,
    ): int {
        if (!$this->proceed) {
            $output->writeln('<error>Aborted</error>');

            return Command::INVALID;
        }

        if ($input->getOption('drop')) {
            $this->dropSchema($output);
        }

        $this->updateSchema($output);

        if ($input->getOption('use-acl')) {
            $this->initACL($output);
            $this->sonataSetupACL($output);
        }

        if (!$input->getOption('no-fixtures')) {
            $this->loadFixtures($output);

            if (Command::FAILURE === $this->publishPages($output)) {
                return Command::FAILURE;
            }
        }

        return Command::SUCCESS;
    }

    public function interact(InputInterface $input, OutputInterface $output): void
    {
        $helper = $this->getHelper('question');
        $output->writeln('<info>Are you sure you want to continue? The will change the structure of your database, by adding tables and columns relavent to the DB</info>');
        $question = new ConfirmationQuestion(
            '<question>Continue with this action? (y/n)</question>',
            false
        );

        $this->proceed = $helper->ask($input, $output, $question);
    }

    public function dropSchema(OutputInterface $output): int
    {
        $command = $this->getApplication()->find('doctrine:schema:drop');

        $arguments = [
            'command' => 'doctrine:schema:drop',
            '--force' => true,
        ];

        $input = new ArrayInput($arguments);

        return $command->run($input, $output);
    }

    /**
     * @return int|string
     */
    private function updateSchema(OutputInterface $output): int
    {
        $command = $this->getApplication()->find('doctrine:schema:update');

        $arguments = [
            'command' => 'doctrine:schema:update',
            '--force' => true,
            '--complete' => true,
        ];

        $input = new ArrayInput($arguments);

        return $command->run($input, $output);
    }

    private function initACL($output): int
    {
        $command = $this->getApplication()->find('acl:init');

        $input = new ArrayInput([]);

        return $command->run($input, $output);
    }

    private function sonataSetupACL($output): int
    {
        $command = $this->getApplication()->find('sonata:admin:setup-acl');

        $input = new ArrayInput([]);

        return $command->run($input, $output);
    }

    /**
     * @return int|string
     */
    private function loadFixtures(OutputInterface $output): int
    {
        $command = $this->getApplication()->find('doctrine:fixtures:load');

        $pages = $this->pageManager->findAll();

        if (count($pages) > 0) {
            $output->writeln('<comment>Date already exist, skipping fixtures</comment>');

            return Command::SUCCESS;
        }

        $arguments = [
            '--group' => ['init_cms'],
            '--append' => true,
        ];

        $input = new ArrayInput($arguments);

        return $command->run($input, $output);
    }

    public function publishPages(OutputInterface $output): int
    {
        $this->registry->resetManager();
        $this->pageManager->resetEntityManager($this->registry->getManager());

        try {
            $pages = $this->pageManager->findAll();
            foreach ($pages as $page) {
                /* @var \Networking\InitCmsBundle\Model\PageInterface $page */
                $this->pageHelper->makePageSnapshot($page);
                $this->pageManager->save($page);
            }

            return 0;
        } catch (\Exception $e) {
            $output->writeln(sprintf('<error>%s</error>', $e->getMessage()));

            return Command::FAILURE;
        }
    }
}
