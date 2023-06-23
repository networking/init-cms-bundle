<?php

declare(strict_types=1);

/**
 * This file is part of the Networking package.
 *
 * (c) net working AG <info@networking.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Networking\InitCmsBundle\Model;

use Networking\InitCmsBundle\Annotation\FormCallback;

/**
 * Class Text.
 *
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
abstract class Text implements ContentInterface, TextInterface
{
    /**
     * @var int
     */
    protected $id;


    protected $text;

    /**
     * @var \DateTime
     */
    protected $createdAt;

    /**
     * @var \DateTime
     */
    protected $updatedAt;

    #[FormCallback]
    public static function buildForm(\Symfony\Component\Form\FormBuilder $formBuilder){
        $formBuilder
            ->add('text',
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
     * Get createdAt.
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set updatedAt.
     *
     * @param \DateTime $updatedAt
     *
     * @return $this
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * Get updatedAt.
     *
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
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
