@php /** @uses /backend/app/Mail/OrderSummary.php */ @endphp
@php /** @var \HiEvents\DomainObjects\OrderDomainObject $order */ @endphp
@php /** @var \HiEvents\DomainObjects\EventDomainObject $event */ @endphp
@php /** @var string $ticketUrl */ @endphp
@php /** @see \HiEvents\Mail\Attendee\AttendeeTicketMail */ @endphp

<x-mail::message>
# You're going to {{ $event->getTitle() }}! 🎉
<br>
<br>

Please find your ticket details below.

<x-mail::button :url="$ticketUrl">
    View Ticket
</x-mail::button>

If you have any questions or need assistance, feel free to reach out to our friendly support team
at {{ $supportEmail ?? 'hello@hi.events' }}.

Best regards,
<br>
{{config('app.name')}}
</x-mail::message>
