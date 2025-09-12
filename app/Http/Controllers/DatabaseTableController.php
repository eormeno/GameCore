<?php

namespace App\Http\Controllers;

use App\Services\DatabaseTable\Contracts\DatabaseTableServiceInterface;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class DatabaseTableController extends Controller
{
    protected $databaseTableService;

    /**
     * Create a new controller instance.
     */
    public function __construct(DatabaseTableServiceInterface $databaseTableService)
    {
        $this->databaseTableService = $databaseTableService;
    }

    /**
     * Get database tables
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $showIgnored = $request->boolean('show_ignored', false);
        $withData = $request->boolean('with_data', false);
        $pattern = $request->input('pattern');
        $tables = $this->databaseTableService->getTables($showIgnored, $withData, $pattern);
        return response()->json($tables);
    }

    /**
     * Get table data
     *
     * @param Request $request
     * @param string $tableName
     * @return JsonResponse
     */
    public function show(Request $request, string $tableName): JsonResponse
    {
        try {
            $limit = $request->integer('limit', 100);
            $offset = $request->integer('offset', 0);
            
            $data = $this->databaseTableService->getTableData($tableName, $limit, $offset);
            return response()->json($data);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to fetch table data',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
