
    @if(isset($purpose))
    @switch($purpose)
        @case('create_files')
        <div>
        <p>Simply select the folder that you want to choose, and that is where it will be uploaded to. You can also create new subfolders here.</p>
        @break;
    @endswitch   
    @else
    <h4 class="page-title">
        @if(isset($textForDirectoryBrowser))
            {{ $textForDirectoryBrowser }}
        @else
            Directory Browser
        @endif
    </h4>
    @endif

    <div class="btn-group" role="group" id="directory_dropdown"> 
        <div class='form-inline dropdown-segment dropdown'>
        <button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
          <span class='folder_name'>Dropdown</span>
          <span class="caret"></span>
        </button>
        <ul class="dropdown-menu">
          <li><a href="#">Dropdown link</a></li>
        </ul>
        </div>
    </div>



    <button class="btn btn-warning hide" id="goto_folder">Visit Folder</button>
    @if(isset($purpose))
        @switch($purpose)
            @case('create_files')
            <div class="row">
            <div class="col-md-6">
            <div class="panel">
            
                <div class="panel-heading"><strong>Full Path: </strong> <span id="full_path_display"></span>. </div>
                <strong>This is where your selected files will be uploaded to!</strong>
                
            </div></div>
            <div class="col-md-6" id="file_list">
                <div class="panel-heading"><strong>List Of Files</strong></div>
                <div id="file_box">
                    <ul id="listof_files">
                    </ul>
                </div>
                <div id="misc_options" class="hide">
                    <div class="panel-heading"><strong>Miscellaneous Options</strong></div>
                    <div class="panel-body">
                        <ul>
                            <li><button class="btn btn-success" id="download_folder">View List of Zip Compilations</button>
                            <div id="link_generator_notice" class="hide">Please wait. Generating links...</div>
                            <ul id="compilation_list">
                                
                            </ul>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            </div>

            </div>
            
            @break;
        @endswitch   
@else
    <br><br>
    @endif



