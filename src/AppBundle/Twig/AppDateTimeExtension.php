<?php

namespace App\Twig;

use App\Entity\Event;
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
            new TwigFilter('app_format_date_day_month', [$this, 'formatDateDayMonth'], ['is_safe' => ['html']]),
            new TwigFilter('app_event_date', [$this, 'eventDate'], ['is_safe' => ['html']]),
        ];
    }

    /**
     * @param Event       $event
     * @param string|null $locale
     * @param bool        $withTime
     * @param string|null $pattern
     *
     * @return string
     */
    public function eventDate(Event $event, ?string $locale = null, bool $withTime = true, ?string $pattern = null): string
    {
        $pattern = $pattern ?: $event->getDateFormat();

        $timeString = '';
        if (false !== \strpos($event->getDateFormat(), 'H')) {
            $timeStart = $this->formatTimeOnly($event->getDate(), $pattern, $locale, 'Europe/Kiev');
            $timeEnd = $this->formatTimeOnly($event->getEndDateFromDates(), $pattern, $locale, 'Europe/Kiev');
            $timeString = \sprintf(',<br> %s–%s', $timeStart, $timeEnd);
        }

        if ($event->isStartAndEndDateSameByFormat('Y-m-d')) {
            $dateString = $this->formatDateOnly($event->getDate(), $pattern, $locale, 'Europe/Kiev');
        } elseif ($event->isStartAndEndDateSameByFormat('Y-m')) {
            $dayStart = $event->getDate()->format('d');
            $dateEnd = $this->formatDateOnly($event->getEndDateFromDates(), $pattern, $locale, 'Europe/Kiev');
            $dateString = \sprintf('%s & %s', $dayStart, $dateEnd);
        } else {
            $dayStart = $this->formatDateOnly($event->getDate(), $pattern, $locale, 'Europe/Kiev');
            $dateEnd = $this->formatDateOnly($event->getEndDateFromDates(), $pattern, $locale, 'Europe/Kiev');
            $dateString = \sprintf('%s,<br> %s', $dayStart, $dateEnd);
        }

        if ($withTime && '' !== $timeString) {
            $dateString = \sprintf('%s%s', $dateString, $timeString);
        }

        return $dateString;
    }

    /**
     * @param Event $event
     * @param null  $locale
     *
     * @return string
     */
    public function formatDateDayMonth(Event $event, $locale = null): string
    {
        $pattern = trim(preg_replace('/[Hm:,Y]+/', '', $event->getDateFormat()));

        return $this->eventDate($event, $locale, false, $pattern);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'app_datetime';
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
    private function formatDateOnly($date, $pattern = null, $locale = null, $timezone = null, $dateType = null)
    {
        if (null !== $pattern) {
            $pattern = trim(preg_replace('/[Hm:,]+/', '', $pattern));
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
    private function formatTimeOnly($time, $pattern = null, $locale = null, $timezone = null, $timeType = null)
    {
        if (null !== $pattern) {
            $pattern = 'HH:mm';
        }

        return $this->intlTwigDateTimeService->formatTime($time, $pattern, $locale, $timezone, $timeType);
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

    /**
     * @param \Datetime|string|int $date
     * @param string|null          $pattern
     * @param string|null          $locale
     * @param string|null          $timezone
     * @param string|null          $dateType
     *
     * @return string
     */
    private function formatDate($date, $pattern = null, $locale = null, $timezone = null, $dateType = null)
    {
        $pattern = $this->checkConvertToSeason($pattern);

        $formattedDate = $this->intlTwigDateTimeService->formatDate($date, $pattern, $locale, $timezone, $dateType);
        if (null !== $pattern && ('uk' === $locale || $this->convertToSeason)) {
            $formattedDate = $this->replaceMonthToNominative($formattedDate, $pattern, $locale);
        }

        return $formattedDate;
    }
}
