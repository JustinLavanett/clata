<?php

namespace TicketKitten\DomainObjects\Generated;

/**
 * THIS FILE IS AUTOGENERATED - DO NOT EDIT IT DIRECTLY.
 * @package TicketKitten\DomainObjects\Generated
 */
abstract class MessageDomainObjectAbstract extends \TicketKitten\DomainObjects\AbstractDomainObject
{
    final public const SINGULAR_NAME = 'message';
    final public const PLURAL_NAME = 'messages';
    final public const ID = 'id';
    final public const EVENT_ID = 'event_id';
    final public const SENT_BY_USER_ID = 'sent_by_user_id';
    final public const SUBJECT = 'subject';
    final public const MESSAGE = 'message';
    final public const TYPE = 'type';
    final public const RECIPIENT_IDS = 'recipient_ids';
    final public const SENT_AT = 'sent_at';
    final public const ATTENDEE_IDS = 'attendee_ids';
    final public const TICKET_IDS = 'ticket_ids';
    final public const ORDER_ID = 'order_id';
    final public const STATUS = 'status';
    final public const SEND_DATA = 'send_data';
    final public const CREATED_AT = 'created_at';
    final public const UPDATED_AT = 'updated_at';
    final public const DELETED_AT = 'deleted_at';

    protected int $id;
    protected int $event_id;
    protected int $sent_by_user_id;
    protected string $subject;
    protected string $message;
    protected string $type;
    protected array|string|null $recipient_ids = null;
    protected ?string $sent_at = null;
    protected array|string|null $attendee_ids = null;
    protected array|string|null $ticket_ids = null;
    protected ?int $order_id = null;
    protected string $status;
    protected array|string|null $send_data = null;
    protected string $created_at;
    protected ?string $updated_at = null;
    protected ?string $deleted_at = null;

    public function toArray(): array
    {
        return [
                    'id' => $this->id ?? null,
                    'event_id' => $this->event_id ?? null,
                    'sent_by_user_id' => $this->sent_by_user_id ?? null,
                    'subject' => $this->subject ?? null,
                    'message' => $this->message ?? null,
                    'type' => $this->type ?? null,
                    'recipient_ids' => $this->recipient_ids ?? null,
                    'sent_at' => $this->sent_at ?? null,
                    'attendee_ids' => $this->attendee_ids ?? null,
                    'ticket_ids' => $this->ticket_ids ?? null,
                    'order_id' => $this->order_id ?? null,
                    'status' => $this->status ?? null,
                    'send_data' => $this->send_data ?? null,
                    'created_at' => $this->created_at ?? null,
                    'updated_at' => $this->updated_at ?? null,
                    'deleted_at' => $this->deleted_at ?? null,
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

    public function setSentByUserId(int $sent_by_user_id): self
    {
        $this->sent_by_user_id = $sent_by_user_id;
        return $this;
    }

    public function getSentByUserId(): int
    {
        return $this->sent_by_user_id;
    }

    public function setSubject(string $subject): self
    {
        $this->subject = $subject;
        return $this;
    }

    public function getSubject(): string
    {
        return $this->subject;
    }

    public function setMessage(string $message): self
    {
        $this->message = $message;
        return $this;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function setType(string $type): self
    {
        $this->type = $type;
        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setRecipientIds(array|string|null $recipient_ids): self
    {
        $this->recipient_ids = $recipient_ids;
        return $this;
    }

    public function getRecipientIds(): array|string|null
    {
        return $this->recipient_ids;
    }

    public function setSentAt(?string $sent_at): self
    {
        $this->sent_at = $sent_at;
        return $this;
    }

    public function getSentAt(): ?string
    {
        return $this->sent_at;
    }

    public function setAttendeeIds(array|string|null $attendee_ids): self
    {
        $this->attendee_ids = $attendee_ids;
        return $this;
    }

    public function getAttendeeIds(): array|string|null
    {
        return $this->attendee_ids;
    }

    public function setTicketIds(array|string|null $ticket_ids): self
    {
        $this->ticket_ids = $ticket_ids;
        return $this;
    }

    public function getTicketIds(): array|string|null
    {
        return $this->ticket_ids;
    }

    public function setOrderId(?int $order_id): self
    {
        $this->order_id = $order_id;
        return $this;
    }

    public function getOrderId(): ?int
    {
        return $this->order_id;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;
        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setSendData(array|string|null $send_data): self
    {
        $this->send_data = $send_data;
        return $this;
    }

    public function getSendData(): array|string|null
    {
        return $this->send_data;
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

    public function setUpdatedAt(?string $updated_at): self
    {
        $this->updated_at = $updated_at;
        return $this;
    }

    public function getUpdatedAt(): ?string
    {
        return $this->updated_at;
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
}
