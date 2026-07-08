<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #f4f4f5; color: #333; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 40px auto; background: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        .header { background: #111827; padding: 24px; text-align: center; color: #ffffff; }
        .header h1 { margin: 0; font-size: 24px; font-weight: 600; letter-spacing: -0.5px; }
        .content { padding: 32px; line-height: 1.6; font-size: 16px; }
        .btn-container { text-align: center; margin: 32px 0; }
        .btn { display: inline-block; background: #3b82f6; color: #ffffff; text-decoration: none; padding: 12px 24px; border-radius: 6px; font-weight: 600; }
        .btn:hover { background: #2563eb; }
        .footer { padding: 24px; text-align: center; font-size: 14px; color: #6b7280; background: #f9fafb; border-top: 1px solid #e5e7eb; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>TechAccessories</h1>
        </div>
        <div class="content">
            <p>Hey there,</p>
            <p>We received a request to reset the password for your TechAccessories account. If you didn't ask for this, you can safely ignore this email.</p>
            <p>Otherwise, click the button below to set up a new password:</p>
            <div class="btn-container">
                <a href="{{ $url }}" class="btn">Reset My Password</a>
            </div>
            <p>This link will expire in 60 minutes.</p>
            <p>Thanks,<br>The TechAccessories Team</p>
        </div>
        <div class="footer">
            If you're having trouble clicking the button, copy and paste this URL into your browser:<br><br>
            <a href="{{ $url }}" style="color: #3b82f6; word-break: break-all;">{{ $url }}</a>
        </div>
    </div>
</body>
</html>