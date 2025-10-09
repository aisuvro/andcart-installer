<?php

namespace Jmrashed\LaravelInstaller\Helpers;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;

class SecurityHelper
{
    /**
     * Sanitize input data to prevent XSS and injection attacks
     * For environment variables, we need to preserve quotes as literals
     */
    public static function sanitizeInput($input, $allowHtml = false, $preserveQuotes = false)
    {
        if (is_array($input)) {
            return array_map(function($item) use ($allowHtml, $preserveQuotes) {
                return self::sanitizeInput($item, $allowHtml, $preserveQuotes);
            }, $input);
        }

        if (!$allowHtml) {
            $input = strip_tags($input);
        }

        // For .env files, don't encode quotes since they need to be literal
        if ($preserveQuotes) {
            return $input;
        }

        return htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Validate database credentials format
     */
    public static function validateDatabaseCredentials($credentials)
    {
        $required = ['hostname', 'port', 'name', 'username'];
        
        foreach ($required as $field) {
            if (empty($credentials["database_$field"])) {
                return false;
            }
        }

        // Validate hostname format
        if (!filter_var($credentials['database_hostname'], FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME)) {
            if (!filter_var($credentials['database_hostname'], FILTER_VALIDATE_IP)) {
                return false;
            }
        }

        // Validate port range
        $port = (int) $credentials['database_port'];
        if ($port < 1 || $port > 65535) {
            return false;
        }

        return true;
    }

    /**
     * Check if request is rate limited
     */
    public static function checkRateLimit($key, $maxAttempts = 5, $decayMinutes = 1)
    {
        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            $seconds = RateLimiter::availableIn($key);
            Log::warning('Rate limit exceeded', [
                'key' => $key,
                'ip' => request()->ip(),
                'retry_after' => $seconds
            ]);
            return $seconds;
        }

        RateLimiter::hit($key, $decayMinutes * 60);
        return false;
    }

    /**
     * Validate environment configuration
     */
    public static function validateEnvironmentConfig($config)
    {
        $dangerous = ['<?php', '<?=', '<script', 'eval(', 'exec(', 'system('];
        
        foreach ($dangerous as $pattern) {
            if (stripos($config, $pattern) !== false) {
                Log::alert('Dangerous code detected in environment config', [
                    'pattern' => $pattern,
                    'ip' => request()->ip()
                ]);
                return false;
            }
        }

        return true;
    }
}