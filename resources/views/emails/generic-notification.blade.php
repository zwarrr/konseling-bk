<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>{{ $title }}</title>
</head>
<body style="margin:0;padding:0;background:#f8fafc;font-family:Segoe UI,Tahoma,Geneva,Verdana,sans-serif;color:#1f2937;">
  <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#f8fafc;padding:24px 0;">
    <tr>
      <td align="center">
        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:560px;background:#ffffff;border:1px solid #e5e7eb;border-radius:14px;overflow:hidden;">
          <tr>
            <td style="background:linear-gradient(135deg,#0F4C9A 0%,#1a6fd4 100%);padding:22px 24px;">
              <div style="color:#dbeafe;font-size:12px;letter-spacing:.4px;text-transform:uppercase;">BK SMKN 1 Ciamis</div>
              <div style="color:#ffffff;font-weight:700;font-size:20px;margin-top:4px;">{{ $title }}</div>
            </td>
          </tr>
          <tr>
            <td style="padding:24px;">
              <p style="margin:0;font-size:14px;line-height:1.75;">{{ $body ?: 'Ada notifikasi baru dari sistem E-Konseling.' }}</p>
            </td>
          </tr>
          <tr>
            <td style="padding:14px 24px;background:#f8fafc;border-top:1px solid #e5e7eb;font-size:12px;color:#94a3b8;">
              Email ini dikirim otomatis oleh sistem E-Konseling.
            </td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
</body>
</html>
