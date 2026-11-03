# Ceylora SMTP + OTP Quick Reference

## 🚀 Quick Setup (5 minutes)

### For Gmail (Easiest)

1. Go to: https://myaccount.google.com/apppasswords
2. Select "Mail" and "Windows Computer"
3. Copy the 16-character password
4. Edit `config/mail.php`:
```php
'username' => 'your.email@gmail.com',
'password' => 'xxxx xxxx xxxx xxxx',  // Paste here
```
5. Test: http://localhost/ceylora/register.php

### For Mailtrap (Development)

1. Sign up free: https://mailtrap.io/
2. Go to: Email Testing → Inbox → SMTP Settings
3. Copy Host, Port, Username, Password
4. Edit `config/mail.php` with those values
5. Test: http://localhost/ceylora/register.php

---

## 📧 How OTP Works

```
User Registration
       ↓
[Create Account]
       ↓
[Generate 6-digit OTP]
       ↓
[Send OTP via SMTP Email]
       ↓
User Gets Email
       ↓
[User Enters OTP]
       ↓
[Verify OTP in Database]
       ↓
[Mark User as Verified]
       ↓
[User Logged In]
       ↓
Next Login: Normal (no OTP needed)
```

---

## 🔧 File Locations

| Path | Purpose |
|------|---------|
| `config/mail.php` | **← EDIT THIS** SMTP settings |
| `register.php` | User registration page |
| `verify-register.php` | OTP verification page |
| `login.php` | Login page |
| `/emails.txt` | **← CHECK THIS** All sent OTPs |
| `/logs/smtp_error.log` | SMTP errors |

---

## 📝 Configuration Template

```php
// config/mail.php
return [
    'host'       => 'smtp.gmail.com',      // or your SMTP host
    'port'       => 587,                   // 587 (TLS) or 465 (SSL)
    'username'   => 'your_email@gmail.com', // Your email
    'password'   => 'your_app_password',    // Your password/API key
    'encryption' => 'tls',                  // 'tls' or 'ssl'
    'from'       => 'noreply@ceylora.com'   // Sender email
];
```

---

## 🧪 Testing Flow

```
1. Register: http://localhost/ceylora/register.php
           ↓
2. Check /emails.txt for OTP code
           ↓
3. Check email inbox for OTP email
           ↓
4. Enter OTP on verification page
           ↓
5. Logged in ✓
           ↓
6. Logout and login with email/password (no OTP needed)
```

---

## ⚡ Common Issues & Fixes

| Problem | Solution |
|---------|----------|
| "Failed to send OTP email" | Check /logs/smtp_error.log for details |
| No email received | Check /emails.txt - if OTP exists, SMTP config wrong |
| Gmail won't send | Use App Password, not Gmail password |
| Connection timeout | Try port 465 with 'ssl' instead of 587 |
| No log files created | SMTP functions might have errors |

---

## 🔐 OTP Security

- ✅ Expires in **15 minutes**
- ✅ **6 digits** generated
- ✅ Cannot reuse old OTP
- ✅ Each email has 1 active OTP only
- ✅ Marked as used after verification

---

## 📋 SMTP Providers

| Provider | Host | Port | Username |
|----------|------|------|----------|
| **Gmail** | smtp.gmail.com | 587 | your_email@gmail.com |
| **Mailtrap** | smtp.mailtrap.io | 587 | from settings |
| **SendGrid** | smtp.sendgrid.net | 587 | apikey |
| **AWS SES** | smtp.email.{region}.amazonaws.com | 587 | from credentials |
| **Office 365** | smtp.office365.com | 587 | your_office_email |

---

## 🎯 Success Checklist

- [ ] SMTP configured in config/mail.php
- [ ] /emails.txt created after first registration attempt
- [ ] OTP email received in inbox
- [ ] OTP verified successfully
- [ ] User logged in automatically
- [ ] Next login works without OTP (for verified users)
- [ ] Unverified users prompted for OTP on login

---

## 📚 Full Documentation

- **SMTP_SETUP_GUIDE.md** - Detailed setup for each provider
- **SMTP_CONFIG_EXAMPLES.php** - Copy-paste configurations
- **OTP_TESTING_GUIDE.md** - Complete testing procedures

---

## 🆘 Emergency Debugging

1. Check email was generated: `cat /emails.txt`
2. Check SMTP errors: `cat /logs/smtp_error.log`
3. Check database: `SELECT * FROM users WHERE email = 'your@email.com';`
4. Check OTP table: `SELECT * FROM otp_verifications WHERE email = 'your@email.com';`
5. Review config: `cat config/mail.php`

---

## Next Steps

1. ✅ Edit `config/mail.php` with your SMTP credentials
2. ✅ Test registration: http://localhost/ceylora/register.php
3. ✅ Check email inbox
4. ✅ Enter OTP and verify
5. ✅ Test login with verified account
6. ✅ Monitor logs for production use

**That's it! Your OTP email system is ready! 🎉**
