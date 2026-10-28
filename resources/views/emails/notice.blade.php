<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }}</title>
</head>
<body style="margin:0;padding:0;background-color:#f1f5f9;font-family:Arial,Helvetica,sans-serif;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f1f5f9;padding:24px 12px;">
    <tr>
        <td align="center">
            <table role="presentation" width="560" cellpadding="0" cellspacing="0" style="max-width:560px;width:100%;background-color:#ffffff;border:1px solid #e2e8f0;border-radius:12px;">
                <tr>
                    <td style="padding:24px 28px 8px;">
                        <p style="margin:0;font-size:12px;font-weight:bold;letter-spacing:.08em;text-transform:uppercase;color:#3d5a80;">StayTrack &middot; Apartment Management</p>
                        <h1 style="margin:8px 0 0;font-size:20px;line-height:28px;color:#0f172a;">{{ $title }}</h1>
                    </td>
                </tr>
                <tr>
                    <td style="padding:8px 28px 0;">
                        @foreach($lines as $line)
                            <p style="margin:0 0 12px;font-size:14px;line-height:22px;color:#334155;">{{ $line }}</p>
                        @endforeach
                    </td>
                </tr>
                @if(! empty($actionUrl) && ! empty($actionLabel))
                    <tr>
                        <td style="padding:12px 28px 0;">
                            <a href="{{ $actionUrl }}" style="display:inline-block;padding:10px 18px;background-color:#0f172a;color:#ffffff;font-size:14px;font-weight:bold;text-decoration:none;border-radius:8px;">{{ $actionLabel }}</a>
                        </td>
                    </tr>
                @endif
                <tr>
                    <td style="padding:20px 28px 24px;">
                        <p style="margin:0;font-size:12px;line-height:18px;color:#94a3b8;">You received this because you have a StayTrack account. If you did not expect it, please contact the property office.</p>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
