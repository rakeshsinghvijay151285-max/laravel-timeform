<?php

namespace App\Http\Controllers;

use App\Models\Leave;
use App\Models\TimeLog;
use Illuminate\Http\Request;
use Carbon\Carbon;

class LeaveController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $leaves = auth()->user()->leaves()->orderBy('start_date', 'desc')->paginate(15);
        return view('leaves.index', compact('leaves'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $today = Carbon::today()->toDateString();
        return view('leaves.create', compact('today'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'nullable|string|max:500',
        ]);

        $startDate = $validated['start_date'];
        $endDate = $validated['end_date'];

        // Check if there are any work reports for dates within the leave period
        $conflictingTimeLogs = TimeLog::where('user_id', auth()->id())
            ->whereBetween('work_date', [$startDate, $endDate])
            ->exists();

        if ($conflictingTimeLogs) {
            return redirect()->back()->withInput()->withErrors(['start_date' => 'Cannot apply leave for dates that have existing work reports. Please delete work reports first.']);
        }

        Leave::create([
            'user_id' => auth()->id(),
            'start_date' => $startDate,
            'end_date' => $endDate,
            'reason' => $validated['reason'] ?? null,
            'status' => 'pending',
        ]);

        return redirect()->route('leaves.index')->with('success', 'Leave application submitted successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Leave $leave)
    {
        $this->authorize('view', $leave);
        return view('leaves.show', compact('leave'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Leave $leave)
    {
        $this->authorize('update', $leave);

        if ($leave->status !== 'pending') {
            return redirect()->route('leaves.index')->with('error', 'Can only edit pending leave applications.');
        }

        return view('leaves.edit', compact('leave'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Leave $leave)
    {
        $this->authorize('update', $leave);

        if ($leave->status !== 'pending') {
            return redirect()->route('leaves.index')->with('error', 'Can only edit pending leave applications.');
        }

        $validated = $request->validate([
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'nullable|string|max:500',
        ]);

        $startDate = $validated['start_date'];
        $endDate = $validated['end_date'];

        // Check if there are any work reports for dates within the leave period
        $conflictingTimeLogs = TimeLog::where('user_id', auth()->id())
            ->whereBetween('work_date', [$startDate, $endDate])
            ->where('id', '!=', $leave->id)
            ->exists();

        if ($conflictingTimeLogs) {
            return redirect()->back()->withInput()->withErrors(['start_date' => 'Cannot apply leave for dates that have existing work reports.']);
        }

        $leave->update([
            'start_date' => $startDate,
            'end_date' => $endDate,
            'reason' => $validated['reason'] ?? null,
        ]);

        return redirect()->route('leaves.index')->with('success', 'Leave application updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Leave $leave)
    {
        $this->authorize('delete', $leave);

        if ($leave->status !== 'pending') {
            return redirect()->route('leaves.index')->with('error', 'Can only delete pending leave applications.');
        }

        $leave->delete();
        return redirect()->route('leaves.index')->with('success', 'Leave application deleted successfully!');
    }

    /**
     * Check conflicts between dates
     */
    public function checkConflict(Request $request)
    {
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');

        if (!$startDate || !$endDate) {
            return response()->json(['has_conflict' => false]);
        }

        $timeLogs = TimeLog::where('user_id', auth()->id())
            ->whereBetween('work_date', [$startDate, $endDate])
            ->get();

        return response()->json([
            'has_conflict' => $timeLogs->isNotEmpty(),
            'conflicting_dates' => $timeLogs->pluck('work_date')->unique()->values(),
        ]);
    }
}
