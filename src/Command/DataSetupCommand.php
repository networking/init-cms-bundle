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

use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManager;
use Networking\InitCmsBundle\Helper\PageHelper;
use Networking\InitCmsBundle\Model\PageManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;

#[AsCommand(name: 'networking:initcms:data-setup', description: 'create and update db schema and append fixtures')]
class DataSetupCommand extends Command
{
    /**
     * @var string
     */
    protected static $defaultName = 'networking:initcms:data-setup';

    protected static $defaultDescription = 'create and update db schema and append fixtures';
    /**
     * @var ManagerRegistry
     */
    protected $registry;
    protected $pageManager;
    protected $pageHelper;

    /**
     * DataSetupCommand constructor.
     * @param ManagerRegistry $registry
     * @param PageManagerInterface $pageManager
     * @param PageHelper $pageHelper
     * @param string|null $name
     */
    public function __construct(
        ManagerRegistry $registry,
        PageManagerInterface $pageManager,
        PageHelper $pageHelper,
        string $name = null
    ) {
        $this->registry = $registry;
        $this->pageManager = $pageManager;
        $this->pageHelper = $pageHelper;
        parent::__construct($name);
    }

    /**
     * configuration for the command.
     */
    protected function configure()
    {
        $this
            ->addOption('drop', '', InputOption::VALUE_NONE, 'If set: drop the existing db schema')
            ->addOption('no-fixtures', '', InputOption::VALUE_NONE, 'If set: don\'t load fixtures')
            ->addOption('use-acl', '', InputOption::VALUE_OPTIONAL, 'If set: use acl', false);
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
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
            $this->publishPages($output);
        }

        return 0;
    }

    /**
     * @param OutputInterface $output
     *
     * @return int
     */
    public function dropSchema(OutputInterface $output)
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
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @return int|string
     */
    private function updateSchema(OutputInterface $output)
    {
        $command = $this->getApplication()->find('doctrine:schema:update');

        $arguments = [
            'command' => 'doctrine:schema:update',
            '--force' => true,
        ];

        $input = new ArrayInput($arguments);

        return $command->run($input, $output);
    }

    /**
     * @param $output
     *
     * @return int
     */
    private function initACL($output)
    {
        $command = $this->getApplication()->find('acl:init');

        $arguments = [
            'command' => 'acl:init',
        ];

        $input = new ArrayInput($arguments);

        return $command->run($input, $output);
    }

    /**
     * @param $output
     *
     * @return int
     */
    private function sonataSetupACL($output)
    {
        $command = $this->getApplication()->find('sonata:admin:setup-acl');

        $arguments = [
            'command' => 'sonata:admin:setup-acl',
        ];

        $input = new ArrayInput($arguments);

        return $command->run($input, $output);
    }

    /**
     * interact
     * unused at the moment.
     *
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @throws \Exception
     */
    protected function interact(InputInterface $input, OutputInterface $output)
    {
    }

    /**
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @return int|string
     */
    private function loadFixtures(OutputInterface $output)
    {
        $command = $this->getApplication()->find('doctrine:fixtures:load');

        $arguments = [
            'command' => 'doctrine:fixtures:load',
            '--group' => ['init_cms'],
            '--append' => true,
        ];

        $input = new ArrayInput($arguments);

        return $command->run($input, $output);
    }

    public function publishPages(OutputInterface $output)
    {
        $this->registry->resetManager();
        $this->pageManager->resetEntityManager($this->registry->getManager());

        try {
            $pages = $this->pageManager->findAll();
            foreach ($pages as $page) {
                /** @var \Networking\InitCmsBundle\Model\PageInterface $page */
                $this->pageHelper->makePageSnapshot($page);
                $this->pageManager->save($page);
            }

            return 0;
        } catch (\Exception $e) {
            $output->writeln(sprintf('<error>%s</error>', $e->getMessage()));
            die;

            return 1;
        }
    }
}
