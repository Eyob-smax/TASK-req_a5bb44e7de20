<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\LedgerEntryType;
use App\Enums\ReconciliationSourceType;
use App\Enums\ReconciliationStatus;
use App\Enums\RefundStatus;
use App\Models\Bill;
use App\Models\LedgerEntry;
use App\Models\ReconciliationFlag;
use App\Models\Refund;
use App\Models\RefundReasonCode;
use App\Models\User;
use App\Support\AuditLogger;
use CampusLearn\Billing\Money;
use CampusLearn\Billing\RefundPolicy;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use RuntimeException;

final class RefundService
{
    public function __construct(
        private readonly RefundPolicy $refundPolicy,
        private readonly AuditLogger $audit,
        private readonly NotificationOrchestrator $notifications,
    ) {
    }

    public function request(Bill $bill, int $amountCents, string $reasonCode, User $operator): Refund
    {
        return DB::transaction(function () use ($bill, $amountCents, $reasonCode, $operator): Refund {
            $decision = $this->refundPolicy->evaluate(
                Money::ofCents($bill->paid_cents),
                Money::ofCents($bill->refunded_cents),
                Money::ofCents($amountCents),
                $reasonCode,
            );

            if (! $decision->allowed) {
                throw new RuntimeException($decision->rejectionMessage ?? 'Refund not allowed.');
            }

            $reasonRow = RefundReasonCode::where('code', $reasonCode)->first();

            return Refund::create([
                'bill_id'          => $bill->id,
                'amount_cents'     => $amountCents,
                'reason_code_id'   => $reasonRow?->id,
                'operator_user_id' => $operator->id,
                'status'           => RefundStatus::Pending,
                'notes'            => null,
                'approved_at'      => null,
                'completed_at'     => null,
                'created_at'       => now(),
            ]);
        });
    }

    public function approve(Refund $refund, User $approver): Refund
    {
        return DB::transaction(function () use ($refund, $approver): Refund {
            if ($refund->status !== RefundStatus::Pending) {
                throw new RuntimeException('Refund is not in pending state.');
            }

            $refund->update([
                'status'      => RefundStatus::Approved,
                'approved_at' => now(),
            ]);

            $paymentEntry = LedgerEntry::where('bill_id', $refund->bill_id)
                ->where('entry_type', LedgerEntryType::Payment)
                ->orderByDesc('created_at')
                ->first();

            $reversalEntry = LedgerEntry::create([
                'user_id'            => $refund->bill->user_id,
                'bill_id'            => $refund->bill_id,
                'entry_type'         => LedgerEntryType::Reversal,
                'amount_cents'       => -$refund->amount_cents,
                'description'        => 'Refund reversal on bill #' . $refund->bill_id,
                'reference_entry_id' => $paymentEntry?->id,
                'correlation_id'     => (string) \Illuminate\Support\Str::uuid(),
                'created_at'         => now(),
            ]);

            LedgerEntry::create([
                'user_id'            => $refund->bill->user_id,
                'bill_id'            => $refund->bill_id,
                'entry_type'         => LedgerEntryType::Refund,
                'amount_cents'       => -$refund->amount_cents,
                'description'        => 'Refund #' . $refund->id,
                'reference_entry_id' => $reversalEntry->id,
                'correlation_id'     => (string) \Illuminate\Support\Str::uuid(),
                'created_at'         => now(),
            ]);

            $bill = $refund->bill;
            $bill->increment('refunded_cents', $refund->amount_cents);

            $refund->update([
                'status'                   => RefundStatus::Completed,
                'completed_at'             => now(),
                'reversal_ledger_entry_id' => $reversalEntry->id,
            ]);

            ReconciliationFlag::create([
                'source_type' => ReconciliationSourceType::Refund,
                'source_id'   => $refund->id,
                'status'      => ReconciliationStatus::Open,
            ]);

            $this->audit->record($approver->id, 'refund.completed', 'refund', $refund->id, [
                'bill_id'      => $refund->bill_id,
                'amount_cents' => $refund->amount_cents,
            ]);

            $this->notifications->notify('billing.refund', [$refund->bill->user_id], [
                ':amount' => number_format($refund->amount_cents / 100, 2),
            ]);

            return $refund->fresh();
        });
    }

    public function reject(Refund $refund, User $actor, string $reason): Refund
    {
        return DB::transaction(function () use ($refund, $actor, $reason): Refund {
            if ($refund->status !== RefundStatus::Pending) {
                throw new RuntimeException('Refund is not in pending state.');
            }

            $refund->update([
                'status' => RefundStatus::Rejected,
                'notes'  => $reason,
            ]);

            $this->audit->record($actor->id, 'refund.rejected', 'refund', $refund->id, [
                'reason' => $reason,
            ]);

            return $refund->fresh();
        });
    }

    public function list(User $actor, int $perPage = 20): LengthAwarePaginator
    {
        $isStaff = $actor->roleAssignments()
            ->whereHas('role', fn ($q) => $q->whereIn('name', ['administrator', 'registrar']))
            ->whereNull('revoked_at')
            ->exists();

        return Refund::with(['bill', 'operator', 'reasonCode'])
            ->when(! $isStaff, fn ($q) => $q->whereHas('bill', fn ($bq) => $bq->where('user_id', $actor->id)))
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }

    public function reasonCodes(): Collection
    {
        return RefundReasonCode::orderBy('code')->get();
    }
}
