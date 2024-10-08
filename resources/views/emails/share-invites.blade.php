@component('mail::message')

# An Application was shared

Dear {{ $name }},

You are receiving this email because you're invited to check an application

Company: {{ $companyName }}

@component('mail::button', ['url' => $url])
Login
@endcomponent

Regards,<br>
{{ config('mail.from.name') }}
@endcomponent
