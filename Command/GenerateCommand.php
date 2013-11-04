<?php
/*
 * This file is part of the Sonata project.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Networking\InitCmsBundle\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\Output;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Sonata\EasyExtendsBundle\Command\GenerateCommand as SonataGenerateCommand;
use Symfony\Component\Finder\Finder;
use Sonata\EasyExtendsBundle\Bundle\BundleMetadata;

/**
 * Generate Application entities from bundle entities
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class GenerateCommand extends SonataGenerateCommand
{
    protected $mappingConfigDirectory;
    protected $extendedConfigDirectory;
    /**
     * {@inheritDoc}
     */
    protected function configure()
    {

        $this
            ->setName('networking:init_cms:generate')
            ->setHelp(
                <<<EOT
                The <info>easy-extends:generate:entities</info> command generating a valid bundle structure from a Vendor Bundle.

  <info>ie: ./app/console sonata:easy-extends:generate SonataUserBundle</info>
EOT
            );

        $this->setDescription('Create entities used by Sonata\'s bundles');

        $this->addArgument('bundle', InputArgument::OPTIONAL, 'The bundle name to "easy-extends"', false);
        $this->addOption(
            'dest',
            'd',
            InputOption::VALUE_OPTIONAL,
            'The base folder where the Application will be created',
            false
        );
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $dest = $input->getOption('dest');
        if ($dest) {
            $dest = realpath($dest);
        } else {
            $dest = $this->getContainer()->get('kernel')->getRootDir().'/../src';
        }


        $configuration = array(
            'application_dir' => sprintf("%s/Application", $dest)
        );

        $bundleName = $input->getArgument('bundle');

        if ($bundleName == false) {
            $output->writeln('');
            $output->writeln('<error>You must provide a bundle name!</error>');
            $output->writeln('');
            $output->writeln('  Bundles availables :');
            foreach ($this->getContainer()->get('kernel')->getBundles() as $bundle) {
                $bundleMetadata = new BundleMetadata($bundle, $configuration);

                if (!$bundleMetadata->isExtendable()) {
                    continue;
                }

                $output->writeln(sprintf('     - %s', $bundle->getName()));
            }

            $output->writeln('');

            return 0;
        }

        $processed = false;
        foreach ($this->getContainer()->get('kernel')->getBundles() as $bundle) {

            if ($bundle->getName() != $bundleName) {
                continue;
            }

            $processed = true;
            $bundleMetadata = new BundleMetadata($bundle, $configuration);

            // generate the bundle file
            if (!$bundleMetadata->isExtendable()) {
                $output->writeln(sprintf('Ignoring bundle : "<comment>%s</comment>"', $bundleMetadata->getClass()));
                continue;
            }

            // generate the bundle file
            if (!$bundleMetadata->isValid()) {
                $output->writeln(
                    sprintf('%s : <comment>wrong folder structure</comment>', $bundleMetadata->getClass())
                );
                continue;
            }

            $output->writeln(sprintf('Processing bundle : "<info>%s</info>"', $bundleMetadata->getName()));

            $this->getContainer()->get('sonata.easy_extends.generator.bundle')
                ->generate($output, $bundleMetadata);

            $output->writeln(sprintf('Processing Doctrine ORM : "<info>%s</info>"', $bundleMetadata->getName()));
            $this->getContainer()->get('sonata.easy_extends.generator.orm')
                ->generate($output, $bundleMetadata);

            $output->writeln(sprintf('Processing Doctrine ODM : "<info>%s</info>"', $bundleMetadata->getName()));
            $this->getContainer()->get('sonata.easy_extends.generator.odm')
                ->generate($output, $bundleMetadata);

            $output->writeln(sprintf('Processing Config files : "<info>%s</info>"', $bundleMetadata->getName()));
            $this->generateConfigFiles($output, $bundleMetadata);

            $output->writeln('');
        }

        if ($processed) {
            $output->writeln('done!');

            return 0;
        }

        $output->writeln(
            sprintf('<error>The bundle \'%s\' does not exist or not defined in the kernel file!</error>', $bundleName)
        );

        return -1;
    }

    public function generateConfigFiles($output, BundleMetadata $bundleMetadata)
    {
        $output->writeln(' - Copy config files');

        $this->setConfigFolder($bundleMetadata);

        $files = $this->getConfigFiles();



        foreach ($files as $file) {
            // copy mapping definition
            $fileName = substr($file->getFileName(), 0, strrpos($file->getFileName(), '.'));

            $dest_file = sprintf(
                '%s/%s',
                $this->getExtendedConfigDirectory(),
                $fileName
            );
            $src_file = sprintf(
                '%s/%s.skeleton',
                $this->getMappingConfigDirectory(),
                $fileName
            );

            if (is_file($dest_file)) {
                $output->writeln(sprintf('   ~ <info>%s</info>', $fileName));
            } else {
                $output->writeln(sprintf('   + <info>%s</info>', $fileName));
                copy($src_file, $dest_file);
            }
        }
    }

    /**
     * @param $bundleMetadata
     */
    public function setConfigFolder(BundleMetadata $bundleMetadata){
        $this->mappingConfigDirectory           = sprintf('%s/Resources/config', $bundleMetadata->getBundle()->getPath());
        $this->extendedConfigDirectory   = sprintf('%s/Resources/config', $bundleMetadata->getExtendedDirectory());
    }

    /**
     * @return mixed
     */
    public function getMappingConfigDirectory()
    {
        return $this->mappingConfigDirectory;
    }

    /**
     * @return mixed
     */
    public function getExtendedConfigDirectory()
    {
        return $this->extendedConfigDirectory;
    }

    /**
     * @return array|\Iterator
     */
    public function getConfigFiles()
    {

        try {
            $f = new Finder;
            $f->name('*.xml.skeleton');
            $f->name('*.yml.skeleton');
            $f->notName('*.orm.xml.skeleton');
            $f->notName('*.orm.yml.skeleton');
            $f->in($this->getMappingConfigDirectory());

            return $f->getIterator();
        } catch (\Exception $e) {
            return array();
        }
    }
}
