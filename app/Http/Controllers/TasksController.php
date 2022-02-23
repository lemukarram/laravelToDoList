<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Task;
use DateTime;

class TasksController extends Controller
{
    public function index()
    {
        $tasks = Task::all();
        return view('dashboard')->with('tasks',$tasks);
    }
    public function add()
    {
    	return view('add');
    }

    public function create(Request $request)
    {
        
        //echo $given->format("Y-m-d H:i:s e");
        //exit($request->tz);
        $this->validate($request, [
            'description' => 'required',
            'deadline'=>'required'
        ]);
        
        //convert timezone to utc for saving in database.
        
    	$task = new Task();
    	$task->description = $request->description;
        $task->deadline = $this->datetimeToUTC($request->deadline,$request->tz);
    	$task->user_id = auth()->user()->id;
    	$task->save();
    	return redirect('/dashboard'); 
    }

    public function edit(Task $task)
    {

    	if (auth()->user()->id == $task->user_id)
        {            
                return view('edit', compact('task'));
        }           
        else {
             return redirect('/dashboard');
         }            	
    }

    public function update(Request $request, Task $task)
    {
    	if(isset($_POST['delete'])) {
    		$task->delete();
    		return redirect('/dashboard');
    	}
    	else
    	{
            $this->validate($request, [
                'description' => 'required',
                'deadline'=>'required'
            ]);


            
            
    		$task->description = $request->description;
            $task->deadline = $this->datetimeToUTC($request->deadline,$request->tz);
	    	$task->save();
	    	return redirect('/dashboard'); 
    	}    	
    }

    private function datetimeToUTC($deadline, $tz){

            $utcTimezone = new \DateTimeZone( 'UTC' );
            $datetime = new DateTime($deadline." ".$tz);
            $datetime->setTimeZone($utcTimezone);

            return $datetime->format("Y-m-d H:i:s");

    }

    public static function UTCToDateTime($deadline){

        $tz = self::getTimezoneApi();
        $utcTimezone = new \DateTimeZone( $tz );
        $datetime = new DateTime($deadline." UTC ");
        $datetime->setTimeZone($utcTimezone);

        return $datetime->format("Y-m-d H:i:s");

    }

    public static function getTimezoneApi(){
        
        return isset($_COOKIE['tz'])?$_COOKIE['tz']:'';
    }
}
