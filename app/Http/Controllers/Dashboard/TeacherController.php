<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\TeacherRequest;
use App\Models\Division;
use App\Models\Grade;
use App\Models\Stage;
use App\Models\Subject;
use App\Services\TeacherService;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\TeachersExport;
use PDF;

class TeacherController extends Controller
{
    protected TeacherService $teacherService;

    public function __construct(TeacherService $teacherService)
    {
        $this->teacherService = $teacherService;
    }

    /**
     * Display a listing of the resource.
     *
     * @return View
     */
    public function index(Request $request): View
    {
        // Get filter parameters
        $search = $request->get('search');
        $status = $request->get('status');
        $stage_id = $request->get('stage_id');
        $subject_id = $request->get('subject_id');
        $sort_by = $request->get('sort_by', 'id');
        $sort_direction = $request->get('sort_direction', 'asc');
        $per_page = $request->get('per_page', 10);

        // Build query with filters
        $query = Teacher::with(['subjects', 'grades', 'stages', 'divisions']);

        // Search filter
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('phone', 'LIKE', "%{$search}%")
                    ->orWhere('other_phone', 'LIKE', "%{$search}%");
            });
        }

        // Status filter
        if ($status !== null && $status !== '') {
            $query->where('status', $status);
        }

        // Stage filter
        if ($stage_id) {
            $query->whereHas('stages', function($q) use ($stage_id) {
                $q->where('stage_id', $stage_id);
            });
        }

        // Subject filter
        if ($subject_id) {
            $query->whereHas('subjects', function($q) use ($subject_id) {
                $q->where('subject_id', $subject_id);
            });
        }

        // Sorting
        $query->orderBy($sort_by, $sort_direction);

        $teachers = $query->paginate($per_page)->withQueryString();

        // Get filter options
        $subjects = Subject::all();
        $stages = Stage::all();

        // Calculate statistics
        $stats = [
            'total_teachers' => Teacher::count(),
            'active_teachers' => Teacher::where('status', 1)->count(),
            'inactive_teachers' => Teacher::where('status', 0)->count(),
            'average_students_per_teacher' =>  10,
//            'average_students_per_teacher' => Teacher::withCount('students')->get()->avg('students_count') ?? 0,
        ];

        return view('dashboard.teachers.index', compact(
            'teachers',
            'subjects',
            'stages',
            'stats',
            'search',
            'status',
            'stage_id',
            'subject_id',
            'sort_by',
            'sort_direction',
            'per_page'
        ));
    }

    /**
     * Toggle teacher activation status
     */
    public function toggleActivation(Teacher $teacher): JsonResponse
    {
        try {
            $teacher->status = !$teacher->status;
            $teacher->save();

            return response()->json([
                'success' => true,
                'message' => __('dashboard.teacher.status_updated'),
                'status' => $teacher->status
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('dashboard.common.error') . ': ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export teachers data
     */
    public function export(Request $request)
    {
        $format = $request->get('format', 'excel');

        try {
            if ($format === 'pdf') {
                $teachers = Teacher::with(['subjects', 'grades', 'stages'])->get();
                $pdf = PDF::loadView('dashboard.teachers.export-pdf', compact('teachers'));
                return $pdf->download('teachers-' . date('Y-m-d') . '.pdf');
            } else {
                return Excel::download(new TeachersExport, 'teachers-' . date('Y-m-d') . '.xlsx');
            }
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Export failed: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return View
     */
    public function create()
    {
        $subjects = Subject::all();
        $stages = Stage::all();
        $grades = Grade::all();
        $divisions = Division::all();

        return view('dashboard.teachers.create', compact('subjects', 'stages', 'grades', 'divisions'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param TeacherRequest $request
     * @return RedirectResponse
     */
    public function store(TeacherRequest $request): RedirectResponse
    {
        try {
            $teacher = $this->teacherService->create($request->validated());

            // Sync many-to-many relationships
            if ($request->has('subjects')) {
                $teacher->subjects()->sync($request->subjects);
            }

            if ($request->has('stages')) {
                $teacher->stages()->sync($request->stages);
            }

            if ($request->has('grades')) {
                $teacher->grades()->sync($request->grades);
            }

            if ($request->has('divisions')) {
                $teacher->divisions()->sync($request->divisions);
            }

            return redirect()->route('teachers.index')
                ->with('success', 'Teacher created successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error creating Teacher: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     *
     * @param Teacher $teacher
     * @return View
     */
    public function show(Teacher $teacher): View
    {
        $teacher->load(['subjects', 'grades', 'stages', 'divisions', 'students', 'lectures']);
        return view('dashboard.teachers.show', compact('teacher'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param Teacher $teacher
     * @return View
     */
    public function edit(Teacher $teacher): View
    {
        $subjects = Subject::all();
        $stages = Stage::all();
        $grades = Grade::all();
        $divisions = Division::all();

        return view('dashboard.teachers.edit', compact('teacher','subjects', 'stages', 'grades', 'divisions'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param TeacherRequest $request
     * @param Teacher $teacher
     * @return RedirectResponse
     */
    public function update(TeacherRequest $request, Teacher $teacher): RedirectResponse
    {
        try {
            $this->teacherService->update($teacher, $request->validated());

            // Sync many-to-many relationships
            $teacher->subjects()->sync($request->subjects ?? []);
            $teacher->stages()->sync($request->stages ?? []);
            $teacher->grades()->sync($request->grades ?? []);
            $teacher->divisions()->sync($request->divisions ?? []);

            return redirect()->route('teachers.index')
                ->with('success', 'Teacher updated successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error updating Teacher: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Teacher $teacher
     * @return RedirectResponse
     */
    public function destroy(Teacher $teacher): RedirectResponse
    {
        try {
            $this->teacherService->delete($teacher);

            return redirect()->route('teachers.index')
                ->with('success', 'Teacher deleted successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error deleting Teacher: ' . $e->getMessage());
        }
    }

    /**
     * AJAX search for teachers
     */
    public function search(Request $request): JsonResponse
    {
        $search = $request->get('search');
        $status = $request->get('status');
        $stage_id = $request->get('stage_id');
        $subject_id = $request->get('subject_id');
        $sort_by = $request->get('sort_by', 'id');
        $sort_direction = $request->get('sort_direction', 'asc');
        $per_page = $request->get('per_page', 10);
        $page = $request->get('page', 1);

        // Build query with filters
        $query = Teacher::with(['subjects', 'grades', 'stages', 'divisions']);
//            ->withCount(['students', 'courses', 'lectures']);

        // Search filter
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('phone', 'LIKE', "%{$search}%")
                    ->orWhere('other_phone', 'LIKE', "%{$search}%");
            });
        }

        // Status filter
        if ($status !== null && $status !== '') {
            $query->where('status', $status);
        }

        // Stage filter
        if ($stage_id) {
            $query->whereHas('stages', function($q) use ($stage_id) {
                $q->where('stage_id', $stage_id);
            });
        }

        // Subject filter
        if ($subject_id) {
            $query->whereHas('subjects', function($q) use ($subject_id) {
                $q->where('subject_id', $subject_id);
            });
        }

        // Sorting
        $query->orderBy($sort_by, $sort_direction);

        $teachers = $query->paginate($per_page, ['*'], 'page', $page);

        // Return JSON response with HTML content
        return response()->json([
            'success' => true,
            'html' => view('dashboard.teachers.partials.table', compact('teachers'))->render(),
            'pagination' => view('dashboard.teachers.partials.pagination', compact('teachers'))->render(),
            'total' => $teachers->total(),
            'current_page' => $teachers->currentPage(),
            'last_page' => $teachers->lastPage()
        ]);
    }

}
