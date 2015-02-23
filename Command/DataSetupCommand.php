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

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputOption;

/**
 * Class DataSetupCommand
 * @package Networking\InitCmsBundle\Command
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
class DataSetupCommand extends ContainerAwareCommand
{
    /**
     * configuration for the command
     */
    protected function configure()
    {
        $this->setName('networking:initcms:data-setup')
            ->setDescription('create and update db schema and append fixtures')
            ->addOption('drop', '', InputOption::VALUE_NONE, 'If set: drop the existing db schema')
            ->addOption('no-fixtures', '', InputOption::VALUE_NONE, 'If set: don\'t load fixtures');

    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if ($input->getOption('drop')) {
            $this->dropSchema($output);
        }

        $this->updateSchema($output);
        $this->initACL($output);
        $this->sonataSetupACL($output);

        if (!$input->getOption('no-fixtures')) {
            $this->loadFixtures($output);
            $this->publishPages($output);
        }
    }

    /**
     * @param OutputInterface $output
     * @return int
     */
    public function dropSchema(OutputInterface $output)
    {
        $command = $this->getApplication()->find('doctrine:schema:drop');

        $arguments = array(
            'command' => 'doctrine:schema:drop',
            '--force' => true,
        );

        $input = new ArrayInput($arguments);

        return $command->run($input, $output);
    }

    /**
     * @param  \Symfony\Component\Console\Output\OutputInterface $output
     * @return int|string
     */
    private function updateSchema(OutputInterface $output)
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
     * @param $output
     * @return int
     */
    private function initACL($output)
    {
        $command = $this->getApplication()->find('init:acl');

        $arguments = array(
            'command' => 'init:acl'
        );

        $input = new ArrayInput($arguments);

        return $command->run($input, $output);
    }

    /**
     * @param $output
     * @return int
     */
    private function sonataSetupACL($output)
    {
        $command = $this->getApplication()->find('sonata:admin:setup-acl');

        $arguments = array(
            'command' => 'sonata:admin:setup-acl'
        );

        $input = new ArrayInput($arguments);

        return $command->run($input, $output);
    }


    /**
     * interact
     * unused at the moment
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @throws \Exception
     */
    protected function interact(InputInterface $input, OutputInterface $output)
    {
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
            '--fixtures' => __DIR__ . '/../Fixtures',
            '--append' => true
        );

        $input = new ArrayInput($arguments);

        return $command->run($input, $output);
    }

    public function publishPages(OutputInterface $output)
    {
        /** @var \Networking\InitCmsBundle\Entity\PageManager $modelManager */
        $modelManager = $this->getContainer()->get('networking_init_cms.page_manager');
        $selectedModels = $modelManager->findAll();

        try {
            foreach ($selectedModels as $selectedModel) {
                /** @var \Networking\InitCmsBundle\Model\PageInterface $selectedModel */
                $selectedModel->setStatus(\Networking\InitCmsBundle\Model\PageInterface::STATUS_PUBLISHED);
                $modelManager->save($selectedModel);
                /** @var $pageHelper \Networking\InitCmsBundle\Helper\PageHelper */
                $pageHelper = $this->getContainer()->get('networking_init_cms.helper.page_helper');
                $pageHelper->makePageSnapshot($selectedModel);
            }
            return 0;
        }catch (\Exception $e){
            $output->writeln($e->getMessage());
            return 1;
        }
    }
}
