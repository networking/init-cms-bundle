<?php

declare(strict_types=1);

/**
 * This file is part of the init_cms_sandbox package.
 *
 * (c) net working AG <info@networking.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Networking\InitCmsBundle\Controller;

use App\Kernel;
use Doctrine\Persistence\ManagerRegistry;
use Networking\InitCmsBundle\Helper\PageHelper;
use Networking\InitCmsBundle\Model\PageInterface;
use Networking\InitCmsBundle\Model\PageManagerInterface;
use Networking\InitCmsBundle\Entity\BasePage as Page;
use Networking\InitCmsBundle\Form\Type\InstallUserType as UserType;
use Sonata\AdminBundle\Form\FormErrorIteratorToConstraintViolationList;
use Sonata\UserBundle\Model\UserManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\StreamOutput;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * Class InitCmsInstallController.
 *
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
class InstallController extends AbstractController
{
    private ?\Symfony\Bundle\FrameworkBundle\Console\Application $application = null;

    private array $consoleOutput = [];

    public function __construct
    (
        private readonly PageManagerInterface $pageManager, 
        private readonly PageHelper $pageHelper, 
        private readonly UserManagerInterface $userManager,
        private readonly ManagerRegistry $doctrine,
        private readonly KernelInterface $kernel
    )
    {
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(Request $request)
    {
        $errorMessage = '';
        $hasDB = false;
        $installed = false;
        try {

            $schemaManager = $this->doctrine->getConnection()->getSchemaManager();
            if ($schemaManager->tablesExist(['page']) !== true) {
                throw new \Exception('Pages not loaded');
            }

            /** @var $page Page */
            $page = $this->pageManager->findOneBy(['isHome' => 1]);

            if (!$page) {
                throw new \Exception('Pages not loaded');
            }

            $url = '/';
            $label = 'Go to the homepage';
            $hasDB = true;
            $installed = true;
        } catch (\Exception $e) {
            $connection = $this->doctrine->getConnection();
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
                'installed' => $installed,
            ]
        );
    }

    /**
     * @param $complete
     * @return RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function installDbAction(Request $request, $complete): \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
    {
        $installFailed = false;
        try {
            $users = $this->userManager->findAll();
            if (count($users) < 1) {
                throw new \Exception('Users not loaded');
            }

            return new RedirectResponse($this->generateUrl('_configure_cms'));
        } catch (\Exception) {
            $connection = $this->doctrine->getConnection();
            try {
                $connection->connect();
            } catch (\Exception) {
                return new RedirectResponse($this->generateUrl('_configure_cms'));
            }
        }

        /** @var \Symfony\Component\Form\Form $form */
        $form = $this->createForm(UserType::class);

        if ('POST' == $request->getMethod()) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $application = new Application($this->kernel);
                $application->setAutoExit(false);

                $this->setApplication($application);
                $formData = $request->get('user');
                $username = $formData['email'];
                $email = $formData['email'];
                $password = $formData['password']['first'];

                $output = $this->getStreamOutput();

                if ($complete == 0) {
                    $this->initACL($output);
                    $this->createDB($output);
                    $returnCode = $this->sonataSetupACL($output);
                    if (!$returnCode) {
                        ++$complete;
                    }
                }

                if ($complete == 1) {
                    $output = $this->getStreamOutput($output);
                    $returnCode = $this->loadFixtures($output);
                    $this->publishPages($output);

                    if (!$returnCode) {
                        ++$complete;
                    }
                }

                if ($complete == 2) {
                    $output = $this->getStreamOutput($output);
                    $returnCode = $this->createAdminUser($output, $username, $email, $password);
                    if (!$returnCode) {
                        ++$complete;
                    }
                }

                if ($complete == 3) {
                    /* @var \Symfony\Component\HttpFoundation\Session\Session $session */
                    $request->getSession()->getFlashBag()->add('success', 'Init CMS was successfully installed');

                    $url = $this->generateUrl('_configure_cms');

                    if($request->isXmlHttpRequest()){
                        return $this->json(['success' => true, 'redirect' => $url]);
                    }

                    return new RedirectResponse($url);

                }
                $request->getSession()->getFlashBag()->add('error', $this->getConsoleDisplay($output));
                $installFailed = true;
            }


            $errors = FormErrorIteratorToConstraintViolationList::transform($form->getErrors(true));

            if($request->isXmlHttpRequest()){
                return $this->json($errors, Response::HTTP_BAD_REQUEST);
            }
        }


        return $this->render(
            '@NetworkingInitCms/InitCmsInstall/index.html.twig',
            [
                'form' => $form->createView(),
                'title' => 'Install the init cms',
                'complete' => (int) $complete,
                'install_failed' => $installFailed,
            ]
        );
    }

    /**
     * @return int
     * @throws \Exception
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
     * @return int
     * @throws \Exception
     */
    private function initACL(OutputInterface $output)
    {
        $output->write('> Initializing the ACL tables', true);
        $arguments = [
            'command' => 'init:acl',
        ];

        $input = new ArrayInput($arguments);

        return $this->getApplication()->run($input, $output);
    }

    /**
     * @return int
     * @throws \Exception
     */
    private function sonataSetupACL(OutputInterface $output)
    {
        $output->write('> Inserting sonata ACL entries', true);
        $arguments = [
            'command' => 'sonata:admin:setup-acl',
        ];

        $input = new ArrayInput($arguments);

        return $this->getApplication()->run($input, $output);
    }

    /**
     * @param $username
     * @param $email
     * @param $password
     * @return int
     * @throws \Exception
     */
    private function createAdminUser(OutputInterface $output, $username, $email, $password)
    {
        $output->write('> Create an admin user', true);
        $arguments = [
            'command' => 'sonata:user:create',
            'username' => $username,
            'email' => $email,
            'password' => $password,
            '--super-admin' => true,
        ];

        $input = new ArrayInput($arguments);

        return $this->getApplication()->run($input, $output);
    }

    /**
     * @return int
     * @throws \Exception
     */
    private function loadFixtures(OutputInterface $output)
    {
        $output->write('> Load dummy cms data', true);
        $arguments = [
            'command' => 'doctrine:fixtures:load',
            '--group' => ['init_cms'],
            '--no-interaction' => true,
        ];

        $input = new ArrayInput($arguments);

        return $this->getApplication()->run($input, $output);
    }

    /**
     * @return int
     */
    public function publishPages(OutputInterface $output)
    {
        /** @var PageInterface[] $selectedModels */
        $selectedModels = $this->pageManager->findAll();

        try {
            foreach ($selectedModels as $selectedModel) {
                $selectedModel->setStatus(PageInterface::STATUS_PUBLISHED);
                $this->pageManager->save($selectedModel);
                $this->pageHelper->makePageSnapshot($selectedModel);
            }

            return 0;
        } catch (\Exception $e) {
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

    private function getConsoleDisplay(StreamOutput $output): string
    {
        rewind($output->getStream());
        $errors = [
            'The following errors occured during setup:',
            trim(stream_get_contents($output->getStream())),
        ];
        fclose($output->getStream());

        return nl2br(implode("\n", $errors));
    }

    /**
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

    public static function getSubscribedServices(): array
    {
        return parent::getSubscribedServices() + [
            'kernel' => '?'.KernelInterface::class,

        ];
    }
}


