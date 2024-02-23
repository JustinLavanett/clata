<?php

namespace TicketKitten\Exports;

use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use TicketKitten\DomainObjects\Enums\QuestionTypeEnum;
use TicketKitten\DomainObjects\QuestionDomainObject;
use TicketKitten\Resources\Attendee\AttendeeResource;
use TicketKitten\Service\Common\Question\QuestionAnswerFormatter;

readonly class AttendeesExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    private LengthAwarePaginator $data;
    private Collection $questions;

    public function __construct(private QuestionAnswerFormatter $questionAnswerFormatter)
    {
    }

    public function withData(LengthAwarePaginator $data, Collection $questions): AttendeesExport
    {
        $this->data = $data;
        $this->questions = $questions;
        return $this;
    }

    public function collection(): AnonymousResourceCollection
    {
        return AttendeeResource::collection($this->data);
    }

    public function headings(): array
    {
        $questionTitles = $this->questions->map(fn($question) => $question->getTitle())->toArray();

        return array_merge([
            'ID',
            'First Name',
            'Last Name',
            'Email',
            'Status',
            'Is Checked In',
            'Checked In At',
            'Ticket ID',
            'Event ID',
            'Public ID',
            'Short ID',
            'Created Date',
            'Last Updated Date'
        ], $questionTitles);
    }

    public function map($attendee): array
    {
        $answers = $this->questions->map(function (QuestionDomainObject $question) use ($attendee) {
            $answer = $attendee->getQuestionAndAnswerViews()
                ->first(fn($qav) => $qav->getQuestionId() === $question->getId())?->getAnswer() ?? '';

            return $this->questionAnswerFormatter->getAnswerAsText(
                $answer,
                QuestionTypeEnum::fromName($question->getType()),
            );
        });

        return array_merge([
            $attendee->getId(),
            $attendee->getFirstName(),
            $attendee->getLastName(),
            $attendee->getEmail(),
            $attendee->getStatus(),
            $attendee->getCheckedInAt() ? 'Yes' : 'No',
            $attendee->getCheckedInAt()
                ? Carbon::parse($attendee->getCheckedInAt())->format('Y-m-d H:i:s')
                : '',
            $attendee->getTicketId(),
            $attendee->getEventId(),
            $attendee->getPublicId(),
            $attendee->getShortId(),
            Carbon::parse($attendee->getCreatedAt())->format('Y-m-d H:i:s'),
            Carbon::parse($attendee->getUpdatedAt())->format('Y-m-d H:i:s'),
        ], $answers->toArray());
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
