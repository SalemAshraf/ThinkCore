<?php

namespace App\Http\Controllers\ApiControllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\InstructorRequestApprovalMail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class InstructorRequestController extends Controller
{
    /**
     * 📄 عرض جميع طلبات الانضمام كمدرسين (pending / rejected)
     */
    public function index()
    {
        $instructorRequests = User::whereIn('approved_status', ['pending', 'rejected'])
            ->paginate(10);

        return response()->json([
            'success' => true,
            'data' => $instructorRequests,
        ], 200);
    }

    /**
     * 📥 تحميل مستند مقدم الطلب
     */
    public function download(User $user)
    {
        abort_unless($user->document && str_starts_with($user->document, 'instructor-documents/') && ! str_contains($user->document, '..'), 404);

        return Storage::disk('local')->download($user->document);
    }

    /**
     * 🔄 تحديث حالة طلب الانضمام
     */
    public function update(Request $request, User $instructors_request)
    {
        $request->validate([
            'status' => 'required|in:pending,approved,rejected',
        ]);

        $instructors_request->approved_status = $request->status;
        $instructors_request->role = $request->status === 'approved' ? 'instructor' : 'student';
        $instructors_request->save();

        // إرسال البريد
        if (config('mail_queue.is_queue')) {
            Mail::to($instructors_request->email)->queue(new InstructorRequestApprovalMail($instructors_request));
        } else {
            Mail::to($instructors_request->email)->send(new InstructorRequestApprovalMail($instructors_request));
        }

        return response()->json([
            'success' => true,
            'message' => 'Instructor request status updated successfully.',
            'data' => $instructors_request,
        ], 200);
    }
}
