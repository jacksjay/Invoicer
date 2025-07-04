<?php

namespace App\Config;

use CodeIgniter\Config\BaseConfig;

class FileMaker extends BaseConfig
{
    /**
     * FileMaker Server URL
     * Should include protocol (https://) and port if needed
     */
    public string $server = 'https://172.16.8.104';

    /**
     * FileMaker Database Name
     */
    public string $database = 'InvoicerS';

    /**
     * FileMaker Username for API access
     */
    public string $username = 'Admin';

    /**
     * FileMaker Password for API access
     */
    public string $password = 'Jk@123';

    /**
     * Default layout name for invoice operations
     */
    public string $invoiceLayout = 'Invoice';

    /**
     * Connection timeout in seconds
     */
    public int $timeout = 30;

    /**
     * Connection timeout in seconds
     */
    public int $connectTimeout = 15;

    /**
     * Whether to verify SSL certificates
     * Set to true in production environment
     */
    public bool $sslVerifyPeer = false;

    /**
     * Whether to verify SSL host
     * Set to true in production environment
     */
    public bool $sslVerifyHost = false;

    /**
     * Session timeout in seconds
     * FileMaker sessions expire after this time of inactivity
     */
    public int $sessionTimeout = 900; // 15 minutes

    /**
     * Maximum number of retry attempts for failed requests
     */
    public int $maxRetries = 3;

    /**
     * Delay between retry attempts in seconds
     */
    public int $retryDelay = 1;

    /**
     * Whether to log FileMaker API requests and responses
     * Useful for debugging but may impact performance
     */
    public bool $enableLogging = true;

    /**
     * Log level for FileMaker operations
     * 1 = Emergency, 2 = Alert, 3 = Critical, 4 = Error,
     * 5 = Warning, 6 = Notice, 7 = Info, 8 = Debug
     */
    public int $logLevel = 4; // Error level

    public function __construct()
    {
        parent::__construct();

        // Override with environment variables if available
        $this->server = env('FILEMAKER_SERVER', $this->server);
        $this->database = env('FILEMAKER_DATABASE', $this->database);
        $this->username = env('FILEMAKER_USERNAME', $this->username);
        $this->password = env('FILEMAKER_PASSWORD', $this->password);
        $this->invoiceLayout = env('FILEMAKER_INVOICE_LAYOUT', $this->invoiceLayout);
        $this->timeout = env('FILEMAKER_TIMEOUT', $this->timeout);
        $this->connectTimeout = env('FILEMAKER_CONNECT_TIMEOUT', $this->connectTimeout);
        $this->sslVerifyPeer = env('FILEMAKER_SSL_VERIFY_PEER', $this->sslVerifyPeer);
        $this->sslVerifyHost = env('FILEMAKER_SSL_VERIFY_HOST', $this->sslVerifyHost);
        $this->enableLogging = env('FILEMAKER_ENABLE_LOGGING', $this->enableLogging);
    }

    /**
     * Get base API URL for FileMaker Data API
     */
    public function getApiBaseUrl(): string
    {
        $server = rtrim(trim($this->server), '/');
        $encodedDatabase = urlencode($this->database);
        return "$server/fmi/data/v1/databases/$encodedDatabase";
    }

    /**
     * Get authentication URL
     */
    public function getAuthUrl(): string
    {
        return $this->getApiBaseUrl() . '/sessions';
    }

    /**
     * Get records URL for a specific layout
     */
    public function getRecordsUrl(string $layout): string
    {
        $encodedLayout = urlencode($layout);
        return $this->getApiBaseUrl() . "/layouts/$encodedLayout/records";
    }

    /**
     * Get session close URL
     */
    public function getSessionCloseUrl(string $token): string
    {
        return $this->getApiBaseUrl() . "/sessions/$token";
    }

    /**
     * Validate configuration
     */
    public function isValid(): bool
    {
        return !empty($this->server) && 
               !empty($this->database) && 
               !empty($this->username) && 
               !empty($this->password);
    }

    /**
     * Get configuration as array (for debugging, excludes sensitive data)
     */
    public function toArray(): array
    {
        return [
            'server' => $this->server,
            'database' => $this->database,
            'username' => $this->username,
            'password' => '***', // Hide password
            'invoiceLayout' => $this->invoiceLayout,
            'timeout' => $this->timeout,
            'connectTimeout' => $this->connectTimeout,
            'sslVerifyPeer' => $this->sslVerifyPeer,
            'sslVerifyHost' => $this->sslVerifyHost,
            'sessionTimeout' => $this->sessionTimeout,
            'maxRetries' => $this->maxRetries,
            'retryDelay' => $this->retryDelay,
            'enableLogging' => $this->enableLogging,
            'logLevel' => $this->logLevel
        ];
    }
}