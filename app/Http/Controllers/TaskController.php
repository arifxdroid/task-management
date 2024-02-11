<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TaskController extends Controller
{
    //

    public function index(Request $request) : View
    {
        return \view('tasks.index');
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
