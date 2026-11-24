<?php

require getenv('APP_DIR').'/vendor/autoload.php';
$app=require getenv('APP_DIR').'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Support\InstructorDecisionOutbox;
$work=getenv('WORK');
if(config('database.default')!=='mysql'||config('database.connections.mysql.database')!=='thinkcore_fictional_qualification'||config('database.connections.mysql.unix_socket')!==$work.'/mysql.sock') throw new RuntimeException('Refusing non-harness database');
Mail::fake();
if(($argv[1]??'')==='worker') {
    $id=(int)$argv[2];$marker=$argv[3];$first=$argv[4]==='first';
    if($first) {
        $paused=false;
        DB::listen(function($query) use (&$paused,$marker,$work) {
            if(!$paused && str_starts_with(strtolower($query->sql),'update `instructor_decision_notifications`')) {
                $paused=true;file_put_contents("$work/$marker.ready",'ready');
                $deadline=microtime(true)+15;
                while(!file_exists("$work/$marker.release")&&microtime(true)<$deadline)usleep(10000);
                if(!file_exists("$work/$marker.release"))throw new RuntimeException('Claim barrier timeout');
            }
        });
    } else DB::connection()->beforeExecuting(function($query) use ($work,$marker) {
        if(str_contains($query,'`instructor_decision_notifications`')&&str_contains(strtolower($query),'for update'))file_put_contents("$work/$marker.attempt",'attempt');
    });
    $result=app(InstructorDecisionOutbox::class)->deliver($id);
    echo json_encode(['result'=>$result,'sent'=>Mail::sent(App\Mail\InstructorRequestApprovalMail::class)->count()])."\n";
    exit;
}
$marker='outbox-'.getmypid();
$id=DB::table('instructor_decision_notifications')->insertGetId(['user_id'=>999999,'decision_key'=>hash('sha256',$marker),'recipient'=>'fictional@example.test','recipient_name'=>'Fictional','decision'=>'approved','decided_at'=>now(),'status'=>'pending','created_at'=>now(),'updated_at'=>now()]);
function startClaim($id,$marker,$role) {
    $pipes=[];$process=proc_open([PHP_BINARY,__FILE__,'worker',(string)$id,$marker,$role],[0=>['pipe','r'],1=>['pipe','w'],2=>['pipe','w']],$pipes);fclose($pipes[0]);return [$process,$pipes];
}
function finishClaim($worker) {
    [$process,$pipes]=$worker;$out=stream_get_contents($pipes[1]);$error=stream_get_contents($pipes[2]);fclose($pipes[1]);fclose($pipes[2]);$code=proc_close($process);
    if($code!==0||!($decoded=json_decode(trim($out),true)))throw new RuntimeException('Worker failed '.$error.$out);
    return $decoded;
}
function waitMarker($file) {
    $deadline=microtime(true)+15;while(!file_exists($file)&&microtime(true)<$deadline)usleep(10000);
    if(!file_exists($file))throw new RuntimeException('Missing claim marker');
}
$a=startClaim($id,$marker,'first');waitMarker("$work/$marker.ready");
$b=startClaim($id,$marker,'second');waitMarker("$work/$marker.attempt");usleep(200000);
$blocked=proc_get_status($b[0])['running'];file_put_contents("$work/$marker.release",'release');
$first=finishClaim($a);$second=finishClaim($b);$row=DB::table('instructor_decision_notifications')->find($id);
if(!$blocked||$first!==['result'=>'sent','sent'=>1]||$second!==['result'=>'skipped','sent'=>0]||$row->status!=='sent'||$row->attempts!==1)throw new RuntimeException('Claim invariant failed');
echo "PASS outbox parallel claims: second worker blocked, one fake send, one skipped, attempts=1\n";
