<?php

namespace Networking\InitCmsBundle\Entity;

use Doctrine\ORM\Mapping as ORM,
    Networking\InitCmsBundle\Entity\LayoutBlock,
    Networking\InitCmsBundle\Entity\ContentInterface;


/**
 * Networking\InitCmsBundle\Entity\Content
 *
 * @ORM\HasLifecycleCallbacks()
 * @ORM\Table(name="text")
 * @ORM\Entity(repositoryClass="Networking\InitCmsBundle\Entity\TextRepository")
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
     * @static
     * @return array
     */
    public static function getFieldDefinition()
    {
        $fields = array(
            array(
                'name' => 'text',
                'type' => 'textarea',
                'options' => array(
                    'required' => false,
                    'label' => 'Text',
                    'property_path' => false,
                    'attr' => array(
                        'class' => 'tinymce',
                        'style' => 'width: 620px; height: 200px;'
                    )
                )
            ),
        );

        return $fields;
    }

    /**
     * @return array
     */
    public function getTemplateOptions()
    {
        return array('text' => $this->getText());
    }

    public function getAdminContent()
    {
        return array(
            'content' => array('text' => $this),
            'template'  => 'NetworkingInitCmsBundle:Text:admin_text_block.html.twig'
        );
    }

}
