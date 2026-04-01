<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>{{ $title }}</title>
</head>
@php
  $context = $context ?? [];
  $originalTopic = trim((string) ($context['original_topic'] ?? ''));
  $originalMessage = trim((string) ($context['original_message'] ?? ''));
@endphp
<body style="margin:0;padding:0;background:#eef3fb;font-family:Arial,Helvetica,sans-serif;color:#1f2937;">
  <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#eef3fb;padding:28px 12px;">
    <tr>
      <td align="center">
        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:600px;background:#ffffff;border:1px solid #dbe4f1;border-radius:16px;overflow:hidden;box-shadow:0 10px 24px rgba(15,76,154,0.08);">
          <tr>
            <td style="background:#0f4c9a;padding:0;">
              <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:linear-gradient(135deg,#0f4c9a 0%,#1f66c2 65%,#3a85e6 100%);">
                <tr>
                  <td style="padding:22px 24px 18px 24px;">
                    <div style="display:inline-block;background:rgba(255,255,255,0.18);color:#e9f2ff;font-size:11px;letter-spacing:.6px;text-transform:uppercase;padding:5px 10px;border-radius:999px;font-weight:700;">E-Konseling</div>
                    <div style="color:#ffffff;font-size:12px;letter-spacing:.4px;text-transform:uppercase;margin-top:12px;opacity:.92;">BK SMKN 1 Ciamis</div>
                    <div style="color:#ffffff;font-weight:700;font-size:24px;line-height:1.35;margin-top:6px;">{{ $title }}</div>
                  </td>
                </tr>
              </table>
            </td>
          </tr>

          <tr>
            <td style="padding:22px 24px 12px 24px;">
              <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="margin-bottom:12px;background:#ffffff;border:1px solid #dbe4f1;border-radius:12px;">
                <tr>
                  <td style="padding:12px 14px 2px 14px;font-size:11px;color:#64748b;text-transform:uppercase;letter-spacing:.3px;font-weight:700;">Subjek Dipilih</td>
                </tr>
                <tr>
                  <td style="padding:0 14px 12px 14px;font-size:15px;line-height:1.6;color:#0f172a;font-weight:700;">{{ $title }}</td>
                </tr>
              </table>

              @if($originalTopic !== '' || $originalMessage !== '')
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="margin-bottom:12px;background:#ffffff;border:1px solid #dbe4f1;border-radius:12px;">
                  <tr>
                    <td style="padding:12px 14px 2px 14px;font-size:11px;color:#64748b;text-transform:uppercase;letter-spacing:.3px;font-weight:700;">Pesan Masuk</td>
                  </tr>
                  @if($originalTopic !== '')
                    <tr>
                      <td style="padding:0 14px 8px 14px;font-size:13px;color:#334155;line-height:1.6;">
                        <span style="font-weight:700;color:#64748b;">Topik:</span> {{ $originalTopic }}
                      </td>
                    </tr>
                  @endif
                  @if($originalMessage !== '')
                    <tr>
                      <td style="padding:0 14px 12px 14px;font-size:14px;color:#0f172a;line-height:1.7;">{!! nl2br(e($originalMessage)) !!}</td>
                    </tr>
                  @endif
                </table>
              @endif

              <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#f8fbff;border:1px solid #dce9fb;border-radius:12px;">
                <tr>
                  <td style="width:4px;background:#2f80ed;border-radius:12px 0 0 12px;"></td>
                  <td style="padding:16px 16px 16px 14px;">
                    <div style="font-size:12px;color:#64748b;text-transform:uppercase;letter-spacing:.3px;font-weight:700;margin-bottom:6px;">Balasan BK</div>
                    <div style="margin:0;font-size:15px;line-height:1.75;color:#0f172a;">{!! nl2br(e($body ?: 'Ada notifikasi baru dari sistem E-Konseling.')) !!}</div>
                  </td>
                </tr>
              </table>
            </td>
          </tr>

          <tr>
            <td style="padding:10px 24px 22px 24px;">
              <div style="font-size:12px;color:#64748b;line-height:1.6;">
                Jika Anda membutuhkan bantuan lebih lanjut, silakan balas email ini atau hubungi tim BK.
              </div>
            </td>
          </tr>

          <tr>
            <td style="padding:14px 24px;background:#f3f7ff;border-top:1px solid #dbe4f1;font-size:12px;color:#7b8ba6;">
              Email ini dikirim otomatis oleh sistem E-Konseling.
            </td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
</body>
</html>
