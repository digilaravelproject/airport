{{-- resources/views/emails/user-credentials.blade.php --}}
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $event === 'created' ? 'Welcome!' : 'Account Updated' }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <style>
        /* Email-safe, inline-like styles used below.
           Keep CSS small and simple for better email client compatibility. */
        body {
            margin: 0;
            padding: 0;
            background-color: #f1f6f9;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            color: #324055;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }
        .wrap {
            width: 100%;
            padding: 28px 16px;
            box-sizing: border-box;
        }
        .center {
            max-width: 680px;
            margin: 0 auto;
        }

        /* outer panel (left + right pale columns in screenshot) */
        .panel {
            background: linear-gradient(90deg, rgba(235,242,247,1) 0%, rgba(255,255,255,1) 20%, rgba(255,255,255,1) 80%, rgba(235,242,247,1) 100%);
            padding: 28px;
            border-radius: 6px;
        }

        /* card */
        .card {
            background: #ffffff;
            border-radius: 6px;
            box-shadow: 0 6px 18px rgba(50,64,85,0.06);
            overflow: hidden;
            border: 1px solid rgba(0,0,0,0.03);
        }
        .card-body {
            padding: 28px;
        }

        h1 {
            margin: 0 0 6px 0;
            font-size: 20px;
            color: #222a3a;
        }
        p.lead {
            margin: 0 0 18px 0;
            color: #5f6b7a;
            line-height: 1.6;
        }

        /* CTA button */
        .btn {
            display:inline-block;
            padding: 10px 18px;
            background: #1f2937; /* dark slate */
            color: #fff !important;
            text-decoration: none !important;
            border-radius: 6px;
            font-weight: 600;
            font-size: 14px;
            box-shadow: 0 4px 10px rgba(31,41,55,0.08);
        }

        /* small helper text (time, fallback link) */
        .muted {
            color: #8b97a6;
            font-size: 13px;
            line-height: 1.5;
        }

        /* table for credentials */
        .cred-table {
            width: 100%;
            border-collapse: collapse;
            margin: 14px 0 18px 0;
        }
        .cred-table td {
            padding: 8px 10px;
            border: 1px solid #eef3f7;
            vertical-align: middle;
            font-size: 14px;
        }
        .cred-key { width: 120px; font-weight: 700; color:#344054; background: #fbfcfe; }
        .cred-val { color:#1f2937; }

        .footer {
            border-top: 1px solid #f0f3f6;
            padding: 18px 28px;
            font-size: 13px;
            color: #8b97a6;
        }

        /* small responsive tweaks */
        @media (max-width:420px) {
            .card-body { padding: 18px; }
            .panel { padding: 18px; }
            .cred-key { width: 110px; font-size:13px; }
        }
    </style>
</head>
<body>
<div class="wrap">
    <div class="center">
        <div class="panel">
            <div class="card" role="article" aria-label="{{ $event === 'created' ? 'Welcome email' : 'Account update' }}">
                <div class="card-body">
                    <h1>
                        @if($event === 'created')
                            Welcome, {{ $user->name }}!
                        @else
                            Account details updated
                        @endif
                    </h1>

                    <p class="lead">
                        @if($event === 'created')
                            You are receiving this email because your account has been created successfully.
                        @else
                            This is a confirmation that your account information was updated.
                        @endif
                    </p>

                    {{-- Credentials table --}}
                    <table class="cred-table" cellpadding="0" cellspacing="0" role="presentation">
                        <tr>
                            <td class="cred-key">Email</td>
                            <td class="cred-val">{{ $user->email }}</td>
                        </tr>

                        @if(!empty($plainPassword))
                        <tr>
                            <td class="cred-key">Password</td>
                            <td class="cred-val" style="letter-spacing:0.3px;">{{ $plainPassword }}</td>
                        </tr>
                        @elseif($event === 'updated' && isset($passwordChanged) && !$passwordChanged)
                        <tr>
                            <td class="cred-key">Password</td>
                            <td class="cred-val">(unchanged)</td>
                        </tr>
                        @endif

                        <tr>
                            <td class="cred-key">Roles</td>
                            <td class="cred-val">
                                @if(!empty($roles) && is_array($roles))
                                    {{ implode(', ', $roles) }}
                                @else
                                    (none)
                                @endif
                            </td>
                        </tr>
                    </table>

                    {{-- primary action (login or reset) --}}
                    <div style="margin:14px 0;">
                        @if(!empty($loginUrl))
                            <a href="{{ $loginUrl }}" class="btn" target="_blank" rel="noopener">Log in</a>
                        @elseif(!empty($resetUrl))
                            <a href="{{ $resetUrl }}" class="btn" target="_blank" rel="noopener">Reset Password</a>
                        @endif
                    </div>

                    <p class="muted">
                        This link will expire in 60 minutes.
                    </p>

                    <p class="muted" style="font-size:13px;">
                        You can change the password using forgot password.
                    </p>

                    <p class="muted" style="font-size:13px;">
                        If you didn't request this, you can safely ignore this email or contact support.
                    </p>
                </div>

                <div class="footer">
                    <p style="margin:0 0 10px 0;">
                        Regards,<br>
                        <strong>{{ config('app.name', 'Application') }}</strong>
                    </p>

                    {{-- Fallback URL (raw link) --}}
                    @if(!empty($loginUrl) || !empty($resetUrl))
                        <p style="margin:0; font-size:12px; color:#9aa9b6;">
                            If the button above does not work, paste this URL into your browser:
                            <br>
                            <a href="{{ $loginUrl ?? $resetUrl }}" target="_blank" rel="noopener" style="color:#0d6efd;">
                                {{ $loginUrl ?? $resetUrl }}
                            </a>
                        </p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
