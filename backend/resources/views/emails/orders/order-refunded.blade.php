@php /** @var \TicketKitten\DomainObjects\OrderDomainObject $order */ @endphp
@php /** @var \TicketKitten\DomainObjects\EventDomainObject $event */ @endphp
@php /** @var \TicketKitten\ValuesObjects\MoneyValue $refundAmount */ @endphp

<x-mail::message>
Hello,

You have received a refund of <b>{{$refundAmount}}</b> for the following event: <b>{{$event->getTitle()}}</b>.

Thank you
</x-mail::message>
