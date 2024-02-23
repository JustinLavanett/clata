<?php

namespace TicketKitten\Service\Handler\Organizer;

use Illuminate\Database\DatabaseManager;
use Throwable;
use TicketKitten\DomainObjects\Enums\OrganizerImageType;
use TicketKitten\DomainObjects\ImageDomainObject;
use TicketKitten\DomainObjects\OrganizerDomainObject;
use TicketKitten\Http\DataTransferObjects\EditOrganizerDTO;
use TicketKitten\Repository\Interfaces\ImageRepositoryInterface;
use TicketKitten\Repository\Interfaces\OrganizerRepositoryInterface;
use TicketKitten\Service\Common\Image\ImageUploadService;

readonly class EditOrganizerHandler
{
    public function __construct(
        private OrganizerRepositoryInterface $organizerRepository,
        private ImageUploadService           $imageUploadService,
        private DatabaseManager              $databaseManager,
        private ImageRepositoryInterface     $imageRepository,
    )
    {
    }

    /**
     * @throws Throwable
     */
    public function handle(EditOrganizerDTO $organizerData): OrganizerDomainObject
    {
        return $this->databaseManager->transaction(
            fn() => $this->editOrganizer($organizerData)
        );
    }

    private function editOrganizer(EditOrganizerDTO $organizerData): OrganizerDomainObject
    {
        $this->organizerRepository->updateWhere(
            attributes: [
                'name' => $organizerData->name,
                'email' => $organizerData->email,
                'phone' => $organizerData->phone,
                'website' => $organizerData->website,
                'description' => $organizerData->description,
                'account_id' => $organizerData->account_id,
                'timezone' => $organizerData->timezone,
                'currency' => $organizerData->currency,
            ],
            where: [
                'id' => $organizerData->id,
                'account_id' => $organizerData->account_id,
            ]
        );

        $this->handleLogo($organizerData);

        return $this->organizerRepository
            ->loadRelation(ImageDomainObject::class)
            ->findById($organizerData->id);
    }

    private function handleLogo(EditOrganizerDTO $organizerData): void
    {
        if ($organizerData->logo === null) {
            return;
        }

        $this->imageRepository->deleteWhere([
            'entity_id' => $organizerData->id,
            'entity_type' => OrganizerDomainObject::class,
            'type' => OrganizerImageType::LOGO->name,
        ]);

        $this->imageUploadService->upload(
            image: $organizerData->logo,
            entityId: $organizerData->id,
            entityType: OrganizerDomainObject::class,
            imageType: OrganizerImageType::LOGO->name,
        );
    }
}
