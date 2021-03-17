
/**
 * Get the URL parameters
 * source: https://css-tricks.com/snippets/javascript/get-url-variables/
 * @param  {String} url The URL
 * @return {Object}     The URL parameters
 */

 String.prototype.escape = function() {
	var tagsToReplace = {
		'&': '&amp;',
		'<': '&lt;',
		'>': '&gt;'
	};
	return this.replace(/[&<>]/g, function(tag) {
		return tagsToReplace[tag] || tag;
	});
};



	
var getParams = function (url) {
	var params = {};
	var parser = document.createElement('a');
	parser.href = url;
	var query = parser.search.substring(1);
	var vars = query.split('&');
	for (var i = 0; i < vars.length; i++) {
		var pair = vars[i].split('=');
		params[pair[0]] = decodeURIComponent(pair[1]);
	}
	return params;
};

var isFileManagerPageCheck = function(){
	return typeof isFileManager !== "undefined" && isInFileManagerPage;
}

var isCreatingFolder = function(){
	return typeof inFolderPage !== "undefined";
}

function clickToPath(currentBasePath){
	manualTrigger = true;
	pathFolders = currentBasePath.split("/");
	$(".goto-tab[href=-2]").trigger("click");
	for(i in pathFolders){
		$(".dropdown-segment").eq(i).find("a.goto-tab").each(function(){
			console.log($(this).attr("folder_name"),$(this).attr("href"));
		});
		$(".dropdown-segment").eq(i).find("a.goto-tab").filter(function(){ return $(this).attr("folder_name") === pathFolders[i] }).trigger("click");
	}
	manualTrigger = false;
}

function addNewFolder(fullPath,name,rootFolderAccessForAdmins = false){

	fullPath = rootFolderAccessForAdmins ? "" : fullPath;

	$.post(siteUrl+"admin/add_subfolder",{_token:window._token,name: name,path: fullPath},function(results){

		console.log(results);
		reset();

	});

}

function displayWhenUploadFinishes2(e, data) {
	console.log(e,data);

	for(i in data.files){
		dataFromDrops[dataFromDrops.length] = data.files[i];
	}

	getListOfFiles(fullPath);
}

function displayWhenUploadFinishes(e, data) {
	$this = $(e.target);
	$parent = $(e.target).parent();

	counter++;
	if(counter === data.result.files.length){
		alert("All files have finished uploading!");
	}
	$.each(data.result.files, function (index, file) {
		var $line = $($('<p/>', {class: "form-group"}).html(file.name + ' (' + file.size + ' bytes)').appendTo($parent.find('.files-list')));
		if ($parent.find('.' + $this.data('bucket') + '-ids').val() != '') {
			$parent.find('.' + $this.data('bucket') + '-ids').val($parent.find('.' + $this.data('bucket') + '-ids').val() + ',');
		}
		$parent.find('.' + $this.data('bucket') + '-ids').val($parent.find('.' + $this.data('bucket') + '-ids').val() + file.id);
	});
	$parent.find('.progress-bar').hide().css(
		'width',
		'0%'
	);
	getListOfFiles(fullPath);
}

function getListOfFiles(fullPath,triggerListOfFiles = false){
	$.post(siteUrl+"admin/basic_list",{_token:window._token,path: fullPath},function(results){
		$("#listof_files").html('');

		if(isFileManagerPageCheck()){
			$("#misc_options").removeClass('hide');
		}

		if(isCreatingFolder()){
			$("[name=full_path]").val(fullPath);
		}


		for(index in results.data.directories){
			name = results.data.directories[index].replace(results.fullPath,"");
			$("#listof_files").append("<li><i class='fa fa-folder-open' aria-hidden='true'></i>&nbsp;"+name+"</li>");
		}
		for(index in results.data.files){
			name = results.data.files[index].replace(results.fullPath,"");

			downloadLink = isFileManagerPageCheck() ? " <a target='_blank' class='btn btn-primary btn-xs download-link' href='"+siteUrl+"admin/get_as_downloadable?link="+encodeURI(results.link)+"/"+encodeURI(name)+"'>Download</a>" : "";

			$("#listof_files").append("<li><i class=\"fa fa-file-excel-o\" aria-hidden=\"true\"></i>&nbsp;<a target='_blank' href='"+siteUrl+encodeURI(results.link)+"/"+encodeURI(name)+"'>"+name+"</a> "+downloadLink+"</li>");
		}

		

		$("#compilation_list").empty();
		$("#link_generator_notice").empty();

	});
}

function reset(){
	DIRECTORY_LIST = [];
	DIRECTORY_LIST_INLINE = [];
	$("#directory_dropdown").html('');
	$(".goto-tab[href=-2]").trigger("click");
	directoryScout();
}

function directoryScout(directory = ""){
	directory = rootFolder;
	$.post(siteUrl+"admin/get_all_folders",{_token:window._token,directory: rootFolder},function(results){
		DIRECTORY_LIST = results.directories;
		DIRECTORY_LIST_INLINE = [];

		urlCheck = getParams(location.href);

		isCreatingFiles = /(admin\/file_manager|files\/create)/.test(location.href);

		viewingFilesNoBasePath = /admin\/files(?!currentBasePath)/.test(location.href);

		isViewingFolder = /admin\/folders(\/[0-9]{1,})?/.test(location.href);

		setCurrentDirectoryDisplay('initial');

		if(isViewingFolder || viewingFilesNoBasePath){
			clickToPath(userEmail);
		}

		if(typeof urlCheck.currentBasePath === "string"){
			clickToPath(urlCheck.currentBasePath);
		}

		if(typeof folderData !== "undefined"){
			clickToPath(folderData.email+"/"+folderData.name);
		}

		if(typeof loadedFolderId === "number"){
			folderPath = $("#folder_id option[value="+loadedFolderId+"]").text();
			fullPath = folderPath;
			$("#subfolder_view").removeClass("hide");
			clickToPath(folderPath);
		}

	},"json").fail(function(error){
		console.log(error);
	});
}

dropdownSegment = $("#directory_dropdown").html();
$("#directory_dropdown").html('');

manualTrigger = false;


function setCurrentDirectoryDisplay(display = "initial",currentArray = [],parentId = null, level = 1){

	currentArray = display === "initial" ? DIRECTORY_LIST : currentArray;

	parentId = display === "initial" ? -1 : parentId ;

	for(index in currentArray){

		dt = currentArray[index];

		dt.parentId = parentId;

		DIRECTORY_LIST_INLINE[DIRECTORY_LIST_INLINE.length] = dt;

		segment = $(dropdownSegment);	

		folderName = (parentId === -1) ? "Root Directory" : dt.folder_name;

		segment.find(".folder_name").text(folderName);
		segment.attr("folder_name",folderName);

		if(display !== "initial"){
			segment.addClass("hide");
		}else{
			segment.find(".btn").removeClass('btn-primary');
			segment.find(".btn").addClass('btn-danger');
		}
		segment.attr("parent_id",parentId);
		segment.attr("folder_id",dt.folder_id);
		segment.attr("full_path",dt.full_path);
		segment.attr("level",level);

		dropdownLinks = $(segment.find(".dropdown-menu").html());
		segment.find(".dropdown-menu").html('');

		console.log(level,dt);

		if(dt.subfolders.length === 0 && level < levelOfSubFolderCreation){
			segment.find(".dropdown-menu,.caret").detach();
			segment.find("button").removeClass("btn-primary").addClass('btn-info');
		}
		
		for(index2 in dt.subfolders){
			crnt = dt.subfolders[index2];
			dropdownLinks.attr('goto-tab',crnt.folder_id);
			dropdownLinks.find('a').addClass("goto-tab").attr("href",crnt.folder_id).text(crnt.folder_name).attr("folder_name",crnt.folder_name);

			segment.find(".dropdown-menu").append("<li>"+dropdownLinks.html()+"</li>");
		}

		if(level >= levelOfSubFolderCreation && !isCreatingFolder()){

			dropdownLinks.find('a').addClass("goto-tab").attr("href","-3").removeAttr("folder_name").html('<strong>Add New Folder</strong>');
			segment.find(".dropdown-menu").append("<li>"+dropdownLinks.html()+"</li>");

		}else{
			if(level ===2){
				segment.find(".dropdown-menu,.caret").detach();
				segment.find(".dropdown-toggle").removeClass("dropdown-toggle");
			}
		}
		if(display === "initial"){
			dropdownLinks.attr('goto-tab','-1');

			dropdownLinks.find('a').addClass("goto-tab").attr("href","-2").removeAttr("folder_name").html('<strong>Root Folder</strong>');

			segment.find(".dropdown-menu").append("<li>"+dropdownLinks.html()+"</li>");
		}

		segment.appendTo("#directory_dropdown");


		if(dt.subfolders.length > 0){
			crnt = dt.subfolders;
			setCurrentDirectoryDisplay("subfolder",crnt,dt.folder_id,level+1);
			// for(index2 in dt.subfolders){
			// 	parentId = currentArray.folder_id;
			// 	currentArray = dt.subfolders[index2];
			// 	console.log(currentArray);
				
			// }
			
		}

	}
}

$(document).ready(function(){
	directoryScout();
})

function loadZipCompilations(){
	$("#compilation_list").empty();
	$.post(siteUrl+"admin/download_folder",{_token:window._token,path: fullPath},function(results){
		downloadNotice = results.message;
		downloadNotice += " <button class='btn btn-danger btn-xs' id='generate_download_link'>Create New Download Link</a>";
		$("#link_generator_notice").html(downloadNotice);
		if(results.status){
			for(i in results.download_links){
				dt = results.download_links[i];
				//highlight the most recent link
				highlightClauses = (i+"" === "0" && results.download_links.length > 0) ? 
				['<b>','</b>',' (Newest)']
				:
				['','','']
				  ;

				$("#compilation_list").append("<li>\
				"+highlightClauses[0]+"<a href='"+siteUrl+"storage/"+dt.full_path_and_file+"'>"+dt.full_path_and_file.replace('zips/FOLDER_COMPILED_','').replace('.zip','')+""+highlightClauses[2]+"</a>"+highlightClauses[1]+"\
				</li>");
			}
		}

	},"json");
}



$("body").on("click",".goto-tab",function(event){
	event.preventDefault();
	if((!isCreatingFiles && !manualTrigger) && !isCreatingFolder()){
		$("#goto_folder").removeClass('hide');
	}
	folderId = $(this).attr("href");
	console.log(parseInt(folderId));
	if(parseInt(folderId) > -3){

		$(this).parents(".dropdown-segment").removeClass('rightclip').nextAll().addClass('hide').removeClass('rightclip');
		for(i in DIRECTORY_LIST_INLINE){
			dt = DIRECTORY_LIST_INLINE[i];
			if(dt.folder_id === parseInt(folderId)){
				fullPath = dt.full_path;
				fullPath = fullPath.replace(/^public\//,"");

				getListOfFiles(fullPath);

				
				$("#directory_dropdown .dropdown-segment[folder_id="+folderId+"]").insertAfter("#directory_dropdown .dropdown-segment[folder_id="+dt.parentId+"]").addClass("rightclip").removeClass('hide');

			}
		}
		if(folderId === "-2"){
			fullPath = "";
		}
	}
	if(folderId === "-3"){

		newFolderName = prompt("Type the name of your new folder");
		if(typeof newFolderName === "string"){
			addNewFolder(fullPath,newFolderName);
		}
	}else if(folderId === "-4"){
		newFolderName = prompt("Type the name of your new folder");
		if(typeof newFolderName === "string"){
			addNewFolder('',newFolderName, true);
		}
	}

	$("#full_path_display").text(fullPath);		
	
});

$("body").on("click","#goto_folder",function(){

	fullPath = fullPath !== "" ? "?currentBasePath="+encodeURI(fullPath) : "";
	location.assign(siteUrl+"admin/files"+fullPath);
});

fullPath = "";

$(document).ready(function(){
	if(isFileManagerPageCheck()){

	$("body").on("click","#generate_download_link",function(event){
		event.preventDefault();
		$.post(siteUrl+"admin/generate_download_link",{_token:window._token},function(results){
			if(results.status){
				//getListOfFiles(fullPath,true);
				if(isFileManagerPageCheck()){
					loadZipCompilations();
				}
			}else{
				alert("Failed to generate download link.");
			}
		});
	});

	$("body").on("click","#download_folder",function(event){
		event.preventDefault();
		$("#link_generator_notice").removeClass("hide");
		loadZipCompilations();
	});
}
})

//UPDATE files SET path = CONCAT((SELECT users.email FROM users WHERE users.id = created_by_id),'/',(SELECT folders.name FROM folders WHERE folders.id = folder_id))