<?php
/**
 * This file is part of the Networking package.
 *
 * (c) net working AG <info@networking.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare(strict_types=1);

namespace Networking\InitCmsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Networking\InitCmsBundle\Annotation\FormCallback;
use Networking\InitCmsBundle\Model\TextInterface;
use Sonata\AdminBundle\Form\FormMapper;

/**
 * Class BaseText.
 *
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
abstract class BaseText extends LayoutBlock implements TextInterface
{

    #[ORM\Column(type: 'text', nullable: true)]
    protected $text;

    #[FormCallback]
    public static function buildForm(FormMapper $formBuilder)
    {
        $formBuilder
            ->add(
                'text',
                \FOS\CKEditorBundle\Form\Type\CKEditorType::class,
                [
                    'label' => false,
                    'required' => false,
                ]
            );
    }

    /**
     * clone action, set id null.
     */
    public function __clone(): void
    {
        $this->id = null;
    }

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set content.
     *
     * @param string $text
     *
     * @return $this
     */
    public function setText($text)
    {
        $this->text = $text;

        return $this;
    }

    /**
     * Get content.
     *
     * @return string
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * Set createdAt.
     *
     * @return $this
     */
    public function setCreatedAt()
    {
        $this->createdAt = new \DateTime();

        return $this;
    }

    /**
     * @return string
     */
    public function getSearchableContent()
    {
        if(!$this->getText()){
            return '';
        }
        return strip_tags($this->getText());
    }

    /**
     * @return array
     */
    public function getAdminContent(): array
    {
        return [];
    }

    /**
     * @param array $params
     *
     * @return array
     */
    public function getTemplateOptions($params = []): array
    {
        return [];
    }

    public function getTemplate() : ?string{
        return '@NetworkingInitCms/Text/frontend_text_block.html.twig';
    }

    /**
     * @return string
     */
    public function getContentTypeName()
    {
        return 'Text Block';
    }
}
