<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\External\SalesforceService;
use Illuminate\Http\Request;

class SalesforceController extends Controller
{
    protected $salesforce;

    public function __construct(SalesforceService $salesforce)
    {
        $this->salesforce = $salesforce;
    }

    /**
     * Search Salesforce opportunities (AJAX endpoint).
     */
    public function searchOpportunities(Request $request)
    {
        try {
            $query = trim($request->input('q', ''));
            $limit = min(max((int)$request->input('limit', 100), 1), 200);
            $cursor = trim($request->input('cursor', ''));

            // Minimum 2 characters
            if (empty($cursor) && (empty($query) || mb_strlen($query) < 2)) {
                return response()->json([
                    'ok' => true,
                    'items' => [],
                    'message' => 'Tapez au moins 2 caractères.',
                ]);
            }

            $result = $this->salesforce->searchOpportunities($query, $limit, $cursor);

            return response()->json($result);

        } catch (\Exception $e) {
            return response()->json([
                'ok' => false,
                'error' => 'Erreur lors de la recherche: ' . $e->getMessage(),
            ], 500);
        }
    }
}
