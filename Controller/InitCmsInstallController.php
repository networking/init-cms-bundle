<?php


namespace Networking\InitCmsBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\StreamOutput;
use Symfony\Component\Console\Input\ArrayInput;
use Networking\InitCmsBundle\Entity\BasePage as Page;
use Sandbox\InitCmsBundle\Form\UserType;

class InitCmsInstallController extends Controller
{
    /**
     * @var \Symfony\Bundle\FrameworkBundle\Console\Application $application
     */
    private $application;

    private $consoleOutput = array();

    /**
     * @Route("/cms_install", name="_configure_cms")
     * @Route("/welcome", name="_welcome_cms")
     * @Template()
     */
    public function indexAction()
    {
        $errorMessage = '';
        $hasDB = false;
        $installed = false;
        try {
            /** @var $page Page */
            $page = $this->getDoctrine()->getRepository('NetworkingInitCmsBundle:Page')->findOneBy(
                array('isHome' => 1, 'locale' => $this->getRequest()->getLocale())
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
            'NetworkingInitCmsBundle:InitCmsInstall:index.html.twig',
            array(
                'action' => array('url' => $url, 'label' => $label),
                'title' => 'Welcome to the init cms',
                'has_DB' => $hasDB,
                'error_message' => $errorMessage,
                'installed' => $installed
            )
        );
    }

    /**
     * @Route("/install_db/{complete}", name="_install_db", requirements={"complete" = "\d+"}, defaults={"complete" = "0"})
     * @Template()
     */
    public function installDbAction(Request $request, $complete)
    {
        $installFailed = false;
        try {
            /** @var $page Page */
            $user = $this->getDoctrine()->getRepository('NetworkingInitCmsBundle:User')->findAll();
            if (count($user) < 1) {
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

        /** @var $form UserType */
        $form = $this->get('form.factory')->create(new UserType());

        if ('POST' == $request->getMethod()) {
            $form->bindRequest($request);
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

                    $message = \Swift_Message::newInstance()
                        ->setSubject('Hello Email')
                        ->setFrom('send@example.com')
                        ->setTo($email)
                        ->setBody(
                        $this->renderView(
                            'NetworkingInitCmsBundle:InitCmsInstall:email.txt.twig',
                            array('name' => $username, 'password' => $password)
                        )
                    );
                    $this->get('mailer')->send($message);

                    return new RedirectResponse($this->generateUrl('_configure_cms'));
                }
                $this->get('session')->getFlashBag()->add('error', $this->getConsoleDisplay($output));
                $installFailed = true;
            }
        }

        return $this->render(
            'NetworkingInitCmsBundle:InitCmsInstall:index.html.twig',
            array(
                'form' => $form->createView(),
                'title' => 'Install the init cms',
                'complete' => $complete,
                'install_failed' => $installFailed
            )
        );
    }

    /**
     * @param  \Symfony\Component\Console\Output\OutputInterface $output
     * @return int|string
     */
    private function createDB(OutputInterface $output)
    {
        $output->write('> Loading the schema of the DB', true);

        $arguments = array(
            'command' => 'doctrine:schema:update',
            '--force' => true,
        );

        $input = new ArrayInput($arguments);

        return $this->getApplication()->run($input);
    }

    /**
     * @param $output
     * @return int
     */
    private function initACL($output)
    {
        $output->write('> Initializing the ACL tables', true);
        $arguments = array(
            'command' => 'init:acl'
        );

        $input = new ArrayInput($arguments);

        return $this->getApplication()->run($input, $output);
    }

    /**
     * @param $output
     * @return int
     */
    private function sonataSetupACL($output)
    {
        $output->write('> Inserting sonata ACL entries', true);
        $arguments = array(
            'command' => 'sonata:admin:setup-acl'
        );

        $input = new ArrayInput($arguments);

        return $this->getApplication()->run($input, $output);
    }


    /**
     * @param  \Symfony\Component\Console\Output\OutputInterface $output
     * @param $username
     * @param $email
     * @param $password
     * @return int|string
     */
    private function createAdminUser(OutputInterface $output, $username, $email, $password)
    {
        $output->write('> Create an admin user', true);
        $arguments = array(
            'command' => 'fos:user:create',
            'username' => $username,
            'email' => $email,
            'password' => $password,
            '--super-admin' => true,
        );

        $input = new ArrayInput($arguments);

        return $this->getApplication()->run($input, $output);
    }

    /**
     * @param  \Symfony\Component\Console\Output\OutputInterface $output
     * @return int|string
     */
    private function loadFixtures(OutputInterface $output)
    {
        $output->write('> Load dummy cms data', true);
        $arguments = array(
            'command' => 'doctrine:fixtures:load',
            '--fixtures' => __DIR__ . '/../Fixtures',
            '--no-interaction' => true
        );

        $input = new ArrayInput($arguments);

        return $this->getApplication()->run($input, $output);
    }

    /**
     * @param $application
     */
    private function setApplication(Application $application)
    {
        $this->application = $application;
    }

    /**
     * @return \Symfony\Bundle\FrameworkBundle\Console\Application
     */
    private function getApplication()
    {
        return $this->application;
    }

    /**
     * @param \Symfony\Component\Console\Output\StreamOutput $output
     * @return string
     */
    private function getConsoleDisplay(StreamOutput $output)
    {
        rewind($output->getStream());
        $errors = array(
            'The following errors occured during setup:',
            trim(stream_get_contents($output->getStream()))
        );
        fclose($output->getStream());


        return nl2br(implode("\n", $errors));
    }

    private function getStreamOutput(StreamOutput $output = null)
    {

        if ($output) {
            rewind($output->getStream());
            $this->consoleOutput[] = trim(stream_get_contents($output->getStream()));
            fclose($output->getStream());
        }

        return new StreamOutput(fopen('php://memory', 'w+', false), StreamOutput::VERBOSITY_VERBOSE);
    }

    private function getFullConsoleDisplay()
    {
        return nl2br(implode("\n", $this->consoleOutput));
    }
}
