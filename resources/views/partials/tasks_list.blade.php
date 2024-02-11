<div class="md:container md:mx-auto mt-5 flex">
    <div class="w-full p-4 bg-white rounded-lg shadow-md">
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center">
                <label for="search" class="sr-only">Search</label>
                <input type="text"
                       id="search"
                       class="w-40 px-3 py-2 text-sm border rounded-lg focus:outline-none"
                       placeholder="Task Search...">
            </div>
            <a href="#" id="addNewTask" type="submit" class="px-4 py-2 text-sm text-white bg-blue-500 rounded-lg hover:bg-blue-600" data-action="New">Add New Task</a>
        </div>
        <table class="w-full text-sm text-left" id="projectTaskListTable">
            <thead class="text-xs uppercase bg-gray-50">
            <tr>
                <th scope="col" class="px-4 py-3"> Project ID</th>
                <th scope="col" class="px-4 py-3"> Task Name</th>
                <th scope="col" class="px-4 py-3"> Status</th>
                <th scope="col" class="px-4 py-3"> Created At</th>
                <th scope="col" class="px-4 py-3"> Actions</th>
            </tr>
            </thead>
            <tbody>
            @foreach($tasks as $task)
                <tr class="bg-white border-b">
                    <td class="hidden project-id">{{$task->project->id}}</td>
                    <td class="hidden task-id">{{$task->id}}</td>
                    <td class="hidden task-description">{{$task->description}}</td>
                    @php
                        $user = null;
                        if(count($task->users)){
                            $user = $task->users[0];
                        }
                    @endphp
                    <td class="hidden task-user-id">{{$user?->id}}</td>
                    <td class="project-code px-4 py-3">
                        <a href="/projects/{{$task->project->project_code}}" class="text-green-500 hover:text-green-600">{{$task->project->project_code}}</a>
                    </td>
                    <td class="task-name px-4 py-3">{{$task->task_name}}</td>
                    <td class="task-status px-4 py-3">{{$task->status}}</td>
                    <td class="px-4 py-3">{{$task->updated_at}}</td>
                    <td class="px-4 py-3">
                        <a href="#" class="btnTaskAction mr-2 text-blue-500 hover:text-blue-600" data-action="Edit">Edit</a>
                        <a href="#" class="btnTaskAction text-green-500 hover:text-green-600" data-action="Update">Update</a>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>

{{--  Add/Update/Edit task modal  --}}
<div class="modal" id="addNewTaskModal">
    <div class="max-w-md mx-auto my-auto hidden justify-center text-center" id="successMessage">
        <h2 class="p-4">Successfully created new Project!</h2>
        <a href="/projects/{{$project->project_code}}" class="mt-5 px-4 py-2 text-sm text-white bg-blue-500 rounded-lg hover:bg-blue-600">Back to projects.</a>
    </div>
    <form class="max-w-md mx-auto" id="newTaskForm">
        <input type="hidden" id="existingData">
        <input type="hidden" id="currentTaskId">
        <h2 class="modal-title text-2xl font-medium text-gray-900 mb-5">Add New Task</h2>
        <p class="text-red-500 mb-5 hidden" id="submissionError">Error!</p>
        <div class="mb-5">
            <label for="taskName" class="block mb-2 text-sm font-medium text-gray-900">Task Name</label>
            <input type="text" id="taskName" class="block w-full p-2.5 text-gray-900 border border-gray-300 rounded-lg sm:text-md focus:ring-blue-500 focus:border-blue-500" placeholder="Enter task name" required>
        </div>
        <div class="mb-5">
            <label for="taskDescription" class="block mb-2 text-sm font-medium text-gray-900">Description</label>
            <textarea id="taskDescription" class="block w-full p-2.5 text-gray-900 border border-gray-300 rounded-lg bg-gray-50 sm:text-md focus:ring-blue-500 focus:border-blue-500" rows="4" placeholder="Enter your description" required></textarea>
        </div>
        <div class="mb-5">
            <label for="taskStatusInput" class="block mb-2 text-sm font-medium text-gray-900">Status</label>
            <select disabled name="taskStatusInput" id="taskStatusInput" class="block w-full p-2.5 text-gray-900 border border-gray-300 rounded-lg sm:text-md focus:ring-blue-500 focus:border-blue-500">
                <option value="Pending">Pending</option>
                <option value="Working">Working</option>
                <option value="Done">Done</option>
            </select>
        </div>
        <div class="mb-20">
            <label for="teammateIdSelect" class="block mb-2 text-sm font-medium text-gray-900">Select Teammate</label>
            <select name="teammateIdSelect" id="teammateIdSelect" class="block w-full p-2.5 text-gray-900 border border-gray-300 rounded-lg sm:text-md focus:ring-blue-500 focus:border-blue-500">
                <option value="">Select</option>
                @foreach($teammates as $teammate)
                    <option value="{{$teammate->id}}">{{$teammate->name}}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" id="taskSubmit" class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm w-full sm:w-auto px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">Submit</button>
    </form>
</div>


<script>

    $(document).ready(function (){

        const successMessageDiv = $('#successMessage');
        const btnAddNewTask = $('#addNewTask');
        const modalUi = $('#addNewTaskModal');
        const projectTaskListTable = $('#projectTaskListTable')
        const newTaskForm = $('#newTaskForm');
        const ajaxUrl = '/tasks'
        const submissionError = $('#submissionError');
        const taskSubmitButton = $('#taskSubmit');
        const currentProjectId = $('#currentProjectId');
        const teammateIdSelect = $('#teammateIdSelect');
        const taskNameInput = $('#taskName');
        const taskDescriptionInput = $('#taskDescription');
        const taskStatusInput = $('#taskStatusInput');
        const currentTaskId = $('#currentTaskId');
        const existingData = $('#existingData');


        // Add/update task button click event
        projectTaskListTable.on('click', '.btnTaskAction', function (){

            let action = $(this).attr('data-action');
            let currentRow = $(this).parents('tr');
            let rowData = {
                projectId : parseInt(currentRow.find('.project-id').text()),
                id : parseInt(currentRow.find('.task-id').text()),
                taskName : currentRow.find('.task-name').text(),
                taskDescription : currentRow.find('.task-description').text(),
                taskStatus : currentRow.find('.task-status').text(),
                action
            }

            if(currentRow.find('.task-user-id').text()){
                rowData.userTeammateId = parseInt(currentRow.find('.task-user-id').text());
                teammateIdSelect.val(rowData.userTeammateId)

            }
            else {
                teammateIdSelect.val('')
            }
            taskStatusInput.val(rowData.taskStatus)
            taskNameInput.val(rowData.taskName);
            taskDescriptionInput.val(rowData.taskDescription);
            taskStatusInput.val(rowData.taskStatus || 'Pending');

            existingData.data(rowData);

            //project-id task-id task-name task-status task-user-id

            console.log(action);
            showTaskModal()
        })

        // Add new project button clicked event.
        btnAddNewTask.on('click', function (e){
            e.preventDefault();

            existingData.data('');
            teammateIdSelect.val('')
            taskStatusInput.val('Pending')
            taskNameInput.val('');
            taskDescriptionInput.val('');

            showTaskModal();
        })

        taskSubmitButton.on('click', function (e){

            e.preventDefault();

            let projectId = currentProjectId.val();
            let taskName = taskNameInput.val();
            let taskDescription = taskDescriptionInput.val();
            let taskStatus = taskStatusInput.val() || 'Pending';
            let userTeammateId = teammateIdSelect.val();
            let action = 'New';
            let id;

            if(existingData.data()){
                id = existingData.data().id
            }

            if(!taskName) {
                showShortError('Task name is require')
                return false;
            }

            if(!taskDescription) {
                showShortError('Task description is require')
                return false;
            }

            if(!userTeammateId) {
                showShortError('Task teammate is require')
                return false;
            }

            taskSubmitButton.attr('disabled');
            saveNewTask(projectId, taskName, taskDescription, taskStatus, userTeammateId, action, id, function (err, message){

                if(err){
                    // Show error.
                    submissionError.removeClass('hidden');
                    submissionError.text(err);
                    setTimeout(function (){
                        submissionError.addClass('hidden');
                    }, 5000)
                }
                else{
                    //Reload the page
                    $('.close-modal').hide()
                    showSuccessUI(true, message)
                }
            })
        })

        function showShortError(message){
            submissionError.removeClass('hidden');
            submissionError.text(message);
            setTimeout(function (){
                submissionError.addClass('hidden');
            }, 5000)
        }

        function showTaskModal(){
            modalUi.modal({
                escapeClose: false,
                clickClose: false,
            })
            console.log('Add/Edit task modal shown!');
        }

        function saveNewTask(project_id, task_name, description, status, user_id, action, id, callback){

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            let postData = {project_id, task_name, description, status, user_id, action};
            if(id){
                postData.id = id;
            }
            if(currentTaskId.val()){
                postData.id = parseInt(currentTaskId.val());
            }
            $.ajax({
                type: "POST",
                url: ajaxUrl,
                data: postData,
                dataType: 'json',
                success: function (data) {
                    let message = 'Successfully created new Task!';
                    if(postData.id){
                        message = 'Update Successful'
                    }
                    return callback(null, message)
                },
                error: function (error) {
                    console.log(error);
                    return callback('Server Error!');
                }
            });
        }

        function showSuccessUI(show, message){

            let defaultMessage = 'Successfully created new Task!';
            if(show){
                successMessageDiv.find('h2').text(message||defaultMessage)
                successMessageDiv.removeClass('hidden')
                newTaskForm.addClass('hidden')
            }
            else {
                successMessageDiv.addClass('hidden')
                newTaskForm.removeClass('hidden')
            }
        }
    })
</script>
