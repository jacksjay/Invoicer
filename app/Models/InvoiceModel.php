<?php

namespace App\Models;

use CodeIgniter\Model;
use App\Config\FileMaker;

class InvoiceModel extends Model
{
    private $config;
    private $token;
    private $lastError;

    public function __construct()
    {
        parent::__construct();
        
        // Load FileMaker configuration from Config file
        $fileMakerConfig = new FileMaker();
        $this->config = [
            'server' => $fileMakerConfig->server,
            'database' => $fileMakerConfig->database,
            'username' => $fileMakerConfig->username,
            'password' => $fileMakerConfig->password,
            'timeout' => $fileMakerConfig->timeout,
            'connectTimeout' => $fileMakerConfig->connectTimeout,
            'sslVerifyPeer' => $fileMakerConfig->sslVerifyPeer,
            'sslVerifyHost' => $fileMakerConfig->sslVerifyHost,
        ];
    }

    /**
     * Authenticate with FileMaker API and store access token
     */
    public function authenticate()
    {
        try {
            $server = rtrim(trim($this->config['server']), '/');
            $encodedDatabase = urlencode($this->config['database']);
            $url = "$server/fmi/data/v1/databases/$encodedDatabase/sessions";
            
            // Initialize cURL request
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => '{}',
                CURLOPT_USERPWD => "{$this->config['username']}:{$this->config['password']}",
                CURLOPT_HTTPAUTH => CURLAUTH_BASIC,
                CURLOPT_HTTPHEADER => [
                    "Content-Type: application/json",
                    "Accept: application/json"
                ],
                CURLOPT_SSL_VERIFYPEER => $this->config['sslVerifyPeer'],
                CURLOPT_SSL_VERIFYHOST => $this->config['sslVerifyHost'],
                CURLOPT_TIMEOUT => $this->config['timeout'],
                CURLOPT_CONNECTTIMEOUT => $this->config['connectTimeout']
            ]);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);
            
            // Handle cURL errors
            if ($curlError) {
                $this->lastError = "Connection error: $curlError";
                log_message('error', 'FileMaker cURL Error: ' . $curlError);
                return false;
            }
            
            // Handle HTTP response errors
            if ($httpCode !== 200) {
                $this->lastError = "Authentication failed with HTTP $httpCode: $response";
                log_message('error', 'FileMaker Auth Error: HTTP ' . $httpCode . ' - ' . $response);
                return false;
            }
            
            // Parse JSON response and extract token
            $data = json_decode($response, true);
            if (!$data || !isset($data['response']['token'])) {
                $this->lastError = "Invalid response format: " . substr($response, 0, 200);
                log_message('error', 'FileMaker Invalid Response: ' . substr($response, 0, 200));
                return false;
            }
            
            $this->token = $data['response']['token'];
            return true;
            
        } catch (\Exception $e) {
            $this->lastError = "Exception during authentication: " . $e->getMessage();
            log_message('error', 'FileMaker Auth Exception: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Fetch all invoice records from FileMaker with pagination support
     */
    public function getAllInvoices($layout = 'Invoice', $limit = 50, $offset = 1)
    {
        if (!$this->token && !$this->authenticate()) {
            return [
                'success' => false,
                'error' => $this->lastError ?? 'Authentication failed',
                'data' => [],
                'foundCount' => 0,
                'returnedCount' => 0
            ];
        }
        
        try {
            $server = rtrim(trim($this->config['server']), '/');
            $encodedDatabase = urlencode($this->config['database']);
            $encodedLayout = urlencode($layout);
            
            // Build URL with pagination parameters
            $url = "$server/fmi/data/v1/databases/$encodedDatabase/layouts/$encodedLayout/records";
            $url .= "?_limit=$limit&_offset=$offset";
            
            // Initialize cURL request for fetching records
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER => [
                    "Authorization: Bearer {$this->token}",
                    "Content-Type: application/json",
                    "Accept: application/json"
                ],
                CURLOPT_SSL_VERIFYPEER => $this->config['sslVerifyPeer'],
                CURLOPT_SSL_VERIFYHOST => $this->config['sslVerifyHost'],
                CURLOPT_TIMEOUT => $this->config['timeout']
            ]);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);
            
            // Handle cURL and HTTP errors
            if ($curlError) {
                log_message('error', 'FileMaker Fetch cURL Error: ' . $curlError);
                return [
                    'success' => false,
                    'error' => "Connection error: $curlError",
                    'data' => [],
                    'foundCount' => 0,
                    'returnedCount' => 0
                ];
            }
            
            if ($httpCode !== 200) {
                log_message('error', 'FileMaker Fetch HTTP Error: ' . $httpCode . ' - ' . $response);
                return [
                    'success' => false,
                    'error' => "Request failed with HTTP $httpCode",
                    'data' => [],
                    'foundCount' => 0,
                    'returnedCount' => 0
                ];
            }
            
            // Decode response and format data
            $data = json_decode($response, true);
            if (!$data || !isset($data['response']['data'])) {
                log_message('error', 'FileMaker Invalid Fetch Response: ' . substr($response, 0, 200));
                return [
                    'success' => false,
                    'error' => "Invalid response format",
                    'data' => [],
                    'foundCount' => 0,
                    'returnedCount' => 0
                ];
            }
            
            // Format invoice data
            $invoices = $this->formatInvoiceData($data['response']['data']);
            
            return [
                'success' => true,
                'data' => $invoices,
                'invoices' => $invoices, // For backward compatibility
                'foundCount' => $data['response']['dataInfo']['foundCount'] ?? count($invoices),
                'returnedCount' => $data['response']['dataInfo']['returnedCount'] ?? count($invoices)
            ];
            
        } catch (\Exception $e) {
            log_message('error', 'FileMaker Fetch Exception: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => "Exception: " . $e->getMessage(),
                'data' => [],
                'foundCount' => 0,
                'returnedCount' => 0
            ];
        }
    }

    /**
     * Search invoices based on criteria
     */
    public function searchInvoices($searchData)
    {
        if (!$this->token && !$this->authenticate()) {
            return [
                'success' => false,
                'error' => $this->lastError ?? 'Authentication failed',
                'data' => [],
                'foundCount' => 0,
                'returnedCount' => 0
            ];
        }
        
        try {
            $server = rtrim(trim($this->config['server']), '/');
            $encodedDatabase = urlencode($this->config['database']);
            $encodedLayout = urlencode('Invoice');
            $url = "$server/fmi/data/v1/databases/$encodedDatabase/layouts/$encodedLayout/_find";
            
            // Build search query
            $query = [];
            if (is_array($searchData)) {
                foreach ($searchData as $field => $value) {
                    if (!empty($value)) {
                        $query[] = [$field => $value];
                    }
                }
            } else {
                // If searchData is a string, search across multiple fields
                $searchTerm = $searchData;
                $query[] = [
                    "InvoiceNumber" => "*$searchTerm*",
                    "CustomerName" => "*$searchTerm*",
                    "Status" => "*$searchTerm*"
                ];
            }
            
            if (empty($query)) {
                return $this->getAllInvoices();
            }
            
            $postData = json_encode(['query' => $query]);
            
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $postData,
                CURLOPT_HTTPHEADER => [
                    "Authorization: Bearer {$this->token}",
                    "Content-Type: application/json",
                    "Accept: application/json"
                ],
                CURLOPT_SSL_VERIFYPEER => $this->config['sslVerifyPeer'],
                CURLOPT_SSL_VERIFYHOST => $this->config['sslVerifyHost'],
                CURLOPT_TIMEOUT => $this->config['timeout']
            ]);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);
            
            if ($curlError) {
                log_message('error', 'FileMaker Search cURL Error: ' . $curlError);
                return [
                    'success' => false,
                    'error' => "Connection error: $curlError",
                    'data' => [],
                    'foundCount' => 0,
                    'returnedCount' => 0
                ];
            }
            
            if ($httpCode !== 200) {
                log_message('error', 'FileMaker Search HTTP Error: ' . $httpCode . ' - ' . $response);
                return [
                    'success' => false,
                    'error' => "Search failed with HTTP $httpCode",
                    'data' => [],
                    'foundCount' => 0,
                    'returnedCount' => 0
                ];
            }
            
            $data = json_decode($response, true);
            if (!$data || !isset($data['response']['data'])) {
                return [
                    'success' => true,
                    'data' => [],
                    'invoices' => [],
                    'foundCount' => 0,
                    'returnedCount' => 0
                ];
            }
            
            $invoices = $this->formatInvoiceData($data['response']['data']);
            
            return [
                'success' => true,
                'data' => $invoices,
                'invoices' => $invoices,
                'foundCount' => $data['response']['dataInfo']['foundCount'] ?? count($invoices),
                'returnedCount' => $data['response']['dataInfo']['returnedCount'] ?? count($invoices)
            ];
            
        } catch (\Exception $e) {
            log_message('error', 'FileMaker Search Exception: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => "Exception: " . $e->getMessage(),
                'data' => [],
                'foundCount' => 0,
                'returnedCount' => 0
            ];
        }
    }

    /**
     * Format raw FileMaker data into standardized invoice array
     */
    private function formatInvoiceData($rawData)
    {
        $invoices = [];
        
        foreach ($rawData as $record) {
            $fieldData = $record['fieldData'];
            $invoices[] = [
                'recordId' => $record['recordId'],
                'invoiceNumber' => $fieldData['InvoiceNumber'] ?? $fieldData['Invoice_Number'] ?? $fieldData['invoice_number'] ?? null,
                'customerName' => $fieldData['CustomerName'] ?? $fieldData['Customer_Name'] ?? $fieldData['customer_name'] ?? null,
                'invoiceDate' => $fieldData['InvoiceDate'] ?? $fieldData['Invoice_Date'] ?? $fieldData['invoice_date'] ?? null,
                'dueDate' => $fieldData['DueDate'] ?? $fieldData['Due_Date'] ?? $fieldData['due_date'] ?? null,
                'totalAmount' => $fieldData['TotalAmount'] ?? $fieldData['Total_Amount'] ?? $fieldData['total_amount'] ?? $fieldData['Amount'] ?? 0,
                'status' => $fieldData['Status'] ?? $fieldData['InvoiceStatus'] ?? $fieldData['Invoice_Status'] ?? 'Pending',
                'description' => $fieldData['Description'] ?? $fieldData['Notes'] ?? null
            ];
        }
        
        return $invoices;
    }

    /**
     * Get the last error message
     */
    public function getLastError()
    {
        return $this->lastError;
    }

    /**
     * Close FileMaker session (cleanup)
     */
    public function closeSession()
    {
        if (!$this->token) {
            return true;
        }
        
        try {
            $server = rtrim(trim($this->config['server']), '/');
            $encodedDatabase = urlencode($this->config['database']);
            $url = "$server/fmi/data/v1/databases/$encodedDatabase/sessions/{$this->token}";
            
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_CUSTOMREQUEST => 'DELETE',
                CURLOPT_HTTPHEADER => [
                    "Authorization: Bearer {$this->token}",
                    "Content-Type: application/json"
                ],
                CURLOPT_SSL_VERIFYPEER => $this->config['sslVerifyPeer'],
                CURLOPT_SSL_VERIFYHOST => $this->config['sslVerifyHost'],
                CURLOPT_TIMEOUT => 10
            ]);
            
            curl_exec($ch);
            curl_close($ch);
            
            $this->token = null;
            return true;
            
        } catch (\Exception $e) {
            log_message('error', 'FileMaker Session Close Error: ' . $e->getMessage());
            return false;
        }
    }
}