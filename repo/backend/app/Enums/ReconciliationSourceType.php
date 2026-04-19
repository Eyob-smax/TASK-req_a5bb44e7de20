<?php

namespace App\Enums;

enum ReconciliationSourceType: string
{
    case Refund           = 'refund';
    case ManualAdjustment = 'manual_adjustment';
    case LedgerMismatch   = 'ledger_mismatch';
}
