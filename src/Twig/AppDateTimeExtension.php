<?php

namespace App\Twig;

use App\Entity\City;
use App\Entity\Event;
use App\Entity\TicketCost;
use App\Traits\TranslatorTrait;
use Sonata\IntlBundle\Twig\Extension\DateTimeExtension;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

/**
 * Class AppDateTimeExtension for replace months name to nominative or season.
 */
class AppDateTimeExtension extends AbstractExtension
{
    use TranslatorTrait;

    const YEAR_SEASON_FORMAT = 'S';

    private $intlTwigDateTimeService;

    /** @var bool */
    private $convertToSeason = false;

    /**
     * @param DateTimeExtension $intlTwigDateTimeService
     */
    public function __construct(DateTimeExtension $intlTwigDateTimeService)
    {
        $this->intlTwigDateTimeService = $intlTwigDateTimeService;
    }

    /**
     * {@inheritdoc}
     */
    public function getFilters()
    {
        return [
            new TwigFilter('app_format_date_day_month', [$this, 'formatDateDayMonth'], ['is_safe' => ['html']]),
            new TwigFilter('app_event_date', [$this, 'eventDate'], ['is_safe' => ['html']]),
            new TwigFilter('app_tickets_price_time_left', [$this, 'ticketsPriceTimeLeft'], ['is_safe' => ['html']]),
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
     *
     * @return string
     */
    public function linksForGoogleCalendar(Event $event): string
    {
        if (false === \strpos($event->getDateFormat(), 'd') || false !== \strpos($event->getDateFormat(), 'S')) {
            return '';
        }

        $linkPattern = '<a href="http://www.google.com/calendar/event?action=TEMPLATE&text=%event_name%&dates=%since%/%till%&details=%event_description%&location=%event_location%&trp=false" target="_blank" rel="nofollow">%title%</a>';

        $location = '';
        if ($event->getCity() instanceof City) {
            $location = $event->isOnline() ? $event->getCity()->getName() : $event->getCity()->getName().' '.$event->getPlace();
        }

        $linkPattern = $this->translator->trans(
            $linkPattern,
            [
                '%event_name%' => $event->getName(),
                '%event_description%' => $event->getDescription(),
                '%event_location%' => $location,
            ]
        );

        $linkString = '<br>';
        $since = $event->getDate();
        $till = $event->getEndDateFromDates();

        if ($event->isStartAndEndDateSameByFormat('Y-m-d')) {
            $linkString .= $this->translator->trans(
                $linkPattern,
                [
                    '%since%' => $since,
                    '%till%' => $till,
                    '%title%' => $this->translator->trans('email_event_registration.add_google_calendar')
                ]
            );
        } else {
            $sinceEnd = clone $since;
            $sinceEnd->setTime($till->format('H'), $till->format('i'));

            $linkString .= $this->translator->trans(
                $linkPattern,
                [
                    '%since%' => $since,
                    '%till%' => $sinceEnd,
                    '%title%' => $this->translator->trans('email_event_registration.add_google_calendar_d1')
                ]
            );

            $sinceFrom = clone $till;
            $sinceFrom->setTime($since->format('H'), $since->format('i'));

            $linkString .= '<br>'.$this->translator->trans(
                $linkPattern,
                [
                    '%since%' => $sinceFrom,
                    '%till%' => $till,
                    '%title%' => $this->translator->trans('email_event_registration.add_google_calendar_d2')
                ]
            );
        }

        return $linkString.'<br>';
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
     * @param TicketCost $ticketCost
     *
     * @return string
     *
     * @throws \Exception
     */
    public function ticketsPriceTimeLeft(TicketCost $ticketCost): string
    {
        $endDate = $ticketCost->getEndDate();

        if (!$endDate instanceof \DateTimeInterface) {
            return '';
        }

        $now = new \DateTime();
        $interval = $now->diff($endDate);

        $minutes = (int) $interval->format('%i');
        $hours = (int) $interval->format('%h');
        $days = (int) $interval->format('%a');

        $result = $days;

        $translateKey = 'tickets.price_days_left';
        if (0 === $days) {
            if ($hours > 0) {
                $translateKey = 'tickets.price_hours_left';
                $result = $hours;
            } else {
                $translateKey = 'tickets.price_minutes_left';
                if (0 === $minutes) {
                    ++$minutes;
                }
                $result = $minutes;
            }
        }

        return $this->translator->trans($translateKey, ['%period_count%' => $result, '%count%' => AppPluralizationExtension::pluralization($result)]);
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
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
     *
     * @return mixed
     */
    private function replaceMonthToNominative($formattedDate, $pattern)
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
            $pattern = \str_replace(self::YEAR_SEASON_FORMAT, 'MMMM', $pattern);
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
            $formattedDate = $this->replaceMonthToNominative($formattedDate, $pattern);
        }

        return $formattedDate;
    }
}
