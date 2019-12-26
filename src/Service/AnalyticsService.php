<?php

namespace App\Service;

use App\Entity\Event;
use App\Entity\Ticket;
use App\Repository\TicketRepository;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManager;

/**
 * Service to get sales analytics data.
 */
class AnalyticsService
{
    private $em;
    private $ticketRepository;

    /**
     * @param EntityManager    $em
     * @param TicketRepository $ticketRepository
     */
    public function __construct(EntityManager $em, TicketRepository $ticketRepository)
    {
        $this->em = $em;
        $this->ticketRepository = $ticketRepository;
    }

    /**
     * Get data for daily statistics of tickets sold.
     *
     * @param Event $event
     *
     * @throws \Exception
     *
     * @return array
     */
    public function getDailyTicketsSoldData(Event $event)
    {
        $since = $this->ticketRepository->getFirstDayOfTicketSales($event);
        $till = $this->getLastDayOfTicketSales($event);

        $formattedResult = [];

        if ($since instanceof \DateTime && $till instanceof \DateTime) {
            $results = $this->ticketRepository->findSoldTicketsCountBetweenDatesForEvent($since, $till, $event);
            // fill the possible gap in sequence of dates
            $dateRange = new \DatePeriod($since, new \DateInterval('P1D'), $till->modify('+1 day'));

            foreach ($dateRange as $date) {
                /* @var $date \DateTime */
                $key = $date->format('Y-m-d');
                $formattedResult[$key][0] = $date;
                $formattedResult[$key][1] = null;
            }

            // merge real data with array of prepared results
            foreach ($results as $result) {
                $formattedResult[$result['date_of_sale']][1] = (int) $result['tickets_sold_number'];
            }
        }

        return $formattedResult;
    }

    /**
     * Get data for summary statistics of tickets sold.
     *
     * @param Event $event
     *
     * @throws \Exception
     *
     * @return array
     */
    public function getSummaryTicketsSoldData(Event $event)
    {
        $results = $this->ticketRepository->getSoldTicketsCountForEvent($event);
        $resultsFreeTickets = $this->ticketRepository->getSoldTicketsCountForEvent($event, true);

        $results['free_tickets_number'] = $resultsFreeTickets['tickets_sold_number'];
        $results['total_tickets_number'] = $results['free_tickets_number'] + $results['tickets_sold_number'];

        return $results;
    }

    /**
     * Get data for compare ticket sales (with previous events).
     *
     * @param Event $event
     *
     * @throws \Exception
     *
     * @return array
     */
    public function getDataForCompareTicketSales(Event $event)
    {
        $weeksMaxNumber = 20; // задаєм максимальну глибину аналізу

        // витягнути івенти з цієї групи
        $events = $this->em->getRepository(Event::class)
            ->findBy(['group' => $event->getGroup()], ['date' => Criteria::DESC], 4);

        // формуєм масив ключів з айдшок івентів, щоб використати його в формуванні заготовки масиву результатів
        $resultsKeys = [$event->getId()];
        foreach ($events as $e) {
            /* @var Event $e */
            $resultsKeys[] = $e->getId();
        }
        $resultsValueTemplate = \array_fill_keys($resultsKeys, null);
        // заповнюєм заготовку масиву результатів
        $results = \array_fill(0, $weeksMaxNumber, $resultsValueTemplate);

        // витягуєм статистику продажів для івентів з цієї групи
        foreach ($events as $e) {
            /* @var Event $e  */
            $dataForDailyStatistics = $this->getDailyTicketsSoldData($e);
            $reverseDataForDailyStatistics = \array_reverse($dataForDailyStatistics);

            // групуєм статистику продажів для івенту по тижнях
            $oneEventResults = [];
            foreach ($reverseDataForDailyStatistics as $oneDateData) {
                /** @var \DateTime $date */
                $date = $oneDateData[0];
                $number = $oneDateData[1];

                $key = $date->format('Y-W');
                $oneEventResults[$key] = (isset($oneEventResults[$key]) ? $oneEventResults[$key] : 0) + $number;
            }

            // мержим отриману статистику івента в загальний масив результатів
            foreach (\array_values($oneEventResults) as $week => $number) {
                if ($week == $weeksMaxNumber) {
                    break;
                }
                $results[$week][$e->getId()] = $number;
            }
        }

        return \array_reverse($results);
    }

    /**
     * Get the last day of ticket sales.
     *
     * @param Event $event
     *
     * @return \DateTime|null
     */
    private function getLastDayOfTicketSales(Event $event): ?\DateTime
    {
        return $event->getDateEnd() ?: $event->getDate();
    }
}
