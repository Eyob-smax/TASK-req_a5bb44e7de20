<?php

namespace App\Enums;

enum OrderStatus: string
{
    case PendingPayment = 'pending_payment';
    case Paid           = 'paid';
    case Canceled       = 'canceled';
    case Refunded       = 'refunded';
    case Redeemed       = 'redeemed';
}
