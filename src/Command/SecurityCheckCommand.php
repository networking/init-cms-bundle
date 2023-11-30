<?php

namespace Networking\InitCmsBundle\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'networking:initcms:security-check',
    description: 'Check installed PHP bundles, and installed NPM packages for security vulnerabilities.',
)]
class SecurityCheckCommand extends Command
{
    public function __construct(private readonly string $projectDir)
    {
        parent::__construct();
    }

    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ): int {
        $io = new SymfonyStyle($input, $output);

        $io->title('Security Check');
        $securityOutput = [];
        $io->writeln('Symfony Security Check...');
        $securityOutput[] = '';
        exec('symfony security:check', $securityOutput, $return_var);

        if ($return_var > 0) {
            $io->error('PHP Bundle Security Check failed.');

            $io->writeln($securityOutput);

            return Command::FAILURE;
        }
        if (file_exists($this->projectDir.'/yarn.lock')) {
            $io->writeln('Running yarn audit...');
            $securityOutput[] = '';
            $securityOutput[] = sprintf('<comment>%s</>', 'Yarn Audit');
            $securityOutput[] = sprintf('<comment>%s</>', '==========');
            $securityOutput[] = '';
            exec('yarn audit', $securityOutput, $return_var);
        }

        if ($return_var > 0) {
            $io->error('Yarn Security Check failed.');

            $io->writeln($securityOutput);

            return Command::FAILURE;
        }

        if (file_exists($this->projectDir.'/package-lock.json')) {
            $io->writeln('Running npm audit...');
            $securityOutput[] = '';
            $securityOutput[] = sprintf('<comment>%s</>', 'NPM Audit');
            $securityOutput[] = sprintf('<comment>%s</>', '=========');
            $securityOutput[] = '';
            exec('npm audit --verbose', $securityOutput, $return_var);
        }

        if ($return_var > 0) {
            $io->error('NPM Security Check failed.');
            $io->writeln($securityOutput);
            return Command::FAILURE;
        }

        $io->writeln($securityOutput);

        $io->success('Security Check completed.');

        return Command::SUCCESS;
    }
}
