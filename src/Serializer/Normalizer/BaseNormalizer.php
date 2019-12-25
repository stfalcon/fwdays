<?php

namespace App\Serializer\Normalizer;

use App\Traits\TranslatorTrait;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

/**
 * BaseNormalizer.
 */
class BaseNormalizer
{
    use TranslatorTrait;

    protected $normalizer;

    /**
     * @param ObjectNormalizer $normalizer
     */
    public function __construct(ObjectNormalizer $normalizer)
    {
        $this->normalizer = $normalizer;
    }

    /**
     * @param float|int $price
     *
     * @return string|null
     */
    public function formatPrice(&$price): ?string
    {
        $decimal = $price > (int) $price ? 2 : 0;

        $formatted = \number_format($price, $decimal, ',', ' ');

        return $this->translator->trans('payment.price', ['%summ%' => $formatted]);
    }
}
