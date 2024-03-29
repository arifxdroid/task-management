<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Role;
use App\Models\Task;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;
use Illuminate\Validation\Rules;

class ProjectController extends Controller
{
    //

    public function index(Request $request) : View
    {

        // Authorize action for creating project or task
        if (Gate::denies('create-project-task')) {
            abort(403, 'Unauthorized action.');
        }

        $projects = Project::get();
        return \view('projects.index', compact('projects'));
    }

    public function show(Request $request, $project_code) : View
    {
//        if (Gate::denies('create-project-task')) {
//            abort(403, 'Unauthorized action.');
//        }

        $teammateRole = Role::where('name', 'Teammate')->first();
        $project = Project::where('project_code', $project_code)->first();
        if(!$project){
            abort(404);
        }
        $tasks = Task::with('users')->with('project')->where('project_id', $project->id)->get();

        // Retrieve all users with the role 'Teammate'
        $teammates = User::where('role_id', $teammateRole->id)->get();


//        dd($tasks);
        return \view('projects.show', compact('tasks', 'project', 'teammates'));
    }

    public function store(Request $request) :View|JsonResponse
    {
        // Authorize action for creating project or task
        if (Gate::denies('create-project-task')) {
            abort(403, 'Unauthorized action.');
        }
        // Validate incoming request data
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        // Check if projectId is provided, if so, update the existing project
        if ($request->has('id')) {
            $project = Project::findOrFail($request->id);
            $project->update($validatedData);
        } else {
            // Otherwise, create a new project
            $validatedData['project_code'] = Project::generateUniqueProjectCode();
            $validatedData['user_id'] = Auth::id();
            $project = Project::create([
                'project_code' => $validatedData['project_code'],
                'name' => $validatedData['name'],
                'user_id' => $validatedData['user_id']
            ]);
        }

        return response()->json(['message' => 'Project ' . ($request->has('project_id') ? 'updated' : 'created') . ' successfully', 'project' => $project], 200);
    }

    public function storeTeammate(Request $request): RedirectResponse|JsonResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'employee_id' => 'required|string|unique:users,employee_id',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role' => 'required|in:Manager,Teammate', // Assuming role is passed from the form
        ]);

        // Retrieve role ID based on role name
        $role = Role::where('name', $request->role)->first();

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'employee_id' => $request->employee_id,
            'password' => Hash::make($request->password),
            'role_id' => $role->id,
        ]);

        event(new Registered($user));
        return response()->json(['message' => 'Teammate added successfully'], 201);
    }
}
