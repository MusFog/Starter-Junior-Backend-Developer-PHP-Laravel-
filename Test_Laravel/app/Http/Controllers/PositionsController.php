<?php

namespace App\Http\Controllers;

use App\Models\Position;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Yajra\DataTables\DataTables;

class PositionsController extends Controller
{
    public function data()
    {
        return DataTables::of(Position::query())
            ->editColumn('name', fn($p) => $p->name)
            ->editColumn('updated_at', fn($p) => $p->updated_at->format('d.m.Y'))
            ->filterColumn('updated_at', function($query, $keyword) {
                try {
                    $date = \Carbon\Carbon::createFromFormat('d.m.Y', $keyword)->format('Y-m-d');
                    $query->whereDate('updated_at', $date);
                } catch (\Exception $e) {
                }
            })
            ->addColumn('action', function ($p) {
                return '
                    <button
                      class="edit-btn btn btn-sm btn-outline-primary"
                      data-id="' . $p->id . '"
                    >
                      ✏️
                    </button>
                    <button class="delete-btn btn btn-sm btn-outline-danger" data-id="' . $p->id . '" data-name="' . $p->name . '" data-toggle="modal" data-target="#deleteModal">🗑️</button>
                ';
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function addPosition(Request $request)
    {
        $request->validate([
            'admin_id' => 'required|exists:users,id',
            'position_name' => 'required|string|min:2|max:256',
        ]);

        $position = Position::create([
            'id' => (string) Str::uuid(),
            'name' => $request->position_name,
            'admin_created_id' => $request->admin_id,
            'admin_updated_id' => $request->admin_id,
        ]);

        if ($position) {
            $user = User::find($request->admin_id);
            $user->positions()->attach($position->id);
        } else {
            return redirect()->route('positions-list')->with(['error' => 'Failed to create a position']);
        }

        return redirect()->route('positions-list')->with(['message' => 'Create']);
    }

    public function updatePosition(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:positions,id',
            'admin_id' => 'required|exists:users,id',
            'position_name' => 'required|string|min:2|max:256',
        ]);

        $position = Position::find($request->id)->update([
            'name' => $request->position_name,
            'admin_updated_id' => $request->admin_id,
        ]);

        if (!$position) return redirect()->route('positions-list')->with(['error' => 'Failed to update an position']);

        return redirect()->route('positions-list')->with(['message' => 'Update']);
    }

    public function edit($id)
    {
        $position = Position::find($id);

        if (!$position) {
            return redirect()->route('employees-list')->with(['error' => 'Invalid position ID']);
        }

        $currentUser = auth()->user();
        $admin = User::firstWhere('email', 'admin@gmail.com');

        $isOwner = $position->admins->contains('id', $currentUser->id);
        $isAdmin = $admin && $currentUser->id === $admin->id;

        if (!$isOwner && !$isAdmin) {
            return redirect()->route('positions-list')->with(['error' => 'Access denied']);
        }

        return view('positions-edit', compact('position'));
    }


    public function getPositionsListAdmin(Request $request)
    {
        $perPage = $request->get('per_page', 20);
        $selectedId = $request->get('selected_id');

        $query = Position::select('id', 'name');

        $positions = $query->paginate($perPage);

        $selected = null;
        if ($selectedId && !$positions->getCollection()->contains('id', $selectedId)) {
            $selected = Position::select('id', 'name')->find($selectedId);
        }

        return response()->json([
            'positions' => $positions,
            'selected' => $selected,
        ]);
    }


    public function removePosition($id)
    {
        $position = Position::find($id);

        if (!$position) {
            return redirect()->route('positions-list')->with(['error' => 'Invalid position ID']);
        }

        $currentUser = auth()->user();
        $admin = User::firstWhere('email', 'admin@gmail.com');

        $isOwner = $position->admins->contains('id', $currentUser->id);
        $isAdmin = $admin && $currentUser->id === $admin->id;

        if (!$isOwner && !$isAdmin) {
            return redirect()->route('positions-list')->with(['error' => 'Access denied']);
        }

        $position->delete();

        return redirect()->route('positions-list')->with(['message' => 'Deleted']);
    }


}
