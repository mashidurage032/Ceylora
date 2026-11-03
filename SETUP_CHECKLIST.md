# ✅ Ceylora OTP System - Setup Checklist

## Phase 1: Verify Prerequisites

- [ ] XAMPP installed and running (Apache + MySQL on port 3307)
- [ ] MySQL running (check phpMyAdmin: http://localhost/phpmyadmin)
- [ ] Database created: `ceylora_db`
- [ ] Tables created (from `db.sql`):
  - [ ] `users` table exists
  - [ ] `otp_verifications` table exists

**If any ❌, run:**
```sql
-- In phpMyAdmin, run: db.sql
```

---

## Phase 2: Understand the System (5 minutes)

- [ ] Read: `QUICK_REFERENCE.md` ← Start here
- [ ] Understand OTP flow: User registers → Gets OTP email → Verifies → Logged in
- [ ] Know where to find logs: `/emails.txt` (most important!)

---

## Phase 3: Choose Email Provider

**Pick ONE option:**

### Option A: Gmail (Easiest - Recommended)
- [ ] Go to: https://myaccount.google.com/apppasswords
- [ ] Select "Mail" and "Windows Computer"
- [ ] Copy the 16-character password
- [ ] **Record:** `your_gmail_address@gmail.com` and `xxxxxxxxxxxxxxxx`

### Option B: Mailtrap (Best for Testing)
- [ ] Sign up free: https://mailtrap.io/
- [ ] Create inbox
- [ ] Go to: Email Testing → Inbox → SMTP Settings
- [ ] Copy Host, Port, Username, Password
- [ ] **Record all values**

### Option C: SendGrid / AWS SES / Office 365
- [ ] See: `SMTP_SETUP_GUIDE.md` for detailed steps
- [ ] Get credentials from provider
- [ ] **Record all values**

---

## Phase 4: Configure SMTP (5 minutes)

**Edit: `config/mail.php`**

```
1. Open file: c:\xampp\htdocs\ceylora\config\mail.php
2. Replace the return [...] block with your provider config
3. Fill in YOUR_* placeholders with recorded values
4. Save file
```

### For Gmail:
```php
return [
    'host'       => 'smtp.gmail.com',
    'port'       => 587,
    'username'   => 'your_email@gmail.com',     // CHANGE THIS
    'password'   => 'your_app_password',         // CHANGE THIS
    'encryption' => 'tls',
    'from'       => 'noreply@ceylora.com'
];
```

### For Mailtrap:
```php
return [
    'host'       => 'smtp.mailtrap.io',          // From Mailtrap settings
    'port'       => 587,                         // From Mailtrap settings
    'username'   => 'your_mailtrap_username',    // From Mailtrap settings
    'password'   => 'your_mailtrap_password',    // From Mailtrap settings
    'encryption' => 'tls',
    'from'       => 'hello@example.com'
];
```

**✓ When done:** config/mail.php should NOT show `your_email` or `your_app_password` text

---

## Phase 5: Verify Configuration (2 minutes)

**Visit this page in browser:**
```
http://localhost/ceylora/otp-system-status.php
```

**Check all boxes show ✅ (green):**
- [ ] Database
- [ ] Users Table
- [ ] OTP Table
- [ ] Email Config (should NOT say "placeholder values")
- [ ] Email Functions
- [ ] Log Directory (may show ⚠️ - that's ok)

**If any ❌ (red):**
1. Read error message carefully
2. Go back to Phase 4 and check config/mail.php
3. Reload this page

---

## Phase 6: Test Registration Flow (10 minutes)

**Step 1: Start Registration**
- [ ] Open: http://localhost/ceylora/register.php
- [ ] Fill form with:
  - Full Name: `Test User`
  - Email: **YOUR REAL EMAIL** (one you can check inbox for)
  - Phone: `+94771234567`
  - Password: `TestPassword123`
  - Confirm Password: `TestPassword123`
  - Address: `123 Test Street`
  - City: `Colombo`
  - Country: `Sri Lanka`

**Step 2: Submit**
- [ ] Click "Create Account" button
- [ ] Should redirect to OTP verification page (not an error page)

**Step 3: Check Email Log**
- [ ] Open file: `c:\xampp\htdocs\ceylora\emails.txt`
- [ ] Should see entry with:
  - Your email
  - OTP code (6 digits)
  - Timestamp

**If NOT there:**
1. Check: `c:\xampp\htdocs\ceylora\logs\smtp_error.log`
2. Read error message
3. Fix config/mail.php
4. Try again

**Step 4: Check Email Inbox**
- [ ] Look in your email inbox
- [ ] Subject: "Ceylora - Your OTP Verification Code"
- [ ] Contains 6-digit OTP
- [ ] May take 1-3 minutes

**If NOT arrived:**
1. Check spam/junk folder
2. Check /emails.txt to verify OTP was sent
3. Wait 5 more minutes
4. If still nothing, check SMTP error log

**Step 5: Enter OTP**
- [ ] Get 6-digit OTP from /emails.txt or email
- [ ] Go back to OTP verification page
- [ ] Enter OTP (6 digits only)
- [ ] Click "Verify"

**Step 6: Check Result**
- [ ] Should redirect to home page (logged in)
- [ ] Page should show you're logged in
- [ ] User registered successfully ✓

---

## Phase 7: Test Login Flow (5 minutes)

**Step 1: Logout**
- [ ] Open: http://localhost/ceylora/logout.php
- [ ] Should redirect to home page (logged out)

**Step 2: Login with Verified Account**
- [ ] Open: http://localhost/ceylora/login.php
- [ ] Enter:
  - Email: (Your registered email)
  - Password: `TestPassword123`
- [ ] Click "Login"
- [ ] Should redirect to home page immediately (no OTP)
- [ ] Should show you're logged in ✓

**Step 3: Verify Database**
- [ ] Open phpMyAdmin
- [ ] Go to: ceylora_db → users table
- [ ] Find your test user
- [ ] Check: `is_verified` = 1 ✓

---

## Phase 8: Advanced Testing (Optional)

### Test Unverified User Login
- [ ] Register new user (different email)
- [ ] **DO NOT verify OTP**
- [ ] Logout
- [ ] Try to login with unverified email
- [ ] Should redirect to OTP page
- [ ] Should send new OTP to email
- [ ] Enter new OTP to verify

### Test OTP Resend
- [ ] Register another new user
- [ ] On OTP page, click "Resend OTP"
- [ ] Check /emails.txt - should see 2 OTPs for same email
- [ ] Use the NEWEST OTP
- [ ] Old OTP should NOT work

### Check Database Records
```sql
-- In phpMyAdmin, run:
SELECT * FROM users;                    -- See all users
SELECT * FROM otp_verifications;        -- See OTP records
```

---

## Phase 9: Monitor in Production

**Ongoing Checklist:**

- [ ] Regularly check `/emails.txt` to ensure OTPs are being sent
- [ ] Monitor `/logs/smtp_error.log` for SMTP failures
- [ ] Ensure SMTP provider account has enough credits
- [ ] Set up email alerts for SMTP errors
- [ ] Backup logs regularly

---

## 🎉 Success Criteria

**You're done when:**

✅ User registers → Gets OTP email  
✅ OTP verification works → User logged in  
✅ Verified users login normally (no OTP)  
✅ Unverified users prompted for OTP on login  
✅ OTP resend works  
✅ `/emails.txt` is being populated  
✅ No errors in `/logs/smtp_error.log`  

---

## 🆘 Troubleshooting Quick Guide

| Problem | Check |
|---------|-------|
| "Failed to send OTP email" | Check `/logs/smtp_error.log` for details |
| OTP never arrives | Check `/emails.txt` - if OTP there, SMTP config wrong |
| Config says "placeholder values" | Edit config/mail.php, replace YOUR_* with real values |
| Port 587 doesn't work | Try port 465 with 'ssl' encryption |
| Gmail won't send | Check if using App Password (not Gmail password) |
| Mailtrap won't send | Verify Host/Port/Username/Password copied exactly |
| OTP expires without verification | This is normal (15 min expiry) - click "Resend OTP" |

---

## 📚 Documentation Reference

Need more info? Check these files:

- **QUICK_REFERENCE.md** - 1-page quick reference
- **SMTP_SETUP_GUIDE.md** - Detailed setup for all providers
- **SMTP_CONFIG_EXAMPLES.php** - Copy-paste ready configs
- **OTP_TESTING_GUIDE.md** - Complete testing procedures
- **OTP_README.md** - System overview

---

## ✨ You're Ready!

Your OTP email verification system is now fully functional. Users can:

1. ✅ Register with email verification
2. ✅ Receive OTP via SMTP email
3. ✅ Verify with OTP
4. ✅ Login normally after verification
5. ✅ Re-verify with OTP if needed

**Estimated time to complete:** 30-45 minutes from config to full testing

**Any issues?** Check the error logs first, then refer to the troubleshooting guide above.

**Happy emailing! 🚀📧**
