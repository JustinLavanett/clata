<?php

namespace TicketKitten\Http\Actions\PromoCodes;

use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use TicketKitten\DomainObjects\Enums\PromoCodeDiscountTypeEnum;
use TicketKitten\DomainObjects\EventDomainObject;
use TicketKitten\Exceptions\ResourceConflictException;
use TicketKitten\Http\Actions\BaseAction;
use TicketKitten\Http\DataTransferObjects\UpsertPromoCodeDTO;
use TicketKitten\Http\Request\PromoCode\CreateUpdatePromoCodeRequest;
use TicketKitten\Http\ResponseCodes;
use TicketKitten\Resources\PromoCode\PromoCodeResource;
use TicketKitten\Service\Common\Ticket\Exception\UnrecognizedTicketIdException;
use TicketKitten\Service\Handler\PromoCode\UpdatePromoCodeHandler;

class UpdatePromoCodeAction extends BaseAction
{
    private UpdatePromoCodeHandler $updatePromoCodeHandler;

    public function __construct(UpdatePromoCodeHandler $promoCodeHandler)
    {
        $this->updatePromoCodeHandler = $promoCodeHandler;
    }

    /**
     * @throws ValidationException
     */
    public function __invoke(CreateUpdatePromoCodeRequest $request, int $eventId, int $promoCodeId): JsonResponse
    {
        $this->isActionAuthorized($eventId, EventDomainObject::class);

        try {
            $promoCode = $this->updatePromoCodeHandler->handle($promoCodeId, new UpsertPromoCodeDTO(
                code: strtolower($request->input('code')),
                event_id: $eventId,
                applicable_ticket_ids: $request->input('applicable_ticket_ids'),
                discount_type: PromoCodeDiscountTypeEnum::fromName($request->input('discount_type')),
                discount: $request->float('discount'),
                expiry_date: $request->input('expiry_date'),
                max_allowed_usages: $request->input('max_allowed_usages'),
            ));
        } catch (ResourceConflictException $e) {
            throw ValidationException::withMessages([
                'code' => $e->getMessage(),
            ]);
        } catch (UnrecognizedTicketIdException $e) {
            throw ValidationException::withMessages([
                'applicable_ticket_ids' => $e->getMessage(),
            ]);
        }

        return $this->resourceResponse(
            resource: PromoCodeResource::class,
            data: $promoCode,
            statusCode: ResponseCodes::HTTP_CREATED
        );
    }
}
