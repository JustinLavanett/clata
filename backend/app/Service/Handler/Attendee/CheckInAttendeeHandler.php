<?php

namespace TicketKitten\Service\Handler\Attendee;

use Carbon\Carbon;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use TicketKitten\DomainObjects\AttendeeDomainObject;
use TicketKitten\DomainObjects\Enums\CheckInAction;
use TicketKitten\DomainObjects\Generated\AttendeeDomainObjectAbstract;
use TicketKitten\DomainObjects\Status\AttendeeStatus;
use TicketKitten\Exceptions\CannotCheckInException;
use TicketKitten\Http\DataTransferObjects\CheckInAttendeeDTO;
use TicketKitten\Repository\Interfaces\AttendeeRepositoryInterface;
use TicketKitten\Repository\Interfaces\UserRepositoryInterface;

readonly class CheckInAttendeeHandler
{
    public function __construct(
        private AttendeeRepositoryInterface $attendeeRepository,
        private UserRepositoryInterface     $userRepository
    )
    {
    }

    /**
     * @throws CannotCheckInException
     * @throws ResourceNotFoundException
     */
    public function handle(CheckInAttendeeDTO $checkInAttendeeDTO): AttendeeDomainObject
    {
        $attendee = $this->fetchAttendee($checkInAttendeeDTO);

        $this->validateAttendeeStatus($attendee);
        $this->validateAction($attendee, $checkInAttendeeDTO);

        $this->updateCheckInStatus($checkInAttendeeDTO);

        return $this->fetchAttendee($checkInAttendeeDTO);
    }

    private function fetchAttendee(CheckInAttendeeDTO $checkInAttendeeDTO): AttendeeDomainObject
    {
        $criteria = [
            AttendeeDomainObjectAbstract::PUBLIC_ID => $checkInAttendeeDTO->attendee_public_id,
            AttendeeDomainObjectAbstract::EVENT_ID => $checkInAttendeeDTO->event_id,
        ];

        $attendee = $this->attendeeRepository->findFirstWhere($criteria);

        if (!$attendee) {
            throw new ResourceNotFoundException();
        }

        return $attendee;
    }

    /**
     * @throws CannotCheckInException
     */
    private function validateAttendeeStatus(AttendeeDomainObject $attendee): void
    {
        if ($attendee->getStatus() !== AttendeeStatus::ACTIVE->name) {
            throw new CannotCheckInException(__('Cannot check in attendee as they are not active.'));
        }
    }

    /**
     * @throws CannotCheckInException
     */
    private function validateAction(AttendeeDomainObject $attendee, CheckInAttendeeDTO $checkInAttendeeDTO): void
    {
        $actionName = $checkInAttendeeDTO->action === CheckInAction::CHECK_IN ? __('in') : __('out');
        $isInvalidCheckIn = $attendee->getCheckedInAt() !== null && $checkInAttendeeDTO->action === CheckInAction::CHECK_IN;
        $isInvalidCheckOut = $attendee->getCheckedInAt() === null && $checkInAttendeeDTO->action === CheckInAction::CHECK_OUT;

        if ($isInvalidCheckIn || $isInvalidCheckOut) {
            $user = $this->userRepository->findById(
                $checkInAttendeeDTO->action === CheckInAction::CHECK_IN ? $attendee->getCheckedInBy() : $attendee->getCheckedOutBy()
            );

            throw new CannotCheckInException(
                __(
                    "Cannot check :actionName attendee as they were already checked :actionName by :fullName :time.",
                    [
                        'actionName' => $actionName,
                        'fullName' => $user->getFullName(),
                        'time' => $checkInAttendeeDTO->action === CheckInAction::CHECK_IN
                            ? Carbon::createFromTimeString($attendee->getCheckedInAt())->ago()
                            : '',
                    ]
                )
            );
        }
    }

    private function updateCheckInStatus(CheckInAttendeeDTO $checkInAttendeeDTO): void
    {
        $updateData = [
            AttendeeDomainObjectAbstract::CHECKED_IN_AT => $checkInAttendeeDTO->action === CheckInAction::CHECK_IN
                ? now()
                : null,
            AttendeeDomainObjectAbstract::CHECKED_IN_BY => $checkInAttendeeDTO->action === CheckInAction::CHECK_IN
                ? $checkInAttendeeDTO->checked_in_by_user_id
                : null,
            AttendeeDomainObjectAbstract::CHECKED_OUT_BY => $checkInAttendeeDTO->action === CheckInAction::CHECK_OUT
                ? $checkInAttendeeDTO->checked_in_by_user_id
                : null,
        ];

        $criteria = [
            AttendeeDomainObjectAbstract::PUBLIC_ID => $checkInAttendeeDTO->attendee_public_id,
            AttendeeDomainObjectAbstract::EVENT_ID => $checkInAttendeeDTO->event_id,
        ];

        $this->attendeeRepository->updateWhere($updateData, $criteria);
    }
}
