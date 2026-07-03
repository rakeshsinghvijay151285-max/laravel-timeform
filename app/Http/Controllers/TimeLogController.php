<?php

namespace App\Http\Controllers;

use App\Models\TimeLog;
use App\Models\Project;
use App\Models\Leave;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class TimeLogController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $timeLogs = auth()->user()->timeLogs()->with('project')->orderBy('work_date', 'desc')->paginate(15);
        return view('time-logs.index', compact('timeLogs'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $projects = Project::where('status', 'active')->get();
        $today = Carbon::today()->toDateString();

        return view('time-logs.create', compact('projects', 'today'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'work_date' => 'required|date|before_or_equal:today',
            'tasks' => 'required|array|min:1',
            'tasks.*.project_id' => 'required|exists:projects,id',
            'tasks.*.task_description' => 'required|string|max:500|min:3',
            'tasks.*.time' => 'required|regex:/^\d{1,2}:\d{2}$/',
        ], [
            'tasks.required' => 'Please add at least one task.',
            'tasks.min' => 'Please add at least one task.',
            'tasks.*.time.regex' => 'Time must be in HH:MM format (e.g., 2:30 or 10:00).',
        ]);

        $workDate = $validated['work_date'];

        // Check if leave exists for this date
        $leaveExists = Leave::where('user_id', auth()->id())
            ->where('status', 'approved')
            ->where('start_date', '<=', $workDate)
            ->where('end_date', '>=', $workDate)
            ->exists();

        if ($leaveExists) {
            return redirect()->back()->withInput()->withErrors(['work_date' => 'Cannot log time for a date with approved leave.']);
        }

        $totalMinutes = 0;
        $timeLogs = [];

        // Calculate total time and parse times
        foreach ($validated['tasks'] as $task) {
            [$hours, $minutes] = explode(':', $task['time']);
            $hours = (int)$hours;
            $minutes = (int)$minutes;

            // Validate individual task time
            if ($hours > 10 || ($hours === 10 && $minutes > 0)) {
                return redirect()->back()->withInput()->withErrors(['tasks' => 'No individual task can exceed 10 hours.']);
            }

            $totalMinutes += ($hours * 60) + $minutes;
            $timeLogs[] = [
                'hours' => $hours,
                'minutes' => $minutes,
                'project_id' => $task['project_id'],
                'task_description' => $task['task_description'],
            ];
        }

        // Validate total daily time
        if ($totalMinutes > (10 * 60)) {
            return redirect()->back()->withInput()->withErrors(['tasks' => 'Total time for the day cannot exceed 10 hours. Current total: ' . floor($totalMinutes / 60) . 'h ' . ($totalMinutes % 60) . 'm']);
        }

        // Check if time logs already exist for this date
        $existingLogs = TimeLog::where('user_id', auth()->id())
            ->where('work_date', $workDate)
            ->get();

        $existingTotalMinutes = $existingLogs->sum(function($log) {
            return ($log->hours * 60) + $log->minutes;
        });

        if (($existingTotalMinutes + $totalMinutes) > (10 * 60)) {
            return redirect()->back()->withInput()->withErrors(['tasks' => 'Adding these tasks would exceed the 10-hour daily limit. Existing time: ' . floor($existingTotalMinutes / 60) . 'h ' . ($existingTotalMinutes % 60) . 'm']);
        }

        // Create time logs
        foreach ($timeLogs as $timeLog) {
            TimeLog::create(array_merge($timeLog, [
                'user_id' => auth()->id(),
                'work_date' => $workDate,
            ]));
        }

        return redirect()->route('time-logs.index')->with('success', 'Time logs created successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(TimeLog $timeLog)
    {
        $this->authorize('view', $timeLog);
        return view('time-logs.show', compact('timeLog'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(TimeLog $timeLog)
    {
        $this->authorize('update', $timeLog);
        $projects = Project::where('status', 'active')->get();
        return view('time-logs.edit', compact('timeLog', 'projects'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, TimeLog $timeLog)
    {
        $this->authorize('update', $timeLog);

        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'task_description' => 'required|string|max:500|min:3',
            'time' => 'required|regex:/^\d{1,2}:\d{2}$/',
        ], [
            'time.regex' => 'Time must be in HH:MM format (e.g., 2:30 or 10:00).',
        ]);

        [$hours, $minutes] = explode(':', $validated['time']);
        $hours = (int)$hours;
        $minutes = (int)$minutes;

        // Validate individual task time
        if ($hours > 10 || ($hours === 10 && $minutes > 0)) {
            return redirect()->back()->withInput()->withErrors(['time' => 'Task time cannot exceed 10 hours.']);
        }

        // Validate total daily time including this update
        $newTaskMinutes = ($hours * 60) + $minutes;
        $oldTaskMinutes = ($timeLog->hours * 60) + $timeLog->minutes;
        $timeDifference = $newTaskMinutes - $oldTaskMinutes;

        $otherLogsTotal = TimeLog::where('user_id', auth()->id())
            ->where('work_date', $timeLog->work_date)
            ->where('id', '!=', $timeLog->id)
            ->sum(\DB::raw('hours * 60 + minutes'));

        if (($otherLogsTotal + $newTaskMinutes) > (10 * 60)) {
            return redirect()->back()->withInput()->withErrors(['time' => 'Total time for the day cannot exceed 10 hours.']);
        }

        $timeLog->update([
            'project_id' => $validated['project_id'],
            'task_description' => $validated['task_description'],
            'hours' => $hours,
            'minutes' => $minutes,
        ]);

        return redirect()->route('time-logs.index')->with('success', 'Time log updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(TimeLog $timeLog)
    {
        $this->authorize('delete', $timeLog);
        $timeLog->delete();
        return redirect()->route('time-logs.index')->with('success', 'Time log deleted successfully!');
    }

    /**
     * Get daily total for AJAX
     */
    public function getDailyTotal(Request $request)
    {
        $workDate = $request->query('work_date');

        $existingLogs = TimeLog::where('user_id', auth()->id())
            ->where('work_date', $workDate)
            ->get();

        $totalMinutes = $existingLogs->sum(function($log) {
            return ($log->hours * 60) + $log->minutes;
        });

        $totalHours = floor($totalMinutes / 60);
        $totalMins = $totalMinutes % 60;

        return response()->json([
            'total_minutes' => $totalMinutes,
            'total_hours' => $totalHours,
            'total_mins' => $totalMins,
            'logs' => $existingLogs->load('project'),
        ]);
    }
}
