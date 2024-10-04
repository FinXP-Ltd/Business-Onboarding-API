@component('mail::message')

Dear {{ $data['first_name'] }} {{ $data['last_name'] }},

Thank you for your interest in our services.

You can now proceed with your business application using our digital platform

@if(isset($data['note']['include_notes']) && $data['note']['include_notes'])
<i>{{ trim($data['note']['message']) }}</i>
@endif

@component('mail::button', ['url' => $data['url']])
Login
@endcomponent

Regards,<br>
{{ config('mail.from.name') }}
@endcomponent
