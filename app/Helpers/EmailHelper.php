<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;

class EmailHelper
{
    /**
     * Send a quick text email using the sendmail mailer with a native mail() fallback.
     * Returns true on success, false otherwise.
     */
    public static function sendViaSendmail(string $to, string $subject, string $body, ?string $fromAddress = null, ?string $fromName = null): bool
    {
        $resolvedFromAddress = $fromAddress ?: (string) Config::get('mail.from.address', 'no-reply@localhost');
        $resolvedFromName = $fromName ?: (string) Config::get('mail.from.name', 'Advanced Coupon System');

        // Try Laravel Mail with the sendmail transport first (fast, minimal setup)
        try {
            Mail::mailer('sendmail')->raw($body, function ($message) use ($to, $subject, $resolvedFromAddress, $resolvedFromName) {
                $message->to($to)
                    ->subject($subject)
                    ->from($resolvedFromAddress, $resolvedFromName);
            });
            return true;
        } catch (\Throwable $e) {
            // Fallback to native mail() below
        }

        // Native mail() fallback with UTF-8 safe headers
        $headers = [];
        $headers[] = 'From: ' . sprintf('%s <%s>', self::encodeDisplayName($resolvedFromName), $resolvedFromAddress);
        $headers[] = 'MIME-Version: 1.0';
        $headers[] = 'Content-Type: text/plain; charset=UTF-8';
        $headers[] = 'X-Mailer: PHP/' . phpversion();

        $headersString = implode("\r\n", $headers);

        return @mail($to, self::encodeSubject($subject), $body, $headersString);
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


