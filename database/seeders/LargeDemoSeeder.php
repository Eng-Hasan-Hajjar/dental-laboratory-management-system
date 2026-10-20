<?php

namespace Database\Seeders;

use App\Models\Doctor;
use App\Models\Invoice;
use App\Models\LabOrder;
use App\Models\Patient;
use App\Models\User;
use App\Models\WorkType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Import a repeatable, fully fictional Excel/CSV demonstration dataset.
 * Only runs on local/testing and keeps existing admin/real records unchanged.
 * Read IMPORT_GUIDE_AR.md for usage and safety.
 */
class LargeDemoSeeder extends Seeder
{
    private string $folder;

    public function run(): void
    {
        if (! app()->environment('local', 'testing')) {
            throw new RuntimeException('Synthetic seeding is restricted to APP_ENV=local or testing.');
        }

        $this->folder = database_path('seeders/demo_csv');
        $admin = User::where('role', 'admin')->firstOrFail();
        $counts = [];

        DB::transaction(function () use ($admin, &$counts): void {
            $staff = ['ADMIN' => $admin->id];
            foreach ($this->csv('staff') as $row) {
                $record = User::firstOrCreate(['email' => $row['email']], [
                    'name' => $row['name'], 'role' => $row['role'],
                    'is_active' => (bool)$row['is_active'],
                    'password' => Hash::make(Str::random(48)),
                ]);
                $staff[$row['ref']] = $record->id;
                $counts['staff'] = ($counts['staff'] ?? 0) + (int)$record->wasRecentlyCreated;
            }

            $doctors = [];
            foreach ($this->csv('doctors') as $row) {
                $record = Doctor::firstOrCreate(['email' => $row['email']], [
                    'name' => $row['name'], 'phone' => $row['phone'],
                    'specialty' => $row['specialty'], 'clinic_name' => $row['clinic_name'],
                    'address' => $row['address'], 'notes' => $row['notes'],
                ]);
                $doctors[$row['ref']] = $record->id;
                $counts['doctors'] = ($counts['doctors'] ?? 0) + (int)$record->wasRecentlyCreated;
            }

            $types = [];
            foreach ($this->csv('work_types') as $row) {
                $record = WorkType::firstOrCreate(['name' => $row['name']], [
                    'description' => $row['description'], 'default_price' => $row['default_price'],
                    'estimated_days' => (int)$row['estimated_days'], 'is_active' => (bool)$row['is_active'],
                ]);
                $types[$row['ref']] = $record->id;
                $counts['work_types'] = ($counts['work_types'] ?? 0) + (int)$record->wasRecentlyCreated;
            }

            $patients = [];
            $patientDoctors = [];
            foreach ($this->csv('patients') as $row) {
                $doctorId = $this->get($doctors, $row['doctor_ref'], 'doctor');
                $record = Patient::firstOrCreate(['phone' => $row['phone']], [
                    'doctor_id' => $doctorId, 'name' => $row['name'],
                    'date_of_birth' => $row['date_of_birth'], 'gender' => $row['gender'],
                    'notes' => $row['notes'],
                ]);
                if ($record->doctor_id !== null && (int)$record->doctor_id !== (int)$doctorId) {
                    throw new RuntimeException("Patient/doctor mismatch for {$row['ref']}");
                }
                $patients[$row['ref']] = $record->id;
                $patientDoctors[$row['ref']] = $doctorId;
                $counts['patients'] = ($counts['patients'] ?? 0) + (int)$record->wasRecentlyCreated;
            }

            $orders = [];
            $createdOrders = [];
            foreach ($this->csv('lab_orders') as $row) {
                $doctorId = $this->get($doctors, $row['doctor_ref'], 'doctor');
                $patientId = $this->get($patients, $row['patient_ref'], 'patient');
                if ($patientDoctors[$row['patient_ref']] !== $doctorId) {
                    throw new RuntimeException("Patient belongs to another doctor: {$row['ref']}");
                }
                if (! array_key_exists($row['status'], LabOrder::STATUSES)) {
                    throw new RuntimeException("Unsupported order status: {$row['status']}");
                }
                if ($row['due_at'] < $row['received_at']) {
                    throw new RuntimeException("Due date precedes received date: {$row['ref']}");
                }
                $record = LabOrder::firstOrCreate(['order_no' => $row['order_no']], [
                    'doctor_id' => $doctorId, 'patient_id' => $patientId,
                    'work_type_id' => $this->get($types, $row['work_type_ref'], 'work type'),
                    'assigned_to' => $row['assigned_to_ref'] !== '' ? $this->get($staff, $row['assigned_to_ref'], 'staff') : null,
                    'shade' => $row['shade'], 'material' => $row['material'],
                    'tooth_numbers' => $row['tooth_numbers'], 'instructions' => $row['instructions'],
                    'received_at' => $row['received_at'], 'due_at' => $row['due_at'],
                    'price' => $row['price'], 'priority' => $row['priority'], 'status' => $row['status'],
                ]);
                if ((int)$record->doctor_id !== (int)$doctorId || (int)$record->patient_id !== (int)$patientId) {
                    throw new RuntimeException("Existing order reference mismatch: {$row['order_no']}");
                }
                if ($record->wasRecentlyCreated) {
                    DB::table('lab_orders')->where('id', $record->id)->update([
                        'created_at' => $row['created_at'], 'updated_at' => $row['created_at'],
                    ]);
                    $createdOrders[$row['ref']] = true;
                }
                $orders[$row['ref']] = $record->id;
                $counts['lab_orders'] = ($counts['lab_orders'] ?? 0) + (int)$record->wasRecentlyCreated;
            }

            $invoices = [];
            $invoiceTotals = [];
            foreach ($this->csv('invoices') as $row) {
                $orderId = $this->get($orders, $row['order_ref'], 'order');
                $expected = (int)round(((float)$row['subtotal'] - (float)$row['discount'] + (float)$row['tax']) * 100);
                $total = (int)round((float)$row['total'] * 100);
                if ($expected < 0 || $expected !== $total) {
                    throw new RuntimeException("Invalid invoice calculation: {$row['number']}");
                }
                $record = Invoice::firstOrCreate(['number' => $row['number']], [
                    'lab_order_id' => $orderId, 'subtotal' => $row['subtotal'],
                    'discount' => $row['discount'], 'tax' => $row['tax'],
                    'total' => $row['total'], 'issued_at' => $row['issued_at'],
                    'notes' => $row['notes'],
                ]);
                if ((int)$record->lab_order_id !== (int)$orderId) {
                    throw new RuntimeException("Invoice links to another order: {$row['number']}");
                }
                if ($record->wasRecentlyCreated) {
                    DB::table('invoices')->where('id', $record->id)->update([
                        'created_at' => $row['issued_at'].' 11:00:00',
                        'updated_at' => $row['issued_at'].' 11:00:00',
                    ]);
                }
                $invoices[$row['ref']] = $record->id;
                $invoiceTotals[$row['ref']] = $total;
                $counts['invoices'] = ($counts['invoices'] ?? 0) + (int)$record->wasRecentlyCreated;
            }

            $paymentsSum = [];
            foreach ($this->csv('payments') as $row) {
                $invoiceId = $this->get($invoices, $row['invoice_ref'], 'invoice');
                $amount = (int)round((float)$row['amount'] * 100);
                if ($amount <= 0 || ! in_array($row['method'], ['cash','bank','card','other'], true)) {
                    throw new RuntimeException("Invalid payment: {$row['ref']}");
                }
                $paymentsSum[$row['invoice_ref']] = ($paymentsSum[$row['invoice_ref']] ?? 0) + $amount;
                if ($paymentsSum[$row['invoice_ref']] > $invoiceTotals[$row['invoice_ref']]) {
                    throw new RuntimeException("Payments exceed invoice total: {$row['invoice_ref']}");
                }
                $record = DB::table('payments')->where('invoice_id', $invoiceId)
                    ->where('reference', $row['reference'])->first();
                if (! $record) {
                    DB::table('payments')->insert([
                        'invoice_id' => $invoiceId,
                        'user_id' => $this->get($staff, $row['user_ref'], 'staff'),
                        'amount' => $row['amount'], 'method' => $row['method'],
                        'reference' => $row['reference'], 'paid_at' => $row['paid_at'],
                        'notes' => $row['notes'],
                        'created_at' => $row['paid_at'].' 12:00:00',
                        'updated_at' => $row['paid_at'].' 12:00:00',
                    ]);
                    $counts['payments'] = ($counts['payments'] ?? 0) + 1;
                }
            }

            // On a repeated run, histories for already-seeded orders are left untouched.
            // Every import is atomic, so a failed first run rolls back all its histories.
            $lastByOrder = [];
            $stageRows = [];
            foreach ($this->csv('order_stage_histories') as $row) {
                $orderRef = $row['order_ref'];
                $orderId = $this->get($orders, $orderRef, 'order');
                if (! isset($createdOrders[$orderRef])) {
                    continue;
                }
                $from = $lastByOrder[$orderRef] ?? '';
                if ($from !== $row['from_status']) {
                    throw new RuntimeException("Broken stage history chain for {$orderRef}");
                }
                if ($from && ! in_array($row['to_status'], LabOrder::TRANSITIONS[$from] ?? [], true)) {
                    throw new RuntimeException("Illegal stage transition for {$orderRef}");
                }
                if (! $from && $row['to_status'] !== 'received') {
                    throw new RuntimeException("First status must be 'received': {$orderRef}");
                }
                $lastByOrder[$orderRef] = $row['to_status'];
                $stageRows[] = [
                    'lab_order_id' => $orderId,
                    'user_id' => $this->get($staff, $row['user_ref'], 'staff'),
                    'from_status' => $row['from_status'] !== '' ? $row['from_status'] : null,
                    'to_status' => $row['to_status'], 'note' => $row['note'],
                    'created_at' => $row['created_at'], 'updated_at' => $row['created_at'],
                ];
                if (count($stageRows) >= 400) {
                    DB::table('order_stage_histories')->insert($stageRows);
                    $counts['order_stage_histories'] = ($counts['order_stage_histories'] ?? 0) + count($stageRows);
                    $stageRows = [];
                }
            }
            if ($stageRows) {
                DB::table('order_stage_histories')->insert($stageRows);
                $counts['order_stage_histories'] = ($counts['order_stage_histories'] ?? 0) + count($stageRows);
            }
            // Check every newly imported order agrees with its chronological stage chain.
            foreach ($lastByOrder as $ref => $status) {
                $actualStatus = DB::table('lab_orders')->where('id',$orders[$ref])->value('status');
                if ($status !== $actualStatus) {
                    throw new RuntimeException("Order status differs from history: {$ref}");
                }
            }
        }, 3);

        foreach ($counts as $name => $count) {
            $this->command?->info("{$name}: +{$count}");
        }
        $this->command?->info('Synthetic dental laboratory data imported successfully.');
    }

    private function get(array $lookup, string $reference, string $kind): int
    {
        if (! array_key_exists($reference, $lookup)) {
            throw new RuntimeException("Unknown {$kind} reference: {$reference}");
        }
        return (int)$lookup[$reference];
    }

    private function csv(string $table): \Generator
    {
        $path = $this->folder.'/'.$table.'.csv';
        $handle = fopen($path, 'r');
        if ($handle === false) {
            throw new RuntimeException("Cannot open {$path}");
        }
        try {
            $header = fgetcsv($handle);
            if (! $header) {
                throw new RuntimeException("Missing CSV header in {$table}");
            }
            $header[0] = preg_replace('/^\xEF\xBB\xBF/', '', $header[0]);
            while (($values = fgetcsv($handle)) !== false) {
                if (count($values) === 1 && $values[0] === null) {
                    continue;
                }
                if (count($values) !== count($header)) {
                    throw new RuntimeException("Invalid CSV column count in {$table}");
                }
                yield array_combine($header, $values);
            }
        } finally {
            fclose($handle);
        }
    }
}
