<?php

namespace TicketKitten\Service\Common\Event;

use Carbon\Carbon;
use Illuminate\Database\DatabaseManager;
use Illuminate\Support\Collection;
use TicketKitten\Http\DataTransferObjects\EventDailyStatsResponseDTO;
use TicketKitten\Http\DataTransferObjects\EventStatsRequestDTO;
use TicketKitten\Http\DataTransferObjects\EventStatsResponseDTO;
use TicketKitten\Service\Common\Event\DTO\EventCheckInStatsResponseDTO;

readonly class EventStatsFetchService
{
    public function __construct(
        private DatabaseManager $db,
    )
    {
    }

    public function getEventStats(EventStatsRequestDTO $requestData): EventStatsResponseDTO
    {
        $eventId = $requestData->event_id;

        // Aggregate total statistics for the event for all time
        $totalsQuery = <<<SQL
        SELECT
            SUM(es.tickets_sold) AS total_tickets_sold,
            SUM(es.orders_created) AS total_orders,
            SUM(es.sales_total_gross) AS total_gross_sales,
            SUM(es.total_tax) AS total_tax,
            SUM(es.total_fee) AS total_fees
        FROM event_statistics es
        WHERE es.event_id = :eventId
          AND es.deleted_at IS NULL;
    SQL;

        // Execute the totals and comparison queries
        $totalsResult = $this->db->selectOne($totalsQuery, ['eventId' => $eventId]);

        // Use the results to populate the response DTO
        return new EventStatsResponseDTO(
            daily_stats: $this->getDailyEventStats($requestData),
            start_date: $requestData->start_date,
            end_date: $requestData->end_date,
            check_in_stats: $this->getCheckedInStats($eventId),
            total_tickets_sold: $totalsResult->total_tickets_sold ?? 0,
            total_orders: $totalsResult->total_orders ?? 0,
            total_gross_sales: $totalsResult->total_gross_sales ?? 0,
            total_fees: $totalsResult->total_fees ?? 0,
            total_tax: $totalsResult->total_tax ?? 0,
        );
    }

    public function getDailyEventStats(EventStatsRequestDTO $requestData): Collection
    {
        $eventId = $requestData->event_id;

        $startDate = $requestData->start_date;
        $endDate = $requestData->end_date;

        $query = <<<SQL
            WITH date_series AS (
              SELECT date::date
              FROM generate_series(
                :startDate::date,
                :endDate::date,
                '1 day'
              ) AS gs(date)
            )
            SELECT
              ds.date,
              COALESCE(SUM(eds.total_fee), 0) AS total_fees,
              COALESCE(SUM(eds.total_tax), 0) AS total_tax,
              COALESCE(SUM(eds.sales_total_gross), 0) AS total_sales_gross,
              COALESCE(SUM(eds.orders_created), 0) AS orders_created,
              COALESCE(SUM(eds.tickets_sold), 0) AS tickets_sold
            FROM date_series ds
            LEFT JOIN event_daily_statistics eds ON ds.date = eds.date AND eds.deleted_at IS NULL AND eds.event_id = :eventId
            GROUP BY ds.date
            ORDER BY ds.date ASC;
        SQL;

        $results = $this->db->select($query, [
            'startDate' => $startDate,
            'endDate' => $endDate,
            'eventId' => $eventId,
        ]);

        $currentTime = Carbon::now('UTC')->toTimeString();

        return collect($results)->map(function (object $result) use ($currentTime) {
            $dateTimeWithCurrentTime = (new Carbon($result->date))->setTimezone('UTC')->format('Y-m-d') . ' ' . $currentTime;

            return new EventDailyStatsResponseDTO(
                date: $dateTimeWithCurrentTime,
                total_fees: $result->total_fees,
                total_tax: $result->total_tax,
                total_sales_gross: $result->total_sales_gross,
                orders_created: $result->orders_created,
                tickets_sold: $result->tickets_sold,
            );
        });
    }

    public function getCheckedInStats(int $eventId): EventCheckInStatsResponseDTO
    {
        $query = <<<SQL
            SELECT
                COUNT(*) AS total_count,
                SUM(CASE WHEN attendees.checked_in_at IS NOT NULL THEN 1 ELSE 0 END) AS checked_in_count
            FROM attendees
            INNER JOIN orders ON orders.id = attendees.order_id
            WHERE orders.event_id = {$eventId}
              AND orders.status = 'COMPLETED'
              AND attendees.status = 'ACTIVE';
        SQL;

        $result = $this->db->select($query)[0];

        return new EventCheckInStatsResponseDTO(
            total_checked_in_attendees: $result->checked_in_count ?? 0,
            total_attendees: $result->total_count ?? 0,
        );
    }
}
