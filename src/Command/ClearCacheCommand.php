<?php

namespace Networking\InitCmsBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class ClearCacheCommand
 * @package Ibrows\SonataTranslationBundle\Command
 */
class ClearCacheCommand extends Command
{

    protected $translator;
    protected $managedLocales;

    public function __construct(TranslatorInterface $translator, $managedLocales, string $name = null)
    {
        $this->managedLocales = $managedLocales;
        $this->translator = $translator;
        parent::__construct($name);
    }

    /**
     *
     */
    public function configure()
    {
        $this
            ->setName('networking:initcms:cleartranslations');
        ;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Remove cache for translations in: '. implode(", ", $this->managedLocales));
        $this->translator->removeLocalesCacheFiles($this->managedLocales);
    }


}