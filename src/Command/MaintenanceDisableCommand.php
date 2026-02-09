<?php

namespace Networking\InitCmsBundle\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'networking:initcms:maintenance-disable', description: 'Maintenance command', aliases: ['maintenance:disable'])]
class MaintenanceDisableCommand extends Command
{
    public function __construct(
        private readonly string $projectDir,
        ?string $name = null,
    ) {
        parent::__construct($name);
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('Maintenance command');
        $output->writeln('Project dir: '.$this->projectDir);

        $maintenanceFile = $this->projectDir.'/maintenance.flag';
        if (file_exists($maintenanceFile)) {
            unlink($maintenanceFile);
            $output->writeln('Maintenance file removed');
        } else {
            $output->writeln('Maintenance file not found');
        }

        return Command::SUCCESS;
    }
}
