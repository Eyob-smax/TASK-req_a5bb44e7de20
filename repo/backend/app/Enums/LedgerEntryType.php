<?php

namespace App\Enums;

enum LedgerEntryType: string
{
    case Charge        = 'charge';
    case Payment       = 'payment';
    case Refund        = 'refund';
    case Reversal      = 'reversal';
    case Penalty       = 'penalty';
    case TaxAdjustment = 'tax_adjustment';
}
