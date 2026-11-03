# 🏛️ Ceylora - OTP Email Verification System

## ✨ What's Been Set Up

Your Ceylora application now has a **complete, production-ready OTP email verification system** for user registration and login.

### System Features

✅ **Email OTP Verification** - 6-digit one-time passwords sent via SMTP  
✅ **User Registration** - Users verify email before account becomes active  
✅ **Smart Login** - Verified users login normally, unverified users need OTP  
✅ **Email Templates** - Professional HTML emails with Ceylora branding  
✅ **Security** - OTP expires in 15 minutes, cannot be reused  
✅ **Logging** - All emails logged to `/emails.txt` for easy debugging  
✅ **Error Handling** - Detailed error logs in `/logs/` directory  

---

## 🚀 Quick Start (Choose Your Email Provider)

### For Gmail (Easiest - 2 minutes)

```
1. Go to: https://myaccount.google.com/apppasswords
2. Select "Mail" and "Windows Computer"
3. Copy the 16-character password
4. Edit: config/mail.php
   - Set 'username' = your Gmail address
   - Set 'password' = the 16-char password
5. Test: http://localhost/ceylora/register.php
```

### For Mailtrap (Best for Development - 3 minutes)

```
1. Sign up free: https://mailtrap.io/
2. Go to: Email Testing → Inbox → SMTP Settings
3. Copy the settings shown
4. Edit: config/mail.php with those values
5. Test: http://localhost/ceylora/register.php
```

### For Other Providers

- **SendGrid** - See SMTP_SETUP_GUIDE.md
- **AWS SES** - See SMTP_SETUP_GUIDE.md  
- **Office 365** - See SMTP_SETUP_GUIDE.md
- **Custom SMTP** - See SMTP_CONFIG_EXAMPLES.php

---

## 📚 Documentation Files

All documentation is in your root directory:

| File | Purpose |
|------|---------|
| **QUICK_REFERENCE.md** | ⭐ Start here - 1-page quick reference |
| **SMTP_SETUP_GUIDE.md** | Detailed setup for each email provider |
| **SMTP_CONFIG_EXAMPLES.php** | Copy-paste ready configurations |
| **OTP_TESTING_GUIDE.md** | Complete testing procedures |
| **otp-system-status.php** | Check system status in browser |

---

## 🎯 How It Works

### User Registration Flow

```
1. User visits: /register.php
2. Fills registration form:
   - Full Name
   - Email
   - Password
   - Address
   - City/Country
   
3. System:
   - Creates unverified user account
   - Generates 6-digit OTP
   - Sends OTP to email via SMTP
   
4. User receives email with OTP
   
5. User visits: /verify-register.php
   - Enters OTP
   - Account becomes verified
   - User automatically logged in
   
6. Next login: Uses email/password (no OTP needed)
```

### Unverified User Login Flow

```
If user tries to login with unverified email:
1. Login: /login.php
2. System detects unverified account
3. Sends new OTP to email
4. Redirects to: /verify-register.php
5. User enters OTP
6. Account verified
7. Logged in
```

---

## 📋 File Structure

```
ceylora/
├── config/
│   └── mail.php                    ← CONFIGURE THIS
├── includes/
│   └── email.php                   ← OTP functions (don't edit)
├── register.php                    ← Registration page
├── verify-register.php             ← OTP verification page
├── login.php                        ← Login page
├── logout.php                       ← Logout
├── otp-system-status.php           ← Status checker
├── logs/                           ← Email logs created here
├── emails.txt                      ← All sent OTPs logged here
├── QUICK_REFERENCE.md              ← Start here
├── SMTP_SETUP_GUIDE.md             ← Detailed setup
├── SMTP_CONFIG_EXAMPLES.php        ← Copy-paste configs
└── OTP_TESTING_GUIDE.md            ← Testing procedures
```

---

## 🔧 Configuration

### Edit `config/mail.php`

Replace the placeholder values with your actual SMTP credentials:

```php
return [
    'host'       => 'smtp.gmail.com',         // SMTP server
    'port'       => 587,                      // Port (587 or 465)
    'username'   => 'your_email@gmail.com',   // Your email
    'password'   => 'your_app_password',      // Your password
    'encryption' => 'tls',                    // 'tls' or 'ssl'
    'from'       => 'noreply@ceylora.com'     // Sender email
];
```

**Important:** 
- For Gmail: Use App Password, NOT your Gmail password
- For other providers: Get credentials from their settings
- All fields are required

---

## 🧪 Testing

### Step 1: Check System Status
```
Open: http://localhost/ceylora/otp-system-status.php
This shows what's configured and what needs setup
```

### Step 2: Configure SMTP
```
Edit: config/mail.php
Add your email provider's SMTP credentials
```

### Step 3: Test Registration
```
Open: http://localhost/ceylora/register.php
Register with a real email you can access
```

### Step 4: Check Email
```
Check /emails.txt for the OTP code
Check your email inbox for OTP email
Enter OTP to verify
```

### Step 5: Verify Login
```
Logout
Login with verified email and password
Should login without OTP
```

---

## 📊 Database Schema

### `users` Table
```sql
- id (auto increment)
- full_name
- email (unique)
- phone
- password (hashed)
- address
- city
- country
- is_verified (0 = not verified, 1 = verified)
- created_at
```

### `otp_verifications` Table
```sql
- id (auto increment)
- email
- otp (6-digit code)
- expires_at (15 minutes from creation)
- is_used (0 = not used, 1 = used)
- created_at
```

---

## 🔍 Debugging

### All sent OTPs are logged to `/emails.txt`

This is the **easiest place to debug**. After any registration attempt, check:

```
cat /emails.txt
```

Should show:
```
========================================
Sent: 2026-07-03 14:30:22
To: user@example.com
Subject: Ceylora - Your OTP Verification Code
OTP Code: 123456
========================================
```

### SMTP Error Logs

If emails aren't sending, check:

```
/logs/smtp_error.log    ← SMTP specific errors
/logs/email.log         ← All email transactions
```

### Common Error Messages

| Error | Solution |
|-------|----------|
| Authentication failed | Check username/password in config/mail.php |
| Connection timeout | Try port 465 with 'ssl' instead of 587 |
| Host not found | Verify host spelling in config/mail.php |
| TLS error | Try 'ssl' instead of 'tls' |

---

## 🚨 Important Notes

1. **Database Required:** Run `db.sql` first if you haven't already
2. **SMTP Required:** Email won't work without SMTP configuration
3. **Test First:** Use Mailtrap or Gmail for testing before production
4. **Monitor Logs:** Check `/emails.txt` and `/logs/` in production
5. **Backup:** Keep copies of logs for debugging user issues

---

## 🎯 Production Checklist

Before going live:

- [ ] SMTP configured with production email provider
- [ ] Test registration works
- [ ] Test login works for verified users
- [ ] Test OTP expiration works
- [ ] Email logs are being created
- [ ] Error logs are being written
- [ ] Email templates look good
- [ ] From email is verified with provider

---

## 🆘 Need Help?

### Quick Reference
👉 Open: `QUICK_REFERENCE.md`

### Detailed Setup
👉 Open: `SMTP_SETUP_GUIDE.md`

### Test Procedures
👉 Open: `OTP_TESTING_GUIDE.md`

### Check Configuration Examples
👉 Open: `SMTP_CONFIG_EXAMPLES.php`

### System Status
👉 Visit: `http://localhost/ceylora/otp-system-status.php`

---

## 📧 Email Provider Recommendations

### For Development
✅ **Mailtrap** (Free, catches all emails) → https://mailtrap.io/

### For Testing
✅ **Gmail** (Free, reliable) → https://myaccount.google.com/apppasswords

### For Production
✅ **SendGrid** (24/7 support) → https://sendgrid.com/  
✅ **AWS SES** (Pay per email) → https://aws.amazon.com/ses/  
✅ **Office 365** (If using Microsoft) → Office 365 email account  

---

## 🎉 You're All Set!

Your OTP email verification system is ready to use. Just configure your SMTP credentials and test it out!

### Next Steps:
1. Open `QUICK_REFERENCE.md` or `SMTP_SETUP_GUIDE.md`
2. Choose your email provider
3. Edit `config/mail.php`
4. Test at `http://localhost/ceylora/register.php`
5. Check `/emails.txt` for OTP logs
6. Done! 🚀

---

**Questions?** Check the documentation files or review the email logs in `/logs/` directory.

Happy emailing! 📧✨
