<?php

namespace HiEvents\Mail\Order;

use HiEvents\DomainObjects\EventDomainObject;
use HiEvents\DomainObjects\OrderDomainObject;
use HiEvents\Helper\Url;
use HiEvents\Mail\BaseMail;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/**
 * @uses /backend/resources/views/emails/orders/order-failed.blade.php
 */
class OrderFailed extends BaseMail
{
    private OrderDomainObject $orderDomainObject;

    private EventDomainObject $eventDomainObject;

    public function __construct(OrderDomainObject $order, EventDomainObject $event)
    {
        parent::__construct();

        $this->orderDomainObject = $order;
        $this->eventDomainObject = $event;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('Your order wasn\'t successful'),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.orders.order-failed',
            with: [
                'event' => $this->eventDomainObject,
                'order' => $this->orderDomainObject,
                'eventUrl' => sprintf(
                    Url::getFrontEndUrlFromConfig(Url::EVENT_HOMEPAGE),
                    $this->eventDomainObject->getId(),
                    $this->eventDomainObject->getSlug(),
                )
            ]
        );
    }
}
