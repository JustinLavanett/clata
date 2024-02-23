<?php

namespace TicketKitten\Resources\Attendee;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use TicketKitten\DomainObjects\AttendeeDomainObject;
use TicketKitten\Resources\Ticket\TicketResourcePublic;

/**
 * @mixin AttendeeDomainObject
 */
class AttendeeResourcePublic extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->getId(),
            'email' => $this->getEmail(),
            'status' => $this->getStatus(),
            'first_name' => $this->getFirstName(),
            'last_name' => $this->getLastName(),
            'public_id' => $this->getPublicId(),
            'checked_in_at' => $this->getCheckedInAt(),
            'short_id' => $this->getShortId(),
            'ticket_id' => $this->getTicketId(),
            'ticket_price_id' => $this->getTicketPriceId(),
            'ticket' => $this->when(!!$this->getTicket(), fn() => new TicketResourcePublic($this->getTicket())),
        ];
    }
}
