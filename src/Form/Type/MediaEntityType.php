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

use Networking\InitCmsBundle\Form\DataTransformer\ModelToIdTransformer;
use Sonata\AdminBundle\Admin\Pool;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class MediaEntityType.
 *
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
class MediaEntityType extends AbstractType
{
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

    /**
     * @param Pool $pool
     */
    public function __construct(Pool $pool)
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
                $options['class'] = $admin->getClass();
            } else {
                $admin = $pool->getAdminByClass($options['class']);
            }

            $options['model_manager'] = $admin->getModelManager();
        }

        $builder
            ->addViewTransformer(new ModelToIdTransformer($options['model_manager'], $options['class']), true);
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
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'model_manager' => null,
                'class' => null,
                'required' => false,
                'provider_name' => false,
                'context' => false,
                'admin_code' => 'sonata.media.admin.media',
                'error_bubbling' => false,
                'compound' => false,
                'data_class' => null

            ]
        );

        $resolver->setRequired(['class']);
        $resolver->addAllowedValues('required', [false, true]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix(): string
    {
        return 'media_entity_type';
    }
}
