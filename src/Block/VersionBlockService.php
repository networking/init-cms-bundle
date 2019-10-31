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

use Sonata\BlockBundle\Block\Service\AbstractBlockService;
use Symfony\Component\HttpFoundation\Response;
use Sonata\BlockBundle\Block\BlockContextInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Twig\Environment;

/**
 * Class OnlineUsersBlockService.
 *
 * @author info@networking.ch
 */
class VersionBlockService extends AbstractBlockService
{
	/**
	 * @var string
	 */
	protected $projectDir;

    /**
     * VersionBlockService constructor.
     * @param Environment $twig
     * @param $projectDir
     */
	public function __construct(Environment $twig, $projectDir)
	{
		$this->projectDir = $projectDir;
		parent::__construct($twig);

	}

    /**
     * {@inheritdoc}
     */
    public function execute(BlockContextInterface $blockContext, Response $response = null)
    {
        $version = 'master';

        if(file_exists($this->projectDir.'/composer.lock')){
            $content = file_get_contents($this->projectDir.'/composer.lock');
            $content = json_decode($content,true);
            foreach ($content['packages'] as $package){
                if('networking/init-cms-bundle' === $package['name']){
                    $version = $package['version'];
                }
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
