<?php

declare(strict_types=1);

namespace App\Traits;

use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * TranslatorTrait.
 */
trait TranslatorTrait
{
    /** @var TranslatorInterface */
    protected $translator;

    /**
     * @param TranslatorInterface $translator
     *
     * @required
     */
    public function setTranslator(TranslatorInterface $translator): void
    {
        $this->translator = $translator;
    }
}
