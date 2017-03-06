function sendFileToServer(formData,status)
{
	var uploadURL = global_image_upload_url;
	var extraData ={}; //Extra Data.
	var jqXHR=jQuery.ajax({
	        xhr: function() {
            var xhrobj = jQuery.ajaxSettings.xhr();
            if (xhrobj.upload) {
                    xhrobj.upload.addEventListener('progress', function(event) {
                        var percent = 0;
                        var position = event.loaded || event.position;
                        var total = event.total;
                        if (event.lengthComputable) {
                            percent = Math.ceil(position / total * 100);
                        }
                        //Set progress
                        status.setProgress(percent);
                    }, false);
                }
            return xhrobj;
        },
	    url: uploadURL,
	    type: "POST",
		contentType:false,
		processData: false,
        cache: false,
        data: formData,
        success: function(data){
        	status.setProgress(100);
        	jQuery("#status1").append("Data from Server:"+data+"<br>");        	
		}
    });	

	status.setAbort(jqXHR);
}//end function

var rowCount=0;
function createStatusbar(obj)
{
	 rowCount++;
	 var row="odd";
	 if(rowCount %2 ==0) row ="even";
	 this.statusbar = jQuery("<div class='statusbar "+row+"'></div>");
     this.filename = jQuery("<div class='filename'></div>").appendTo(this.statusbar);
     this.size = jQuery("<div class='filesize'></div>").appendTo(this.statusbar);
     this.progressBar = jQuery("<div class='progressBar'><div></div></div>").appendTo(this.statusbar);
     this.abort = jQuery("<div class='abort'>Abort</div>").appendTo(this.statusbar);
     obj.after(this.statusbar);
    
    this.setFileNameSize = function(name,size)
    {
    	var sizeStr="";
    	var sizeKB = size/1024;
    	if(parseInt(sizeKB) > 1024)
    	{
    		var sizeMB = sizeKB/1024;
    		sizeStr = sizeMB.toFixed(2)+" MB";
    	}
    	else
    	{
    		sizeStr = sizeKB.toFixed(2)+" KB";
    	}
    
    	this.filename.html(name);
    	this.size.html(sizeStr);
    }
    this.setProgress = function(progress)
    {		
	 	var progressBarWidth =progress*this.progressBar.width()/ 100;  
		this.progressBar.find('div').animate({ width: progressBarWidth }, 10).html(progress + "%&nbsp;");
		if(parseInt(progress) >= 100)
		{
			this.abort.hide();
		}
	}
	this.setAbort = function(jqxhr)
	{
		var sb = this.statusbar;
		this.abort.click(function()
		{
			jqxhr.abort();
			sb.hide();
		});
	}
}//end function 

function handleFileUpload(files,obj)
{
   for (var i = 0; i < files.length; i++) 
   {
   		var fd = new FormData();
	   	fd.append('file', files[i]);
   	
   		var status = new createStatusbar(obj); //Using this we can set progress.
   		status.setFileNameSize(files[i].name,files[i].size);
   		sendFileToServer(fd,status);
   
   }//end for
}//end function

function monitorFileDropUploadProcess(element)
{
	var obj = element;
	obj.on('dragenter', function (e) 
	{
		e.stopPropagation();
		e.preventDefault();
		jQuery(this).css('border', '2px solid #0B85A1');
	});
	
	obj.on('dragover', function (e) 
	{
		 e.stopPropagation();
		 e.preventDefault();
	});
	
	obj.on('drop', function (e) 
	{
		 jQuery(this).css('border', '2px dotted #0B85A1');
		 e.preventDefault();
		 var files = e.originalEvent.dataTransfer.files;
	
		 //We need to send dropped files to Server
		 handleFileUpload(files,obj);
	});
	
	jQuery(document).on('dragenter', function (e) 
	{
		e.stopPropagation();
		e.preventDefault();
	});
	
	jQuery(document).on('dragover', function (e) 
	{
	  e.stopPropagation();
	  e.preventDefault();
	  obj.css('border', '2px dotted #0B85A1');
	});
	
	jQuery(document).on('drop', function (e) 
	{
		e.stopPropagation();
		e.preventDefault();
	});
}//end function