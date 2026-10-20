# التحليل التقني — نظام إدارة مخابر الأسنان

## الهدف

توحيد السجل المخبرى للأطباء والمرضى والأعمال والأسعار والفواتير. يقدم Laravel 12 خدمات HTTP MVC وREST API، وتقوم Blade بعرض واجهات RTL، ويخزن MySQL البيانات الأساسية.

## طبقات النظام

1. **Presentation:** `resources/views` + `public/css/app.css` + `public/js/app.js`.
2. **Routing & Authorization:** `routes/web.php`, `routes/api.php`, `EnsureRole`, `EnsureActiveUser`.
3. **Application controllers:** التحقق من المدخلات، وتنظيم معاملات قاعدة البيانات، وإرسال نتائج HTTP.
4. **Eloquent models:** العلاقات، الأنواع والحالات، الاحتساب المشتق للرصيد.
5. **Persistence:** ملفات Migrations + قاعدة MySQL بترميز UTF8MB4.

## العلاقات (ERD نصي)

```text
Doctor    1 ─────── N Patient
Doctor    1 ─────── N LabOrder
Patient   1 ─────── N LabOrder
WorkType  1 ─────── N LabOrder
User      1 ─────── N LabOrder (assigned_to, optional)
LabOrder  1 ─────── N OrderStageHistory
User      1 ─────── N OrderStageHistory (actor)
LabOrder  1 ─────── 1 Invoice
Invoice   1 ─────── N Payment
User      1 ─────── N Payment (actor)
User      1 ─────── N PersonalAccessToken
```

## المسارات الأساسية

- Dashboard: `/dashboard`
- Doctors: `/doctors`
- Patients: `/patients`
- Lab orders: `/orders`
- Work types: `/work-types`
- Invoices and payments: `/invoices`
- Reports: `/reports`
- Users: `/users`
- API login: `/api/v1/login`

## سير دورة الطلب

```text
received → designing → manufacturing → quality_control → ready → delivered
                         ↑                  ↓                 ↓
                         └──── rework ─────┘       quality_control (return)
```

- الإلغاء مسموح في المراحل `received`, `designing`, `manufacturing`.
- في `delivered` و`cancelled` لا يوجد انتقال لاحق.
- كل انتقال يضاف إلى `order_stage_histories` ولا يتم تغيير الحالة مباشرة من نموذج التعديل.
- موظف `technician` يمكنه تحديث طلب مكلّف به فقط.

## المحاسبة

- عند إنشاء `LabOrder`، تُنشأ `Invoice` بالقيمة التقديرية نفسها، ضمن معاملة واحدة.
- يمكن تعديل الخصم والإضافات، لكن لا يمكن إنقاص الإجمالي عن مجموع المدفوعات المسجلة.
- الدفعات تحفظ في `payments` ولا يمكن تسجيل مبلغ أكبر من المتبقي.
- المدفوعات محفوظة كحركات مالية منفصلة ولا يوفر الإصدار الأكاديمي حذفها أو إلغاءها؛ الإلغاء المحاسبي والتسويات يحتاجان ميزات إضافية وسجلات تدقيق في نسخة الإنتاج.
- وحدة العملة في نموذج التخرج الافتراضي: USD، قابلة للتخصيص لاحقاً.

## بنود التوسعة المقترحة

- صلاحيات دقيقة عبر Policies بدلاً من أدوار ثابتة.
- سجل تدقيق لتعديل بيانات المرضى والفواتير والمبالغ (Audit Trail).
- رفع ملفات صور الأشعة ضمن تخزين خاص وآمن ومصرح به.
- إشعارات آلية وQueue بجدول `jobs` وجدولة Scheduler.
- تصدير PDF للفواتير، طابعة حرارية، دعم العملات المحلية.
- حجز مخزون المواد وتتبع مخزون البورسلان والزيركون والتكاليف.
- تكامل REST API خارجي مع بوابة الطبيب أو تطبيق الجوال.

## ملاحظات السلامة

يجب عدم استخدام السجلات التجريبية لتمثيل أشخاص حقيقيين. ينبغي نقل التطبيق إلى HTTPS، واستخدام حسابات فردية، وتفعيل النسخ الاحتياطي قبل تخزين أي بيانات حقيقية. يتطلب الاستخدام المهني مراجعة فنية وأمنية وتشريعية مستقلة.
