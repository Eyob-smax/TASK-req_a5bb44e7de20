<?php

namespace App\Enums;

enum AppointmentResourceType: string
{
    case Facility         = 'facility';
    case RegistrarMeeting = 'registrar_meeting';
    case Generic          = 'generic';
}
