<?php

namespace TicketKitten\Service\Handler\Attendee;

use Illuminate\Database\DatabaseManager;
use Illuminate\Validation\ValidationException;
use Throwable;
use TicketKitten\DomainObjects\AttendeeDomainObject;
use TicketKitten\DomainObjects\Enums\TicketType;
use TicketKitten\DomainObjects\Generated\AttendeeDomainObjectAbstract;
use TicketKitten\DomainObjects\Generated\TicketDomainObjectAbstract;
use TicketKitten\DomainObjects\TicketPriceDomainObject;
use TicketKitten\Exceptions\NoTicketsAvailableException;
use TicketKitten\Http\DataTransferObjects\EditAttendeeDTO;
use TicketKitten\Repository\Interfaces\AttendeeRepositoryInterface;
use TicketKitten\Repository\Interfaces\TicketRepositoryInterface;
use TicketKitten\Service\Common\Ticket\TicketQuantityService;

class EditAttendeeHandler
{
    private AttendeeRepositoryInterface $attendeeRepository;

    private TicketRepositoryInterface $ticketRepository;

    private TicketQuantityService $ticketQuantityService;

    private DatabaseManager $databaseManager;

    public function __construct(
        AttendeeRepositoryInterface $attendeeRepository,
        TicketRepositoryInterface   $ticketRepository,
        TicketQuantityService       $ticketQuantityService,
        DatabaseManager             $databaseManager,
    )
    {
        $this->attendeeRepository = $attendeeRepository;
        $this->ticketRepository = $ticketRepository;
        $this->ticketQuantityService = $ticketQuantityService;
        $this->databaseManager = $databaseManager;
    }

    /**
     * @throws ValidationException
     * @throws Throwable
     */
    public function handle(EditAttendeeDTO $editAttendeeDTO): AttendeeDomainObject
    {
        return $this->databaseManager->transaction(function () use ($editAttendeeDTO) {
            $this->validateTicketId($editAttendeeDTO);

            $attendee = $this->getAttendee($editAttendeeDTO);

            $this->adjustTicketQuantities($attendee, $editAttendeeDTO);

            return $this->updateAttendee($editAttendeeDTO);
        });
    }

    private function adjustTicketQuantities(AttendeeDomainObject $attendee, EditAttendeeDTO $editAttendeeDTO): void
    {
        if ($attendee->getTicketPriceId() !== $editAttendeeDTO->ticket_price_id) {
            $this->ticketQuantityService->decreaseTicketPriceQuantitySold($editAttendeeDTO->ticket_price_id);
            $this->ticketQuantityService->increaseTicketPriceQuantitySold($attendee->getTicketPriceId());
        }
    }

    private function updateAttendee(EditAttendeeDTO $editAttendeeDTO): AttendeeDomainObject
    {
        return $this->attendeeRepository->updateByIdWhere($editAttendeeDTO->attendee_id, [
            'first_name' => $editAttendeeDTO->first_name,
            'last_name' => $editAttendeeDTO->last_name,
            'email' => $editAttendeeDTO->email,
            'ticket_id' => $editAttendeeDTO->ticket_id,
        ], [
            'event_id' => $editAttendeeDTO->event_id,
        ]);
    }

    /**
     * @throws ValidationException
     * @throws NoTicketsAvailableException
     */
    private function validateTicketId(EditAttendeeDTO $editAttendeeDTO): void
    {
        $ticket = $this->ticketRepository
            ->loadRelation(TicketPriceDomainObject::class)
            ->findFirstWhere([
                TicketDomainObjectAbstract::ID => $editAttendeeDTO->ticket_id,
            ]);

        if ($ticket->getEventId() !== $editAttendeeDTO->event_id) {
            throw ValidationException::withMessages([
                'ticket_id' => __('Ticket ID is not valid'),
            ]);
        }

        $availableQuantity = $this->ticketRepository->getQuantityRemainingForTicketPrice(
            ticketId: $editAttendeeDTO->ticket_id,
            ticketPriceId: $ticket->getType() === TicketType::TIERED->name
                ? $editAttendeeDTO->ticket_price_id
                : $ticket->getTicketPrices()->first()->getId(),
        );

        if ($availableQuantity <= 0) {
            throw new NoTicketsAvailableException(__('There are no tickets available. ' .
                'If you would like to assign this ticket to this attendee,' .
                ' please adjust the ticket\'s available quantity.'));
        }
    }

    /**
     * @throws ValidationException
     */
    private function getAttendee(EditAttendeeDTO $editAttendeeDTO): AttendeeDomainObject
    {
        $attendee = $this->attendeeRepository->findFirstWhere([
            AttendeeDomainObjectAbstract::EVENT_ID => $editAttendeeDTO->event_id,
            AttendeeDomainObjectAbstract::ID => $editAttendeeDTO->attendee_id,
        ]);

        if ($attendee === null) {
            throw ValidationException::withMessages([
                'attendee_id' => __('Attendee ID is not valid'),
            ]);
        }

        return $attendee;
    }
}
