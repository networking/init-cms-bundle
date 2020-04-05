<?php


namespace Networking\InitCmsBundle\Entity;


use Networking\InitCmsBundle\Model\MenuItem as ModelMenuItem;

class BaseMenuItem extends ModelMenuItem
{
    /**
     * Hook on pre-persist operations.
     */
    public function prePersist()
    {
        if ($this->getParent()) {
            $this->setLocale();
        }
    }
}