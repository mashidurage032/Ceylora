# Ceylora SMTP Setup Guide - Email OTP Verification

## Overview
Your Ceylora application now has a complete **email-based OTP verification system** for user registration and login. This guide will help you set up SMTP to send OTP emails.

---

## How It Works

### Registration Flow
1. User fills registration form
2. System creates unverified user account
3. **6-digit OTP sent to user's email**
4. User enters OTP to verify email
5. Account becomes verified ✓
6. User can login

### Login Flow
- Verified users: Login normally
- Unverified users: Login triggers OTP verification

---

## SMTP Configuration Options

### Option 1: Gmail (Recommended for Testing)

**Why Gmail?** Free, reliable, and works instantly.

#### Steps:

1. **Go to Gmail App Passwords:**
   - Open: https://myaccount.google.com/apppasswords
   - Make sure 2-Step Verification is enabled first!
   - Select "Mail" and "Windows Computer"
   - Click "Generate"
   - Copy the 16-character password (ignore spaces)

2. **Update `config/mail.php`:**
   ```php
   'host'       => 'smtp.gmail.com',
   'port'       => 587,
   'username'   => 'your.email@gmail.com',    // Your Gmail address
   'password'   => 'xxxx xxxx xxxx xxxx',      // 16-char app password (copy exactly)
   'encryption' => 'tls',
   'from'       => 'noreply@ceylora.com'       // Can be any format
   ```

---

### Option 2: Mailtrap.io (Best for Development)

**Why Mailtrap?** Catches all emails in a sandbox. Perfect for testing.

#### Steps:

1. **Sign up free:**
   - Go to: https://mailtrap.io/
   - Create free account
   - Verify email

2. **Get SMTP credentials:**
   - Dashboard → Email Testing → Inbox Settings
   - Click "SMTP Settings"
   - Copy the settings shown

3. **Update `config/mail.php`:**
   ```php
   'host'       => 'smtp.mailtrap.io',
   'port'       => 587,                    // or 465 for SSL
   'username'   => 'xxxxxxx',              // From Mailtrap settings
   'password'   => 'xxxxxxx',              // From Mailtrap settings
   'encryption' => 'tls',                  // or 'ssl'
   'from'       => 'hello@example.com'     // Any format
   ```

4. **View sent emails:**
   - All OTP emails will appear in Mailtrap Dashboard
   - Check email content, headers, and links

---

### Option 3: AWS SES (Production)

#### Steps:

1. **AWS Console:**
   - Go to Amazon SES
   - Verify your email domain
   - Create SMTP credentials
   - Request production access (if needed)

2. **Update `config/mail.php`:**
   ```php
   'host'       => 'smtp.email.us-east-1.amazonaws.com',  // Replace region
   'port'       => 587,
   'username'   => 'your_smtp_username',    // From SES credentials
   'password'   => 'your_smtp_password',    // From SES credentials
   'encryption' => 'tls',
   'from'       => 'verified@yourdomain.com' // Must be verified in SES
   ```

---

### Option 4: SendGrid (Production)

#### Steps:

1. **SendGrid Setup:**
   - Create account: https://sendgrid.com/
   - Go to Settings → API Keys
   - Create new API Key
   - Copy the key

2. **Update `config/mail.php`:**
   ```php
   'host'       => 'smtp.sendgrid.net',
   'port'       => 587,
   'username'   => 'apikey',                // Always 'apikey'
   'password'   => 'SG.xxxxxxxxxxxxx',      // Your SendGrid API Key
   'encryption' => 'tls',
   'from'       => 'noreply@ceylora.com'
   ```

---

## Testing Your Configuration

### Method 1: Test Registration (Recommended)

1. Open: `http://localhost/ceylora/register.php`
2. Fill in registration form with **real email** you can access
3. If SMTP is configured correctly:
   - ✅ You'll be redirected to OTP verification page
   - ✅ Check your email inbox for OTP
   - ✅ Enter OTP to complete registration

### Method 2: Check Log Files

If emails aren't being sent, check these logs:

- **`/emails.txt`** - All sent emails (human-readable) ← **Check this first!**
- **`/logs/email.log`** - Raw SMTP logs
- **`/logs/smtp_error.log`** - SMTP errors

Example email.log entry:
```
[2026-07-03 14:30:22] To: user@example.com | Subject: Ceylora - Your OTP Verification Code | OTP: 123456
```

### Method 3: Check Browser Console

1. Open Developer Tools (F12)
2. Go to Console tab
3. Check for any JavaScript errors
4. Check Network tab to see if requests were made

---

## Troubleshooting

### ❌ "Failed to send OTP email"

**Check these in order:**

1. **Is SMTP configured?**
   ```
   Open config/mail.php
   Check if you replaced placeholder values
   ```

2. **Check email logs:**
   ```
   Open /emails.txt
   Should see: [timestamp] To: your_email | OTP: 123456
   If missing → SMTP settings are wrong
   ```

3. **Check SMTP error log:**
   ```
   Open /logs/smtp_error.log
   Look for error messages
   Common: "Authentication failed", "Connection timeout"
   ```

4. **Verify SMTP credentials:**
   - For Gmail: Make sure you used App Password, not Gmail password
   - For Mailtrap: Copy credentials exactly as shown
   - For AWS/SendGrid: Verify credentials are correct

5. **Check firewall:**
   - Some networks block port 587
   - Try port 465 with SSL instead
   - Or use a different network

### ❌ OTP doesn't arrive in email

- Check spam/junk folder
- Verify email address is correct
- Check `/emails.txt` to confirm OTP was generated
- Wait 2-3 minutes (sometimes emails are slow)

### ❌ "Connection failed"

**Solution:**
- Change port: Try 465 (SSL) instead of 587 (TLS)
- Change encryption: Try 'ssl' instead of 'tls'
- Change host: Try 'smtp.gmail.com:587'

---

## File Locations

| File | Purpose |
|------|---------|
| `config/mail.php` | SMTP configuration (UPDATE THIS) |
| `includes/email.php` | Email sending functions |
| `register.php` | User registration + OTP sending |
| `verify-register.php` | OTP verification |
| `login.php` | Login with OTP for unverified users |
| `emails.txt` | Log of all sent emails (check this!) |
| `logs/email.log` | Detailed email logs |
| `logs/smtp_error.log` | SMTP error logs |

---

## Quick Start (Gmail)

If you just want to get started quickly with Gmail:

1. Enable 2-Step Verification: https://myaccount.google.com/security
2. Get App Password: https://myaccount.google.com/apppasswords
3. Edit `config/mail.php`:
   ```php
   'username' => 'your.email@gmail.com',
   'password' => 'your_16_char_app_password_no_spaces',
   ```
4. Test registration: http://localhost/ceylora/register.php

---

## Security Notes

- ✅ OTP expires in **15 minutes**
- ✅ OTP is **6 digits** (secure enough for email verification)
- ✅ OTP is hashed in database before verification
- ✅ Used OTPs are marked and cannot be reused
- ✅ One active OTP per email at a time

---

## Need Help?

Check these resources:
- Gmail App Password: https://support.google.com/accounts/answer/185833
- Mailtrap Docs: https://mailtrap.io/blog/nodejs-smtp/
- AWS SES: https://docs.aws.amazon.com/ses/
- SendGrid: https://sendgrid.com/docs/

---

## What's Next?

Once SMTP is working:
1. ✅ Test user registration with OTP
2. ✅ Test login with unverified accounts
3. ✅ Monitor email logs in `/emails.txt`
4. ✅ Go to production with your chosen SMTP service
