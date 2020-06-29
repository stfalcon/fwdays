<?php

declare(strict_types=1);

namespace App\Service\Google;

use App\Entity\Event;
use Symfony\Bundle\FrameworkBundle\Routing\Router;

/**
 * GoogleCalendarEventFactory.
 */
class GoogleCalendarEventFactory
{
    /** @var Router */
    private $router;

    /**
     * @param Router $router
     */
    public function __construct(Router $router)
    {
        $this->router = $router;
    }

    /**
     * @param Event $appEvent
     *
     * @return \Google_Service_Calendar_Event
     */
    public function create(Event $appEvent): \Google_Service_Calendar_Event
    {
        $googleEvent = new \Google_Service_Calendar_Event();

        if (!$appEvent->getDate() instanceof \DateTime) {
            throw new \InvalidArgumentException('Since date for event can not be null');
        }

        if (!$appEvent->getEndDateFromDates() instanceof \DateTime) {
            throw new \InvalidArgumentException('Till date for event can not be null');
        }

        $sinceDate = clone $appEvent->getDate();
        $tillDate = clone $appEvent->getEndDateFromDates();

        $startEvent = new \Google_Service_Calendar_EventDateTime();
        $endEvent = new \Google_Service_Calendar_EventDateTime();

        $startEvent->setDate($sinceDate->format('Y-m-d'));
        $endEvent->setDate($tillDate->format('Y-m-d'));

        $googleEvent->setStart($startEvent);
        $googleEvent->setEnd($endEvent);
        $appEventUrl = $this->router->generate('event_show', ['slug' => $appEvent->getSlug()], Router::ABSOLUTE_URL);
        $googleEvent->setDescription($appEventUrl);
        $googleEvent->setSummary($appEvent->getName());

        return $googleEvent;
    }
}
