<x-mail::message>
# Let's Reset

You are receiving this email because we received a password reset request for your account.
Please click the following link to reset your password:

<x-mail::button :url="$url">
Reset Password
</x-mail::button>

Regards,<br>
{{ config('mail.from.name') }}
</x-mail::message>
