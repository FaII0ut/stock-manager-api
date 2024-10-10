<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDispatchRequest;
use App\Http\Requests\UpdateDispatchRequest;
use App\Http\Resources\DispatchResource;
use App\Models\Dispatch;
use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Support\Facades\DB;

class DispatchController extends Controller
{
    public function index()
    {
        return DispatchResource::collection(Dispatch::query()->latest('id')->paginate());
    }


    public function store(StoreDispatchRequest $request)
    {
        $validatedData = $request->validated();
        // Add user_id to the validated data

        $validatedData['user_id'] = Auth::id();

        $dispatch = Dispatch::create($validatedData);

        Item::find($request->get('item_id'))->decrement('stock', $request->get('quantity'));

        return new DispatchResource($dispatch);
    }

    public function show(Dispatch $dispatch)
    {
        return new DispatchResource($dispatch);
    }

    public function update(UpdateDispatchRequest $request, Dispatch $dispatch)
    {
        // First, revert the original dispatch by incrementing the stock
        Item::find($dispatch->item_id)->increment('stock', $dispatch->quantity);

        // Update the dispatch with new data
        $dispatch->update($request->validated());

        // Now, apply the new dispatch by decrementing the stock
        Item::find($dispatch->item_id)->decrement('stock', $dispatch->quantity);

        return new DispatchResource($dispatch);
    }

    public function destroy(Dispatch $dispatch)
    {
        DB::transaction(function () use ($dispatch) {
            // Increment the stock of the associated item
            Item::find($dispatch->item_id)->increment('stock', $dispatch->quantity);

            // Delete the dispatch
            $dispatch->delete();
        });

        return response()->noContent();
    }
    public function exportMonthlyJson(Request $request)
    {
        $data = $this->getMonthlyDispatchData($request);

        return response()->json([
            'summary' => $data['summary'],
            'dispatches' => $data['dispatches']
        ]);
    }

    public function exportMonthlyCsv(Request $request)
    {
        $data = $this->getMonthlyDispatchData($request);

        $filename = "dispatches_{$data['summary']['year']}_{$data['summary']['month']}.csv";

        $headers = [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];

        $columns = ['ID', 'Staff ID', 'Staff Name', 'Item ID', 'Item Name', 'Quantity', 'Date'];

        $callback = function () use ($data) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Summary']);
            fputcsv($file, ['Year', $data['summary']['year']]);
            fputcsv($file, ['Month', $data['summary']['month']]);
            fputcsv($file, ['Total Dispatches', $data['summary']['total_dispatches']]);
            fputcsv($file, ['Total Quantity', $data['summary']['total_quantity']]);
            fputcsv($file, ['Staff Count', $data['summary']['staff_count']]);
            fputcsv($file, ['Item Count', $data['summary']['item_count']]);
            fputcsv($file, []); // Empty line
            fputcsv($file, ['Dispatches']);
            fputcsv($file, ['ID', 'Staff ID', 'Staff Name', 'Item ID', 'Item Name', 'Quantity', 'Date']);

            foreach ($data['dispatches'] as $dispatch) {
                fputcsv($file, [
                    $dispatch->id,
                    $dispatch->staff_id,
                    $dispatch->staff_name,
                    $dispatch->item_id,
                    $dispatch->item_name,
                    $dispatch->quantity,
                    $dispatch->created_at
                ]);
            }

            fclose($file);
        };

        return new StreamedResponse($callback, 200, $headers);
    }

    private function getMonthlyDispatchData(Request $request)
    {
        $request->validate([
            'year' => 'required|integer|min:2000|max:' . (date('Y') + 1),
            'month' => 'required|integer|min:1|max:12',
            'staff_id' => 'nullable|exists:users,id'
        ]);

        $year = $request->input('year');
        $month = $request->input('month');
        $staffId = $request->input('staff_id');

        $query = Dispatch::query()
            ->join('users', 'dispatches.user_id', '=', 'users.id')
            ->join('staff', 'dispatches.staff_id', '=', 'staff.id')
            ->join('items', 'dispatches.item_id', '=', 'items.id')
            ->whereYear('dispatches.created_at', $year)
            ->whereMonth('dispatches.created_at', $month)
            ->select(
                'dispatches.id',
                'users.name as user_name',
                'staff.name as staff_name',
                'items.id as item_id',
                'items.name as item_name',
                'dispatches.quantity',
                'dispatches.created_at'
            );

        if ($staffId) {
            $query->where('dispatches.user_id', $staffId);
        }

        $dispatches = $query->get();

        $summary = [
            'year' => $year,
            'month' => $month,
            'total_dispatches' => $dispatches->count(),
            'total_quantity' => $dispatches->sum('quantity'),
            'staff_count' => $dispatches->unique('staff_id')->count(),
            'item_count' => $dispatches->unique('item_id')->count(),
        ];

        return [
            'summary' => $summary,
            'dispatches' => $dispatches
        ];
    }
}
