<?php

declare(strict_types=1);

namespace TicketKitten\Validator;

use Illuminate\Routing\Route;
use TicketKitten\DomainObjects\Enums\QuestionBelongsTo;
use TicketKitten\DomainObjects\Generated\QuestionDomainObjectAbstract;
use TicketKitten\DomainObjects\Generated\TicketDomainObjectAbstract;
use TicketKitten\DomainObjects\QuestionDomainObject;
use TicketKitten\DomainObjects\TicketDomainObject;
use TicketKitten\DomainObjects\TicketPriceDomainObject;
use TicketKitten\Repository\Eloquent\Value\Relationship;
use TicketKitten\Repository\Interfaces\QuestionRepositoryInterface;
use TicketKitten\Repository\Interfaces\TicketRepositoryInterface;
use TicketKitten\Validator\Rules\AttendeeQuestionRule;
use TicketKitten\Validator\Rules\OrderQuestionRule;

class CompleteOrderValidator extends BaseValidator
{
    public function __construct(
        private readonly QuestionRepositoryInterface $questionRepository,
        private readonly TicketRepositoryInterface   $ticketRepository,
        private readonly Route                       $route
    )
    {
    }

    public function rules(): array
    {
        $questions = $this->questionRepository
            ->loadRelation(
                new Relationship(TicketDomainObject::class, [
                    new Relationship(TicketPriceDomainObject::class)
                ])
            )
            ->findWhere(
                [QuestionDomainObjectAbstract::EVENT_ID => $this->route->parameter('event_id')]
            );
        $orderQuestions = $questions->filter(
            fn(QuestionDomainObject $question) => $question->getBelongsTo() === QuestionBelongsTo::ORDER->name
        );
        $ticketQuestions = $questions->filter(
            fn(QuestionDomainObject $question) => $question->getBelongsTo() === QuestionBelongsTo::TICKET->name
        );

        $tickets = $this->ticketRepository
            ->loadRelation(TicketPriceDomainObject::class)
            ->findWhere(
                [TicketDomainObjectAbstract::EVENT_ID => $this->route->parameter('event_id')]
            );

        return [
            'order.first_name' => ['required', 'string', 'max:40'],
            'order.last_name' => ['required', 'string', 'max:40'],
            'order.questions' => new OrderQuestionRule($orderQuestions, $tickets),
            'order.email' => 'required|email',
            'attendees.*.first_name' => ['required', 'string', 'max:40'],
            'attendees.*.last_name' => ['required', 'string', 'max:40'],
            'attendees.*.email' => ['required', 'email'],
            'attendees' => new AttendeeQuestionRule($ticketQuestions, $tickets),

            // Address validation is intentionally not strict, as we want to support all countries
            'order.address.address_line_1' => ['string', 'max:255'],
            'order.address.address_line_2' => ['string', 'max:255', 'nullable'],
            'order.address.city' => ['string', 'max:85'],
            'order.address.state_or_region' => ['string', 'max:85'],
            'order.address.zip_or_postal_code' => ['string', 'max:85'],
            'order.address.country' => ['string', 'max:2'],
        ];
    }

    public function messages(): array
    {
        return [
            'order.first_name' => __('First name is required'),
            'order.last_name' => __('Last name is required'),
            'order.email' => __('A valid email is required'),
            'attendees.*.first_name' => __('First name is required'),
            'attendees.*.last_name' => __('Last name is required'),
            'attendees.*.email' => __('A valid email is required'),
        ];
    }
}
