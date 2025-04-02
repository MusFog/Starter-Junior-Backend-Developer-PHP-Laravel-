<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddEmployeeRequest;
use App\Http\Requests\UpdateEmployeeRequest;
use App\Models\Employee;
use App\Models\Position;
use App\Models\User;
use App\Services\ImageService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Yajra\DataTables\DataTables;

class EmployeeController extends Controller
{
    public function data()
    {
        return DataTables::of(Employee::query())
            ->editColumn('employee_name', fn($e) => $e->employee_name)
            ->editColumn('name as position_name', fn($e) => $e->position->name ?? $e->position)
            ->editColumn('employment_date', fn($e) => $e->employment_date->format('d.m.Y'))
            ->filterColumn('employment_date', function($query, $keyword) {
                try {
                    $date = \Carbon\Carbon::createFromFormat('d.m.Y', $keyword)->format('Y-m-d');
                    $query->whereDate('employment_date', $date);
                } catch (\Exception $e) {
                }
            })
            ->editColumn('salary', fn($e) => '$' . $e->salary)
            ->editColumn('image_path', function ($e) {
                if (!$e->image_path) return '-';
                $src = asset('storage/' . $e->image_path);
                return "<img src='{$src}' width='50' style='border-radius:50%'/>";
            })
            ->rawColumns(['image_path'])
            ->addColumn('action', function ($e) {
                return '
                    <button
                      class="edit-btn btn btn-sm btn-outline-primary"
                      data-id="' . $e->id . '"
                    >
                      ✏️
                    </button>
                    <button class="delete-btn btn btn-sm btn-outline-danger" data-id="' . $e->id . '" data-name="' . $e->name . '" data-toggle="modal" data-target="#deleteModal">🗑️</button>
                ';
            })
            ->rawColumns(['image_path', 'action'])
            ->make(true);
    }

    public function addEmployee(AddEmployeeRequest $request)
    {
        $path = ImageService::checkImage($request->processed_image_path);

        $admin_id = auth()->id();

        $position_name = Position::find($request->position_id)->name;
        $supervisor_name = Employee::find($request->supervisor_id)->employee_name;

        $employee = Employee::create([
            'id' => Str::uuid(),
            'user_id' => $request->user_id,
            'employee_name' => $request->employee_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'position_name' => $position_name,
            'position_id' => $request->position_id,
            'salary' => $request->salary,
            'supervisor_name' => $supervisor_name,
            'supervisor_id' => $request->supervisor_id,
            'employment_date' => $request->employment_date,
            'admin_created_id' => $admin_id,
            'admin_updated_id' => $admin_id,
            'image_path' => $path,
        ]);

        if (!$employee) return redirect()->route('employees-list')->with(['error' => 'Failed to create an employee']);

        return redirect()->route('employees-list')->with(['message' => 'Create']);
    }

    public function edit($id)
    {
        $employee = Employee::find($id);

        if (!$employee) {
            return redirect()->route('employees-list')->with(['error' => 'Invalid employee ID']);
        }

        $supervisor = $employee->supervisor_id
            ? Employee::find($employee->supervisor_id)
            : null;

        $employee->supervisor_user_id = $supervisor?->user_id;

        $position = $employee->position;

        $users = $position->admins;

        $currentUser = auth()->user();

        $admin = User::firstWhere('email', 'admin@gmail.com');

        $isOwner = $users->contains('id', $currentUser->id);
        $isAdmin = $admin && $currentUser->id === $admin->id;


        if (!$isOwner && !$isAdmin) {
            return redirect()->route('employees-list')->with(['error' => 'Access denied']);
        }

        if ($isAdmin) {
            return redirect()->route('employees-list')->with(['error' => 'Ти є адмін']);
        }

        $positions = $currentUser->positions;

        if ($positions->isEmpty()) {
            return redirect()->route('employees-list')->with(['error' => 'No positions found']);
        }

        return view('employees-edit', compact(['employee', 'positions']));
    }

    public function updateEmployee(UpdateEmployeeRequest $request)
    {
        $path = ImageService::checkImage($request->processed_image_path);

        $admin_id = auth()->id();

        $position_name = Position::find($request->position_id)->name;
        $supervisor_name = Employee::find($request->supervisor_id)->employee_name;;

        $employee = Employee::find($request->id)->update([
            'employee_name' => $request->employee_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'position_name' => $position_name,
            'position_id' => $request->position_id,
            'salary' => $request->salary,
            'supervisor_name' => $supervisor_name,
            'supervisor_id' => $request->supervisor_id,
            'employment_date' => $request->employment_date,
            'admin_updated_id' => $admin_id,
            'image_path' => $path,
        ]);

        if (!$employee) return redirect()->route('employees-list')->with(['error' => 'Failed to update an employee']);

        return redirect()->route('employees-list')->with(['message' => 'Update']);
    }

    public function image(Request $request)
    {
        $request->validate([
            'image_path' => 'required|image|mimes:jpg,png|max:5120|dimensions:min_width=300,min_height=300',
        ]);

        $path = ImageService::handleImageUpload($request->image_path);

        return response()->json(['image_path' => $path]);
    }

    public function removeImage(Request $request)
    {
        $request->validate([
            'image_path' => 'required|string'
        ]);

        if (ImageService::deleteImage($request->image_path)) {
            return response()->json(['success' => true]);
        } else {
            return response()->json([
                'success' => false,
                'error' => 'Failed to delete the image. Photo not found.'
            ], 422);
        }
    }

    public function search(Request $request)
    {
        $request->validate([
            'name' => 'nullable|string|min:2|max:256',
            'employee_name' => 'nullable|string|min:2|max:256',
        ]);

        $searchLimit = config('employee.searchLimit');
        $query = trim($request->name ?? $request->employee_name);

        if ($request->has('name')) {
            $users = User::select('id as user_id', 'name', 'email', 'phone')
                ->where('name', 'like', "{$query}%")
                ->limit($searchLimit)
                ->get()
                ->map(function ($item) use ($query) {
                    return [
                        'user_id' => $item->user_id,
                        'name' => $item->name,
                        'email' => $item->email,
                        'phone' => $item->phone,
                    ];
                });

            return response()->json($users);
        }

        if ($request->has('employee_name')) {
            $employees = Employee::select('id', 'user_id', 'employee_name', 'email', 'phone')
                ->where('employee_name', 'like', "{$query}%")
                ->limit($searchLimit)
                ->get()
                ->map(function ($item) use ($query) {
                    return [
                        'supervisor_id' => $item->id,
                        'supervisor_user_id' => $item->user_id,
                        'employee_name' => $item->employee_name,
                        'email' => $item->email,
                        'phone' => $item->phone,
                    ];
                });

            return response()->json($employees);
        }

        return response()->json([], 400);
    }

    public function removeEmployee($id)
    {
        $employee = Employee::find($id);

        if (!$employee) {
            return redirect()->route('employees-list')->with(['error' => 'Invalid employee ID']);
        }

        $currentUser = auth()->user();
        $admin = User::firstWhere('email', 'admin@gmail.com');

        $isOwner = $employee->position->admins->contains('id', $currentUser->id);
        $isAdmin = $admin && $currentUser->id === $admin->id;

        if (!$isOwner && !$isAdmin) {
            return redirect()->route('employees-list')->with(['error' => 'Access denied']);
        }

        $employee->reassignSubordinates();
        ImageService::deleteImage($employee->image_path);
        $employee->delete();

        return redirect()->route('employees-list')->with(['message' => 'Deleted']);
    }

}
