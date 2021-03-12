@inject('request', 'Illuminate\Http\Request')
<!-- Left side column. contains the sidebar -->
<aside class="main-sidebar">
    <!-- sidebar: style can be found in sidebar.less -->
    <section class="sidebar">
        <ul class="sidebar-menu">

             

            <li class="{{ $request->segment(2) == 'home' ? 'active' : '' }}">
                <a href="{{ url('/') }}">
                    <i class="fa fa-wrench"></i>
                    <span class="title">@lang('quickadmin.qa_dashboard')</span>
                </a>
            </li>

            
            @can('user_management_access')
            <li class="treeview active">
                <a href="#">
                    <i class="fa fa-users"></i>
                    <span class="title">@lang('quickadmin.user-management.title')</span>
                    <span class="pull-right-container">
                        <i class="fa fa-angle-left pull-right"></i>
                    </span>
                </a>
                <ul class="treeview-menu menu-open">
                
                @can('role_access')
                <li class="{{ $request->segment(2) == 'roles' ? 'active active-sub' : '' }}">
                        <a href="{{ route('admin.roles.index') }}">
                            <i class="fa fa-briefcase"></i>
                            <span class="title">
                                @lang('quickadmin.roles.title')
                            </span>
                        </a>
                    </li>
                @endcan
                @can('user_access')
                <li class="{{ $request->segment(2) == 'users' ? 'active active-sub' : '' }}">
                        <a href="{{ route('admin.users.index') }}">
                            <i class="fa fa-user"></i>
                            <span class="title">
                                @lang('quickadmin.users.title')
                            </span>
                        </a>
                    </li>
                @endcan
                </ul>
            </li>
            @endcan
            @can('folder_access')
            <li class="{{ $request->segment(2) == 'folders' ? 'active' : '' }}">
                <a href="{{ route('admin.folders.index') }}">
                    <i class="fa fa-gears"></i>
                    <span class="title">@lang('quickadmin.folders.title')</span>
                </a>
            </li>
            @endcan
            
            @can('file_access') 
            <li class="{{ $request->segment(2) == 'files' && !preg_match("#create(\/)?$#",$request->getPathInfo()) ? 'active' : '' }}">
                <a href="{{ route('admin.files.index') }}">
                    <i class="fa fa-gears"></i>
                    <span class="title">@lang('quickadmin.files.title')</span>
                </a>
            </li>
            @endcan

            @can('patient_access')
            <li class="{{ $request->segment(2) == 'patients' && !preg_match("#create(\/)?$#",$request->getPathInfo()) ? 'active' : '' }}">
                <a href="{{ url('admin/patients') }}">
                    <i class="fa fa-list-alt" aria-hidden="true"></i>
                    <span class="title">Hyperlink</span>
                </a>
            </li>
            @endcan

            <?php if (
                app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('ror_supervision')
            ): ?> 
            <li class="{{ $request->segment(2) == 'assign_file_ror' ? 'active' : '' }}">
                <a href="{{ url('admin/assign_file_ror') }}">
                    <i class="fa fa-file-archive-o"></i>
                    <span class="title">Assign Files</span>
                </a>
            </li>

            <?php endif; ?>

            <?php if (
                app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('ror_maintenance')
            ): ?> 

            <li class="{{ $request->segment(2) == 'list_of_files_ror' ? 'active' : '' }}">
                <a href="{{ url('admin/list_of_files_ror') }}">
                    <i class="fa fa-list-alt"></i>
                    <span class="title">List of Assigned Files</span>
                </a>
            </li>
            
            <?php endif; ?>

            @can('file_access')
            @can('patient_access')
            <li class="{{ $request->segment(2) == 'patients' && preg_match("#create(\/)?$#",$request->getPathInfo()) ? 'active' : '' }}">
                <a href="{{ url('admin/patients/create') }}">
                    <i class="fa fa-plus"></i>
                    <span class="title"><strong>New Patient Entry</strong></span>
                </a>
            </li>
            @endcan
            @endcan

            @can('file_manager')
            <li class="{{ $request->segment(2) == 'file_manager' ? 'active' : '' }}">
                <a href="{{ url('admin/file_manager') }}">
                    <i class="fa fa-file"></i>
                    <span class="title">File Manager</span>
                </a>
            </li>
            @endcan


            <li class="{{ $request->segment(1) == 'change_password' ? 'active' : '' }}">
                <a href="{{ route('auth.change_password') }}">
                    <i class="fa fa-key"></i>
                    <span class="title">@lang('quickadmin.qa_change_password')</span>
                </a>
            </li>



            <li>
                <a href="#logout" onclick="$('#logout').submit();">
                    <i class="fa fa-arrow-left"></i>
                    <span class="title">@lang('quickadmin.qa_logout')</span>
                </a>
            </li>
        </ul>
    </section>
</aside>

