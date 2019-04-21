<?php

namespace Application\Bundle\DefaultBundle\Service;

use Stfalcon\Bundle\EventBundle\Entity\Event;
use Stfalcon\Bundle\EventBundle\Entity\Payment;
use Doctrine\ORM\EntityManager;

/**
 * Service to get sales analytics data
 */
class AnalyticsService
{
    /** @var EntityManager */
    protected $em;

    /**
     * @param EntityManager $em
     */
    public function __construct($em)
    {
        $this->em = $em;
    }

    /**
     * Get data for daily statistics of tickets sold
     *
     * @param Event $event
     *
     * @throws \Exception
     *
     * @return array
     */
    public function getDailyTicketsSoldData(Event $event)
    {
        $dateFrom = $this->getFirstDayOfTicketSales($event);
        $dateTo = $this->getLastDayOfTicketSales($event);

        $qb = $this->em->createQueryBuilder();
        $qb->select('DATE(p.updatedAt) as date_of_sale, COUNT(t.id) as tickets_sold_number')
            ->from('Stfalcon\Bundle\EventBundle\Entity\Ticket', 't')
            ->join('t.payment', 'p')
            ->where($qb->expr()->eq('t.event', ':event'))
            ->andWhere($qb->expr()->gte('p.updatedAt', ':date_from'))
            ->andWhere($qb->expr()->lte('p.updatedAt', ':date_to'))
            ->andWhere($qb->expr()->eq('p.status', ':status'))
            ->andWhere('p.amount > 0') // тільки реально продані квитки
            ->setParameters([
                'event' => $event,
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
                'status' => Payment::STATUS_PAID,
            ])
            ->addGroupBy('date_of_sale')
        ;

        $results = $qb
            ->getQuery()
            ->getResult()
        ;

        // fill the possible gap in sequence of dates
        $dateRange = new \DatePeriod($dateFrom, new \DateInterval('P1D'), $dateTo->modify('+1 day'));

        $formattedResult = [];
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

        return $formattedResult;
    }


    /**
     * Get data for summary statistics of tickets sold
     *
     * @param Event|null $event
     *
     * @throws \Exception
     * @return array
     */
    public function getSummaryTicketsSoldData(Event $event)
    {
        $qbTicketsSold = $this->em->createQueryBuilder();
        $qbTicketsSold->select('COUNT(t.id) as tickets_sold_number, SUM(t.amount) as tickets_amount')
            ->from('Stfalcon\Bundle\EventBundle\Entity\Ticket', 't')
            ->join('t.payment', 'p')
            ->andWhere($qbTicketsSold->expr()->eq('t.event', ':event'))
            ->andWhere($qbTicketsSold->expr()->eq('p.status', ':status'))
            ->setParameters([
                'event' => $event,
                'status' => Payment::STATUS_PAID,
            ])
        ;

        $qbFreeTickets = clone $qbTicketsSold;

        $qbTicketsSold->andWhere('p.amount > 0');
        $results = $qbTicketsSold->getQuery()->getSingleResult();

        $qbFreeTickets->andWhere('p.amount = 0'); //free_tickets_number
        $resultsFreeTickets = $qbFreeTickets->getQuery()->getSingleResult();

        $results['free_tickets_number'] = $resultsFreeTickets['tickets_sold_number'];
        $results['total_tickets_number'] = $results['free_tickets_number'] + $results['tickets_sold_number'];

        return $results;
    }
    /**
     * Get data for compare ticket sales (with previous events)
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
        $events = $this->em->getRepository('StfalconEventBundle:Event')
            ->findBy(['group' => $event->getGroup()], ['date' => 'DESC'], 4);

        // формуєм масив ключів з айдшок івентів, щоб використати його в формуванні заготовки масиву результатів
        $resultsKeys = [$event->getId()];
        foreach ($events as $e) {
            /* @var $e Event */
            $resultsKeys[] = $e->getId();
        }
        $resultsValueTemplate = array_fill_keys($resultsKeys, null);
        // заповнюєм заготовку масиву результатів
        $results = array_fill(0, $weeksMaxNumber, $resultsValueTemplate);

        // витягуєм статистику продажів для івентів з цієї групи
        foreach ($events as $e) {
            /* @var $e Event */
            $dataForDailyStatistics = $this->getDailyTicketsSoldData($e);
            $reverseDataForDailyStatistics = array_reverse($dataForDailyStatistics);

            // групуєм статистику продажів для івенту по тижнях
            $oneEventResults = [];
            foreach ($reverseDataForDailyStatistics as $oneDateData) {
                /* @var $date \DateTime */
                $date = $oneDateData[0];
                $number = $oneDateData[1];

                $key = $date->format("Y-W");
                $oneEventResults[$key] = (isset($oneEventResults[$key]) ? $oneEventResults[$key] : 0) + $number;
            }

            // мержим отриману статистику івента в загальний масив результатів
            foreach (array_values($oneEventResults) as $week => $number) {
                if ($week == $weeksMaxNumber) {
                    break;
                }
                $results[$week][$e->getId()] = $number;
            }
        }

        return array_reverse($results);
    }

    /**
     * Get the first day of ticket sales (get createdAt of the first event ticket)
     *
     * @param Event $event
     *
     * @throws \Doctrine\ORM\Query\QueryException
     *
     * @return \DateTime
     */
    private function getFirstDayOfTicketSales(Event $event)
    {
        $qb = $this->em->createQueryBuilder();
        $qb->select('t.createdAt')
            ->from('Stfalcon\Bundle\EventBundle\Entity\Ticket', 't')
            ->where($qb->expr()->eq('t.event', ':event'))
            ->andWhere($qb->expr()->eq('t.event', ':event'))
            ->setParameters([
                'event' => $event,
            ])
            ->orderBy('t.createdAt', 'ASC')
            ->setMaxResults(1);

        $date = $qb->getQuery()
            ->getSingleScalarResult();

        return new \DateTime($date);
    }

    /**
     * Get the last day of ticket sales
     *
     * @param Event $event
     *
     * @return \DateTime|null
     */
    private function getLastDayOfTicketSales(Event $event)
    {
        return $event->getDateEnd() ?: $event->getDate();
    }
}
