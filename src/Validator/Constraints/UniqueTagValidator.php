<?php
/**
 * This file is part of the demo_cms  package.
 *
 * (c) net working AG <info@networking.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Networking\InitCmsBundle\Validator\Constraints;

use Gedmo\Sluggable\Util\Urlizer;
use Networking\InitCmsBundle\Admin\Model\TagAdmin;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class UniqueTagValidator extends ConstraintValidator
{

    /**
     * @var TagAdmin
     */
    protected $tagAdmin;

    public function setTagAdmin(TagAdmin $tagAdmin)
    {
        $this->tagAdmin = $tagAdmin;
    }

        /**
     * {@inheritDoc}
     */
    public function validate($value, Constraint $constraint)
    {

        $tags = $this->tagAdmin
            ->getModelManager()
            ->findBy(
                $this->tagAdmin->getClass(),
                ['name' => $value->getName(), 'parent' => $value->getParent()
                ]);


        if (count($tags) > 0) {
            foreach ($tags as $tag) {
                if ($tag->getId() != $value->getId()) {
                    $this->context->addViolationAt('name', $constraint->message, ['{{ value }}' => $value->getName()]);
                    return false;
                }
            }
        }
        return true;

    }
}