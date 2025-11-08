<?php
/**
 * Brevo Email Configuration
 *
 * SETUP INSTRUCTIONS:
 * 1. Copy this file to 'email-config.php' (remove .example)
 * 2. Sign up at https://brevo.com (free account - 300 emails/day)
 * 3. Go to Settings > SMTP & API > API Keys
 * 4. Create a new API key and paste it below
 * 5. Update the FROM_EMAIL with your verified sender email
 */

// Your Brevo API key (get from https://app.brevo.com/settings/keys/api)
define('BREVO_API_KEY', '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');

// Your verified sender email (must be verified in Brevo)
define('BREVO_FROM_EMAIL', 'noreply@carbontype.co');

// Sender name that appears in emails
define('BREVO_FROM_NAME', 'Carbontype');
