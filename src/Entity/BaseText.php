<?php
/**
 * This file is part of the Networking package.
 *
 * (c) net working AG <info@networking.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Networking\InitCmsBundle\Entity;

use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Networking\InitCmsBundle\Annotation as Sonata;
use Networking\InitCmsBundle\Model\ContentInterface;
use Networking\InitCmsBundle\Model\TextInterface;
use Sonata\AdminBundle\Form\FormMapper;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Class BaseText.
 *
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
abstract class BaseText extends LayoutBlock implements ContentInterface, TextInterface
{

    /**
     * @var int
     */
    protected $id;

    /**
     * @var string

     */
    protected $text;

    /**
     * @Sonata\FormCallback
     */
    public static function buildForm(FormMapper $form){
        $form->add(
            'text',
            CKEditorType::class,
               [
                   "label_render" => false,
                   "horizontal_input_wrapper_class" => "col-md-12",
                   "horizontal_label_offset_class" => "",
                   "layout" => "horizontal",
                   "label" => false,
                   "required"=>true,
                   "constraints" => new NotBlank()
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
     * @return \Networking\InitCmsBundle\Model\Text
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
