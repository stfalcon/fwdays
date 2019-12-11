<?php

namespace Application\Bundle\DefaultBundle\Twig;

use Sonata\IntlBundle\Twig\Extension\DateTimeExtension;
use Symfony\Component\Translation\TranslatorInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

/**
 * Class AppDateTimeExtension for replace months name to nominative or season.
 */
class AppDateTimeExtension extends AbstractExtension
{
    const YEAR_SEASON_FORMAT = 'S';

    private $intlTwigDateTimeService;
    private $convertToSeason = false;
    private $translator;

    /**
     * AppDateTimeExtension constructor.
     *
     * @param DateTimeExtension   $intlTwigDateTimeService
     * @param TranslatorInterface $translator
     */
    public function __construct(DateTimeExtension $intlTwigDateTimeService, TranslatorInterface $translator)
    {
        $this->intlTwigDateTimeService = $intlTwigDateTimeService;
        $this->translator = $translator;
    }

    /**
     * {@inheritdoc}
     */
    public function getFilters()
    {
        return [
            new TwigFilter('app_format_date', [$this, 'formatDate'], ['is_safe' => ['html']]),
            new TwigFilter('app_format_date_day_month', [$this, 'formatDateDayMonth'], ['is_safe' => ['html']]),
            new TwigFilter('app_format_date_only', [$this, 'formatDateOnly'], ['is_safe' => ['html']]),
            new TwigFilter('app_format_time', [$this, 'formatTime'], ['is_safe' => ['html']]),
            new TwigFilter('app_format_time_only', [$this, 'formatTimeOnly'], ['is_safe' => ['html']]),
            new TwigFilter('app_format_datetime', [$this, 'formatDatetime'], ['is_safe' => ['html']]),
        ];
    }

    /**
     * @param \Datetime|string|int $date
     * @param string|null          $pattern
     * @param string|null          $locale
     * @param string|null          $timezone
     * @param string|null          $dateType
     *
     * @return string
     */
    public function formatDate($date, $pattern = null, $locale = null, $timezone = null, $dateType = null)
    {
        $pattern = $this->checkConvertToSeason($pattern);

        $formattedDate = $this->intlTwigDateTimeService->formatDate($date, $pattern, $locale, $timezone, $dateType);
        if (null !== $pattern && ('uk' === $locale || $this->convertToSeason)) {
            $formattedDate = $this->replaceMonthToNominative($formattedDate, $pattern, $locale);
        }

        return $formattedDate;
    }

    /**
     * @param \Datetime|string|int $date
     * @param string|null          $pattern
     * @param string|null          $locale
     * @param string|null          $timezone
     * @param string|null          $dateType
     *
     * @return string
     */
    public function formatDateOnly($date, $pattern = null, $locale = null, $timezone = null, $dateType = null)
    {
        if (null !== $pattern) {
            $pattern = trim(preg_replace('/[Hm:,]+/', '', $pattern));
        }

        return $this->formatDate($date, $pattern, $locale, $timezone, $dateType);
    }

    /**
     * @param \Datetime|string|int $date
     * @param string|null          $pattern
     * @param string|null          $locale
     * @param string|null          $timezone
     * @param string|null          $dateType
     *
     * @return string
     */
    public function formatDateDayMonth($date, $pattern = null, $locale = null, $timezone = null, $dateType = null)
    {
        if (null !== $pattern) {
            $pattern = trim(preg_replace('/[Hm:,Y]+/', '', $pattern));
        }

        return $this->formatDate($date, $pattern, $locale, $timezone, $dateType);
    }

    /**
     * @param \Datetime|string|int $time
     * @param string|null          $pattern
     * @param string|null          $locale
     * @param string|null          $timezone
     * @param string|null          $timeType
     *
     * @return string
     */
    public function formatTime($time, $pattern = null, $locale = null, $timezone = null, $timeType = null)
    {
        $pattern = $this->checkConvertToSeason($pattern);

        $formattedDate = $this->intlTwigDateTimeService->formatTime($time, $pattern, $locale, $timezone, $timeType);
        if (null !== $pattern && ('uk' === $locale || $this->convertToSeason)) {
            $formattedDate = $this->replaceMonthToNominative($formattedDate, $pattern, $locale);
        }

        return $formattedDate;
    }

    /**
     * @param \Datetime|string|int $time
     * @param string|null          $pattern
     * @param string|null          $locale
     * @param string|null          $timezone
     * @param string|null          $timeType
     *
     * @return string
     */
    public function formatTimeOnly($time, $pattern = null, $locale = null, $timezone = null, $timeType = null)
    {
        if (null !== $pattern) {
            $pattern = 'HH:mm';
        }

        return $this->intlTwigDateTimeService->formatTime($time, $pattern, $locale, $timezone, $timeType);
    }

    /**
     * @param \Datetime|string|int $time
     * @param string|null          $pattern
     * @param string|null          $locale
     * @param string|null          $timezone
     * @param string|null          $dateType
     * @param string|null          $timeType
     *
     * @return string
     */
    public function formatDatetime($time, $pattern = null, $locale = null, $timezone = null, $dateType = null, $timeType = null)
    {
        $pattern = $this->checkConvertToSeason($pattern);

        $formattedDate = $this->intlTwigDateTimeService->formatDatetime($time, $pattern, $locale, $timezone, $dateType, $timeType);
        if (null !== $pattern && ('uk' === $locale || $this->convertToSeason)) {
            $formattedDate = $this->replaceMonthToNominative($formattedDate, $pattern, $locale);
        }

        return $formattedDate;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'app_datetime';
    }

    /**
     * if $pattern have not day, than do Nominative month name in locale,
     * or, if it must be season, than replace month name with season name.
     *
     * @param string $formattedDate
     * @param string $pattern
     * @param string $locale
     *
     * @return mixed
     */
    private function replaceMonthToNominative($formattedDate, $pattern, $locale)
    {
        $result = $formattedDate;

        if (null !== $pattern &&
            false === strpos($pattern, 'd') &&
            false === strpos($pattern, 'j')
        ) {
            $words = \explode(' ', $formattedDate);
            foreach ($words as $key => $word) {
                $words[$key] = $this->translator->transChoice($word, (int) $this->convertToSeason + 1, [], 'year_season');
            }
            $result = \implode(' ', $words);
        }

        return $result;
    }

    /**
     * Check for YEAR_SEASON_FORMAT in $pattern and replace it for full month name.
     *
     * @param string|null $pattern
     *
     * @return mixed
     */
    private function checkConvertToSeason($pattern)
    {
        $this->convertToSeason = null !== $pattern ? (false !== strpos($pattern, self::YEAR_SEASON_FORMAT)) : false;
        if ($this->convertToSeason) {
            $pattern = str_replace(self::YEAR_SEASON_FORMAT, 'MMMM', $pattern);
        }

        return $pattern;
    }
}
