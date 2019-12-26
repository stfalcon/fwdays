<?php

declare(strict_types=1);

namespace App\Traits;
//@todo remove deprecated and transchoice
use Symfony\Bundle\FrameworkBundle\Translation\Translator;

/**
 * TranslatorTrait.
 */
trait TranslatorTrait
{
    /** @var Translator */
    protected $translator;

    /**
     * @param Translator $translator
     *
     * @required
     */
    public function setTranslator(Translator $translator): void
    {
        $this->translator = $translator;
    }
}
