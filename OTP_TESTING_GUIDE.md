# Ceylora - OTP Email System Testing Checklist

## Prerequisites ✓

- [ ] XAMPP is running (Apache + MySQL)
- [ ] Database imported (run db.sql)
- [ ] SMTP configured in config/mail.php
- [ ] You have access to the email inbox you'll register with

---

## Complete Test Flow

### Test 1: Registration with OTP

**Goal:** Verify user receives OTP email during registration

**Steps:**

1. [ ] Open: http://localhost/ceylora/register.php
2. [ ] Fill registration form:
   - Full Name: `Test User`
   - Email: **Your real email** (one you can check)
   - Phone: `+94771234567`
   - Password: `Test@1234`
   - Confirm Password: `Test@1234`
   - Address: `123 Test St`
   - City: `Colombo`
   - Country: `Sri Lanka`

3. [ ] Click "Create Account"
4. [ ] You should be redirected to OTP verification page
5. [ ] Check `/emails.txt` file in root directory:
   - [ ] Should see entry with your email and OTP code
   - [ ] Example: `OTP Code: 123456`

6. [ ] Check your email inbox:
   - [ ] Look for: "Ceylora - Your OTP Verification Code"
   - [ ] Should arrive within 2-3 minutes
   - [ ] Contains 6-digit OTP

7. [ ] Enter OTP on verification page and click "Verify"
8. [ ] Should redirect to index.php (logged in ✓)

**Expected Result:**
```
✅ User registered
✅ OTP sent to email
✅ OTP verified
✅ User logged in automatically
✅ is_verified = 1 in database
```

---

### Test 2: Login with Verified Account

**Goal:** Verify verified user can login normally

**Steps:**

1. [ ] Logout: http://localhost/ceylora/logout.php
2. [ ] Open: http://localhost/ceylora/login.php
3. [ ] Login with credentials from Test 1:
   - Email: (Your registered email)
   - Password: `Test@1234`

4. [ ] Click "Login"
5. [ ] Should redirect to index.php immediately (no OTP)

**Expected Result:**
```
✅ Logged in without OTP verification
✅ No redirect to OTP page
```

---

### Test 3: Register New User (Unverified)

**Goal:** Test registering without verifying OTP

**Steps:**

1. [ ] Open: http://localhost/ceylora/register.php
2. [ ] Register with different email:
   - Full Name: `Unverified User`
   - Email: **Different email** (or temporary disposable email)
   - Password: `Test@5678`

3. [ ] Check `/emails.txt` → Copy the OTP code
4. [ ] **DO NOT verify** - close the page instead
5. [ ] Logout

**Expected Result:**
```
✅ User created but is_verified = 0
✅ User cannot login normally (will be sent to OTP page)
```

---

### Test 4: Login with Unverified Account

**Goal:** Verify unverified user must verify OTP before login

**Steps:**

1. [ ] Open: http://localhost/ceylora/login.php
2. [ ] Try to login with unverified email:
   - Email: (Your unverified email from Test 3)
   - Password: `Test@5678`

3. [ ] Should redirect to verify-register.php
4. [ ] Check `/emails.txt` → Get the new OTP
5. [ ] Enter OTP
6. [ ] Should redirect to index.php (logged in ✓)

**Expected Result:**
```
✅ Unverified user redirected to OTP page
✅ OTP sent again
✅ After verification: logged in and is_verified = 1
```

---

### Test 5: OTP Resend

**Goal:** Verify OTP resend works

**Steps:**

1. [ ] Open: http://localhost/ceylora/register.php
2. [ ] Register with another new email:
   - Email: **Third email**
   - Password: `Test@9012`

3. [ ] On OTP page, click "Resend OTP" button
4. [ ] Check `/emails.txt`:
   - [ ] Should see TWO entries for this email
   - [ ] One with old OTP, one with new OTP

5. [ ] Check email inbox:
   - [ ] Should see TWO emails (or latest one with new OTP)

6. [ ] Use the NEWEST OTP code to verify
7. [ ] Should verify successfully

**Expected Result:**
```
✅ New OTP sent
✅ Old OTP invalidated
✅ Only new OTP works for verification
```

---

### Test 6: OTP Expiration

**Goal:** Verify expired OTP cannot be used

**This is harder to test manually, but here's how:**

1. [ ] Register new user
2. [ ] Check `/emails.txt` → Copy OTP
3. [ ] **Wait 16 minutes** (OTP expires after 15 minutes)
4. [ ] Try entering the old OTP
5. [ ] Should see: "Invalid or expired OTP"
6. [ ] Click "Resend OTP"
7. [ ] Enter new OTP
8. [ ] Should verify successfully

**Expected Result:**
```
✅ Old OTP rejected
✅ Resend OTP works
```

---

## Troubleshooting Test Failures

### ❌ "Failed to send OTP email" during registration

**Check:**
```
1. Is config/mail.php updated with real SMTP credentials?
   → Open config/mail.php and verify not showing YOUR_* placeholders

2. Check /emails.txt - Did OTP get generated?
   → If /emails.txt shows the OTP, SMTP is the issue
   → If /emails.txt is empty, database issue

3. Check /logs/smtp_error.log for error details
   → Authentication failed? Check credentials
   → Connection timeout? Check port/host
   → TLS error? Try port 465 with 'ssl' encryption

4. Check email spam folder
   → Some emails go to spam initially
   → Add noreply@ceylora.com to contacts to whitelist
```

### ❌ OTP arrives late (5+ minutes)

**Solutions:**
- This is normal for first email
- Check spam/promotions folder
- Try refreshing browser
- Wait a few more minutes

### ❌ "Invalid or expired OTP" when you just entered it

**Check:**
- Is the OTP correct? Double-check against /emails.txt
- Did you copy it correctly (no extra spaces)?
- Are you using the latest OTP (if you clicked resend)?
- Is it less than 15 minutes old?

### ❌ User not logging in after OTP verification

**Check:**
- Is the is_verified column set to 1 in database?
- Are you using the same email/password?
- Clear browser cookies and try again

---

## Database Verification

**To manually check registration status:**

```sql
-- In phpMyAdmin or MySQL client, run:

-- See all users
SELECT id, email, full_name, is_verified, created_at FROM users;

-- See OTP records
SELECT email, otp, is_used, expires_at, created_at FROM otp_verifications;

-- See verified users
SELECT * FROM users WHERE is_verified = 1;

-- See unverified users
SELECT * FROM users WHERE is_verified = 0;
```

---

## File Locations for Debugging

| File | What to Check |
|------|---------------|
| `/emails.txt` | All sent OTP emails (timestamp, email, OTP code) |
| `/logs/email.log` | Raw SMTP transaction logs |
| `/logs/smtp_error.log` | SMTP error messages |
| `config/mail.php` | SMTP configuration (check for placeholders) |

---

## Success Criteria ✓

You know it's working when:

✅ User registers → Redirected to OTP page immediately  
✅ OTP email arrives in inbox within 2-3 minutes  
✅ OTP is 6-digit code  
✅ Email subject is "Ceylora - Your OTP Verification Code"  
✅ Entering correct OTP logs user in  
✅ User can login normally next time  
✅ Resend button works  
✅ Old OTP doesn't work (after resend)  
✅ Email log files are created and populated  

---

## Going to Production

Once all tests pass:

1. [ ] Update config/mail.php with production SMTP
2. [ ] Update config/mail.php from field to official company email
3. [ ] Test 1-2 times with production server
4. [ ] Monitor /logs/smtp_error.log in production
5. [ ] Set up email alerts for SMTP errors
6. [ ] Regularly check that emails are being sent
