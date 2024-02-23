<?php

namespace TicketKitten\DomainObjects\Enums;

enum MessageTypeEnum
{
    use BaseEnum;

    case ORDER;
    case TICKET;
    case ATTENDEE;
    case EVENT;
}
