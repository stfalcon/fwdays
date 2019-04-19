<?php

namespace Application\Bundle\DefaultBundle\Service;

use Stfalcon\Bundle\EventBundle\Entity\Event;
use Stfalcon\Bundle\EventBundle\Entity\Payment;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\NoResultException;

class StatisticService
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
     * Get data for daily statistics of tickets sold.
     *
     * @param Event $event
     *
     * @return array
     *
     * @throws \Exception
     */
    public function getDataForDailyStatisticsOfTicketsSold(Event $event)
    {
        $dateFrom = $this->_getFirstDayOfTicketSales($event);
        $dateTo = $this->_getLastDayOfTicketSales($event);

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
     * Get data for total statistics of tickets sold.
     *
     * @param Event|null $event
     *
     * @return array
     *
     * @throws \Exception
     */
    public function getDataForTotalStatisticsOfTicketsSold(Event $event)
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
     * Get the first day of ticket sales (get createdAt of the first event ticket).
     *
     * @param Event $event
     *
     * @return \DateTime
     *
     * @throws \Doctrine\ORM\Query\QueryException
     */
    private function _getFirstDayOfTicketSales(Event $event)
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

        try {
            $date = $qb->getQuery()
                ->getSingleScalarResult();
        } catch (NoResultException $e) {
            $date = $this->_getLastDayOfTicketSales($event)->modify('-1 week')->format('Y-m-d');
        }

        return new \DateTime($date);
    }

    /**
     * Get the last day of ticket sales.
     *
     * @param Event $event
     *
     * @return \DateTime|null
     */
    private function _getLastDayOfTicketSales(Event $event)
    {
        return $event->getDateEnd() ?: $event->getDate();
    }

    /**
     * Get data for forecasting tickets sales (based on previous events).
     *
     * @param Event $event
     */
    public function getDataForForecastingTicketsSales(Event $event)
    {
        $weeksMaxNumber = 20; // задаєм максимальну глибину аналізу

        // витягнути івенти з цієї групи
        $events = $this->em->getRepository('StfalconEventBundle:Event')
            ->findBy(['group' => $event->getGroup()], ['date' => 'DESC'], 4);

        // формуєм масив ключів з айдшок івентів, щоб використати його в формуванні заготовки масиву результатів
        $resultsKeys = [$event->getId()];
        foreach ($events as $e) {
            $resultsKeys[] = $e->getId();
        }
        $resultsValueTemplate = array_fill_keys($resultsKeys, null);
        // заповнюєм заготовку масиву результатів
        $results = array_fill(0, $weeksMaxNumber, $resultsValueTemplate);

        // витягуєм статистику продажів для івентів з цієї групи
        foreach ($events as $event) {
            $dataForDailyStatistics = $this->getDataForDailyStatisticsOfTicketsSold($event);
            $reverseDataForDailyStatistics = array_reverse($dataForDailyStatistics);

            // групуєм статистику продажів для івенту по тижнях
            $oneEventResults = [];
            foreach ($reverseDataForDailyStatistics as $oneDateData) {
                $date = $oneDateData[0];
                $number = $oneDateData[1];

                $key = $date->format('Y-W');
                $oneEventResults[$key] = (isset($oneEventResults[$key]) ? $oneEventResults[$key] : 0) + $number;
            }

            // мержим отриману статистику івента в загальний масив результатів
            foreach (array_values($oneEventResults) as $week => $number) {
                if ($week == $weeksMaxNumber) {
                    break;
                }
                $results[$week][$event->getId()] = $number;
            }
        }

        return array_reverse($results);
    }
}
