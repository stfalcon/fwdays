<?php

namespace App\Serializer\Normalizer;

use App\Entity\Ticket;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * TicketNormalizer.
 */
class TicketNormalizer extends BaseNormalizer implements NormalizerInterface
{
    private $params;

    /**
     * @param TranslatorInterface $translator
     * @param ObjectNormalizer    $normalizer
     * @param array               $params
     */
    public function __construct(TranslatorInterface $translator, ObjectNormalizer $normalizer, array $params)
    {
        parent::__construct($translator, $normalizer);
        $this->params = $params;
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

        $discountAmount = 100 * (float) $this->params['discount'];
        $data['amount'] = $this->formatPrice($data['amount']);
        $data['amount_without_discount'] = $this->formatPrice($data['amount_without_discount']);
        $data['discount_description'] = '';
        if ($data['has_discount']) {
            if ($data['promo_code']) {
                $data['discount_description'] = $this->translator->trans('payment.discount.cupon', ['%summ%' => $data['promo_code']['discount_amount']]);
            } else {
                $data['discount_description'] = $this->translator->trans('payment.discount.member', ['%summ%' => $discountAmount]);
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
