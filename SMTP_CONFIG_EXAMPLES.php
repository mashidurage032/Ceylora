<?php
/**
 * ====================================================================
 * CEYLORA - QUICK SMTP CONFIGURATION EXAMPLES
 * ====================================================================
 * 
 * Copy the appropriate example below into config/mail.php
 * Replace placeholder values (marked with YOUR_*)
 * 
 * Don't forget to keep the <?php opening tag and ?> closing tag!
 */

// ────────────────────────────────────────────────────────────────
// 1. GMAIL (Production - Uses App Password)
// ────────────────────────────────────────────────────────────────
// Get App Password: https://myaccount.google.com/apppasswords
// (Must have 2-Step Verification enabled first)

/*
return [
    'host'       => 'smtp.gmail.com',
    'port'       => 587,
    'username'   => 'YOUR_GMAIL_ADDRESS@gmail.com',
    'password'   => 'YOUR_16_CHARACTER_APP_PASSWORD',  // NOT your Gmail password!
    'encryption' => 'tls',
    'from'       => 'noreply@ceylora.com'
];
*/


// ────────────────────────────────────────────────────────────────
// 2. MAILTRAP (Development - Best for Testing)
// ────────────────────────────────────────────────────────────────
// Sign up: https://mailtrap.io/ (Free)
// Get credentials from: Email Testing > Inbox > SMTP Settings

/*
return [
    'host'       => 'smtp.mailtrap.io',
    'port'       => 587,
    'username'   => 'YOUR_MAILTRAP_USERNAME',
    'password'   => 'YOUR_MAILTRAP_PASSWORD',
    'encryption' => 'tls',
    'from'       => 'hello@example.com'
];
*/


// ────────────────────────────────────────────────────────────────
// 3. SENDGRID (Production)
// ────────────────────────────────────────────────────────────────
// Sign up: https://sendgrid.com/
// Get API Key from: Settings > API Keys > Create API Key

/*
return [
    'host'       => 'smtp.sendgrid.net',
    'port'       => 587,
    'username'   => 'apikey',  // Always use 'apikey' as username
    'password'   => 'SG.YOUR_SENDGRID_API_KEY_HERE',
    'encryption' => 'tls',
    'from'       => 'noreply@ceylora.com'
];
*/


// ────────────────────────────────────────────────────────────────
// 4. AWS SES (Production)
// ────────────────────────────────────────────────────────────────
// Setup: https://console.aws.amazon.com/ses/
// Create SMTP credentials from: Email Addresses > SMTP Credentials

/*
return [
    'host'       => 'smtp.email.us-east-1.amazonaws.com',  // Replace 'us-east-1' with your AWS region
    'port'       => 587,
    'username'   => 'YOUR_AWS_SES_SMTP_USERNAME',
    'password'   => 'YOUR_AWS_SES_SMTP_PASSWORD',
    'encryption' => 'tls',
    'from'       => 'noreply@yourdomain.com'  // Must be verified in SES
];
*/


// ────────────────────────────────────────────────────────────────
// 5. SENDINBLUE / BREVO (Production)
// ────────────────────────────────────────────────────────────────
// Sign up: https://www.brevo.com/
// Get credentials from: SMTP & API > SMTP

/*
return [
    'host'       => 'smtp-relay.brevo.com',
    'port'       => 587,
    'username'   => 'YOUR_BREVO_EMAIL',
    'password'   => 'YOUR_BREVO_SMTP_KEY',
    'encryption' => 'tls',
    'from'       => 'noreply@ceylora.com'
];
*/


// ────────────────────────────────────────────────────────────────
// 6. OFFICE 365 / MICROSOFT 365 (Production)
// ────────────────────────────────────────────────────────────────
// Using your Microsoft 365 business email

/*
return [
    'host'       => 'smtp.office365.com',
    'port'       => 587,
    'username'   => 'YOUR_OFFICE365_EMAIL@yourcompany.com',
    'password'   => 'YOUR_OFFICE365_PASSWORD',
    'encryption' => 'tls',
    'from'       => 'YOUR_OFFICE365_EMAIL@yourcompany.com'
];
*/


// ────────────────────────────────────────────────────────────────
// 7. CUSTOM SMTP SERVER
// ────────────────────────────────────────────────────────────────
// For any other SMTP server

/*
return [
    'host'       => 'smtp.yourmailserver.com',  // Your SMTP host
    'port'       => 587,                        // Usually 587 (TLS) or 465 (SSL)
    'username'   => 'YOUR_EMAIL@yourdomain.com',
    'password'   => 'YOUR_PASSWORD',
    'encryption' => 'tls',                      // 'tls' or 'ssl' or empty/null
    'from'       => 'noreply@ceylora.com'
];
*/


// ────────────────────────────────────────────────────────────────
// PORT REFERENCE
// ────────────────────────────────────────────────────────────────
// 587  = TLS (Start with unencrypted, then upgrade)  [RECOMMENDED]
// 465  = SSL (Direct SSL connection)
// 25   = Plain (Old, rarely used)


// ────────────────────────────────────────────────────────────────
// ENCRYPTION REFERENCE
// ────────────────────────────────────────────────────────────────
// 'tls'  = TLS encryption (port 587)       [RECOMMENDED]
// 'ssl'  = SSL encryption (port 465)
// null   = No encryption (not recommended)
// ''     = No encryption (not recommended)


// ────────────────────────────────────────────────────────────────
// HOW TO USE THIS FILE
// ────────────────────────────────────────────────────────────────
// 1. Find the configuration matching your email provider above
// 2. Copy the entire return [...] block
// 3. Open config/mail.php
// 4. Replace everything with your copied configuration
// 5. Fill in YOUR_* placeholders with your actual credentials
// 6. Save config/mail.php
// 7. Test with: http://localhost/ceylora/register.php
//
// That's it! OTP emails will now be sent from your chosen provider.
?>
