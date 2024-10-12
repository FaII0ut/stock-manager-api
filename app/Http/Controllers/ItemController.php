<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreItemRequest;
use App\Http\Requests\UpdateItemRequest;
use App\Http\Resources\ItemResource;
use App\Models\Item;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ItemController extends Controller
{
    public function index(Request $request)
    {
        $query = Item::query()->with('category');

        // Single search parameter
        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('sku', 'like', '%' . $search . '%')
                  ->orWhere('name', 'like', '%' . $search . '%')
                  ->orWhere('description', 'like', '%' . $search . '%');
            });
        }

        // Sort results
        $sortBy = $request->input('sort_by', 'id');
        $sortOrder = $request->input('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Paginate results
        $perPage = $request->input('per_page', 15);
        $items = $query->paginate();

        return ItemResource::collection($items);

    }

    public function store(StoreItemRequest $request)
    {
        $item = Item::create($request->validated());

        return new ItemResource($item);
    }

    public function show(Item $item)
    {
        return new ItemResource($item);
    }

    public function update(UpdateItemRequest $request, Item $item)
    {
        $item->update($request->validated());

        return new ItemResource($item);
    }

    public function destroy(Item $item)
    {
        $item->delete();

        return response()->noContent();
    }

    public function minimumStockStats()
    {
        return Item::whereNotNull('min_count')->get();
    }

    // public function minimumStockStats()
    // {
    //     $itemsWithMinimumStock = Item::whereNotNull('min_count')
    //         ->where('stock', '<=', 'min_count')
    //         ->get();

    //     $totalItems = $itemsWithMinimumStock->count();
    //     $itemsBelowMinimum = $itemsWithMinimumStock->filter(function ($item) {
    //         return $item->stock < $item->min_count;
    //     })->count();

    //     return response()->json([
    //         'total_items_with_minimum_stock' => $totalItems,
    //         'items_below_minimum_stock' => $itemsBelowMinimum,
    //         'items' => ItemResource::collection($itemsWithMinimumStock),
    //     ]);
    // }


    public function exportItemsJson(Request $request)
    {
        $data = $this->getItemsData($request);

        $formattedItems = $data['items']->map(function ($item) {
            return [
                'id' => $item->id,
                'sku' => $item->sku,
                'category' => $item->category ? [
                    'id' => $item->category->id,
                    'name' => $item->category->name,
                ] : null,
                'name' => $item->name,
                'code' => $item->code? $item->code : null,
                'description' => $item->description,
                'price' => $item->price,
                'stock' => $item->stock,
                'min_count' => $item->min_count,
                'status' => $item->status,

                'created_at' => $item->created_at,
            ];
        });

        return response()->json([
            'summary' => $data['summary'],
            'items' => $formattedItems
        ]);
    }

    public function exportItemsCsv(Request $request)
    {
        $data = $this->getItemsData($request);

        $filename = "items_export.csv";

        $headers = [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];

        $columns = ['ID', 'Category', 'SKU', 'Code', 'Name', 'Description', 'Price', 'Stock', 'Minimum Count', 'Status', 'Created At', 'Updated At'];

        $callback = function () use ($data, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Summary']);
            fputcsv($file, ['Total Items', $data['summary']['total_items']]);
            fputcsv($file, ['Active Items', $data['summary']['active_items']]);
            fputcsv($file, ['Total Stock', $data['summary']['total_stock']]);
            fputcsv($file, ['Total Value', $data['summary']['total_value']]);
            fputcsv($file, ['Items Below Minimum Count', $data['summary']['items_below_minimum']]);
            fputcsv($file, []); // Empty line
            fputcsv($file, ['Items']);
            fputcsv($file, $columns);

            foreach ($data['items'] as $item) {
                fputcsv($file, [
                    $item->id,
                    $item->category ? $item->category->name : 'N/A',
                    $item->sku,
                    $item->code? $item->code : null,
                    $item->name,
                    $item->description,
                    $item->price,
                    $item->stock,
                    $item->min_count,
                    $item->status ? 'Active' : 'Inactive',
                    $item->created_at,
                    $item->updated_at
                ]);
            }

            fclose($file);
        };

        return new StreamedResponse($callback, 200, $headers);
    }

    private function getItemsData(Request $request)
    {
        $request->validate([
            'category_id' => 'nullable|exists:categories,id',
        ]);

        $query = Item::with('category');


        if ($request->has('category_id')) {
            $query->where('category_id', $request->input('category_id'));
        }

        $items = $query->get();

        $summary = [
            'total_items' => $items->count(),
            'active_items' => $items->where('status', true)->count(),
            'total_stock' => $items->sum('stock'),
            'total_value' => $items->sum(function ($item) {
                return $item->stock * $item->price;
            }),
            'items_below_minimum' => $items->filter(function ($item) {
                return $item->min_count !== null && $item->stock < $item->min_count;
            })->count(),
        ];

        return [
            'summary' => $summary,
            'items' => $items
        ];
    }
}
