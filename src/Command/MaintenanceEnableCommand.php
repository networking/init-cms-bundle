<?php

namespace Networking\InitCmsBundle\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'networking:initcms:maintenance-enable', description: 'Maintenance command', aliases: ['maintenance:enable'])]
class MaintenanceEnableCommand extends Command
{
    public function __construct(
        private readonly string $projectDir,
        ?string $name = null)
    {
        parent::__construct($name);
    }

    public function configure(): void
    {
        // require optional arguments of ip-addresses
        $this->addArgument('ip-addresses', InputArgument::OPTIONAL, 'Comma seperated list of IP addresses');
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('Maintenance command');
        $output->writeln('Project dir: '.$this->projectDir);

        $ipAddresses = $input->getArgument('ip-addresses');

        if (!$ipAddresses) {
            $output->writeln('No IP addresses provided');
        }

        if (false !== $ipAddresses) {
            $ipAddresses = explode(',', $ipAddresses);
        }

        // find maintenance.flag file, if not found create and add ip-addresses to it
        $maintenanceFile = $this->projectDir.'/maintenance.flag';
        $file = fopen($maintenanceFile, 'w');

        if (false !== $ipAddresses) {
            foreach ($ipAddresses as $ipAddress) {
                fwrite($file, $ipAddress."\n");
            }
        }
        fclose($file);

        $output->writeln('Maintenance file updated');

        return Command::SUCCESS;
    }
}
