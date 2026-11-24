<?php

use App\Models\User;
use App\Support\InstructorDecisionOutbox;
use App\Support\InstructorReviewSnapshot;
use App\Mail\InstructorRequestApprovalMail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

function decideApplication($test, string $decision='approved'): User {
    Storage::fake('local');Storage::disk('local')->put('instructor-documents/outbox.pdf','fictional');
    $user=User::factory()->create(['role'=>'student','approved_status'=>'pending','document'=>'instructor-documents/outbox.pdf']);
    $test->actingAs(taxonomyAdmin(),'admin')->put('/admin/instructors-requests/'.$user->id,['status'=>$decision,'snapshot'=>InstructorReviewSnapshot::token($user)])->assertRedirect();
    return $user;
}

test('decision commits immutable outbox without invoking transport and stale replay adds nothing', function () {
    Mail::fake();$user=decideApplication($this);
    $row=DB::table('instructor_decision_notifications')->sole();
    expect($row->recipient)->toBe($user->email);expect($row->decision)->toBe('approved');expect($row->status)->toBe('pending');
    $this->put('/admin/instructors-requests/'.$user->id,['status'=>'approved','snapshot'=>InstructorReviewSnapshot::token($user)])->assertConflict();
    expect(DB::table('instructor_decision_notifications')->count())->toBe(1);Mail::assertNothingSent();Mail::assertNothingQueued();
    $oldEmail=$user->email;$oldName=$user->name;
    $user->email='changed@example.test';$user->name='Later name';$user->approved_status='rejected';$user->save();
    expect(app(InstructorDecisionOutbox::class)->deliver($row->id))->toBe('sent');
    Mail::assertSent(InstructorRequestApprovalMail::class,fn($mail)=>$mail->hasTo($oldEmail)&&$mail->instructor->approved_status==='approved'&&$mail->instructor->name===$oldName);
    expect(app(InstructorDecisionOutbox::class)->deliver($row->id))->toBe('skipped');Mail::assertSentCount(1);
});

test('transport failure is durable with bounded retry and manual terminal reset', function () {
    Mail::fake();decideApplication($this);$row=DB::table('instructor_decision_notifications')->sole();$service=app(InstructorDecisionOutbox::class);
    $manager=app('mail.manager');
    Mail::shouldReceive('to')->andThrow(new RuntimeException('Sensitive transport detail must not persist'));
    for($i=1;$i<=5;$i++) {
        expect($service->deliver($row->id))->toBe($i===5?'failed':'pending');
        $current=DB::table('instructor_decision_notifications')->find($row->id);
        expect($current->attempts)->toBe($i);expect($current->last_error)->toBe(RuntimeException::class);
        if($i<5) { expect($service->deliver($row->id))->toBe('skipped');$this->travel(61)->minutes(); }
    }
    expect($service->deliver($row->id))->toBe('skipped');expect($service->retry($row->id))->toBeTrue();
    Mail::swap($manager);Mail::fake();expect($service->deliver($row->id))->toBe('sent');Mail::assertSentCount(1);
    expect(DB::table('instructor_decision_notifications')->find($row->id)->sent_at)->not->toBeNull();
});

test('failed or claimed earlier rejection blocks later approval until delivered and snapshots remain ordered', function () {
    Mail::fake();$user=decideApplication($this,'rejected');$first=DB::table('instructor_decision_notifications')->sole();
    $this->put('/admin/instructors-requests/'.$user->id,['status'=>'approved','snapshot'=>InstructorReviewSnapshot::token($user->fresh())])->assertRedirect();
    $second=DB::table('instructor_decision_notifications')->latest('id')->first();$service=app(InstructorDecisionOutbox::class);
    expect($service->deliver($second->id))->toBe('skipped');
    DB::table('instructor_decision_notifications')->where('id',$first->id)->update(['status'=>'sending','claim_token'=>'existing-worker','claimed_at'=>now()]);
    expect($service->deliver($first->id))->toBe('skipped');
    $this->travel(16)->minutes();expect($service->deliver($first->id))->toBe('sent');expect($service->deliver($second->id))->toBe('sent');
    Mail::assertSentCount(2);Mail::assertSent(InstructorRequestApprovalMail::class,fn($mail)=>$mail->instructor->approved_status==='rejected');
});

test('decision and outbox insert are atomic if persistence fails', function () {
    Mail::fake();Storage::fake('local');Storage::disk('local')->put('instructor-documents/atomic.pdf','fictional');
    $user=User::factory()->create(['role'=>'student','approved_status'=>'pending','document'=>'instructor-documents/atomic.pdf']);
    $fail=true;
    DB::listen(function($query) use (&$fail) {if($fail && str_starts_with(strtolower($query->sql),'insert into "instructor_decision_notifications"')) { $fail=false;throw new RuntimeException('Injected outbox write failure'); }});
    try {$this->actingAs(taxonomyAdmin(),'admin')->put('/admin/instructors-requests/'.$user->id,['status'=>'approved','snapshot'=>InstructorReviewSnapshot::token($user)])->assertServerError();} finally {$fail=false;}
    expect($user->fresh()->role)->toBe('student');expect($user->fresh()->approved_status)->toBe('pending');expect(DB::table('instructor_decision_notifications')->count())->toBe(0);Mail::assertNothingSent();
});

test('delivery command defaults to dry-run and does not reset failed events without explicit send', function () {
    Mail::fake();decideApplication($this);
    $this->artisan('thinkcore:deliver-instructor-decisions')->expectsOutputToContain('Dry run only')->assertSuccessful();Mail::assertNothingSent();
    $this->artisan('thinkcore:deliver-instructor-decisions',['--retry'=>1])->assertExitCode(2);
    $this->artisan('thinkcore:deliver-instructor-decisions',['--send'=>true])->assertSuccessful();Mail::assertSentCount(1);
});
