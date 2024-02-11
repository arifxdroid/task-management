<x-app-layout>
    <div class="md:container md:mx-auto mt-5 flex">

        <div class="w-full p-4 bg-white rounded-lg shadow-md">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center">
                    <label for="search" class="sr-only">Search</label>
                    <input type="text"
                           id="search"
                           class="w-40 px-3 py-2 text-sm border rounded-lg focus:outline-none"
                           placeholder="Search...">
                </div>
                @can('create-project-task')
                    <a href="#" id="addNewProject" type="submit" class="px-4 py-2 text-sm text-white bg-blue-500 rounded-lg hover:bg-blue-600">Add New</a>
                @endcan
            </div>
            <table class="w-full text-sm text-left" id="projectListTable">
                <thead class="text-xs uppercase bg-gray-50">
                <tr>
                    <th scope="col" class="px-4 py-3"> ID</th>
                    <th scope="col" class="px-4 py-3"> Name</th>
                    <th scope="col" class="px-4 py-3"> Updated At</th>
                    <th scope="col" class="px-4 py-3"> Actions</th>
                </tr>
                </thead>
                <tbody>
                @foreach($projects as $project)
                    <tr class="bg-white border-b">
                        <td class="hidden project-id">{{$project->id}}</td>
                        <td class="project-code px-4 py-3">{{$project->project_code}}</td>
                        <td class="project-name px-4 py-3">{{$project->name}}</td>
                        <td class="px-4 py-3">{{$project->updated_at}}</td>
                        <td class="px-4 py-3">
                            <a href="#" class="btnProjectEdit mr-2 text-blue-500 hover:text-blue-600">Edit</a>
                            <a href="/projects/{{$project->project_code}}" class="text-green-500 hover:text-green-600">View</a>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>

    </div>

    <div class="modal" id="addNewModal">
        <div class="max-w-md mx-auto my-auto hidden justify-center text-center" id="successMessage">
            <h2 class="p-4">Successfully created new Project!</h2>
            <a href="/projects" class="mt-5 px-4 py-2 text-sm text-white bg-blue-500 rounded-lg hover:bg-blue-600">Back to projects.</a>
        </div>
        <form class="max-w-md mx-auto" id="newProjectForm">
            <h2 class="modal-title text-2xl font-medium text-gray-900 mb-5">Add New Project</h2>
            <p class="text-red-500 mb-5 hidden" id="submissionError">Error!</p>
            <input type="hidden" name="currentProjectId" id="currentProjectId">
            <div class="mb-5">
                <label for="projectName" class="block mb-2 text-sm font-medium text-gray-900">Project Name</label>
                <input type="text" id="projectName" class="block w-full p-2.5 text-gray-900 border border-gray-300 rounded-lg sm:text-md focus:ring-blue-500 focus:border-blue-500" placeholder="Enter project name" required>
            </div>
            <button type="submit" id="projectCreate" class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm w-full sm:w-auto px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">Submit</button>
        </form>
    </div>
</x-app-layout>

<script>

    $(document).ready(function (){

        const btnAddNewProject = $('#addNewProject');
        const modalUi = $('#addNewModal');
        const projectNameInputField = $('#projectName');
        const projectSubmitButton = $('#projectCreate');
        const successMessageDiv = $('#successMessage');
        const newProjectForm = $('#newProjectForm');
        const submissionError = $('#submissionError');
        const currentProjectId = $('#currentProjectId');
        const projectListTable = $('#projectListTable');
        const modalTitle = $('.modal-title');
        const ajaxUrl = '/projects';

        // Add edit project button click event
        projectListTable.on('click', '.btnProjectEdit', function (){

            let currentRow = $(this).parents('tr');
            let id = currentRow.find('.project-id').text();
            let name = currentRow.find('.project-name').text();
            showProjectModal(id, name)
        })

        // Add new project button clicked event.
        btnAddNewProject.on('click', function (e){
            e.preventDefault();
            showProjectModal();
        })

        // Add new project form submit button clicked event.
        projectSubmitButton.on('click', function (e){

            e.preventDefault();
            let projectName = projectNameInputField.val()
            if(projectName){

                projectSubmitButton.attr('disabled');
                saveNewProject(projectName, function (err, message){

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
            }
        })

        function showProjectModal(projectId = '', projectName = ''){

            projectNameInputField.val(projectName);

            if(projectId && projectId.length){
                modalTitle.text('Edit Project')
            }else {
                modalTitle.text('Add New Project')
            }

            showSuccessUI(false);
            currentProjectId.val(projectId)

            modalUi.modal({
                escapeClose: false,
                clickClose: false,
            })
            console.log('Add/Edit project modal shown!');
        }

        function saveNewProject(projectName, callback){

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            let postData = {name: projectName};
            if(currentProjectId.val()){
                postData.id = parseInt(currentProjectId.val());
            }
            console.log(currentProjectId.val());
            $.ajax({
                type: "POST",
                url: ajaxUrl,
                data: postData,
                dataType: 'json',
                success: function (data) {
                    let message = 'Successfully created new Project!';
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

            let defaultMessage = 'Successfully created new Project!';
            if(show){
                successMessageDiv.find('h2').text(message||defaultMessage)
                successMessageDiv.removeClass('hidden')
                newProjectForm.addClass('hidden')
            }
            else {
                successMessageDiv.addClass('hidden')
                newProjectForm.removeClass('hidden')
            }
        }
    })
</script>
