<?php

namespace App\Serializer\Normalizer;

use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * BaseNormalizer.
 */
class BaseNormalizer
{
    protected $translator;
    protected $normalizer;

    /**
     * @param TranslatorInterface $translator
     * @param ObjectNormalizer    $normalizer
     */
    public function __construct(TranslatorInterface $translator, ObjectNormalizer $normalizer)
    {
        $this->translator = $translator;
        $this->normalizer = $normalizer;
    }

    /**
     * @param float|int $price
     *
     * @return string|null
     */
    public function formatPrice(&$price): ?string
    {
        if (!isset($price)) {
            return null;
        }

        $decimal = $price > (int) $price ? 2 : 0;

        $formatted = \number_format($price, $decimal, ',', ' ');

        return $this->translator->trans('payment.price', ['%summ%' => $formatted]);
    }
}
