<?php
/**
 * This file is part of the init_cms_sandbox package.
 *
 * (c) net working AG <info@networking.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
 

namespace Networking\InitCmsBundle\Model;

use Symfony\Component\Form\FormEvent;


/**
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
interface LayoutBlockFormListenerInterface {


    /**
     * Bind the content type objects variables from the form.
     * If needed create an new content type object, or change to a new type deleting the old one.
     * Set the Content objects contentType and objectId fields accordingly.
     *
     * @param \Symfony\Component\Form\FormEvent $event
     */
    public function postBindData(FormEvent $event);


    /**
     * Adds the form fields for the content object to the layoutBlock form
     *
     * @param  \Symfony\Component\Form\FormEvent $event
     * @throws \RuntimeException
     */
    public function preSetData(FormEvent $event);
}
 