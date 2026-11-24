<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\InstructorRequestApprovalMail;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
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
            $locked->approved_status = $data['status'];
            $locked->role = $data['status'] === 'approved' ? 'instructor' : 'student';
            $locked->save();
        });
        $instructors_request->refresh();

        if (config('mail_queue.is_queue')) {
            Mail::to($instructors_request->email)->queue(new InstructorRequestApprovalMail($instructors_request));
        } else {
            Mail::to($instructors_request->email)->send(new InstructorRequestApprovalMail($instructors_request));
        }

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
