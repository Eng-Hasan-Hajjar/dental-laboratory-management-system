# API examples — Laravel Sanctum

### Token

POST `/api/v1/login`

```json
{"email":"admin@yourdomain.test","password":"YOUR_PASSWORD","device_name":"web-admin"}
```

### Create order

POST `/api/v1/orders` with `Authorization: Bearer TOKEN`

```json
{
 "doctor_id":1,"patient_id":1,"work_type_id":1,
 "received_at":"2026-10-20","due_at":"2026-10-27",
 "price":120.00,"priority":"normal",
 "shade":"A2","material":"Zircon","tooth_numbers":"11,12",
 "instructions":"Check occlusion"
}
```

Expected on success: HTTP `201` with created order and invoice.

### Change stage

PATCH `/api/v1/orders/1/status`

```json
{"status":"designing","note":"Started design"}
```

Workflow: `received → designing → manufacturing → quality_control → ready → delivered`, with rework from quality control to manufacturing, and permitted cancellation from the earlier stages.

All API authenticated routes need the `Accept: application/json` header and Bearer token. IDs shown here are illustrative; use actual database IDs. Default pagination is 15, maximum 50 per page.
