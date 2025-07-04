<?php

namespace App\Libraries;

use Exception;

class FileMakerAPI
{
    private $host;
    private $database;
    private $username;
    private $password;
    private $token;
    private $baseUrl;
    private $timeout = 30;

    public function __construct()
    {
        $this->host = getenv('FILEMAKER_HOST');
        $this->database = getenv('FILEMAKER_DATABASE');
        $this->username = getenv('FILEMAKER_USERNAME');
        $this->password = getenv('FILEMAKER_PASSWORD');
        
        if (!$this->host || !$this->database || !$this->username || !$this->password) {
            throw new Exception('FileMaker configuration is incomplete. Please check your .env file.');
        }
        
        $this->baseUrl = "https://{$this->host}/fmi/data/vLatest/databases/{$this->database}";
    }

    /**
     * Authenticate and get session token
     */
    public function authenticate()
    {
        $url = $this->baseUrl . "/sessions";
        
        $response = $this->makeRequest($url, 'POST', [], [
            'Content-Type: application/json',
            'Authorization: Basic ' . base64_encode($this->username . ':' . $this->password)
        ]);

        if ($response['httpCode'] === 200 && isset($response['data']['response']['token'])) {
            $this->token = $response['data']['response']['token'];
            log_message('info', 'FileMaker authentication successful');
            return true;
        }

        log_message('error', 'FileMaker Authentication Failed: ' . json_encode($response));
        return false;
    }

    /**
     * Get all records from a layout with sorting
     */
    public function getRecords($layout, $limit = 100, $offset = 1, $sort = null)
    {
        if (!$this->token && !$this->authenticate()) {
            return false;
        }

        $url = $this->baseUrl . "/layouts/{$layout}/records";
        $params = ["_limit" => $limit, "_offset" => $offset];
        
        if ($sort && is_array($sort)) {
            $params['_sort'] = json_encode($sort);
        }
        
        $url .= '?' . http_build_query($params);

        $response = $this->makeRequest($url, 'GET', [], [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->token
        ]);

        if ($response['httpCode'] === 200) {
            return $response['data'];
        }

        log_message('error', 'FileMaker Get Records Failed: ' . json_encode($response));
        return false;
    }

    /**
     * Get a single record by recordId
     */
    public function getRecord($layout, $recordId)
    {
        if (!$this->token && !$this->authenticate()) {
            return false;
        }

        $url = $this->baseUrl . "/layouts/{$layout}/records/{$recordId}";

        $response = $this->makeRequest($url, 'GET', [], [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->token
        ]);

        if ($response['httpCode'] === 200) {
            return $response['data'];
        }

        log_message('error', 'FileMaker Get Record Failed: ' . json_encode($response));
        return false;
    }

    /**
     * Find records with query and sorting
     */
    public function findRecords($layout, $query = [], $limit = 100, $offset = 1, $sort = null)
    {
        if (!$this->token && !$this->authenticate()) {
            return false;
        }

        $url = $this->baseUrl . "/layouts/{$layout}/_find";

        $postData = [
            'query' => $query,
            'limit' => $limit,
            'offset' => $offset
        ];

        if ($sort && is_array($sort)) {
            $postData['sort'] = $sort;
        }

        $response = $this->makeRequest($url, 'POST', $postData, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->token
        ]);

        if ($response['httpCode'] === 200) {
            return $response['data'];
        }

        // Handle "No records match the request" error (code 401)
        if ($response['httpCode'] === 404 || 
            (isset($response['data']['messages'][0]['code']) && $response['data']['messages'][0]['code'] == '401')) {
            return [
                'response' => [
                    'data' => [],
                    'foundCount' => 0,
                    'returnedCount' => 0
                ],
                'messages' => [['code' => '401', 'message' => 'No records match the request']]
            ];
        }

        log_message('error', 'FileMaker Find Records Failed: ' . json_encode($response));
        return false;
    }

    /**
     * Create a new record
     */
    public function createRecord($layout, $fieldData)
    {
        if (!$this->token && !$this->authenticate()) {
            return false;
        }

        $url = $this->baseUrl . "/layouts/{$layout}/records";

        $postData = [
            'fieldData' => $fieldData
        ];

        $response = $this->makeRequest($url, 'POST', $postData, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->token
        ]);

        if ($response['httpCode'] === 200) {
            return $response['data'];
        }

        log_message('error', 'FileMaker Create Record Failed: ' . json_encode($response));
        return false;
    }

    /**
     * Update a record
     */
    public function updateRecord($layout, $recordId, $fieldData)
    {
        if (!$this->token && !$this->authenticate()) {
            return false;
        }

        $url = $this->baseUrl . "/layouts/{$layout}/records/{$recordId}";

        $postData = [
            'fieldData' => $fieldData
        ];

        $response = $this->makeRequest($url, 'PATCH', $postData, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->token
        ]);

        if ($response['httpCode'] === 200) {
            return $response['data'];
        }

        log_message('error', 'FileMaker Update Record Failed: ' . json_encode($response));
        return false;
    }

    /**
     * Delete a record
     */
    public function deleteRecord($layout, $recordId)
    {
        if (!$this->token && !$this->authenticate()) {
            return false;
        }

        $url = $this->baseUrl . "/layouts/{$layout}/records/{$recordId}";

        $response = $this->makeRequest($url, 'DELETE', [], [
            'Authorization: Bearer ' . $this->token
        ]);

        if ($response['httpCode'] === 200) {
            return $response['data'];
        }

        log_message('error', 'FileMaker Delete Record Failed: ' . json_encode($response));
        return false;
    }

    /**
     * Execute a script
     */
    public function executeScript($layout, $scriptName, $scriptParameter = null)
    {
        if (!$this->token && !$this->authenticate()) {
            return false;
        }

        $url = $this->baseUrl . "/layouts/{$layout}/script/{$scriptName}";

        $postData = [];
        if ($scriptParameter !== null) {
            $postData['script.param'] = $scriptParameter;
        }

        $response = $this->makeRequest($url, 'GET', $postData, [
            'Authorization: Bearer ' . $this->token
        ]);

        if ($response['httpCode'] === 200) {
            return $response['data'];
        }

        log_message('error', 'FileMaker Execute Script Failed: ' . json_encode($response));
        return false;
    }

    /**
     * Get database names (if user has access)
     */
    public function getDatabases()
    {
        $url = "https://{$this->host}/fmi/data/vLatest/databases";

        $response = $this->makeRequest($url, 'GET', [], [
            'Authorization: Basic ' . base64_encode($this->username . ':' . $this->password)
        ]);

        if ($response['httpCode'] === 200) {
            return $response['data'];
        }

        log_message('error', 'FileMaker Get Databases Failed: ' . json_encode($response));
        return false;
    }

    /**
     * Get layout names
     */
    public function getLayouts()
    {
        if (!$this->token && !$this->authenticate()) {
            return false;
        }

        $url = $this->baseUrl . "/layouts";

        $response = $this->makeRequest($url, 'GET', [], [
            'Authorization: Bearer ' . $this->token
        ]);

        if ($response['httpCode'] === 200) {
            return $response['data'];
        }

        log_message('error', 'FileMaker Get Layouts Failed: ' . json_encode($response));
        return false;
    }

    /**
     * Get layout metadata
     */
    public function getLayoutMetadata($layout)
    {
        if (!$this->token && !$this->authenticate()) {
            return false;
        }

        $url = $this->baseUrl . "/layouts/{$layout}";

        $response = $this->makeRequest($url, 'GET', [], [
            'Authorization: Bearer ' . $this->token
        ]);

        if ($response['httpCode'] === 200) {
            return $response['data'];
        }

        log_message('error', 'FileMaker Get Layout Metadata Failed: ' . json_encode($response));
        return false;
    }

    /**
     * Close session
     */
    public function logout()
    {
        if (!$this->token) {
            return true;
        }

        $url = $this->baseUrl . "/sessions/{$this->token}";

        $response = $this->makeRequest($url, 'DELETE', [], [
            'Authorization: Bearer ' . $this->token
        ]);

        $this->token = null;
        log_message('info', 'FileMaker session closed');
        return true;
    }

    /**
     * Make HTTP request to FileMaker Data API
     */
    private function makeRequest($url, $method = 'GET', $data = [], $headers = [])
    {
        $ch = curl_init();
        
        // Basic cURL options
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 3,
            CURLOPT_USERAGENT => 'CodeIgniter-FileMaker-Client/1.0'
        ]);

        // Set method-specific options
        switch (strtoupper($method)) {
            case 'POST':
                curl_setopt($ch, CURLOPT_POST, true);
                if (!empty($data)) {
                    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
                }
                break;
                
            case 'PUT':
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
                if (!empty($data)) {
                    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
                }
                break;
                
            case 'PATCH':
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
                if (!empty($data)) {
                    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
                }
                break;
                
            case 'DELETE':
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
                break;
                
            case 'GET':
            default:
                if (!empty($data)) {
                    $url .= (strpos($url, '?') !== false ? '&' : '?') . http_build_query($data);
                    curl_setopt($ch, CURLOPT_URL, $url);
                }
                break;
        }

        // Set headers
        if (!empty($headers)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }

        // Execute request
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        
        curl_close($ch);

        // Handle cURL errors
        if ($response === false || !empty($error)) {
            log_message('error', 'cURL Error: ' . $error);
            return [
                'success' => false,
                'httpCode' => 0,
                'data' => null,
                'error' => $error
            ];
        }

        // Parse JSON response
        $decodedResponse = json_decode($response, true);
        
        return [
            'success' => $httpCode >= 200 && $httpCode < 300,
            'httpCode' => $httpCode,
            'data' => $decodedResponse,
            'rawResponse' => $response
        ];
    }

    /**
     * Check if API is connected and authenticated
     */
    public function isConnected()
    {
        return !empty($this->token);
    }

    /**
     * Test connection to FileMaker server
     */
    public function testConnection()
    {
        try {
            $result = $this->authenticate();
            if ($result) {
                $this->logout();
                return [
                    'success' => true,
                    'message' => 'Successfully connected to FileMaker server'
                ];
            }
            
            return [
                'success' => false,
                'message' => 'Failed to authenticate with FileMaker server'
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Connection error: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get API version info
     */
    public function getApiInfo()
    {
        $url = "https://{$this->host}/fmi/data/vLatest/productInfo";

        $response = $this->makeRequest($url, 'GET');

        if ($response['httpCode'] === 200) {
            return $response['data'];
        }

        return false;
    }
}