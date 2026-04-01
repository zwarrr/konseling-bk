<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Setup Akun Berhasil</title>
</head>
<body style="margin:0;padding:0;background:#f8fafc;font-family:Segoe UI,Tahoma,Geneva,Verdana,sans-serif;color:#1f2937;">
  <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#f8fafc;padding:24px 0;">
    <tr>
      <td align="center">
        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:560px;background:#ffffff;border:1px solid #e5e7eb;border-radius:14px;overflow:hidden;">
          <tr>
            <td style="background:linear-gradient(135deg,#0F4C9A 0%,#1a6fd4 100%);padding:22px 24px;">
              <div style="color:#dbeafe;font-size:12px;letter-spacing:.4px;text-transform:uppercase;">BK SMKN 1 Ciamis</div>
              <div style="color:#ffffff;font-weight:700;font-size:20px;margin-top:4px;">Setup Akun Berhasil</div>
            </td>
          </tr>
          <tr>
            <td style="padding:24px;">
              <p style="margin:0 0 10px;font-size:14px;line-height:1.7;">Halo <strong>{{ $name }}</strong>,</p>
              <p style="margin:0 0 10px;font-size:14px;line-height:1.7;">
                Akun {{ $roleLabel }} kamu sudah berhasil disiapkan.
              </p>

              <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="margin:14px 0;background:#f8fbff;border:1px solid #dce9fb;border-radius:10px;">
                <tr>
                  <td style="padding:12px 14px 4px 14px;font-size:11px;color:#64748b;text-transform:uppercase;letter-spacing:.3px;font-weight:700;">Informasi Akun</td>
                </tr>
                <tr>
                  <td style="padding:0 14px 4px 14px;font-size:13px;color:#334155;line-height:1.7;">
                    <strong>Role:</strong> {{ $roleLabel }}
                  </td>
                </tr>
                <tr>
                  <td style="padding:0 14px 4px 14px;font-size:13px;color:#334155;line-height:1.7;">
                    <strong>Email:</strong> {{ $email }}
                  </td>
                </tr>
                <tr>
                  <td style="padding:0 14px 4px 14px;font-size:13px;color:#334155;line-height:1.7;">
                    <strong>ID Login:</strong> {{ $loginId }}
                  </td>
                </tr>
                <tr>
                  <td style="padding:0 14px 14px 14px;font-size:13px;color:#334155;line-height:1.7;">
                    <strong>Password:</strong> {{ $plainPassword }}
                  </td>
                </tr>
              </table>

              <p style="margin:16px 0 0;font-size:13px;color:#64748b;line-height:1.7;">
                Simpan informasi ini dengan aman. Jika kamu merasa tidak melakukan perubahan ini, segera hubungi tim BK.
              </p>
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
