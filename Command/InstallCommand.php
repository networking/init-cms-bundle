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

class InstallCommand extends Command
{
    /**
     * configuration for the command
     */
    protected function configure()
    {
        $this->setName('networking:cms:install')
                ->setDescription('Install the Networking Init cms')
//                ->setDefinition(
//                    array(
//                        new InputArgument('username', InputArgument::REQUIRED, 'The owner (username) of all automatically generated objects'),
//                        new InputArgument('user_entity', InputArgument::REQUIRED, 'The Handler for users if unsure, try "ApplicationSonataUserBundle:User"')
//                    )
//                )
        ;

    }

    /**
     * @param  \Symfony\Component\Console\Input\InputInterface   $input
     * @param  \Symfony\Component\Console\Output\OutputInterface $output
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->createDB($output);
        $this->initACL($output);
        $this->sonataSetupACL($output);
        $this->loadFixtures($output);
        $this->createAdminUser($output);
        $this->dumpAssetic($output);

//        $username   = $input->getArgument('username');
//        $userEntity   = $input->getArgument('user_entity');
//        $this->sonataGenerateObjectACL($output, $username, $userEntity);
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
     * unused at the moment
     * @param $output
     * @param $username
     * @param string $bundle
     * @return int
     */
    private function sonataGenerateObjectACL($output, $username, $bundle='NetworkingUserBundle:User')
    {
        $command = $this->getApplication()->find('sonata:admin:generate-object-acl');

        $arguments = array(
            'command' => 'sonata:admin:generate-object-acl',
            '--object_owner' => $username,
            '--user_entity' => $bundle
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
//        if (!$input->getArgument('username')) {
//            $username = $this->getHelper('dialog')->askAndValidate(
//                $output,
//                'Please choose an existing username.'."\n".'He will be the owner of all automatically generated objects: ',
//                function($username) {
//                    if (empty($username)) {
//                        throw new \Exception('Username can not be empty');
//                    }
//
//                    return $username;
//                }
//            );
//            $input->setArgument('username', $username);
//        }
//
//        if (!$input->getArgument('user_entity')) {
//            $userEntity = $this->getHelper('dialog')->askAndValidate(
//                $output,
//                'Please choose a user_entity (f.e. "ApplicationSonataUserBundle:User"): ',
//                function($userEntity) {
//                    if (empty($userEntity)) {
//                        throw new \Exception('user_entity can not be empty');
//                    }
//
//                    return $userEntity;
//                }
//            );
//            $input->setArgument('user_entity', $userEntity);
//        }
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
