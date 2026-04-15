<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $appName }} - Kode Verifikasi 2FA</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', 'Oxygen', 'Ubuntu', 'Cantarell', sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 0;
            background-color: #f5f5f5;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .email-wrapper {
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px 20px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 600;
        }
        .content {
            padding: 30px 20px;
        }
        .greeting {
            font-size: 16px;
            margin-bottom: 20px;
            color: #333;
        }
        .code-section {
            background-color: #f8f9fa;
            border-left: 4px solid #667eea;
            padding: 20px;
            margin: 25px 0;
            border-radius: 4px;
        }
        .code-label {
            font-size: 12px;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 10px;
        }
        .code-display {
            background-color: white;
            border: 2px dashed #667eea;
            padding: 15px;
            text-align: center;
            font-size: 32px;
            font-weight: bold;
            letter-spacing: 5px;
            color: #667eea;
            font-family: 'Courier New', monospace;
            border-radius: 4px;
            word-spacing: 10px;
        }
        .code-display-small {
            font-size: 24px;
            letter-spacing: 3px;
        }
        .expires {
            text-align: center;
            color: #e74c3c;
            font-size: 14px;
            margin-top: 15px;
            font-weight: 500;
        }
        .info {
            background-color: #e8f4f8;
            border-left: 4px solid #3498db;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
            font-size: 14px;
            color: #2c3e50;
        }
        .warning {
            background-color: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
            font-size: 14px;
            color: #856404;
        }
        .footer {
            background-color: #f8f9fa;
            padding: 20px;
            text-align: center;
            font-size: 12px;
            color: #666;
            border-top: 1px solid #eee;
        }
        .footer-link {
            color: #667eea;
            text-decoration: none;
        }
        .footer-link:hover {
            text-decoration: underline;
        }
        .button {
            display: inline-block;
            background-color: #667eea;
            color: white;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 4px;
            margin: 10px 0;
            font-size: 14px;
        }
        .button:hover {
            background-color: #5568d3;
        }
        .security-note {
            background-color: #f0f7ff;
            border-left: 4px solid #0066cc;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
            font-size: 13px;
            color: #003d99;
        }
        .divider {
            height: 1px;
            background-color: #eee;
            margin: 20px 0;
        }
        ul {
            margin: 15px 0;
            padding-left: 20px;
        }
        li {
            margin: 8px 0;
        }
        strong {
            color: #667eea;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="email-wrapper">
            <!-- Header -->
            <div class="header">
                <h1>🔐 {{ __('messages.two_factor_email_subject') }}</h1>
            </div>

            <!-- Main Content -->
            <div class="content">
                <p class="greeting">
                    Halo, <strong>{{ $user->name }}</strong>
                </p>

                <p>
                    Anda telah memulai proses login dengan verifikasi 2 langkah yang diaktifkan. Gunakan kode berikut untuk melanjutkan login Anda ke aplikasi <strong>{{ $appName }}</strong>.
                </p>

                <!-- OTP Code Section -->
                <div class="code-section">
                    <div class="code-label">Kode Verifikasi Anda</div>
                    <div class="code-display code-display-small">{{ $kode }}</div>
                    <div class="expires">
                        ⏰ Kode ini berlaku selama {{ $expiresIn }}
                    </div>
                </div>

                <!-- Information -->
                <div class="info">
                    ℹ️ <strong>Cara Menggunakan Kode</strong>:
                    <ul style="margin: 10px 0 0 0;">
                        <li>Salin kode 6 digit di atas</li>
                        <li>Kembali ke halaman verifikasi login</li>
                        <li>Tempel kode dan klik Verifikasi</li>
                    </ul>
                </div>

                <!-- Security Warning -->
                <div class="warning">
                    ⚠️ <strong>Peringatan Keamanan</strong>:
                    <ul style="margin: 10px 0 0 0;">
                        <li>Jangan bagikan kode ini kepada siapapun</li>
                        <li>Tim kami tidak akan pernah meminta kode Anda</li>
                        <li>Jika Anda tidak memulai login ini, abaikan email ini</li>
                    </ul>
                </div>

                <!-- Device Information (Optional) -->
                <div class="security-note">
                    🔒 Login ini dimulai dari perangkat. Jika bukan Anda, segera ubah password Anda.
                </div>

                <div class="divider"></div>

                <p style="font-size: 14px; color: #666;">
                    Pertanyaan atau bantuan?
                    <a href="mailto:support@example.com" style="color: #667eea; text-decoration: none;">
                        Hubungi tim support kami
                    </a>
                </p>
            </div>

            <!-- Footer -->
            <div class="footer">
                <p style="margin: 0 0 10px 0;">
                    © {{ date('Y') }} {{ $appName }}. Semua hak cipta dilindungi.
                </p>
                <p style="margin: 0; color: #999; font-size: 11px;">
                    Email ini dikirim ke {{ $user->email }}<br>
                    Karena Anda memiliki verifikasi 2 langkah yang diaktifkan
                </p>
            </div>
        </div>
    </div>
</body>
</html>
