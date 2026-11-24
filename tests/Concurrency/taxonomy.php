<?php
require getenv('APP_DIR').'/vendor/autoload.php';
$app=require getenv('APP_DIR').'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use App\Models\{User,Course,CourseCategory,CourseLevel,CourseLanguage};
$work=getenv('WORK');
if (config('database.default')!=='mysql' || config('database.connections.mysql.database')!=='thinkcore_fictional_qualification' || config('database.connections.mysql.unix_socket')!==$work.'/mysql.sock') throw new RuntimeException('Refusing non-harness database');
if (($argv[1]??'')==='worker') {
 [$script,$mode,$operation,$kind,$id,$courseId,$ownerId,$marker]=$argv;
 $table=['category'=>'course_categories','level'=>'course_levels','language'=>'course_languages'][$kind];
 if ($marker!=='none') {
  $paused=false;
  DB::listen(function($query) use ($table,$work,$marker,&$paused) {
   if (!$paused && str_contains($query->sql,'`'.$table.'`') && str_contains(strtolower($query->sql),'for update')) {
    $paused=true;file_put_contents("$work/$marker.ready",'ready');
    $deadline=microtime(true)+15;
    while (!file_exists("$work/$marker.release") && microtime(true)<$deadline) usleep(10000);
    if (!file_exists("$work/$marker.release")) throw new RuntimeException('Barrier timeout');
   }
  });
 }
 if ($marker==='none') DB::connection()->beforeExecuting(function ($query) use ($table,$work) {
  if (str_contains($query,'`'.$table.'`') && str_contains(strtolower($query),'for update')) file_put_contents($work.'/second.attempt','attempt');
 });
 $request=Request::create('/local-qualification',$operation==='assign'?'POST':($operation==='child'?'POST':'DELETE'));
 $request->setLaravelSession(app('session')->driver());
 $request->session()->start();
 $request->setUserResolver(fn()=>User::findOrFail($ownerId));
 if ($operation==='assign') {
  $request->merge(['id'=>$courseId,'current_step'=>2,$kind=>$id]);
  $controller=new App\Http\Controllers\Frontend\CourseController;
 } else {
  $prefix=['category'=>'categories','level'=>'levels','language'=>'languages'][$kind];
  $route=new Route($request->method(),'/local-qualification',fn()=>null);
  $route->name('admin.course-'.($operation==='child'?'sub-categories':$prefix).'.'.($operation==='child'?'store':'destroy'));
  $route->bind($request);
  $route->setParameter(['category'=>'course_category','level'=>'course_level','language'=>'course_language'][$kind],$id);
  $request->setRouteResolver(fn()=>$route);
  if ($operation==='child') $request->merge(['name'=>'Child '.bin2hex(random_bytes(8)),'status'=>1]);
  $controller=new App\Http\Controllers\Admin\TaxonomyController;
 }
 app()->instance('request',$request);
 $request->setUserResolver(fn()=>User::findOrFail($ownerId));
 try {
  $response=match($operation){'assign'=>$controller->moreinfo($request),'child'=>$controller->store($request),default=>$controller->destroy($request)};
  echo json_encode(['operation'=>$operation,'status'=>$response->getStatusCode()])."\n";
 } catch (Illuminate\Validation\ValidationException $e) {echo json_encode(['operation'=>$operation,'status'=>422])."\n";}
 catch (Symfony\Component\HttpKernel\Exception\HttpExceptionInterface $e) {echo json_encode(['operation'=>$operation,'status'=>$e->getStatusCode()])."\n";}
 catch (Illuminate\Database\Eloquent\ModelNotFoundException $e) {echo json_encode(['operation'=>$operation,'status'=>404])."\n";}
 catch (Throwable $e) {fwrite(STDERR,get_class($e).':'.$e->getMessage()."\n".$e->getTraceAsString());exit(1);}
 exit;
}
$version=DB::selectOne('SELECT VERSION() AS version')->version;
echo "engine=$version isolation=".DB::selectOne('SELECT @@transaction_isolation AS isolation')->isolation." head=".trim(shell_exec('git rev-parse HEAD'))."\n";
$owner=User::factory()->create(['role'=>'instructor','approved_status'=>'approved']);
function spawnWorker($operation,$kind,$id,$course,$owner,$marker) {
 $cmd=[PHP_BINARY,__FILE__,'worker',$operation,$kind,(string)$id,(string)$course,(string)$owner,$marker];
 $pipes=[];$proc=proc_open($cmd,[0=>['pipe','r'],1=>['pipe','w'],2=>['pipe','w']],$pipes);
 fclose($pipes[0]);return [$proc,$pipes];
}
function finishWorker($worker) {
 [$proc,$pipes]=$worker;$out=stream_get_contents($pipes[1]);$error=stream_get_contents($pipes[2]);fclose($pipes[1]);fclose($pipes[2]);$code=proc_close($proc);
 if ($code!==0) throw new RuntimeException('Worker failed: '.$error.$out);
 $result=json_decode(trim($out),true);if (!$result || !in_array($result['status'],[302,404,409,422])) throw new RuntimeException('Unexpected result '.$out);
 return $result;
}
// Outside the PHPUnit outer transaction, exercise actual five-attempt exhaustion.
$retryOwner=User::factory()->create(['role'=>'instructor','approved_status'=>'approved']);
$retryCourse=new Course;$retryCourse->instructor_id=$retryOwner->id;$retryCourse->title='Retry fixture';$retryCourse->slug='retry-'.bin2hex(random_bytes(8));$retryCourse->capacity=10;
$image='thumbnail/qualification-'.bin2hex(random_bytes(8)).'.png';
Illuminate\Support\Facades\Storage::disk('public')->put($image,'original fictional image');
$retryCourse->thumbnail='/storage/'.$image;$retryCourse->save();
$attempts=0;
Course::updating(function () use (&$attempts) { $attempts++;throw new PDOException('Deadlock found when trying to get lock; try restarting transaction',40001); });
$r=Request::create('/local-qualification','POST',['id'=>$retryCourse->id,'current_step'=>2,'capacity'=>20]);
app()->instance('request',$r);$r->setUserResolver(fn()=>$retryOwner);
try {(new App\Http\Controllers\Frontend\CourseController)->moreinfo($r);throw new RuntimeException('Expected exhausted deadlock');}
catch(PDOException $expected) {}
finally {Course::flushEventListeners();}
if($attempts!==5 || $retryCourse->fresh()->capacity!==10 || Illuminate\Support\Facades\Storage::disk('public')->get($image)!=='original fictional image') throw new RuntimeException('Retry exhaustion invariant failed');
Illuminate\Support\Facades\Storage::disk('public')->delete($image);
echo "PASS retry exhaustion: five attempts, unchanged course and original image bytes\n";
$rounds=0;
foreach (range(1,3) as $round) foreach (['category','level','language','child'] as $kind) foreach ([true,false] as $writerFirst) {
 $type=$kind==='child'?'category':$kind;
 $model=['category'=>CourseCategory::class,'level'=>CourseLevel::class,'language'=>CourseLanguage::class][$type];
 $item=new $model;$item->name='Race '.bin2hex(random_bytes(8));$item->slug='race-'.bin2hex(random_bytes(8));if($type==='category')$item->status=1;$item->save();
 $course=new Course;$course->instructor_id=$owner->id;$course->title='Fictional concurrency';$course->slug='race-'.bin2hex(random_bytes(8));$course->save();
 $marker='case-'.getmypid().'-'.++$rounds;
 $write=$kind==='child'?'child':'assign';
 $first=spawnWorker($writerFirst?$write:'delete',$type,$item->id,$course->id,$owner->id,$marker);
 $deadline=microtime(true)+15;
 while(!file_exists("$work/$marker.ready") && microtime(true)<$deadline) usleep(10000);
 if(!file_exists("$work/$marker.ready")) { print_r(finishWorker($first)); throw new RuntimeException('First worker never locked'); }
 if(file_exists($work.'/second.attempt')) unlink($work.'/second.attempt');
 $second=spawnWorker($writerFirst?'delete':$write,$type,$item->id,$course->id,$owner->id,'none');
 $deadline=microtime(true)+15;
 while(!file_exists($work.'/second.attempt') && microtime(true)<$deadline) usleep(10000);
 if(!file_exists($work.'/second.attempt')) throw new RuntimeException('Second worker never attempted lock');
 usleep(200000);
 $blocked=proc_get_status($second[0])['running'];
 file_put_contents("$work/$marker.release",'release');
 $a=finishWorker($first);$b=finishWorker($second);
 $column=['category'=>'category_id','level'=>'course_level_id','language'=>'course_language_id'][$type];
 $exists=$model::whereKey($item->id)->exists();$ref=Course::whereKey($course->id)->value($column);
 $children=$type==='category'?CourseCategory::where('parent_id',$item->id)->count():0;
 if ((!$exists && ($ref==$item->id || ($type==='category' && $children))) || !$blocked) throw new RuntimeException('Invariant/blocking failed');
 echo json_encode(compact('round','kind','writerFirst','blocked','a','b','exists','ref','children'))."\n";
}
foreach (['category_id'=>'course_categories','course_level_id'=>'course_levels','course_language_id'=>'course_languages'] as $column=>$table) {
 $orphans=DB::table('courses as c')->leftJoin("$table as t",'t.id',"c.$column")->whereNotNull("c.$column")->whereNull('t.id')->count();
 if($orphans)throw new RuntimeException('Dangling course taxonomy');
}
if (DB::table('course_categories as c')->leftJoin('course_categories as p','p.id','c.parent_id')->whereNotNull('c.parent_id')->whereNull('p.id')->count()) throw new RuntimeException('Dangling child');
echo "PASS $rounds orchestrated two-process races; no dangling taxonomy or parent references\n";
