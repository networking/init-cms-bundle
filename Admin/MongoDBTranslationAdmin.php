<?php
/**
 * This file is part of the init_cms_sandbox package.
 *
 * (c) net working AG <info@networking.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace Networking\InitCmsBundle\Admin;

use Ibrows\SonataTranslationBundle\Admin\TranslationAdmin as BaseTranslationAdmin;

/**
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
class MongoDBTranslationAdmin extends BaseTranslationAdmin
{

    protected function configureDatagridFilters(DatagridMapper $filter)
    {
        $filter
            ->add('key', 'doctrine_mongodb_string')
            ->add('domain', 'doctrine_mongodb_string');
    }

}
