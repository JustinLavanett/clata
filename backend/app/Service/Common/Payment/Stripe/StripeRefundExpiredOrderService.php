<?php

namespace TicketKitten\Service\Common\Payment\Stripe;

use Brick\Math\Exception\MathException;
use Brick\Math\Exception\NumberFormatException;
use Brick\Math\Exception\RoundingNecessaryException;
use Brick\Money\Exception\UnknownCurrencyException;
use Illuminate\Contracts\Mail\Mailer;
use Psr\Log\LoggerInterface;
use Stripe\Exception\ApiErrorException;
use Stripe\PaymentIntent;
use TicketKitten\DomainObjects\OrderDomainObject;
use TicketKitten\DomainObjects\StripePaymentDomainObject;
use TicketKitten\Mail\PaymentSuccessButOrderExpiredMail;
use TicketKitten\Repository\Interfaces\EventRepositoryInterface;
use TicketKitten\ValuesObjects\MoneyValue;

readonly class StripeRefundExpiredOrderService
{
    public function __construct(
        private StripePaymentIntentRefundService $refundService,
        private Mailer                           $mailer,
        private LoggerInterface                  $logger,
        private EventRepositoryInterface         $eventRepository,
    )
    {
    }

    /**
     * @throws ApiErrorException
     * @throws RoundingNecessaryException
     * @throws MathException
     * @throws UnknownCurrencyException
     * @throws NumberFormatException
     */
    public function refundExpiredOrder(
        PaymentIntent             $paymentIntent,
        StripePaymentDomainObject $stripePayment,
        OrderDomainObject         $order,
    ): void
    {
        $event = $this->eventRepository->findById($order->getEventId());

        $this->refundService->refundPayment(
            MoneyValue::fromMinorUnit($paymentIntent->amount, strtoupper($paymentIntent->currency)),
            $stripePayment,
        );

        $this->mailer->to($order->getEmail())->send(new PaymentSuccessButOrderExpiredMail(
            order: $order,
            event: $event,
        ));

        $this->logger->info('Refunded expired order', [
            'order_id' => $order->getId(),
            'event_id' => $event->getId(),
            'payment_intent_id' => $paymentIntent->id,
        ]);
    }
}
