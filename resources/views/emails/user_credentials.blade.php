<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>User Credentials</title>
</head>
<body style="font-family: Arial, sans-serif; color:#111; line-height:1.5;">
    <h2 style="margin-bottom:0.25rem;">
        @if($event === 'created') Welcome, {{ $user->name }}! @else Account Updated @endif
    </h2>
    <p style="margin-top:0; color:#555;">Hi {{ $user->name }},</p>

    @if($event === 'created')
        <p>Your account has been created successfully. Below are your login details:</p>
    @else
        <p>Your account details have been updated. Latest info is below:</p>
    @endif

    <table cellpadding="6" cellspacing="0" style="border-collapse:collapse; margin: 12px 0;">
        <tr>
            <td style="border:1px solid #ddd;"><strong>Email</strong></td>
            <td style="border:1px solid #ddd;">{{ $user->email }}</td>
        </tr>
        @if($plainPassword)
        <tr>
            <td style="border:1px solid #ddd;"><strong>Password</strong></td>
            <td style="border:1px solid #ddd;">{{ $plainPassword }}</td>
        </tr>
        @elseif($event === 'updated' && !$passwordChanged)
        <tr>
            <td style="border:1px solid #ddd;"><strong>Password</strong></td>
            <td style="border:1px solid #ddd;">(unchanged)</td>
        </tr>
        @endif
        <tr>
            <td style="border:1px solid #ddd;"><strong>Roles</strong></td>
            <td style="border:1px solid #ddd;">
                @if(!empty($roles))
                    {{ implode(', ', $roles) }}
                @else
                    (none)
                @endif
            </td>
        </tr>
    </table>

    @isset($loginUrl)
        <p>You can log in here: <a href="{{ $loginUrl }}">{{ $loginUrl }}</a></p>
    @endisset

    <p style="color:#666; font-size:12px; margin-top:20px;">
        If you didn’t request this, please contact support immediately.
    </p>
</body>
</html>
