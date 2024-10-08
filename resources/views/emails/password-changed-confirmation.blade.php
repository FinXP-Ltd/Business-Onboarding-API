<x-mail::message>
# Your Password has been Changed

This is to confirm that the password for your account has been successfully changed. <br>
Your account is now secured with the new password that you have set. <br>

If you did not change your password, please contact us immediately to report any
unauthorized access to your account.<br>

If you have any issues or concerns regarding your account,
please do not hesitate to contact our customer support team for further assistance.<br>

Regards,<br>
{{ config('mail.from.name') }} <br>

<x-mail::button :url="config('app.asset_url')">
Go to my Account
</x-mail::button>

</x-mail::message>
