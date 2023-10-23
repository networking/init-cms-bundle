<?php

declare(strict_types=1);

namespace Networking\InitCmsBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

#[AsCommand(name: 'networking:initcms:cleartranslations', description: 'Clear translations')]
class ClearCacheCommand extends Command
{

    protected $translator;
    /** @TODO Remove in later versions */



    public function __construct(TranslatorInterface $translator, protected $managedLocales, string $name = null)
    {
        $this->translator = $translator;
        parent::__construct($name);
    }


    /**
     * @return int|null|void
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Remove cache for translations in: '. implode(", ", $this->managedLocales));
        $this->translator->removeLocalesCacheFiles($this->managedLocales);
    }


}
