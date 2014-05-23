<?php
/**
 * This file is part of the Networking package.
 *
 * (c) net working AG <info@networking.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Networking\InitCmsBundle\Form\Type;

use Doctrine\ORM\EntityRepository;
use Networking\InitCmsBundle\Admin\Pool;
use Sonata\AdminBundle\Form\DataTransformer\ModelToIdTransformer;
use Sonata\AdminBundle\Form\Type\ModelHiddenType;
use Symfony\Bridge\Doctrine\Form\ChoiceList\EntityChoiceList;
use Symfony\Bridge\Doctrine\Form\ChoiceList\ORMQueryBuilderLoader;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * Class MediaEntityType
 * @package Networking\InitCmsBundle\Form\Type
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
class MediaEntityType extends ModelHiddenType
{
    /**
     * @var array
     */
    private $choiceListCache = array();

    /**
     * @var null
     */
    public $context = null;

    /**
     * @var null
     */
    public $providerName = null;

    /**
     * @var Pool
     */
    public $pool;

    public function setPool(Pool $pool)
    {
        $this->pool = $pool;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if ($options['model_manager'] === null) {
            $pool = $this->pool;
            $adminCode = $options['admin_code'];
            if ($adminCode !== null) {
                $admin = $pool->getAdminByAdminCode($adminCode);
            } else {
                $admin = $pool->getAdminByClass($options['class']);
            }

            $options['model_manager'] = $admin->getModelManager();
        }

        parent::buildForm($builder, $options);
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {

        $pool = $this->pool;

        $adminCode = $options['admin_code'];

        if ($adminCode !== null) {
            $admin = $pool->getAdminByAdminCode($adminCode);
        } else {
            $admin = $pool->getAdminByClass($options['class']);
        }

        $view->vars['admin'] = $admin;
        $view->vars['provider_name'] = $options['provider_name'];
        $view->vars['context'] = $options['context'];
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {

        parent::setDefaultOptions($resolver);

        $resolver->setDefaults(
            array(
                'required' => false,
                'provider_name' => false,
                'context' => false,
                'admin_code' => 'sonata.media.admin.media',
                'error_bubbling' => false,

            )
        );

        $resolver->setRequired(array('class'));


        $resolver->addAllowedValues(array('required' => array(false)));
    }


    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'media_entity_type';
    }
}