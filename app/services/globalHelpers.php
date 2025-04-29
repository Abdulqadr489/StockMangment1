<?php

namespace App\services;

use Illuminate\Http\Request;

trait globalHelpers
{
    private function applySearch($query, Request $request, $columnName, $relation = null)
    {
        if ($search = $request->input('search')) {
            if ($relation) {
                $query->whereHas($relation, function ($q) use ($columnName, $search) {
                    $q->where($columnName, 'like', "%$search%");
                });
            } else {
                $query->where($columnName, 'like', "%$search%");
            }
        }
    }


    private function applySorting($query, Request $request, $defaultSortBy = 'created_at')
    {

        $sortBy = $request->filled('sort_by') ? $request->input('sort_by') : $defaultSortBy;
        $sortOrder = in_array($request->input('sort_order'), ['asc', 'desc']) ? $request->input('sort_order') : 'desc';

        if (str_contains($sortBy, '.')) {
            [$relation, $column] = explode('.', $sortBy);

            $model = $query->getModel();

            if (method_exists($model, $relation)) {
                $relationInstance = $model->$relation();
                $relatedTable = $relationInstance->getRelated()->getTable();
                $foreignKey = $relationInstance->getForeignKeyName();
                $ownerKey = $relationInstance->getOwnerKeyName();
                $mainTable = $model->getTable();

                $query->leftJoin($relatedTable, "$relatedTable.$ownerKey", '=', "$mainTable.$foreignKey")
                      ->orderBy("$relatedTable.$column", $sortOrder)
                      ->select("$mainTable.*");

                return;
            }
        }

        // If not a relation or relation method doesn't exist
        $query->orderBy($sortBy, $sortOrder);
    }






    protected function handleApiException(\Throwable $e,string $message = 'Server Error',int $code = 500,array $extra = []): \Illuminate\Http\JsonResponse {
        $response = array_merge([
            'message' => $message,
            'error' => $e->getMessage(),
        ], $extra);

        return response()->json($response, $code);
    }

    protected function handleApiSuccess(string $message = 'Server Error',int $code = 500,array $extra = []): \Illuminate\Http\JsonResponse {
        $response = array_merge([
            'message' => $message,
        ], $extra);

        return response()->json($response, $code);
    }
}
