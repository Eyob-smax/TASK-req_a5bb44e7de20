<?php

namespace App\Enums;

enum OrderTimelineEvent: string
{
    case Created          = 'created';
    case PaymentInitiated = 'payment_initiated';
    case Paid             = 'paid';
    case AutoClosed       = 'auto_closed';
    case Canceled         = 'canceled';
    case Refunded         = 'refunded';
    case Redeemed         = 'redeemed';
}
