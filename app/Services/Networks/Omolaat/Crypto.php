<?php

declare(strict_types=1);

// High-performance PHP port of Bubble payload crypto used in the Python scripts.
// Implements AES-CBC with manual PKCS7 padding and PBKDF2-MD5 key derivation
// to match exactly the behavior in payload_encrypter.py and payload_decrypter.py.

namespace App\Services\Networks\Omolaat;

final class Crypto
{
    // Fixed IV tags as used by Bubble (same as Python)
    private const FIXED_IV_Y = 'po9';
    private const FIXED_IV_X = 'fl1';

    /**
     * Add PKCS7 padding to the given binary string to a 16-byte block.
     */
    private static function padPkcs7(string $data): string
    {
        $padLen = 16 - (strlen($data) % 16);
        if ($padLen === 0) {
            $padLen = 16; // Always add a full block when already aligned
        }
        return $data . str_repeat(chr($padLen), $padLen);
    }

    /**
     * Remove PKCS7 padding from a binary string.
     */
    private static function unpadPkcs7(string $data): string
    {
        $len = strlen($data);
        if ($len === 0) {
            return $data;
        }
        $pad = ord($data[$len - 1]);
        if ($pad < 1 || $pad > 16) {
            // Fallback: return as-is to preserve backward compatibility
            return $data;
        }
        return substr($data, 0, $len - $pad);
    }

    /**
     * PBKDF2-MD5 key derivation.
     */
    private static function pbkdf2Md5(string $password, string $salt, int $iterations, int $length): string
    {
        // hash_pbkdf2 returns hex by default; use raw_output=true for binary.
        return hash_pbkdf2('md5', $password, $salt, $iterations, $length, true);
    }

    /**
     * AES-256-CBC encrypt with manual PKCS7 padding and ZERO_PADDING flag.
     */
    private static function aesCbcEncrypt(string $plaintext, string $key, string $iv): string
    {
        $padded = self::padPkcs7($plaintext);
        // OPENSSL_ZERO_PADDING ensures OpenSSL does not add its own padding.
        $ciphertext = openssl_encrypt($padded, 'AES-256-CBC', $key, OPENSSL_ZERO_PADDING | OPENSSL_RAW_DATA, $iv);
        if ($ciphertext === false) {
            throw new \RuntimeException('OpenSSL encryption failed');
        }
        return $ciphertext;
    }

    /**
     * AES-256-CBC decrypt with manual PKCS7 unpadding and ZERO_PADDING flag.
     */
    private static function aesCbcDecrypt(string $ciphertext, string $key, string $iv): string
    {
        $decrypted = openssl_decrypt($ciphertext, 'AES-256-CBC', $key, OPENSSL_ZERO_PADDING | OPENSSL_RAW_DATA, $iv);
        if ($decrypted === false) {
            throw new \RuntimeException('OpenSSL decryption failed');
        }
        return self::unpadPkcs7($decrypted);
    }

    /**
     * Encrypt fixed-IV values (x or y) exactly like Python encrypt_with_fixed_iv.
     */
    public static function encryptWithFixedIv(string $appname, string $data, string $fixedIvTag): string
    {
        $derivedIv = self::pbkdf2Md5($fixedIvTag, $appname, 7, 16);
        $derivedKey = self::pbkdf2Md5($appname, $appname, 7, 32);
        $ciphertext = self::aesCbcEncrypt($data, $derivedKey, $derivedIv);
        return base64_encode($ciphertext);
    }

    /**
     * Decrypt fixed-IV values (x or y).
     */
    public static function decryptWithFixedIv(string $appname, string $encryptedB64, string $fixedIvTag): string
    {
        $ciphertext = base64_decode($encryptedB64, true);
        if ($ciphertext === false) {
            throw new \InvalidArgumentException('Invalid base64');
        }
        $derivedIv = self::pbkdf2Md5($fixedIvTag, $appname, 7, 16);
        $derivedKey = self::pbkdf2Md5($appname, $appname, 7, 32);
        return self::aesCbcDecrypt($ciphertext, $derivedKey, $derivedIv);
    }

    /**
     * Encrypt main payload (z) using timestamp and IV bytes.
     */
    public static function encryptPayload(string $appname, string $timestamp, string $ivBytes, string $payload): string
    {
        $keyMaterial = str_replace("\x01", '', $appname . $timestamp);
        $derivedKey = self::pbkdf2Md5($keyMaterial, $appname, 7, 32);
        $derivedIv = self::pbkdf2Md5($ivBytes, $appname, 7, 16);
        $ciphertext = self::aesCbcEncrypt($payload, $derivedKey, $derivedIv);
        return base64_encode($ciphertext);
    }

    /**
     * Decrypt main payload (z).
     */
    public static function decryptPayload(string $appname, string $timestamp, string $ivBytes, string $encryptedB64): string
    {
        $ciphertext = base64_decode($encryptedB64, true);
        if ($ciphertext === false) {
            throw new \InvalidArgumentException('Invalid base64');
        }
        $keyMaterial = str_replace("\x01", '', $appname . $timestamp);
        $derivedKey = self::pbkdf2Md5($keyMaterial, $appname, 7, 32);
        $derivedIv = self::pbkdf2Md5($ivBytes, $appname, 7, 16);
        return self::aesCbcDecrypt($ciphertext, $derivedKey, $derivedIv);
    }

    /**
     * High-level encryption for Bubble payloads. Returns x/y/z + raw timestamp/iv.
     * $payload can be array|object|string; arrays/objects will be json-encoded UTF-8.
     */
    public static function encryptBubblePayload(string $appname, $payload, ?string $timestamp = null, ?string $ivBytes = null): array
    {
        if ($timestamp === null) {
            $timestamp = (string) (int) (microtime(true) * 1000);
        }
        if ($ivBytes === null) {
            $ivBytes = random_bytes(16);
        }
        if (is_array($payload) || is_object($payload)) {
            $payload = json_encode($payload, JSON_UNESCAPED_UNICODE);
        }

        $timestampWithSuffix = $timestamp . '_1';
        $yEncrypted = self::encryptWithFixedIv($appname, $timestampWithSuffix, self::FIXED_IV_Y);
        $xEncrypted = self::encryptWithFixedIv($appname, $ivBytes, self::FIXED_IV_X);
        $zEncrypted = self::encryptPayload($appname, $timestamp, $ivBytes, (string) $payload);

        return [
            'x' => $xEncrypted,
            'y' => $yEncrypted,
            'z' => $zEncrypted,
            'timestamp' => $timestamp,
            'iv' => bin2hex($ivBytes),
        ];
    }

    /**
     * High-level decryption for Bubble payloads. Returns [timestamp, ivBytes, payloadStr].
     */
    public static function decryptBubblePayload(string $appname, string $xEncrypted, string $yEncrypted, string $zEncrypted): array
    {
        $decodedYRaw = self::decryptWithFixedIv($appname, $yEncrypted, self::FIXED_IV_Y);
        $decodedYStr = $decodedYRaw;
        $timestamp = $decodedYStr;
        if (substr($decodedYStr, -2) === '_1') {
            $timestamp = substr($decodedYStr, 0, -2);
        } else {
            $timestamp = str_replace('_1', '', $decodedYStr);
        }

        $ivBytes = self::decryptWithFixedIv($appname, $xEncrypted, self::FIXED_IV_X);
        // Backward compatibility cleanup (mirrors Python logic)
        $ivBytes = str_replace(["\x0e", "\r", "\x0f"], '', $ivBytes);

        $payloadStr = self::decryptPayload($appname, $timestamp, $ivBytes, $zEncrypted);
        return [$timestamp, $ivBytes, $payloadStr];
    }
}
