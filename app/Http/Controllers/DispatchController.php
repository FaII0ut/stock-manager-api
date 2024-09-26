<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDispatchRequest;
use App\Http\Requests\UpdateDispatchRequest;
use App\Http\Resources\DispatchResource;
use App\Models\Dispatch;
use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
        Item::find($request->get('item_id'))->increment('stock', $dispatch->quantity);
        $dispatch->update($request->validated());
        Item::find($request->get('item_id'))->decrement('stock', $request->get('quantity'));

        return new DispatchResource($dispatch);
    }

    public function destroy(Dispatch $dispatch)
    {
        $dispatch->delete();

        return response()->noContent();
    }
}
