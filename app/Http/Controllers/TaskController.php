<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class TaskController extends Controller
{
    //

    public function index(Request $request) : View
    {

//        $userId = Auth::id();
        $teammateRole = Role::where('name', 'Teammate')->first();
        $tasks = Task::with('users')->whereHas('users', function($query){
            $query->where('user_id', '=',  Auth::id() );
        })->with('project')->get();

        // Retrieve all users with the role 'Teammate'
        $teammates = User::where('role_id', $teammateRole->id)->get();
        $project = null;


//        dd($tasks);
        return \view('tasks.index', compact('tasks', 'teammates', 'project'));
    }

    public function store(Request $request) : View | JsonResponse
    {

//        $project_id = $request->project_id;
//        $task_name = $request->task_name;
//        $description = $request->description;
//        $status = $request->status;
//        $user_id = $request->user_id;

        $message = '';
        // Validate incoming request data
        $validatedData = $request->validate([
            'task_id' => 'sometimes|exists:tasks,id',
            'project_id' => 'required|exists:projects,id',
            'task_name' => 'required|string|max:255',
            'description' => 'required|string',
            'status' => 'required|in:Pending,Working,Done',
        ]);

        if($request->has('id')){

            $task = Task::findOrFail($request->id);
            $task->update($validatedData);
            $message = 'Task updated successfully';
        }
        else{
            $task = Task::create($validatedData);
            $message = 'Task created successfully';
        }

        if($request->has('user_id')){

            $user = User::findOrFail($request->user_id);
            $taskAddUser = Task::findOrFail($task->id);
            $taskAddUser->users()->detach();
            $taskAddUser->users()->attach($user);
//            $taskAddUser->save();
        }

        return response()->json(['message' => $message, 'task' => $task]);
    }
}
