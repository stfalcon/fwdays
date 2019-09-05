<?php

namespace Application\Bundle\DefaultBundle\Serializer\Normalizer;

use Application\Bundle\DefaultBundle\Entity\Ticket;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * TicketNormalizer.
 */
class TicketNormalizer implements NormalizerInterface
{
    private $translator;
    private $normalizer;
    private $params;

    /**
     * @param TranslatorInterface $translator
     * @param ObjectNormalizer    $normalizer
     * @param array               $params
     */
    public function __construct(TranslatorInterface $translator, ObjectNormalizer $normalizer, array $params)
    {
        $this->translator = $translator;
        $this->normalizer = $normalizer;
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

    /**
     * @param float|int $price
     *
     * @return string|null
     */
    private function formatPrice(&$price): ?string
    {
        if (!isset($price)) {
            return null;
        }

        $formatted = \number_format($price, 0, ',', ' ');
        $result = $this->translator->trans('payment.price', ['%summ%' => $formatted]);

        return $result;
    }
}
