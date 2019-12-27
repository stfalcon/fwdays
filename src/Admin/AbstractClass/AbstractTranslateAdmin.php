<?php

namespace App\Admin\AbstractClass;

use Sonata\AdminBundle\Admin\AbstractAdmin;

/**
 * AbstractTranslateAdmin.
 */
class AbstractTranslateAdmin extends AbstractAdmin
{
    /**
     * {@inheritdoc}
     */
    public function preUpdate($object): void
    {
        $this->removeNullTranslate($object);
    }

    /**
     * {@inheritdoc}
     */
    public function prePersist($object): void
    {
        $this->removeNullTranslate($object);
    }

    /**
     * @param $object
     */
    public function removeNullTranslate($object): void
    {
        foreach ($object->getTranslations() as $key => $translation) {
            if (!$translation->getContent()) {
                $object->getTranslations()->removeElement($translation);
            }
        }
    }
}
