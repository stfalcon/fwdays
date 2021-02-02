<?php

declare(strict_types=1);

namespace App\Service\Discount;

use App\Entity\Option;
use App\Repository\OptionRepository;

/**
 * DiscountService.
 */
class DiscountService
{
    /** @var OptionRepository */
    private $optionRepository;
    private $appConfig;

    /**
     * @param OptionRepository $optionRepository
     * @param array            $appConfig
     */
    public function __construct(OptionRepository $optionRepository, array $appConfig)
    {
        $this->optionRepository = $optionRepository;
        $this->appConfig = $appConfig;
    }

    /**
     * @return float
     */
    public function getFloatDiscount(): float
    {
        $option = $this->optionRepository->findOneBy(['key' => 'REGULAR_PARTICIPANT_DISCOUNT']);
        if ($option instanceof Option) {
            try {
                return $option->getTypedValue() / 100;
            } catch (\Throwable $e) {
            }
        }

        return $this->appConfig['discount'];
    }
}
