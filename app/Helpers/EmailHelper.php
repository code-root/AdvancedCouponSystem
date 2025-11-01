<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class EmailHelper
{
    /**
     * Send a quick text email using native PHP mail() function only.
     * TEMPORARY: Currently using only native mail() function, other methods disabled.
     * Returns true on success, false otherwise.
     */
    public static function sendViaSendmail(string $to, string $subject, string $body, ?string $fromAddress = null, ?string $fromName = null): bool
    {
        $resolvedFromAddress = $fromAddress ?: (string) Config::get('mail.from.address', 'no-reply@localhost');
        $resolvedFromName = $fromName ?: (string) Config::get('mail.from.name', 'Advanced Coupon System');

        Log::info('EmailHelper: Attempting to send email via native mail()', [
            'to' => $to,
            'subject' => $subject,
            'from' => $resolvedFromAddress,
            'method' => 'native_mail'
        ]);

        // Use only native mail() function temporarily with enhanced headers for inbox delivery
        try {
            $domain = parse_url(Config::get('app.url', 'http://localhost'), PHP_URL_HOST) ?: 'localhost';
            $messageId = '<' . time() . '.' . uniqid() . '@' . $domain . '>';
            $date = date('r'); // RFC 2822 formatted date
            
            $headers = [];
            
            // Essential headers for inbox delivery
            $headers[] = 'From: ' . sprintf('%s <%s>', self::encodeDisplayName($resolvedFromName), $resolvedFromAddress);
            $headers[] = 'Reply-To: ' . $resolvedFromAddress;
            $headers[] = 'Return-Path: ' . $resolvedFromAddress;
            $headers[] = 'Message-ID: ' . $messageId;
            $headers[] = 'Date: ' . $date;
            
            // MIME headers
            $headers[] = 'MIME-Version: 1.0';
            $headers[] = 'Content-Type: text/plain; charset=UTF-8';
            $headers[] = 'Content-Transfer-Encoding: 8bit';
            
            // Priority and classification
            $headers[] = 'X-Priority: 3'; // Normal priority (1=Highest, 3=Normal, 5=Lowest)
            $headers[] = 'X-MSMail-Priority: Normal';
            $headers[] = 'Importance: normal';
            
            // Anti-spam headers
            $headers[] = 'Precedence: bulk';
            $headers[] = 'X-Mailer: trakifi/' . Config::get('app.version', '1.0') . ' (PHP/' . phpversion() . ')';
            
            // List-Unsubscribe header (helps with deliverability)
            $headers[] = 'List-Unsubscribe: <mailto:' . $resolvedFromAddress . '?subject=unsubscribe>';
            $headers[] = 'List-Unsubscribe-Post: List-Unsubscribe=One-Click';
            
            // Authentication headers (helps with inbox delivery)
            $headers[] = 'X-Authentication-Warning: ' . $domain . ' sender verified';
            
            $headersString = implode("\r\n", $headers);
            $encodedSubject = self::encodeSubject($subject);

            // Use additional parameters for mail() function
            $additionalParams = '-f' . $resolvedFromAddress;
            $result = @mail($to, $encodedSubject, $body, $headersString, $additionalParams);
            
            if ($result) {
                Log::info('EmailHelper: Email sent successfully via native mail()', [
                    'to' => $to,
                    'subject' => $subject
                ]);
                return true;
            } else {
                Log::error('EmailHelper: Native mail() returned false', [
                    'to' => $to,
                    'subject' => $subject
                ]);
                return false;
            }
        } catch (\Throwable $e) {
            Log::error('EmailHelper: Native mail() failed', [
                'to' => $to,
                'subject' => $subject,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }

    private static function encodeSubject(string $subject): string
    {
        return '=?UTF-8?B?' . base64_encode($subject) . '?=';
    }

    private static function encodeDisplayName(string $name): string
    {
        // Encode non-ASCII names; otherwise, quote if special chars present
        if (preg_match('/[^\x20-\x7E]/', $name)) {
            return '=?UTF-8?B?' . base64_encode($name) . '?=';
        }
        if (preg_match('/[(),:;<>@\[\\\]\"]/',$name)) {
            return '"' . addcslashes($name, '\\"') . '"';
        }
        return $name;
    }
}


