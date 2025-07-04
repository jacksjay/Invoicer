<?php
namespace App\Controllers;

use App\Models\InvoiceModel;

class Invoice extends BaseController
{
    private $invoiceModel;

    public function __construct()
    {
        $this->invoiceModel = new InvoiceModel();
    }

    /**
     * Display list of all invoices
     */
    public function index()
    {
        $limit = $this->request->getGet('limit') ?? 50;
        $page = $this->request->getGet('page') ?? 1;
        $offset = ($page - 1) * $limit + 1;

        // Get search parameters
        $searchData = [
            'invoice_number' => $this->request->getGet('invoice_number'),
            'client_name' => $this->request->getGet('client_name'),
            'status' => $this->request->getGet('status')
        ];

        // Check if any search criteria is provided
        $hasSearchCriteria = array_filter($searchData);

        if ($hasSearchCriteria) {
            // For search, we'll use the search term from the first non-empty field
            $searchTerm = '';
            foreach ($searchData as $key => $value) {
                if (!empty($value)) {
                    $searchTerm = $value;
                    break;
                }
            }
            $result = $this->invoiceModel->searchInvoices($searchTerm);
        } else {
            $result = $this->invoiceModel->getAllInvoices('Invoice');
        }

        $data = [
            'title' => 'Invoice List',
            'invoices' => $result['invoices'] ?? [],
            'success' => $result['success'] ?? false,
            'error' => $result['error'] ?? null,
            'foundCount' => count($result['invoices'] ?? []),
            'returnedCount' => count($result['invoices'] ?? []),
            'currentPage' => $page,
            'limit' => $limit,
            'searchData' => $searchData
        ];

        return view('invoice/index', $data);
    }

    /**
     * AJAX endpoint for invoice list
     */
    public function getInvoicesAjax()
    {
        $limit = $this->request->getPost('limit') ?? 50;
        $page = $this->request->getPost('page') ?? 1;
        $offset = ($page - 1) * $limit + 1;

        $result = $this->invoiceModel->getAllInvoices('Invoice');

        return $this->response->setJSON($result);
    }

    /**
     * Clean up resources when controller is destroyed
     */
    public function __destruct()
    {
        if ($this->invoiceModel) {
            $this->invoiceModel->closeSession();
        }
    }
}