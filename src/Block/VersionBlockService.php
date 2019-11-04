<?php
/**
 * This file is part of the Networking package.
 *
 * (c) net working AG <info@networking.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Networking\InitCmsBundle\Block;

use Networking\InitCmsBundle\NetworkingInitCmsBundle;
use Sonata\BlockBundle\Block\Service\AbstractAdminBlockService;
use Sonata\BlockBundle\Model\BlockInterface;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\CoreBundle\Validator\ErrorElement;
use Symfony\Component\HttpFoundation\Response;
use Sonata\BlockBundle\Block\BlockContextInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;

/**
 * Class OnlineUsersBlockService.
 *
 * @author info@networking.ch
 */
class VersionBlockService extends AbstractAdminBlockService
{
	/**
	 * @var string
	 */
	protected $projectDir;

	/**
	 * @param string          $name
	 * @param EngineInterface $templating
	 */
	public function __construct($name, EngineInterface $templating, $projectDir)
	{
		parent::__construct($name, $templating);

		$this->projectDir = $projectDir;
	}

    /**
     * {@inheritdoc}
     */
    public function execute(BlockContextInterface $blockContext, Response $response = null)
    {
	    $content = file_get_contents($this->projectDir.'/composer.lock');
	    $content = json_decode($content,true);
	    foreach ($content['packages'] as $package){
	    	if('networking/init-cms-bundle' === $package['name']){
	    		$version = $package['version'];
		    }
	    }
        return $this->renderResponse(
            $blockContext->getTemplate(),
            [
                'block' => $blockContext->getBlock(),
	            'version' => $version
            ],
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
        // TODO: Implement buildEditForm() method.
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'Version Block';
    }

    /**
     * {@inheritdoc}
     */
    public function configureSettings(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'template' => '@NetworkingInitCms/Block/block_version.html.twig',
            ]
        );
    }
}
