<?php

namespace TicketKitten\Service\Handler\TaxAndFee;

use Psr\Log\LoggerInterface;
use TicketKitten\DomainObjects\TaxAndFeesDomainObject;
use TicketKitten\Exceptions\ResourceNameAlreadyExistsException;
use TicketKitten\Http\DataTransferObjects\UpsertTaxDTO;
use TicketKitten\Repository\Interfaces\TaxAndFeeRepositoryInterface;
use TicketKitten\Service\Common\Tax\DuplicateTaxService;

class CreateTaxOrFeeHandler
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
        if ($this->duplicateTaxService->isDuplicate($data->name, $data->account_id)) {
            throw new ResourceNameAlreadyExistsException(
                sprintf('The name \'%s\' already exists', $data->name),
            );
        }

        /** @var TaxAndFeesDomainObject $tax */
        $tax = $this->taxRepository->create([
            'name' => $data->name,
            'description' => $data->description,
            'calculation_type' => $data->calculation_type->name,
            'rate' => $data->rate,
            'is_active' => $data->is_active,
            'is_default' => $data->is_default,
            'account_id' => $data->account_id,
            'type' => $data->type->name,
        ]);

        $this->logger->info('Created tax', [
            'id' => $tax->getId(),
            'name' => $tax->getName(),
        ]);

        return $tax;
    }
}
