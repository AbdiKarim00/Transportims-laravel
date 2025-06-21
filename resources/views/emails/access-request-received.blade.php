@component('mail::message')
# Access Request Received

Dear {{ $requestData['name'] }},

Thank you for requesting access to the Portal. We have received your request and will review it shortly.

**Request Details:**
- Name: {{ $requestData['name'] }}
- Email: {{ $requestData['email'] }}
- Department: {{ $requestData['department'] }}
@if($requestData['message'])
- Message: {{ $requestData['message'] }}
@endif

We will process your request and get back to you as soon as possible. If you have any questions in the meantime, please don't hesitate to contact us.

Thanks,<br>
{{ config('app.name') }}
@endcomponent 