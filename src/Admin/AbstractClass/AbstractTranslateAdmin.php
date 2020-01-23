<?php

namespace App\Admin\AbstractClass;

use App\Entity\AbstractClass\AbstractTranslation;
use App\Model\Translatable\TranslatableInterface;
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
     * @param TranslatableInterface $object
     */
    public function removeNullTranslate(TranslatableInterface $object): void
    {
        /** @var AbstractTranslation $translation */
        foreach ($object->getTranslations() as $translation) {
            if (!$translation->getContent()) {
                $object->getTranslations()->removeElement($translation);
            }
        }
    }
}
