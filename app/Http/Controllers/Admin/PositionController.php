<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PositionStoreRequest;
use App\Http\Requests\Admin\PositionUpdateRequest;
use App\Models\Position;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PositionController extends Controller
{
    public function __construct()
    {
        $this->middleware('role:national-president');
    }

    public function index(): View
    {
        $q = request()->string('q')->trim()->toString();

        $positionsQuery = Position::query()->orderBy('name');

        if ($q !== '') {
            $positionsQuery->where('name', 'like', '%' . $q . '%');
        }

        $positions = $positionsQuery->paginate(10)->withQueryString();

        return view('admin.positions.index', [
            'positions' => $positions,
            'q' => $q,
        ]);
    }

    public function create(): View
    {
        return view('admin.positions.create');
    }

    public function store(PositionStoreRequest $request): RedirectResponse
    {
        Position::create($request->validated());

        return redirect()
            ->route('admin.positions.index')
            ->with('success', 'Position created successfully.');
    }

    public function edit(Position $position): View
    {
        return view('admin.positions.edit', [
            'position' => $position,
        ]);
    }

    public function update(PositionUpdateRequest $request, Position $position): RedirectResponse
    {
        $position->update($request->validated());

        return redirect()
            ->route('admin.positions.index')
            ->with('success', 'Position updated successfully.');
    }

    public function destroy(Position $position): RedirectResponse
    {
        if ($position->members()->exists()) {
            return redirect()
                ->route('admin.positions.index')
                ->with('error', 'Cannot delete position because it still contains members');
        }

        $position->delete();

        return redirect()
            ->route('admin.positions.index')
            ->with('success', 'Position deleted successfully.');
    }
}
