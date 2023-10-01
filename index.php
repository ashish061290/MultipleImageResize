<!DOCTYPE html>
<html lang="en">
 <head>
 <title>Multiple Files Resize</title>
 <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
  <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.7.2/dropzone.min.css" />

</head>
 <body>
 <style>
  .heading{
	  text-align:center;
    background-color:#298C8C;
    color:#fff;
    padding:5px;
    border-radius:10px;
  }
  .pad{
	  padding:18px;
  }
  .pad td{ padding:12px !important; }
  .pad td legend{ font-size:16px !important; font-weight:400 !important; }
 </style>
    <div class="container">
	 <div class="row pad">
	 <div class="heading">
	 <h2>Multiple Files Resize</h2></div>
	 <form method="post" class="form form-horizontal" enctype="multipart/form-data">
   <div class="dropzone"></div>
	   <table class="table table-responsive pad">
	     <tr>
		   <td><div class="form-group">
             <label for="file">Select Files:</label>
             <input type="file" class="form-control" id="file" name="muliple_files[]" multiple>
          </div></td>
		  <td>
		   <table>
		   <tr>
		   <td><fieldset>
      <legend>Select Width Height Large Files:</legend>
	  <div class="form-group">
      <label for="text">Large Size Width(PX):</label>
      <input type="text" class="form-control" id="large_w" name="large_w" required />
      </div>
	  <div class="form-group">
      <label for="text">Large Size Height(PX):</label>
      <input type="text" class="form-control" id="large_h" name="large_h" required />
      </div>
	  </fieldset>
	   </td>
		   </tr></table>
		   </td>
		   <td>
		   <table>
		   <tr>
		   <td> 
	  <fieldset>
      <legend>Select Width Height Small Files:</legend>
	  <div class="form-group">
      <label for="text">Small Size Width(PX):</label>
      <input type="text" class="form-control" id="small_w" name="small_w" required />
      </div>
	  <div class="form-group">
      <label for="text">Small Size Height(PX):</label>
      <input type="text" class="form-control" id="small_h" name="small_h" required />
      </div>
	  </fieldset>
	   </td>
		   </tr></table>
		   </td>
		 </tr>
	   </table>
	 <input type="submit" name="resize" value="Resize" class="btn btn-success"> 
	   </form>
	  </div>
	  </div>
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
  <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.7.2/dropzone.min.js"></script>  
  <script>
//Disabling autoDiscover
Dropzone.autoDiscover = false;

$(function() {
    //Dropzone class
    var myDropzone = new Dropzone(".dropzone", {
        url: "index.php",
        paramName: "muliple_files",
        maxFilesize: 2,
        maxFiles: 10,
        acceptedFiles: "image/*,application/pdf",
        autoProcessQueue: true
    });
    
    $('#startUpload').click(function(){           
        myDropzone.processQueue();
    });
});
</script>
 </body>
</html>
<?php
 $large_path = "Upload/Large/";
 $small_path = "Upload/Small/";
 $path="Upload/";
 $msg = "";
//resize function 
      if(isset($_POST['resize'])){
		  $large_w = $_POST['large_w'];
          $large_h = $_POST['large_h'];
          $small_w = $_POST['small_w'];
          $small_h = $_POST['small_h'];
		   //print_r($_FILES); die();
		  if(!empty($_FILES['muliple_files']['name']));
		  $types = array("jpg","jpeg","png");
		    
		  foreach($_FILES['muliple_files']['name'] as $key=>$val1){
			  $img_name = $_FILES['muliple_files']['name'][$key];
			  $tmp_name = $_FILES['muliple_files']['tmp_name'][$key];
				$ext = pathinfo($img_name, PATHINFO_EXTENSION);
				if(in_array($ext,$types)){
				 $msg = 1;
			     $result[$key] = ImageSizeCrop($tmp_name,$img_name,$large_w,$large_h,$small_w,$small_h,$path,$large_path,$small_path,$key);
			   }
			    if($msg==""){
                echo "<script>alert('Only png,jpg,jpeg files allowed...')</script>"; return false; 
			  }
		   }
		     if(!empty($result)){
				// Get real path for our folder
				$rootPath = realpath('Upload');
				// Initialize archive object
				$zip = new ZipArchive();
				$zipname = "img/".time().".zip";
				$zip->open($zipname, ZipArchive::CREATE | ZipArchive::OVERWRITE);
				// Create recursive directory iterator
				/** @var SplFileInfo[] $files */
				$files = new RecursiveIteratorIterator(
					new RecursiveDirectoryIterator($rootPath),
					RecursiveIteratorIterator::LEAVES_ONLY
				); 
					foreach($files as $name => $file){
					// Skip directories (they would be added automatically)
					if (!$file->isDir()){
						// Get real and relative path for current file
						$filePath = $file->getRealPath();
						$relativePath = substr($filePath, strlen($rootPath) + 1);
						// Add current file to archive
						$zip->addFile($filePath, $relativePath);
					}
				 }
              // Zip archive will be created only after closing object
                   $zip->close();
                    if(file_exists($zipname)){
						             header('Content-Type: application/zip');
                         header('Content-disposition: attachment; filename='.$zipname);
                         header('Content-Length: ' . filesize($zipname));
                         readfile($zipname);
                 foreach($result as $res){
                  foreach($res as $path){
                     unlink($path['file_path']);
                  }
                 }      
				   }
			  } 
		}
     function createZip($zip, $folder){
       if(is_dir($folder)) {
        $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($folder),
        RecursiveIteratorIterator::LEAVES_ONLY
    );
     foreach($files as $name => $file) {
        // Skip directories (they will be added automatically)
        if (!$file->isDir()){
            // Get real and relative path for current file
            $filePath = $file->getRealPath();
            $relativePath = substr($filePath, strlen($folder) + 1);

            // Add current file to archive
            $zip->addFile($filePath, $relativePath);
        } else {
            exit("Unable to open directory " . $folder);
        }
    } 
     } else {
        exit($folder . " is not a directory.");
    }
  }
	  function ImageSizeCrop($tmp_name,$img_name,$large_w,$large_h,$small_w,$small_h,$path,$large_path,$small_path,$i){ 
      //$path2 = substr($path,3);
      $filename_old=$img_name;
      $oldpath=$tmp_name;
      $ext = pathinfo($img_name, PATHINFO_EXTENSION);
      $newpath=$path.$filename_old;
         $Integer = strtotime(Date('Y-m-d h:i:s')).$i;
         $img_name="Imgo".$Integer.".".$ext;
         $newpath=$path.$img_name;
         $filename_old = $img_name;
		// echo "#".$oldpath."#".$newpath;
      if(move_uploaded_file($oldpath,$newpath)){}else{ die('error'); }
      //$link=$_SERVER["SERVER_ADDR"]."/Xenium/admin/media/elearn/".$filename_old;
      $link123=$path.$filename_old;
      ini_set('display_errors',1);
      error_reporting(E_ALL);
      $filename = $filename_old;
      $thumb_filename=  $large_path.$filename; //largesize
      $original_info = getimagesize($link123);
      $original_w = $original_info[0];
      $original_h = $original_info[1];
      $original_img = imagecreatefromjpeg($link123);
      $thumb_w = $large_w;
      $thumb_h = $large_h;
      $thumb_img = imagecreatetruecolor($thumb_w, $thumb_h);
      imagecopyresampled($thumb_img, $original_img,
                         0, 0,
                         0, 0,
                         $thumb_w, $thumb_h,
                        $original_w, $original_h);
      imagejpeg($thumb_img, $thumb_filename);
      imagedestroy($thumb_img);
      //unlink($link123);
        $res['large'] = array("file_path"=>$thumb_filename);
        $thumb_w = $small_w;
        $thumb_h = $small_h;
        $ext = pathinfo($filename, PATHINFO_EXTENSION);
        $Integer = strtotime(Date('Y-m-d h:i:s')).$i;
        $img_name="Imgs".$Integer.".".$ext;
        $newpath=$small_path.$img_name;
        $thumb_filename= $newpath; //smallsize
      $thumb_img = imagecreatetruecolor($thumb_w, $thumb_h);						
	    imagecopyresampled($thumb_img, $original_img,
                         0, 0,
                         0, 0,
                         $thumb_w, $thumb_h,
                         $original_w, $original_h);							
                 imagejpeg($thumb_img, $thumb_filename);
                $res['small'] = array("file_path"=>$newpath);				 
                 imagedestroy($thumb_img);
				 unlink($link123);
          return $res;      
   }
    ?>
