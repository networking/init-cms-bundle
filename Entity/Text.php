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

use Doctrine\ORM\Mapping as ORM,
    Networking\InitCmsBundle\Entity\LayoutBlock,
    Networking\InitCmsBundle\Entity\ContentInterface,
    Ibrows\Bundle\SonataAdminAnnotationBundle\Annotation as Sonata;


/**
 * Networking\InitCmsBundle\Entity\Content
 *
 * @ORM\HasLifecycleCallbacks()
 * @ORM\Table(name="text")
 * @ORM\Entity(repositoryClass="Networking\InitCmsBundle\Entity\TextRepository")
 *
 *  @author net working AG <info@networking.ch>
 */
class Text implements ContentInterface
{
    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var integer $content
     *
     * @ORM\OneToOne(targetEntity="LayoutBlock", cascade={"persist"})
     * @ORM\JoinColumn(name="layout_block_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $layoutBlock;


    /**
     * @var text $content
     * @ORM\Column(name="text", type="text", nullable=true)
     * @Sonata\FormMapper(name="text", type="textarea", options={"label_render" = false, "required"=false, "property_path" = false, "attr"={"class"="wysiwyg-editor"}}, fieldDescriptionOptions={"inline_block" = true})
     */
    protected $text;

    /**
     * @var \DateTime $createdAt
     * @ORM\Column(name="created_at", type="datetime")
     */
    protected $createdAt;

    /**
     * @var \DateTime $updatedAt
     * @ORM\Column(name="updated_at", type="datetime")
     */
    protected $updatedAt;

    public function __clone(){
        $this->id = null;
        $this->layoutBlock = null;
    }

    /**
     * @ORM\PrePersist
     */
    public function onPrePersist()
    {

        $this->createdAt = $this->updatedAt = new \DateTime("now");
    }

    /**
     * Hook on pre-update operations
     * @ORM\PreUpdate
     */
    public function onPreUpdate()
    {
        $this->updatedAt = new \DateTime('now');
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param  LayoutBlock $layoutBlock
     * @return Text
     */
    public function setLayoutBlock(LayoutBlock $layoutBlock)
    {
        $this->layoutBlock = $layoutBlock;

        return $this;
    }

    /**
     * @return ContentRoute
     */
    public function getLayoutBlock()
    {
        return $this->layoutBlock;
    }

    /**
     * Set content
     *
     * @param  text $text
     * @return Text
     */
    public function setText($text)
    {
        $this->text = $text;

        return $this;
    }

    /**
     * Get content
     *
     * @return text
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * Set createdAt
     *
     * @return LayoutBlock
     */
    public function setCreatedAt()
    {
        $this->createdAt = new \DateTime();

        return $this;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set updatedAt
     *
     * @param  \DateTime   $updatedAt
     * @return LayoutBlock
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * Get updatedAt
     *
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }


    /**
     * @param array $params
     * @return array
     */
    public function getTemplateOptions($params = array())
    {
        return array('text' => $this->getText());
    }

    /**
     * @return array
     */
    public function getAdminContent()
    {
        return array(
            'content' => array('text' => $this),
            'template'  => 'NetworkingInitCmsBundle:Text:admin_text_block.html.twig'
        );
    }

    /**
     * @return string
     */
    public function getContentTypeName()
    {
        return 'Text Block';
    }

}
