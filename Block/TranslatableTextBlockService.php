<?php
/*
 * This file is part of the Networking package.
 *
 * (c) net working AG <info@networking.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Networking\InitCmsBundle\Block;

use Sonata\BlockBundle\Block\BaseBlockService,
    Sonata\BlockBundle\Model\BlockInterface,
    Sonata\AdminBundle\Form\FormMapper,
    Sonata\AdminBundle\Validator\ErrorElement,
    Symfony\Component\HttpFoundation\Response;

class TranslatableTextBlockService extends BaseBlockService
{

    /**
     * {@inheritdoc}
     */
    public function execute(BlockInterface $block, Response $response = null)
    {
        $settings = array_merge($this->getDefaultSettings(), $block->getSettings());

        return $this->renderResponse(
            'NetworkingInitCmsBundle:Block:block_translatable_text.html.twig',
            array(
                'block' => $block,
                'settings' => $settings
            ),
            $response
        );
    }

    /**
     * {@inheritdoc}
     */
    public function validateBlock(ErrorElement $errorElement, BlockInterface $block)
    {
        // TODO: Implement validateBlock() method.
    }

    /**
     * {@inheritdoc}
     */
    public function buildEditForm(FormMapper $formMapper, BlockInterface $block)
    {
        $formMapper->add(
            'settings',
            'sonata_type_immutable_array',
            array(
                'keys' => array(
                    array('translation_key', 'text', array()),
                    array('translation_domain', 'text', array()),
                )
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'Translatable Text Block';
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultSettings()
    {
        return array(
            'translation_key' => 'Insert your translation key',
            'translation_domain' => 'Insert the name of the translation domain',
        );
    }
}