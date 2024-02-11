<x-app-layout>

    <div class="md:container md:mx-auto mt-5 flex">
        <div class="w-full p-4 bg-white rounded-lg shadow-md">
            <p class="text-2xl font-medium text-gray-900 mb-5">
                <input type="hidden" id="currentProjectId" value="{{$project->id}}">
                <span>({{$project->project_code}})</span>
                <span>{{$project->name}}</span>
            </p>
        </div>
    </div>

    @include('partials.tasks_list')

</x-app-layout>


