<?php
namespace Database\Seeders;

use App\Models\{Doctor,Patient,WorkType,LabOrder,Invoice,Payment,User};
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/** Fictional records only: no real patient information. */
class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('role', 'admin')->firstOrFail();
        DB::transaction(function () use ($admin): void {
            $doctors = [
                Doctor::firstOrCreate(['email'=>'demo-doctor1@example.test'],['name'=>'د. سامر أحمد (تجريبي)','phone'=>'000-000-0001','clinic_name'=>'عيادة النور - تجريبي','specialty'=>'تركيبات الأسنان']),
                Doctor::firstOrCreate(['email'=>'demo-doctor2@example.test'],['name'=>'د. لينا محمود (تجريبي)','phone'=>'000-000-0002','clinic_name'=>'عيادة الابتسامة - تجريبي','specialty'=>'طب الأسنان التجميلي']),
            ];
            $patients = [
                Patient::firstOrCreate(['name'=>'حالة تجريبية رقم ١'],['doctor_id'=>$doctors[0]->id,'phone'=>'000-111-0001']),
                Patient::firstOrCreate(['name'=>'حالة تجريبية رقم ٢'],['doctor_id'=>$doctors[1]->id,'phone'=>'000-111-0002']),
                Patient::firstOrCreate(['name'=>'حالة تجريبية رقم ٣'],['doctor_id'=>$doctors[0]->id,'phone'=>'000-111-0003']),
            ];
            $types = WorkType::orderBy('id')->take(3)->get();
            foreach (range(1, 9) as $n) {
                $patient = $patients[($n - 1) % 3];
                $doctor = $patient->doctor;
                $type = $types[($n - 1) % $types->count()];
                $status = ['received','designing','manufacturing','quality_control','ready','delivered','received','manufacturing','ready'][$n-1];
                $order = LabOrder::firstOrCreate(['order_no'=>sprintf('DEMO-%04d',$n)], [
                    'doctor_id'=>$doctor->id,'patient_id'=>$patient->id,'work_type_id'=>$type->id,
                    'received_at'=>today()->subDays(9-$n),'due_at'=>today()->addDays($n-4),
                    'status'=>$status,'priority'=>$n % 3 === 0 ? 'high' : 'normal',
                    'shade'=>'A2','material'=>'زيركون (تجريبي)','tooth_numbers'=>'11,12',
                    'instructions'=>'سجل اختبار وهمي للتوضيح فقط', 'price'=>$type->default_price,
                ]);
                $order->history()->firstOrCreate(['to_status'=>$status], ['user_id'=>$admin->id,'from_status'=>null,'note'=>'سجل بيانات تجريبية']);
                $invoice = Invoice::firstOrCreate(['lab_order_id'=>$order->id],[
                    'number'=>sprintf('DEMO-INV-%04d',$n),'subtotal'=>$order->price,'discount'=>0,'tax'=>0,
                    'total'=>$order->price,'issued_at'=>today()->subDays(9-$n),
                ]);
                if ($n % 2 === 0) {
                    Payment::firstOrCreate(['invoice_id'=>$invoice->id,'reference'=>'DEMO-PAY-'.$n], [
                        'user_id'=>$admin->id, 'amount'=>round((float)$invoice->total / 2,2),
                        'method'=>'cash','paid_at'=>today(),'notes'=>'دفعة وهمية لغرض العرض',
                    ]);
                }
            }
        });
    }
}
