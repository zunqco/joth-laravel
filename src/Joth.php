<?php

namespace Octha\Joth;

use Illuminate\Http\Request;

class Joth
{
    public static function generateKey($secret = null)
    {
        $hash = is_null($secret)
            ? bin2hex(random_bytes(24))
            : hash('sha256', (string) $secret);

        return [
            substr($hash, 0, 32),
            substr($hash, 32, 16),
        ];
    }

    /**
     * Encode any value
     *
     * @param mixed $value Value to encode
     * @param string|int — Your secret password
     * @return string
     */
    public static function encode($value, $secret)
    {
        [$passphrase, $iv] = static::generateKey($secret);

        return base64_encode((string) openssl_encrypt(
            (string) $value,
            'aes-256-cbc',
            $passphrase,
            true,
            $iv
        ));
    }

    /**
     * Decode a previously encrypted value
     *
     * @param string $str — Encoded value
     * @param string|int $secret — Your secret password
     * @return string|false — The decrypted string on success or false on failure.
     */
    public static function decode(string $str, $secret)
    {
        [$passphrase, $iv] = static::generateKey($secret);

        return openssl_decrypt(
            base64_decode($str),
            'aes-256-cbc',
            $passphrase,
            true,
            $iv
        );
    }

    /**
     * Get attributes from request
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public static function getAttrs(Request $request)
    {
        $config = config();

        foreach ($config->get('joth.attributes.routes', []) as $path => $attrs) {
            if (
                $request->fullUrlIs($path)
                || $request->is($path)
                || $request->is(trim($path, '/'))
            ) {
                return $attrs;
            }
        }

        return $config->get('joth.attributes.globals', []);
    }
}
