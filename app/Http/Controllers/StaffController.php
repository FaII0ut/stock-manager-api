<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreStaffRequest;
use App\Http\Requests\UpdateStaffRequest;
use App\Http\Resources\StaffResource;
use App\Models\Staff;
use Illuminate\Http\Request;

class StaffController extends Controller
{
    public function index()
    {
        return StaffResource::collection(Staff::query()->latest('id')->paginate());
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
