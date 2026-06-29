<?php

/**
 * Parse database host/port/socket from DB_HOST format.
 * Fully optimized for PHP 8+ strict type matching.
 */
class WpTesting_WordPress_Value_DbHost extends WpTesting_WordPress_Value_Base
{
    private $host = null;
    private $port = null;
    private $socket = null;

    public function __construct($value)
    {
        // تبدیل ورودی به رشته برای جلوگیری از خطاهای نوع داده در PHP 8+
        $stringValue = (string)$value;
        parent::__construct($stringValue);

        // First peel off the socket parameter from the right, if it exists.
        $socketPosition = strpos($stringValue, ':/' );
        if ($socketPosition !== false) {
            $this->socket = substr($stringValue, $socketPosition + 1);
            $stringValue = substr($stringValue, 0, $socketPosition);
        }

        // We need to check for an IPv6 address first.
        // An IPv6 address will always contain at least two colons.
        $isIpv6 = (substr_count($stringValue, ':') > 1);

        if ($isIpv6) {
            $pattern = '#^(?:\[)?(?<host>[0-9a-fA-F:]+)(?:\]:(?<port>[\d]+))?#';
        } else {
            $pattern = '#^(?<host>[^:/]*)(?::(?<port>[\d]+))?#';
        }

        $matches = array();
        preg_match($pattern, $stringValue, $matches);

        if (!empty($matches['host'])) {
            $this->host = $matches['host'];
            if ($isIpv6 && extension_loaded('mysqlnd')) {
                $this->host = '['.$this->host.']';
            }
        }
        if (!empty($matches['port'])) {
            $this->port = (int)$matches['port'];
        }
    }

    /**
     * @return string|null
     */
    public function host()
    {
        return $this->host;
    }

    /**
     * @return int|null
     */
    public function port()
    {
        return $this->port;
    }

    /**
     * @return string|null
     */
    public function socket()
    {
        return $this->socket;
    }
}
