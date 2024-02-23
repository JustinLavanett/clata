<?php

namespace TicketKitten\Service\Handler\PromoCode;

use TicketKitten\DomainObjects\Enums\PromoCodeDiscountTypeEnum;
use TicketKitten\DomainObjects\Generated\PromoCodeDomainObjectAbstract;
use TicketKitten\DomainObjects\PromoCodeDomainObject;
use TicketKitten\Exceptions\ResourceConflictException;
use TicketKitten\Helper\DateHelper;
use TicketKitten\Http\DataTransferObjects\UpsertPromoCodeDTO;
use TicketKitten\Repository\Interfaces\EventRepositoryInterface;
use TicketKitten\Repository\Interfaces\PromoCodeRepositoryInterface;
use TicketKitten\Service\Common\Ticket\EventTicketValidationService;
use TicketKitten\Service\Common\Ticket\Exception\UnrecognizedTicketIdException;

readonly class CreatePromoCodeHandler
{
    public function __construct(
        private PromoCodeRepositoryInterface $promoCodeRepository,
        private EventTicketValidationService $eventTicketValidationService,
        private EventRepositoryInterface     $eventRepository,
    )
    {
    }

    /**
     * @throws ResourceConflictException
     * @throws UnrecognizedTicketIdException
     */
    public function handle(int $eventId, UpsertPromoCodeDTO $promoCodeDTO): PromoCodeDomainObject
    {
        $this->checkForDuplicateCode($promoCodeDTO->code, $eventId);
        $this->eventTicketValidationService->validateTicketIds($promoCodeDTO->applicable_ticket_ids, $eventId);
        $event = $this->eventRepository->findById($eventId);

        return $this->promoCodeRepository->create([
            PromoCodeDomainObjectAbstract::EVENT_ID => $eventId,
            PromoCodeDomainObjectAbstract::CODE => $promoCodeDTO->code,
            PromoCodeDomainObjectAbstract::DISCOUNT => $promoCodeDTO->discount_type === PromoCodeDiscountTypeEnum::NONE
                ? 0.00
                : (float)$promoCodeDTO->discount,
            PromoCodeDomainObjectAbstract::DISCOUNT_TYPE => $promoCodeDTO->discount_type?->name,
            PromoCodeDomainObjectAbstract::EXPIRY_DATE => $promoCodeDTO->expiry_date
                ? DateHelper::convertToUTC($promoCodeDTO->expiry_date, $event->getTimezone())
                : null,
            PromoCodeDomainObjectAbstract::MAX_ALLOWED_USAGES => $promoCodeDTO->max_allowed_usages,
            PromoCodeDomainObjectAbstract::APPLICABLE_TICKET_IDS => $promoCodeDTO->applicable_ticket_ids,
        ]);
    }

    /**
     * @throws ResourceConflictException
     */
    private function checkForDuplicateCode(string $code, int $eventId): void
    {
        $existingPromoCode = $this->promoCodeRepository->findFirstWhere([
            PromoCodeDomainObjectAbstract::EVENT_ID => $eventId,
            PromoCodeDomainObjectAbstract::CODE => $code,
        ]);

        if ($existingPromoCode !== null) {
            throw new ResourceConflictException(
                __('Promo code :code already exists', ['code' => $code])
            );
        }
    }
}
