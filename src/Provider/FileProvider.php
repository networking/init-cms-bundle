<?php

declare(strict_types=1);
/**
 * Created by PhpStorm.
 * User: yorkie
 * Date: 06.08.18
 * Time: 13:11.
 */

namespace Networking\InitCmsBundle\Provider;

use Sineflow\ClamAV\Exception\SocketException;
use Sineflow\ClamAV\Scanner;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\Form\Validator\ErrorElement;
use Sonata\MediaBundle\CDN\Server;
use Sonata\MediaBundle\Model\MediaInterface;
use Sonata\MediaBundle\Provider\FileProvider as BaseProvider;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\HttpFoundation\File\Exception\UploadException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileProvider extends BaseProvider
{
    protected ?Scanner $scanner = null;

    public function setScanner(Scanner $scanner): void
    {
        $this->scanner = $scanner;
    }

    protected function generateReferenceName(MediaInterface $media): string
    {
        return $media->getMetadataValue('filename');
    }

    public function validate(ErrorElement $errorElement, MediaInterface $media): void
    {
        parent::validate($errorElement, $media);

        $uoloadedFile = $media->getBinaryContent();

        if ($uoloadedFile instanceof UploadedFile && $this->scanner) {
            try {
                $scanResult = $this->scanner->scan(
                    $uoloadedFile->getPathname()
                );
                if (!$scanResult->isClean()) {
                    $errorElement->with('binaryContent')->addViolation(
                        'The file is infected with the %virus_name% virus.',
                        ['%virus_name%' => $scanResult->getVirusName()]
                    )->end();
                }
            } catch (SocketException $e) {
                @trigger_error(
                    $e->getMessage(),
                    \E_USER_WARNING
                );

                return;
            }
        }
    }

    protected function doTransform(MediaInterface $media): void
    {
        if ($media->getBinaryContent() instanceof UploadedFile && $this->scanner) {
            try {
                $scanResult = $this->scanner->scan(
                    $media->getBinaryContent()->getPathname()
                );
                if (!$scanResult->isClean()) {
                    throw new UploadException(sprintf('The file is infected with a virus'));
                }
            } catch (SocketException $e) {
                @trigger_error(
                    $e->getMessage(),
                    \E_USER_WARNING
                );
                // do nothing
            }
        }

        parent::doTransform($media);
    }

    public function buildEditForm(FormMapper $formMapper): void
    {
        $formMapper->add('name');
        $formMapper->add('authorName');
        $formMapper->add('description');
        $formMapper->add('copyright');
        $formMapper->add('binaryContent',
            FileType::class,
            ['label' => 'form.label_binary_content_new', 'required' => false]
        );
        $formMapper->add('enabled', null, ['required' => false], ['inline_block' => true]);

        if (!$this->getCdn() instanceof Server) {
            $formMapper->add(
                'cdnIsFlushable',
                null,
                ['required' => false],
                ['inline_block' => true]
            );
        }
    }
}
