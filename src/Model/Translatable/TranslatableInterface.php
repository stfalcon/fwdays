<?php

declare(strict_types=1);

namespace App\Model\Translatable;

use Doctrine\Common\Collections\ArrayCollection;
use Gedmo\Translatable\Translatable;

/**
 * TranslatableInterface.
 */
interface TranslatableInterface extends Translatable
{
    /** @return ArrayCollection */
    public function getTranslations();
}
