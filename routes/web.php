<?php
Route::get('/', function () { return redirect('/admin/home'); });

// Authentication Routes...
$this->get('login', 'Auth\LoginController@showLoginForm')->name('auth.login');
$this->post('login', 'Auth\LoginController@login')->name('auth.login');
$this->post('logout', 'Auth\LoginController@logout')->name('auth.logout');

// Change Password Routes...
$this->get('change_password', 'Auth\ChangePasswordController@showChangePasswordForm')->name('auth.change_password');
$this->patch('change_password', 'Auth\ChangePasswordController@changePassword')->name('auth.change_password');

// Password Reset Routes...
$this->get('password/reset', 'Auth\ForgotPasswordController@showLinkRequestForm')->name('auth.password.reset');
$this->post('password/email', 'Auth\ForgotPasswordController@sendResetLinkEmail')->name('auth.password.reset');
$this->get('password/reset/{token}', 'Auth\ResetPasswordController@showResetForm')->name('password.reset');
$this->post('password/reset', 'Auth\ResetPasswordController@reset')->name('auth.password.reset');

// Registration Routes..
$this->get('register', 'Auth\RegisterController@showRegistrationForm')->name('auth.register');
$this->post('register', 'Auth\RegisterController@register')->name('auth.register');

Route::group(['middleware' => ['auth'], 'prefix' => 'admin', 'as' => 'admin.'], function () {
    
    Route::get('/home', 'HomeController@index');

    Route::resource('subscriptions', 'Admin\SubscriptionsController');
    Route::resource('payments', 'Admin\PaymentsController');
    Route::resource('roles', 'Admin\RolesController');
    Route::post('roles_mass_destroy', ['uses' => 'Admin\RolesController@massDestroy', 'as' => 'roles.mass_destroy']);
    Route::resource('users', 'Admin\UsersController');
    Route::post('users_mass_destroy', ['uses' => 'Admin\UsersController@massDestroy', 'as' => 'users.mass_destroy']);

    Route::resource('folders', 'Admin\FoldersController');

    Route::resource('patients','Admin\PatientsController');

    Route::post('new_patient', ['uses' => 'Admin\PatientsController@newPatient']);

    Route::post('list_patients', ['uses' => 'Admin\PatientsController@listPatients']);

    Route::post('list_files', ['uses' => 'Admin\PatientsController@listFiles']);

    Route::post('search_users', ['uses' => 'Admin\PatientsController@searchUsers']);

    Route::post('folders_mass_destroy', ['uses' => 'Admin\FoldersController@massDestroy', 'as' => 'folders.mass_destroy']);

    Route::post('get_all_folders', ['uses' => 'Admin\FoldersController@getFolderList', 'as' => 'folders.get_list']);
    Route::post('add_subfolder', ['uses' => 'Admin\FoldersController@addSubFolder', 'as' => 'folders.add_subfolder']);
    Route::post('basic_list', ['uses' => 'Admin\FoldersController@getListOfFiles', 'as' => 'folders.basic_list']);
    Route::post('folders_restore/{id}', ['uses' => 'Admin\FoldersController@restore', 'as' => 'folders.restore']);


    

    Route::delete('folders_perma_del/{id}', ['uses' => 'Admin\FoldersController@perma_del', 'as' => 'folders.perma_del']);
    Route::resource('files', 'Admin\FilesController');

    Route::get('list_of_files_ror', ['uses' => 'Admin\FilesController@listFilesROR']);
    Route::get('assign_file_ror', ['uses' => 'Admin\FilesController@assignFilesROR']);

    //file manager actions
    Route::get('file_manager', ['uses' => 'Admin\FilesController@fileManager']);
    Route::get('get_as_downloadable', ['uses' => 'Admin\FilesController@getAsDownloadable']);
    Route::post('download_folder', ['uses' => 'Admin\FilesController@downloadFolder']);
    Route::post('generate_download_link', ['uses' => 'Admin\FilesController@generateDownloadLink']);

    //jsons/ROR actions
    Route::post('assign_as_remark', ['uses' => 'Admin\FilesController@assignAsRemark']);
    Route::post('submit_assignments', ['uses' => 'Admin\FilesController@submitAssignments']);
    Route::post('delete_assignment', ['uses' => 'Admin\FilesController@deleteAssignment']);
    Route::post('mark_as_complete', ['uses' => 'Admin\FilesController@markAsComplete']);
    Route::post('reset_as_pending', ['uses' => 'Admin\FilesController@resetAsPending']);
    Route::post('finish_editing_remark', ['uses' => 'Admin\FilesController@finishEditingRemark']);
    Route::post('edit_remark', ['uses' => 'Admin\FilesController@editRemark']);
    
    //--------fetch data
    Route::post('list_of_employees', ['uses' => 'Admin\FilesController@listOfEmployeesROR']);
    Route::post('list_of_unassigned_files', ['uses' => 'Admin\FilesController@listOfUnassignedFilesROR']);
    Route::post('list_of_eligible_files_for_remark', ['uses' => 'Admin\FilesController@listOfEligibleFilesForRemark']);

    //Route::post('folders/getFolderList', ['uses' => 'Admin\FoldersController@getFolderList', 'as' => 'folders.getFolderList']);
    Route::get('/{uuid}/download', 'Admin\DownloadsController@download');
    Route::post('files_mass_destroy', ['uses' => 'Admin\FilesController@massDestroy', 'as' => 'files.mass_destroy']);
    Route::post('files_restore/{id}', ['uses' => 'Admin\FilesController@restore', 'as' => 'files.restore']);
    Route::delete('files_perma_del/{id}', ['uses' => 'Admin\FilesController@perma_del', 'as' => 'files.perma_del']);
    Route::post('/spatie/media/upload', 'Admin\SpatieMediaController@create')->name('media.upload');
    Route::post('/spatie/media/remove', 'Admin\SpatieMediaController@destroy')->name('media.remove');
 
});
