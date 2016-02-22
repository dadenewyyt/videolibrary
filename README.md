Video Uploader and Thmbnails Gnerator of diffretn size for windows and linux - PHP library
=========================

This a video library created by daniel adenew for use inside your any of PHP frameworks as platform indpendent and supports video conversion , video upload and automatic video thumbnail generation and support database integration with your video model and may more...



How to  Use 
*************************

The following Environment settings are required inorder to work with upload and automatic video conversion and thumbnails configuration .

Since , this project uses the open source multimedia manupluation libaray called ffmpeg which oeprates its code written in C and available as executable for windows and linux. 

ffmpeg can be available here : <a href ="https://www.ffmpeg.org">ffmpeg</a>

Prior before running a test this library or executable needs to be installed and must be avaialble in your environemnt path.
Or there is a function on the  this php library called <b>UploadService.php</b> which accepts $ffmpeg_path as paramater youcan also use that .

But ,libary asssumes you have already configured your environemnt path variable to hold ffmpeg command.


Next step
****************************************
Configuare your PHP.INI settings 
****************************************
This php libarray makes advanatage of <b>PHP.INI setttings</b> of the following to be turned on :

1. post_max_size=30M ;make sure this size is that what you want
2. file_uploads=On ;
3. upload_max_filesize=30M ;;make sure this size is that what you want and must match with above setting
4. extension=php_fileinfo.dll
   extension=php_gd2.dll 
must be turned on to detect file http header on post.

The libaray also gives you an option to set your <b>MAX_FILE_SIZE_LIMIT</b> 
*******************************************************************************************************









All Rights Reserved (C). December 1 , 2014 .
