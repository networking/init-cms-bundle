<?php
/**
 * This file is part of the init_cms_sandbox package.
 *
 * (c) net working AG <info@networking.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Networking\InitCmsBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\StreamOutput;
use Symfony\Component\Console\Input\ArrayInput;
use Networking\InitCmsBundle\Entity\BasePage as Page;
use Networking\InitCmsBundle\Form\Type\InstallUserType as UserType;

/**
 * Class InitCmsInstallController
 * @package Networking\InitCmsBundle\Controller
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
class InitCmsInstallController extends Controller
{
    /**
     * @var \Symfony\Bundle\FrameworkBundle\Console\Application $application
     */
    private $application;

    /**
     * @var array
     */
    private $consoleOutput = [];

    /**
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(Request $request)
    {
        $errorMessage = '';
        $hasDB = false;
        $installed = false;
        try {
            /** @var $page Page */
            $pageManager = $this->get('networking_init_cms.page_manager');
            $page = $pageManager->findOneBy(
                ['isHome' => 1, 'locale' => $request->getLocale()]
            );
            if (!$page) {
                throw new \Exception('Pages not loaded');
            }
            $url = $page->getFullPath();
            $label = 'Go to the homepage';
            $hasDB = true;
            $installed = true;

        } catch (\Exception $e) {
            $connection = $this->getDoctrine()->getConnection();
            try {
                $connection->connect();
                $hasDB = true;
            } catch (\Exception $e) {
                $errorMessage = $e->getMessage();
            }

            $url = $this->generateUrl('_install_db');
            $label = 'Install init cms';
        }

        return $this->render(
            '@NetworkingInitCms/InitCmsInstall/index.html.twig',
            [
                'action' => ['url' => $url, 'label' => $label],
                'title' => 'Welcome to the init cms',
                'has_DB' => $hasDB,
                'error_message' => $errorMessage,
                'installed' => $installed
            ]
        );
    }

    /**
     * @param Request $request
     * @param $complete
     * @return RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function installDbAction(Request $request, $complete)
    {
        $installFailed = false;
        try {
            /** @var \FOS\UserBundle\Doctrine\UserManager $userManager */
            $userManager = $this->get('fos_user.user_manager');
            $users = $userManager->findUsers();
            if (count($users) < 1) {
                throw new \Exception('Users not loaded');
            }

            return new RedirectResponse($this->generateUrl('_configure_cms'));
        } catch (\Exception $e) {
            $connection = $this->getDoctrine()->getConnection();
            try {
                $connection->connect();
            } catch (\Exception $e) {
                return new RedirectResponse($this->generateUrl('_configure_cms'));
            }
        }

        /** @var \Symfony\Component\Form\Form $form */
        $form =  $this->createForm(new UserType());

        if ('POST' == $request->getMethod()) {
            $form->handleRequest($request);

            if ($form->isValid()) {

                $kernel = $this->get('kernel');
                $application = new Application($kernel);
                $application->setAutoExit(false);

                $this->setApplication($application);
                $formData = $request->get('user');
                $username = $formData['username'];
                $email = $formData['email'];
                $password = $formData['password']['first'];


                $output = $this->getStreamOutput();


                if ($complete == 0) {
                    $this->initACL($output);
                    $this->createDB($output);
                    $returnCode = $this->sonataSetupACL($output);
                    if (!$returnCode) {
                        $complete++;
                    }
                }

                if ($complete == 1) {
                    $output = $this->getStreamOutput($output);
                    $returnCode = $this->loadFixtures($output);
                    $this->publishPages($output);

                    if (!$returnCode) {
                        $complete++;
                    }
                }

                if ($complete == 2) {
                    $output = $this->getStreamOutput($output);
                    $returnCode = $this->createAdminUser($output, $username, $email, $password);
                    if (!$returnCode) {
                        $complete++;
                    }
                }


                if ($complete == 3) {
                    /** @var \Symfony\Component\HttpFoundation\Session\Session $session */
                    $this->get('session')->getFlashBag()->add('success', 'Init CMS was successfully installed');

                    return new RedirectResponse($this->generateUrl('_configure_cms'));
                }
                $this->get('session')->getFlashBag()->add('error', $this->getConsoleDisplay($output));
                $installFailed = true;
            }
        }

        return $this->render(
            '@NetworkingInitCms/InitCmsInstall/index.html.twig',
            [
                'form' => $form->createView(),
                'title' => 'Install the init cms',
                'complete' => $complete,
                'install_failed' => $installFailed
            ]
        );
    }

    /**
     * @param OutputInterface $output
     * @return int
     */
    private function createDB(OutputInterface $output)
    {
        $output->write('> Loading the schema of the DB', true);

        $arguments = [
            'command' => 'doctrine:schema:update',
            '--force' => true,
        ];

        $input = new ArrayInput($arguments);

        return $this->getApplication()->run($input);
    }

    /**
     * @param OutputInterface $output
     * @return int
     */
    private function initACL(OutputInterface $output)
    {
        $output->write('> Initializing the ACL tables', true);
        $arguments = [
            'command' => 'init:acl'
        ];

        $input = new ArrayInput($arguments);

        return $this->getApplication()->run($input, $output);
    }

    /**
     * @param OutputInterface $output
     * @return int
     */
    private function sonataSetupACL(OutputInterface $output)
    {
        $output->write('> Inserting sonata ACL entries', true);
        $arguments = [
            'command' => 'sonata:admin:setup-acl'
        ];

        $input = new ArrayInput($arguments);

        return $this->getApplication()->run($input, $output);
    }


    /**
     * @param OutputInterface $output
     * @param $username
     * @param $email
     * @param $password
     * @return int|string
     */
    private function createAdminUser(OutputInterface $output, $username, $email, $password)
    {
        $output->write('> Create an admin user', true);
        $arguments = [
            'command' => 'fos:user:create',
            'username' => $username,
            'email' => $email,
            'password' => $password,
            '--super-admin' => true,
        ];

        $input = new ArrayInput($arguments);

        return $this->getApplication()->run($input, $output);
    }

    /**
     * @param  OutputInterface $output
     * @return int|string
     */
    private function loadFixtures(OutputInterface $output)
    {
        $output->write('> Load dummy cms data', true);
        $arguments = [
            'command' => 'doctrine:fixtures:load',
            '--fixtures' => __DIR__ . '/../Fixtures',
            '--no-interaction' => true
        ];

        $input = new ArrayInput($arguments);

        return $this->getApplication()->run($input, $output);
    }

    /**
     * @param OutputInterface $output
     * @return int
     */
    public function publishPages(OutputInterface $output)
    {
        /** @var \Networking\InitCmsBundle\Entity\PageManager $modelManager */
        $modelManager = $this->get('networking_init_cms.page_manager');
        $selectedModels = $modelManager->findAll();

        try {
            foreach ($selectedModels as $selectedModel) {
                /** @var \Networking\InitCmsBundle\Model\PageInterface $selectedModel */
                $selectedModel->setStatus(\Networking\InitCmsBundle\Model\PageInterface::STATUS_PUBLISHED);
                $modelManager->save($selectedModel);
                /** @var $pageHelper \Networking\InitCmsBundle\Helper\PageHelper */
                $pageHelper = $this->get('networking_init_cms.helper.page_helper');
                $pageHelper->makePageSnapshot($selectedModel);
            }
            return 0;
        }catch (\Exception $e){
            $output->writeln($e->getMessage());
            return 1;
        }
    }

    /**
     * @param $application
     */
    private function setApplication(Application $application)
    {
        $this->application = $application;
    }

    /**
     * @return Application
     */
    private function getApplication()
    {
        return $this->application;
    }

    /**
     * @param StreamOutput $output
     * @return string
     */
    private function getConsoleDisplay(StreamOutput $output)
    {
        rewind($output->getStream());
        $errors = [
            'The following errors occured during setup:',
            trim(stream_get_contents($output->getStream()))
        ];
        fclose($output->getStream());


        return nl2br(implode("\n", $errors));
    }

    /**
     * @param StreamOutput $output
     * @return StreamOutput
     */
    private function getStreamOutput(StreamOutput $output = null)
    {

        if ($output) {
            rewind($output->getStream());
            $this->consoleOutput[] = trim(stream_get_contents($output->getStream()));
            fclose($output->getStream());
        }

        return new StreamOutput(fopen('php://memory', 'w+', false), StreamOutput::VERBOSITY_VERBOSE);
    }
}
