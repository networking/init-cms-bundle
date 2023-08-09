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
use Networking\InitCmsBundle\Model\ContentInterface;
use Networking\InitCmsBundle\Model\TextInterface;
use Sonata\BlockBundle\Form\Mapper\FormMapper;

/**
 * Class BaseText.
 *
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
abstract class BaseText extends LayoutBlock implements ContentInterface, TextInterface
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
                    'horizontal_input_wrapper_class' => 'col-md-12',
                    'horizontal_label_offset_class' => '',
                    'label' => false,
                    'label_render' => false,
                    'required' => false,
                ]
            );
    }

    /**
     * clone action, set id null.
     */
    public function __clone()
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
     * @param array $params
     *
     * @return array
     */
    public function getTemplateOptions($params = [])
    {
        return ['text' => $this->getText()];
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
    public function getAdminContent()
    {
        return [
            'content' => ['text' => $this],
            'template' => '@NetworkingInitCms/Text/admin_text_block.html.twig',
        ];
    }

    /**
     * @return string
     */
    public function getContentTypeName()
    {
        return 'Text Block';
    }
}
