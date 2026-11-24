<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class InstructorRequestController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $instructorRequests = User::whereNotNull('document')->whereIn('approved_status', ['pending','rejected'])->latest()->paginate(15);

        return view('admin.instructor-request.index', compact('instructorRequests'));
    }

    public function download(User $user)
    {
        abort_unless($user->document && str_starts_with($user->document, 'instructor-documents/') && ! str_contains($user->document, '..'), 404);

        return Storage::disk('local')->download($user->document);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $instructors_request): RedirectResponse
    {
        $data = $request->validate(['status' => 'required|in:approved,rejected', 'snapshot'=>'required|string|size:64']);
        \Illuminate\Support\Facades\DB::transaction(function () use ($instructors_request, $data) {
            $locked = User::whereKey($instructors_request->id)->lockForUpdate()->firstOrFail();
            abort_unless($locked->role === 'student' && in_array($locked->approved_status, ['pending','rejected']) && $locked->document, 409);
            abort_unless(hash_equals(\App\Support\InstructorReviewSnapshot::token($locked), $data['snapshot']), 409);
            if ($data['status'] === 'approved') {
                abort_unless(str_starts_with($locked->document, 'instructor-documents/') && !str_contains($locked->document, '..') && Storage::disk('local')->exists($locked->document), 422, 'Private application document must be available before approval.');
            }
            $notification = [
                'user_id'=>$locked->id,
                'decision_key'=>hash('sha256', $locked->id.':'.$data['snapshot'].':'.$data['status']),
                'recipient'=>$locked->email, 'recipient_name'=>$locked->name,
                'decision'=>$data['status'], 'decided_at'=>now(),
                'status'=>'pending', 'created_at'=>now(), 'updated_at'=>now(),
            ];
            $locked->approved_status = $data['status'];
            $locked->role = $data['status'] === 'approved' ? 'instructor' : 'student';
            $locked->save();
            \Illuminate\Support\Facades\DB::table('instructor_decision_notifications')->insert($notification);
        });
        return redirect()->back()
            ->with('status', 'تم حفظ قرار مراجعة طلب التدريس.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
