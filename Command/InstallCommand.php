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
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class InstallCommand extends Command
{
    /**
     * configuration for the command
     */
    protected function configure()
    {
        $this->setName('networking:initcms:install')
                ->setDescription("Install the Networking Init cms: create update schema, load fixtures, create super user, dump assetic resources")
                ->addOption('username', '', InputOption::VALUE_REQUIRED, 'username of the to be created super user')
                ->addOption('email', '', InputOption::VALUE_REQUIRED, 'the email address of the to be created super user')
                ->addOption('password', '', InputOption::VALUE_REQUIRED, 'password of the to be created super user')
        ;

    }

    /**
     * @param  \Symfony\Component\Console\Input\InputInterface   $input
     * @param  \Symfony\Component\Console\Output\OutputInterface $output
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->setupData($output);
        $this->createAdminUser($input, $output);
        $this->dumpAssetic($output);
    }

    private function setupData($output)
    {
         $command = $this->getApplication()->find('networking:initcms:data-setup');

        $arguments = array(
            'command' => 'networking:initcms:data-setup',
            '--drop' => true,
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
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return mixed
     */
    private function createAdminUser(InputInterface $input, OutputInterface $output)
    {
        $arguments = array();

        $arguments['command'] = 'fos:user:create';

        if($input->getOption('username')){
            $arguments['username'] = $input->getOption('username');
        }
        if($input->getOption('email')){
            $arguments['email'] = $input->getOption('email');
        }
        if($input->getOption('password')){
            $arguments['password'] = $input->getOption('password');
        }
        $command = $this->getApplication()->find('fos:user:create');

        $arguments['--super-admin'] = true;


        $input = new ArrayInput($arguments);

        return $command->run($input, $output);
    }


}
