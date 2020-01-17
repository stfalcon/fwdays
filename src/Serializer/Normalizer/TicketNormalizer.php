<?php

namespace App\Serializer\Normalizer;

use App\Entity\Ticket;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

/**
 * TicketNormalizer.
 */
class TicketNormalizer extends BaseNormalizer implements NormalizerInterface
{
    private $appConfig;

    /**
     * @param ObjectNormalizer $normalizer
     * @param array            $appConfig
     */
    public function __construct(ObjectNormalizer $normalizer, array $appConfig)
    {
        parent::__construct($normalizer);
        $this->appConfig = $appConfig;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = [])
    {
        if (!$object instanceof Ticket) {
            return $object;
        }

        $data = $this->normalizer->normalize($object, $format, $context);

        if (\is_array($data)) {
            $discountAmount = 100 * (float) $this->appConfig['discount'];
            $data['amount'] = $this->formatPrice($data['amount']);
            $data['amount_without_discount'] = $this->formatPrice($data['amount_without_discount']);
            $data['discount_description'] = '';
            if ($data['has_discount']) {
                if (isset($data['promo_code']) && $data['promo_code']) {
                    $data['discount_description'] = $this->translator->trans('payment.discount.cupon', ['%summ%' => $data['promo_code']['discount_amount']]);
                } else {
                    $data['discount_description'] = $this->translator->trans('payment.discount.member', ['%summ%' => $discountAmount]);
                }
            }
        }

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof Ticket;
    }
}
