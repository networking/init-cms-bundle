<?php
/**
 * Created by PhpStorm.
 * User: yorkie
 * Date: 04.10.18
 * Time: 10:57
 */

namespace Networking\InitCmsBundle\Command;


use SensioLabs\Security\Result;
use SensioLabs\Security\SecurityChecker;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SecurityCheckCommand extends Command {

	protected static $defaultName = 'networking:initcms:security-check';

	private $checker;

	private $mailer;

	private $lockFile = 'composer.lock';

	public function __construct(SecurityChecker $checker, \Swift_Mailer $mailer ) {
		$this->checker = $checker;
		$this->mailer  = $mailer;

		parent::__construct();
	}

	/**
	 * configuration for the command.
	 */
	protected function configure() {
		$this->setName( 'networking:initcms:security-check' )
		     ->setDescription( 'Check security patches in composer.lock' )
		     ->addArgument( 'email', InputArgument::REQUIRED, 'email address to send report to' );
	}

	public function execute( InputInterface $input, OutputInterface $output ) {
	    /** @var Result $vulnerabilities */
		$vulnerabilities = $this->checker->check( $this->lockFile, 'json');

		if (!$vulnerabilities instanceof Result) {
			$output->writeln( $this->getHelperSet()->get( 'formatter' )->formatBlock( 'Security Checker Server returned garbage.', 'error', true ) );

			return 127;
		}


//		if ( $vulnerabilities->count() > 0 ) {


			$message = new \Swift_Message();
		    $message
			                         ->setSubject('Security Check')
			                         ->setFrom('log@initcms.com')
			                         ->setTo($input->getArgument('email'))
			                         ->setBody(
				                         $this->writeEmail($vulnerabilities),
				                         'text/plain'
			                         );
			$this->mailer->send($message);

			return 1;
//		}

	}


	/**
	 * @param array $vulnerabilities
	 *
	 * @return string
	 */
	public function writeEmail(  $vulnerabilities ) {
		$message = "Symfony Security Check Report\n";
		$message .= sprintf( "Checked file: %s\n", realpath( $this->lockFile ) );
		$message .= "\n";
		foreach ( $vulnerabilities as $dependency => $issues ) {
			$message .= sprintf( "%s (%s)\n", $dependency, $issues['version'] );
			$message .= "----------------------------\n";
			$message .= "\n";

			$details = array_map( function ( $value ) {
				return sprintf( "* %s: %s\n   %s", $value['cve'] ?: '(no CVE ID)', $value['title'], $value['link'] );
			}, $issues['advisories'] );

			foreach ( $details as $detail ) {
				$message .= sprintf( "%s \n", $detail );
				$message .= "\n";
			}
			$message .= "\n";
		}
		$message .= 'This checker can only detect vulnerabilities that are referenced in the SensioLabs security advisories database. Execute this command regularly to check the newly discovered vulnerabilities.';

		return $message;
	}

}
