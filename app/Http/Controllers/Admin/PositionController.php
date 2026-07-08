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
    public function index(): View
    {
        $q = request()->string('q')->trim()->toString();

        $positionsQuery = Position::query()->withCount('members')->orderBy('name');

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
        $position = Position::create($request->validated());

        activity()
            ->performedOn($position)
            ->causedBy(auth()->user())
            ->withProperties([
                'position_id' => $position->id,
                'position_name' => $position->name,
            ])
            ->log('created');

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

        activity()
            ->performedOn($position)
            ->causedBy(auth()->user())
            ->withProperties([
                'position_id' => $position->id,
                'position_name' => $position->name,
            ])
            ->log('updated');

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

        activity()
            ->performedOn($position)
            ->causedBy(auth()->user())
            ->withProperties([
                'position_id' => $position->id,
                'position_name' => $position->name,
            ])
            ->log('deleted');

        $position->delete();

        return redirect()
            ->route('admin.positions.index')
            ->with('success', 'Position deleted successfully.');
    }
}
