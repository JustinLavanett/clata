<?php

namespace TicketKitten\DomainObjects\Generated;

/**
 * THIS FILE IS AUTOGENERATED - DO NOT EDIT IT DIRECTLY.
 * @package TicketKitten\DomainObjects\Generated
 */
abstract class EventDailyStatisticDomainObjectAbstract extends \TicketKitten\DomainObjects\AbstractDomainObject
{
    final public const SINGULAR_NAME = 'event_daily_statistic';
    final public const PLURAL_NAME = 'event_daily_statistics';
    final public const ID = 'id';
    final public const EVENT_ID = 'event_id';
    final public const SALES_TOTAL_GROSS = 'sales_total_gross';
    final public const TOTAL_TAX = 'total_tax';
    final public const SALES_TOTAL_BEFORE_ADDITIONS = 'sales_total_before_additions';
    final public const TICKETS_SOLD = 'tickets_sold';
    final public const ORDERS_CREATED = 'orders_created';
    final public const DATE = 'date';
    final public const CREATED_AT = 'created_at';
    final public const DELETED_AT = 'deleted_at';
    final public const UPDATED_AT = 'updated_at';
    final public const TOTAL_FEE = 'total_fee';
    final public const VERSION = 'version';

    protected int $id;
    protected int $event_id;
    protected float $sales_total_gross = 0.0;
    protected float $total_tax = 0.0;
    protected float $sales_total_before_additions = 0.0;
    protected int $tickets_sold = 0;
    protected int $orders_created = 0;
    protected string $date;
    protected string $created_at;
    protected ?string $deleted_at = null;
    protected ?string $updated_at = null;
    protected float $total_fee = 0.0;
    protected int $version = 0;

    public function toArray(): array
    {
        return [
                    'id' => $this->id ?? null,
                    'event_id' => $this->event_id ?? null,
                    'sales_total_gross' => $this->sales_total_gross ?? null,
                    'total_tax' => $this->total_tax ?? null,
                    'sales_total_before_additions' => $this->sales_total_before_additions ?? null,
                    'tickets_sold' => $this->tickets_sold ?? null,
                    'orders_created' => $this->orders_created ?? null,
                    'date' => $this->date ?? null,
                    'created_at' => $this->created_at ?? null,
                    'deleted_at' => $this->deleted_at ?? null,
                    'updated_at' => $this->updated_at ?? null,
                    'total_fee' => $this->total_fee ?? null,
                    'version' => $this->version ?? null,
                ];
    }

    public function setId(int $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setEventId(int $event_id): self
    {
        $this->event_id = $event_id;
        return $this;
    }

    public function getEventId(): int
    {
        return $this->event_id;
    }

    public function setSalesTotalGross(float $sales_total_gross): self
    {
        $this->sales_total_gross = $sales_total_gross;
        return $this;
    }

    public function getSalesTotalGross(): float
    {
        return $this->sales_total_gross;
    }

    public function setTotalTax(float $total_tax): self
    {
        $this->total_tax = $total_tax;
        return $this;
    }

    public function getTotalTax(): float
    {
        return $this->total_tax;
    }

    public function setSalesTotalBeforeAdditions(float $sales_total_before_additions): self
    {
        $this->sales_total_before_additions = $sales_total_before_additions;
        return $this;
    }

    public function getSalesTotalBeforeAdditions(): float
    {
        return $this->sales_total_before_additions;
    }

    public function setTicketsSold(int $tickets_sold): self
    {
        $this->tickets_sold = $tickets_sold;
        return $this;
    }

    public function getTicketsSold(): int
    {
        return $this->tickets_sold;
    }

    public function setOrdersCreated(int $orders_created): self
    {
        $this->orders_created = $orders_created;
        return $this;
    }

    public function getOrdersCreated(): int
    {
        return $this->orders_created;
    }

    public function setDate(string $date): self
    {
        $this->date = $date;
        return $this;
    }

    public function getDate(): string
    {
        return $this->date;
    }

    public function setCreatedAt(string $created_at): self
    {
        $this->created_at = $created_at;
        return $this;
    }

    public function getCreatedAt(): string
    {
        return $this->created_at;
    }

    public function setDeletedAt(?string $deleted_at): self
    {
        $this->deleted_at = $deleted_at;
        return $this;
    }

    public function getDeletedAt(): ?string
    {
        return $this->deleted_at;
    }

    public function setUpdatedAt(?string $updated_at): self
    {
        $this->updated_at = $updated_at;
        return $this;
    }

    public function getUpdatedAt(): ?string
    {
        return $this->updated_at;
    }

    public function setTotalFee(float $total_fee): self
    {
        $this->total_fee = $total_fee;
        return $this;
    }

    public function getTotalFee(): float
    {
        return $this->total_fee;
    }

    public function setVersion(int $version): self
    {
        $this->version = $version;
        return $this;
    }

    public function getVersion(): int
    {
        return $this->version;
    }
}
