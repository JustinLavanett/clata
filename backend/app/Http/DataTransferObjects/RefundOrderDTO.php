<?php

namespace TicketKitten\Http\DataTransferObjects;

use TicketKitten\DataTransferObjects\BaseDTO;

class RefundOrderDTO extends BaseDTO
{
    public function __construct(
        public readonly int   $event_id,
        public readonly int   $order_id,
        public readonly float $amount,
        public readonly bool  $notify_buyer,
        public readonly bool  $cancel_order
    )
    {
    }
}
