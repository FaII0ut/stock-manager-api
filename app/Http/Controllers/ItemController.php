<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreItemRequest;
use App\Http\Requests\UpdateItemRequest;
use App\Http\Resources\ItemResource;
use App\Models\Item;
use Illuminate\Http\Request;

class ItemController extends Controller
{
    public function index(Request $request)
    {
        $query = Item::query();

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
        $itemsWithMinimumStock = Item::whereNotNull('minimum_count')
            ->get();

        return ItemResource::collection($itemsWithMinimumStock);
    }

    // public function minimumStockStats()
    // {
    //     $itemsWithMinimumStock = Item::whereNotNull('minimum_count')
    //         ->where('stock', '<=', 'minimum_count')
    //         ->get();

    //     $totalItems = $itemsWithMinimumStock->count();
    //     $itemsBelowMinimum = $itemsWithMinimumStock->filter(function ($item) {
    //         return $item->stock < $item->minimum_count;
    //     })->count();

    //     return response()->json([
    //         'total_items_with_minimum_stock' => $totalItems,
    //         'items_below_minimum_stock' => $itemsBelowMinimum,
    //         'items' => ItemResource::collection($itemsWithMinimumStock),
    //     ]);
    // }


}
