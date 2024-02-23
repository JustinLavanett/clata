<?php

namespace TicketKitten\Service\Handler\TaxAndFee;

use Psr\Log\LoggerInterface;
use TicketKitten\DomainObjects\TaxAndFeesDomainObject;
use TicketKitten\Exceptions\ResourceNameAlreadyExistsException;
use TicketKitten\Http\DataTransferObjects\UpsertTaxDTO;
use TicketKitten\Repository\Interfaces\TaxAndFeeRepositoryInterface;
use TicketKitten\Service\Common\Tax\DuplicateTaxService;

class EditTaxHandler
{
    private TaxAndFeeRepositoryInterface $taxRepository;

    private LoggerInterface $logger;

    private DuplicateTaxService $duplicateTaxService;

    public function __construct(
        TaxAndFeeRepositoryInterface $taxRepository,
        LoggerInterface              $logger,
        DuplicateTaxService          $duplicateTaxService
    )
    {
        $this->taxRepository = $taxRepository;
        $this->logger = $logger;
        $this->duplicateTaxService = $duplicateTaxService;
    }

    /**
     * @throws ResourceNameAlreadyExistsException
     */
    public function handle(UpsertTaxDTO $data): TaxAndFeesDomainObject
    {
        $existing = $this->taxRepository->findWhere([
            'name' => $data->name,
            'account_id' => $data->account_id,
        ]);

        if ($existing->isNotEmpty() && $existing->first()->getId() !== $data->id) {
            throw new ResourceNameAlreadyExistsException(
                sprintf('The name \'%s\' already exists', $data->name),
            );
        }

        $this->taxRepository->updateWhere(
            attributes: [
                'name' => $data->name,
                'description' => $data->description,
                'calculation_type' => $data->calculation_type->name,
                'rate' => $data->rate,
                'is_active' => $data->is_active,
                'is_default' => $data->is_default,
                'type' => $data->type->name,
            ],
            where: [
                'id' => $data->id,
                'account_id' => $data->account_id,
            ]
        );

        /** @var TaxAndFeesDomainObject $tax */
        $tax = $this->taxRepository->findById($data->id);

        $this->logger->info('Updated tax', [
            'id' => $tax->getId(),
            'name' => $tax->getName(),
        ]);

        return $tax;
    }
}
