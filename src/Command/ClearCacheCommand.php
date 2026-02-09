<?php

declare(strict_types=1);

namespace Networking\InitCmsBundle\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsCommand(name: 'networking:initcms:cleartranslations', description: 'Clear translations')]
class ClearCacheCommand extends Command
{
    protected $translator;

    /** @TODO Remove in later versions */
    public function __construct(TranslatorInterface $translator, protected $managedLocales, ?string $name = null)
    {
        $this->translator = $translator;
        parent::__construct($name);
    }

    /**
     * @return int|void|null
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('Remove cache for translations in: '.implode(', ', $this->managedLocales));
        $this->translator->removeLocalesCacheFiles($this->managedLocales);

        return Command::SUCCESS;
    }
}
