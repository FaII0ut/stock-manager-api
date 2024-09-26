<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreStaffRequest;
use App\Http\Requests\UpdateStaffRequest;
use App\Http\Resources\StaffResource;
use App\Models\Staff;
use Illuminate\Http\Request;

class StaffController extends Controller
{
    public function index(Request $request)
    {
        $query = Staff::query();

        // Single search parameter
        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('nid', 'like', '%' . $search . '%')
                  ->orWhere('staff_code', 'like', '%' . $search . '%')
                  ->orWhere('phone', 'like', '%' . $search . '%');
            });
        }

        // Individual field searches
        if ($request->has('name')) {
            $query->where('name', 'like', '%' . $request->input('name') . '%');
        }

        if ($request->has('nid')) {
            $query->where('nid', 'like', '%' . $request->input('nid') . '%');
        }

        if ($request->has('staff_code')) {
            $query->where('staff_code', 'like', '%' . $request->input('staff_code') . '%');
        }

        if ($request->has('phone')) {
            $query->where('phone', 'like', '%' . $request->input('phone') . '%');
        }

        // Sort results
        $sortBy = $request->input('sort_by', 'id');
        $sortOrder = $request->input('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Paginate results
        $perPage = $request->input('per_page', 15);
        $staff = $query->latest('id')->paginate($perPage);

        return StaffResource::collection($staff);
    }

    public function store(StoreStaffRequest $request)
    {
        $staff = Staff::create($request->validated());

        return new StaffResource($staff);
    }

    public function show(Staff $staff)
    {
        return new StaffResource($staff);
    }

    public function update(UpdateStaffRequest $request, Staff $staff)
    {
        $staff->update($request->validated());

        return new StaffResource($staff);
    }

    public function destroy(Staff $staff)
    {
        $staff->delete();

        return response()->noContent();
    }
}
