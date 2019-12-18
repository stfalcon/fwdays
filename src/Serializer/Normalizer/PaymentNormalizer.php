<?php

namespace App\Serializer\Normalizer;

use App\Entity\Payment;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * PaymentNormalizer.
 */
class PaymentNormalizer extends BaseNormalizer implements NormalizerInterface
{
    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = [])
    {
        if (!$object instanceof Payment) {
            return $object;
        }

        $data = $this->normalizer->normalize($object, $format, $context);

        if ($data['user']['balance'] > 0 && $data['user']['balance'] > $data['amount']) {
            $data['user']['balance'] = $data['amount'];
        }

        $data['base_amount'] = $data['amount'] + $data['fwdays_amount'];
        $data['amount_formatted'] = $this->formatPrice($data['amount']);
        $data['base_amount'] = $this->formatPrice($data['base_amount']);
        $data['fwdays_amount_formatted'] = $this->translator->trans('pay.bonus.title', ['%sum%' => $this->formatPrice($data['fwdays_amount'])]);

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof Payment;
    }
}
