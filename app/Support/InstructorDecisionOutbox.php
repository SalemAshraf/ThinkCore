<?php

namespace App\Support;

use App\Mail\InstructorRequestApprovalMail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

final class InstructorDecisionOutbox
{
    public const MAX_ATTEMPTS = 5;

    public function deliver(int $id): string
    {
        // Never hold database locks across a network send. A claim expires only
        // after 15 minutes; transport timeouts must remain below this lease.
        $claim = DB::transaction(function () use ($id) {
            $row = DB::table('instructor_decision_notifications')->where('id',$id)->lockForUpdate()->first();
            if (!$row || in_array($row->status,['sent','failed'],true)) return null;
            if ($row->next_attempt_at && now()->lt($row->next_attempt_at)) return null;
            if ($row->status==='sending' && $row->claimed_at && now()->subMinutes(15)->lt($row->claimed_at)) return null;
            // Keep each applicant's event order, even after an earlier terminal
            // failure, until an operator explicitly retries that earlier event.
            if (DB::table('instructor_decision_notifications')->where('user_id',$row->user_id)->where('id','<',$row->id)->where('status','!=','sent')->exists()) return null;
            if ($row->attempts >= self::MAX_ATTEMPTS) {
                DB::table('instructor_decision_notifications')->where('id',$id)->update(['status'=>'failed','failed_at'=>now(),'updated_at'=>now()]);
                return null;
            }
            $row->claim_token=(string)Str::uuid();
            $row->attempts++;
            DB::table('instructor_decision_notifications')->where('id',$id)->update(['status'=>'sending','attempts'=>$row->attempts,'claim_token'=>$row->claim_token,'claimed_at'=>now(),'updated_at'=>now()]);
            return $row;
        },5);
        if (!$claim) return 'skipped';
        try {
            Mail::to($claim->recipient)->send(new InstructorRequestApprovalMail((object)[
                'name'=>$claim->recipient_name,'approved_status'=>$claim->decision,
            ]));
        } catch (\Throwable $error) {
            // Store only the error class. Transport messages may contain secrets.
            $terminal=$claim->attempts>=self::MAX_ATTEMPTS;
            $this->finish($claim,['status'=>$terminal?'failed':'pending','last_error'=>get_class($error),'failed_at'=>$terminal?now():null,'next_attempt_at'=>$terminal?null:now()->addMinutes(min(60,2**$claim->attempts))]);
            return $terminal?'failed':'pending';
        }
        $this->finish($claim,['status'=>'sent','sent_at'=>now(),'failed_at'=>null,'last_error'=>null,'next_attempt_at'=>null]);
        return 'sent';
    }

    private function finish(object $claim,array $values): void
    {
        DB::table('instructor_decision_notifications')->where('id',$claim->id)->where('claim_token',$claim->claim_token)->update($values+['claim_token'=>null,'claimed_at'=>null,'updated_at'=>now()]);
    }

    public function retry(int $id): bool
    {
        return (bool)DB::table('instructor_decision_notifications')->where('id',$id)->where('status','failed')->update(['status'=>'pending','attempts'=>0,'failed_at'=>null,'next_attempt_at'=>null,'claim_token'=>null,'claimed_at'=>null,'updated_at'=>now()]);
    }
}
