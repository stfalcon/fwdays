<?php

namespace Application\Bundle\DefaultBundle\Serializer\Normalizer;

use Application\Bundle\DefaultBundle\Entity\Payment;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * PaymentNormalizer.
 */
class PaymentNormalizer implements NormalizerInterface
{
    private $translator;
    private $normalizer;

    /**
     * @param TranslatorInterface $translator
     * @param ObjectNormalizer    $normalizer
     * @param array               $params
     */
    public function __construct(TranslatorInterface $translator, ObjectNormalizer $normalizer, array $params)
    {
        $this->translator = $translator;
        $this->normalizer = $normalizer;
    }

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
        $data['amount'] = $this->formatPrice($data['amount']);
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
