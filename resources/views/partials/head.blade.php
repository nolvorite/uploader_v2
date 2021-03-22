<meta charset="utf-8">
<title>
    @lang('quickadmin.quickadmin_title')
</title>

<meta http-equiv="X-UA-Compatible"
      content="IE=edge">
<meta content="width=device-width, initial-scale=1.0"
      name="viewport"/>
<meta http-equiv="Content-type"
      content="text/html; charset=utf-8">

<!-- Tell the browser to be responsive to screen width -->
<meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
<!-- Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<!-- Ionicons -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/ionicons/2.0.1/css/ionicons.min.css">
<!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
<!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
<!--[if lt IE 9]>
<script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
<script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
<![endif]-->

<link href="{{ url('adminlte/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
<link rel="stylesheet"
      href="{{ url('quickadmin/css') }}/select2.min.css"/>
<link href="{{ url('adminlte/css/AdminLTE.min.css') }}" rel="stylesheet">
<link href="{{ url('adminlte/css/custom.css') }}" rel="stylesheet">
<link href="{{ url('adminlte/css/skins/skin-blue.min.css') }}" rel="stylesheet">

<link rel="stylesheet"
      href="https://code.jquery.com/ui/1.11.3/themes/smoothness/jquery-ui.css">
<link rel="stylesheet"
      href="//cdn.datatables.net/1.10.9/css/jquery.dataTables.min.css"/>
<link rel="stylesheet"
      href="https://cdn.datatables.net/select/1.2.0/css/select.dataTables.min.css"/>
<link rel="stylesheet"
      href="//cdn.datatables.net/buttons/1.2.4/css/buttons.dataTables.min.css"/>
<link rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/jquery-ui-timepicker-addon/1.4.5/jquery-ui-timepicker-addon.min.css"/>
<link rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.5.0/css/bootstrap-datepicker.standalone.min.css"/>

<link href="{{ url('js/dropzone/basic.css') }}" rel="stylesheet">
<link href="{{ url('js/dropzone/dropzone.css') }}" rel="stylesheet">

<style type="text/css">
      .form-inline.dropdown-segment {
          display: inline-block;
      }

      .dropdown-segment .dropdown-menu{max-height: 250px; overflow: auto; padding: 3px;}

      .btn-group .dropdown-segment .btn{border-left-width:0;border-radius: 0;}
      .btn-group .dropdown-segment .btn:hover,.btn-group .dropdown-segment .btn[aria-expanded=true]{border-left-width:1px;margin-left:-1px}
      .btn-group .dropdown-segment:first-child .btn{border-radius: 5px 0px 0px 5px;border-left-width:1px}
      .btn-group .dropdown-segment:first-child .btn:hover,.btn-group .dropdown-segment:first-child .btn[aria-expanded=true]{margin-left:0}
      .btn-group .dropdown-segment.rightclip .btn{border-radius: 0px 5px 5px 0px;border-right-width:1px}

      li{word-wrap: break-word;}

      #drag_drop_box {
          background: #c0c0c0;
          min-height: auto;
          border-radius: 5px;
          box-shadow: inset 0 10px 10px rgba(0,0,0,.2);
          padding: 20px 0;
          text-align: center;
      }

      #drag_drop_box h2{margin:0;font-size:200%;font-weight:bold;}

      .dz-default.dz-message {
    margin: 0;
    font-size: 200%;
    font-weight: bold;
}

ul#listof_files {
    max-height: 200px;
    overflow: auto;
}

#full_path_display{text-decoration: underline}

.remarks{width:300px;}

.panel-body.table-responsive {
    max-height: none;
    overflow-x: unset;
}

ul.dropdown-menu {}

ul.dropdown-menu.dropdown-menu-right {
    max-height: 200px;
    max-width:160px;
    overflow-y: auto;
}

.remark-editor{min-height:140px;}

.file_info{    max-width: 200px;
    display: block;
    word-wrap: break-word;
}

</style>

@if(Auth::check())

<script type="text/javascript">
    const isAnAdmin = {{ (auth()->user()->role_id === 1) ? 'true' : 'false' }};
    const isFileManager = {{ (auth()->user()->role_id === 1 || auth()->user()->role_id === 5) ? 'true' : 'false' }};
    const rootFolder = "";
    const userEmail = "{{auth()->user()->email}}";
    const levelOfSubFolderCreation = 3;
    const isInFileManagerPage = {{ (Request::segment(2) === "file_manager") ? 'true' : 'false' }};
</script>

@endif