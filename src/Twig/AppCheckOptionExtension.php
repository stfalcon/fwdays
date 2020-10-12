<?php

namespace App\Twig;

use App\Entity\Option;
use App\Repository\OptionRepository;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

/**
 * AppCheckOptionExtension.
 */
class AppCheckOptionExtension extends AbstractExtension
{
    /** @var OptionRepository */
    private $optionRepository;

    /**
     * @param OptionRepository $optionRepository
     */
    public function __construct(OptionRepository $optionRepository)
    {
        $this->optionRepository = $optionRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function getFilters()
    {
        return [
            new TwigFilter('app_is_option', [$this, 'isOption']),
            new TwigFilter('app_get_option', [$this, 'getOption']),
        ];
    }

    /**
     * @param string $optionKey
     *
     * @return bool
     */
    public function isOption(string $optionKey): bool
    {
        $option = $this->optionRepository->findOneBy(['key' => $optionKey]);
        $result = true;

        if ($option instanceof Option) {
            $result = $option->getTypedValue();
        }

        return $result;
    }

    /**
     * @param string $optionKey
     *
     * @return float|int|mixed|string|null
     */
    public function getOption(string $optionKey)
    {
        $option = $this->optionRepository->findOneBy(['key' => $optionKey]);
        $result = null;

        if ($option instanceof Option) {
            $result = $option->getTypedValue();
        }

        return $result;
    }
}
