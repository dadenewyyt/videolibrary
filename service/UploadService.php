<?php
/**
 * Email:dadenew.yyt@gmail.com
 * Author: Daniel Adenew
 * Date: 11/26/14
 * Time: 9:41 AM
 * www.cytekservices.com . Craig Robinson
 */

/**
 * CodeIgniter 2.1.4
 * Some methods like xss clean and video
 * name regx methods are taken from codeigniter upload class.
 * Thanks to the developers!
 */
require_once('model/VideoModel.php');

class UploadService
{

    protected $maxFileSizeAllowed = 0;
    protected $max_width = 0;
    protected $max_height = 0;
    protected $max_filename = 0;
    protected $allowed_types = "";
    protected $postFileTempName = "";
    protected $postFileName = "";
    protected $orig_name = "";
    protected $postFileType = "";
    protected $postFileSize = "";
    protected $fileExtension = "";
    protected $upload_path = "";
    protected $overwrite = FALSE;
    protected $encrypt_name = FALSE;
    protected $is_image = FALSE;
    protected $image_width = '';
    protected $image_height = '';
    protected $image_type = '';
    protected $image_size_str = '';
    protected $error_msg = array();
    protected $mimes = array();
    protected $remove_spaces = TRUE;
    protected $xss_clean = FALSE;
    protected $temp_prefix = "temp_file_";
    protected $client_name = '';
    protected $_file_name_override = '';
    protected $allowed_file_type_headers = array();
    protected $user_file_upload_html_field = '';
    protected $max_file_size = '';

    public function __construct()
    {
        //initilaize
        //initialize allowed array of file types extensions  if not already set
        $this->allowed_file_type_headers = array("video/mp4", "audio/wma", "image/pjpeg", "image/gif", "image/jpeg", "mp4", "wma");

    }

    protected function getHttpPostFileVariable($html_field_file)
    {

        $config_field = USER_FILE_UPLOAD_FIELD_NAME;

        $config_max_file_size = MAX_FILE_SIZE_LIMIT;

        /**
         * Remember: ALL VARIABLES USED HERE ARE
         *
         * PROPERTY OF UPLOAD SERVICE CLASS
         *
         */

        $this->user_file_upload_html_field = isset($html_field_file) ? $html_field_file : $config_field;

        $this->maxFileSizeAllowed = $config_max_file_size;

        $this->postFileName = isset($_FILES[$this->user_file_upload_html_field]['name']) ? $_FILES[$this->user_file_upload_html_field]['name'] : die('Error , no file for upload is detected');

        //clean file name
        $this->postFileName = $this->clean_file_name($this->postFileName);

        $this->postFileSize = isset($_FILES[$this->user_file_upload_html_field]['size']) ? $_FILES[$this->user_file_upload_html_field]['size'] : die('Error , file size is unknown');

        $this->postFileType = isset($_FILES[$this->user_file_upload_html_field]['type']) ? $_FILES[$this->user_file_upload_html_field]['type'] : die('Error , file is type can\'t be detected');

        $this->postFileTempName = isset($_FILES[$this->user_file_upload_html_field]['tmp_name']) ? $_FILES[$this->user_file_upload_html_field]['tmp_name'] : null;

        $security_check_result = $this->do_xss_clean();

        if ($security_check_result) {

            die('Error : there is a security flaw detected on this file.Can\t upload!');

            return;
        }

        /**
         * set property from user or
         * use the configuration
         * constant values if not and convert to lower for html5
         **/
        $fileExtension = strtolower(pathinfo($this->postFileName, PATHINFO_EXTENSION));

        if (in_array($this->postFileType, $this->allowed_file_type_headers)
            && ($this->postFileSize < $this->maxFileSizeAllowed)
            && in_array($fileExtension, $this->allowed_file_type_headers)
           )  {

            if ($_FILES[$this->user_file_upload_html_field]["error"] > 0) {

                echo("Return Code: " . $_FILES[$this->user_file_upload_html_field]["error"] . "<br />");

            } else {

                $extract_temp = explode(".", $_FILES[$this->user_file_upload_html_field]["name"]);
                $get_prefix = DEFAULT_VIDEO_NAME_PREFIX;

                /**
                 * You can append user id here instead of a random number
                 * so that load video according to user id later
                 */

                $new_file_name = $get_prefix . base64_encode( rand(1, 99999) ) . '.' . end($extract_temp);

                $this->postFileName = $new_file_name;

                 /**
                 * Now deliver File
                 */

                return $new_file_name;
            }

        } else {

            echo("Invalid file" . 'check file size configuration on your server');
        }

    }

    public function upload_file_now($field_name)
    {

        /**
         * pre process all _FILE stuff ,
         *validation and so on
         * now ready to Upload
         **/

       $file_name = $this->getHttpPostFileVariable($field_name);

        $complete_file_path_server = DEFAULT_VIDEO_STORE_LOCATION . "/" . $file_name;

         /**
         *
         * Do file path correction
         *
         */

        if (strtoupper(substr(PHP_OS, 0, 3) == 'WIN')) {

            $complete_file_path_server = str_replace('/', DIRECTORY_SEPARATOR, $complete_file_path_server);

        } else {

            $complete_file_path_server = str_replace('\\', DIRECTORY_SEPARATOR, $complete_file_path_server);
        }


         /**
         * //don't turn this on production
         * echo( "Upload: " . $this->postFileName . "<br />");
         * echo( "Type: " .  $this->postFileType . "<br />");
         * echo( "Size: " . ( $this->postFileSize / 1024) . " Kb<br />");
         * echo( "Temp file: " .  $this->postFileTempName . "<br />");
         * echo( "Temp file: " . $this->postFileTempName . "<br />");
         *
         */

        if (file_exists($complete_file_path_server)) {

            echo($file_name . " already exists. ");

        } else {

            /**
             * Create upload directory if not exist
             */

            if (!is_dir(DEFAULT_VIDEO_STORE_LOCATION)) {

                @mkdir(DEFAULT_VIDEO_STORE_LOCATION, '0777', true);
            }

            if (is_dir(DEFAULT_VIDEO_STORE_LOCATION)) {

                move_uploaded_file($this->postFileTempName, $complete_file_path_server);

                echo("Stored in: " . DEFAULT_VIDEO_STORE_LOCATION . "/" . $file_name);

                /**
                 * generate thumbs
                 */
                $this->ffmepg_generate_thumbnails($complete_file_path_server);

            } else {

                echo("Unable to create a folder: " . DEFAULT_VIDEO_STORE_LOCATION . 'And Upload a video file');

            }
        }

    }

    protected function ffmepg_generate_thumbnails($src_video_path, $debug = false, $overwrite = true)
    {

         $log_file = 'logs/' . 'ffmpeg_log.log';

         /**
         * Change the ffmpeg_path according to your server machine setting
         * / manually or leave it blank if is installed
         * and available on command line / console.
         */

         /**
         * You can set your path here manually if u have not already put ffmpeg on the environment
         */

         $ffmpeg_path ='' ; // leave empity to use no manaul paths

        //TODO:checkout if THE ffmepg installed or not ?
        // $fileExtension = pathinfo($src_video_path , PATHINFO_EXTENSION);

        /**
         *
         * DEBUG LEVEL MESSAGE SET TO TRUE
         *
         */

        if ($overwrite === true) {
            $additional_command = '-y';
        } else {
            $additional_command = '-n';
        }

        /**
         * DEBUG LEVEL MESSAGE SET TO TRUE
         */
        if ($debug === true) {
            $additional_command .= '-loglevel debug';
        }

        /**
         * check if OS is windows adjust log setting parameters accordingly
         */

        if (strtoupper(substr(PHP_OS, 0, 3) != 'WIN'))
            $log_settings = " </dev/null >/dev/null 2> " . $log_file . " &";
        else
            $log_settings = " 2> " . $log_file . " &";

        $input_file = $src_video_path;

        $retval = null;

        foreach (VideoModel::get_thumbnail_sizes() as $size) {

            $video_name = explode(".", $this->postFileName);

            $thumb_output_file = DEFAULT_VIDEO_STORE_LOCATION . '/' . "thumb_" . $size . "_" . base64_encode($video_name[0]) . ".jpg";

            $cmd = sprintf('%sffmpeg -i %s %s -an -ss 00:00:01 -r 1 -vframes 1 -s %s %s %s', $ffmpeg_path, null, $input_file, $size, $thumb_output_file, $log_settings);

            if (!file_exists($thumb_output_file)) {

                if (strtoupper(substr(PHP_OS, 0, 3) == 'WIN'))
                    $cmd = str_replace('/', DIRECTORY_SEPARATOR, $cmd);
                else
                    $cmd = str_replace('\\', DIRECTORY_SEPARATOR, $cmd);

                exec($cmd, $output, $retval);

            }
        }


        if ($retval)
            return false;

        return $thumb_output_file;

    }

    public function generate_thumbnail($video_src_fileName, $overwrite = true)
    {


        if (isset($video_src_fileName) && !empty($video_src_fileName)) {

            $complete_file_path_server = DEFAULT_VIDEO_STORE_LOCATION . "/" . $video_src_fileName;

            $file_found_on_server = file_exists($complete_file_path_server) or die('Error video file was found!');

            if (!empty($file_found_on_server) && $file_found_on_server == true) {

                // Change the path according to your server.
                $ffmpeg_path = 'C:\\Users\\DEVELOPER4\\Downloads\\Compressed\\ffmpeg-latest-win32-static\\ffmpeg-20141124-git-5182a2a-win32-static\\bin\\';

                //TODO:checkout if THE ffmepg installed or not ?

                $fileExtension = pathinfo($video_src_fileName, PATHINFO_EXTENSION);

                $additional_command = '';
                //CHECK IF overwrite file is true or false
                if ($overwrite === true) {
                    $additional_command = '-y';
                } else {
                    $additional_command = '-n';
                }

                //DEBUG LEVEL MESSAGE SET TO TRUE
                if (debug === true) {
                    $additional_command .= '-loglevel debug';
                }
                $dest_name = 0;
                $cmd = sprintf('%sffmpeg -i %s %s -an -ss 00:00:05 -r 1 -vframes 1 -y %s', $ffmpeg_path, $additional_command, $video_src_fileName, $dest_name);

                if (strtoupper(substr(PHP_OS, 0, 3) == 'WIN'))
                    $cmd = str_replace('/', DIRECTORY_SEPARATOR, $cmd);
                else
                    $cmd = str_replace('\\', DIRECTORY_SEPARATOR, $cmd);

                exec($cmd, $output, $retval);

                if ($retval)
                    return false;

                return $dest_name;

            }
        }


    }

    public static function clean_name($name)
    {
        return preg_replace("/[^A-Za-z0-9]/", "", $name);
    }

    public static function convert_to_mp4($input_file, $format)
    {

        /*if(file_exists($input_file) && isset($format) ) {

            foreach (VideoModel::get_html5_video_formats() as $format) {

                if ($format == ".webm") { //  && "." . $file_extension != ".webm"
                    $response["before_webm_exec"]=time();
                    exec("FUEL_ENV=production ffmpeg -y -i " . $input_file . " -vcodec libvpx -acodec libvorbis " . $output_file . " </dev/null >/dev/null 2>> " . $log_file . ".webm &");
                    $response["after_webm_exec"]=time();
                } else if ($format == ".ogg") { //&& "." . $file_extension != ".ogg"
                    $response["before_ogg_exec"]=time();
                    exec("FUEL_ENV=production  ffmpeg -y -i " . $input_file . " -vcodec libtheora -acodec libvorbis  " . $output_file . " </dev/null >/dev/null 2>> " . $log_file . ".ogg &");
                    $response["after_ogg_exec"]=time();
                } else if ($format == ".mp4") {
                    $response["before_mp4_exec"]=time();
                    exec("FUEL_ENV=production  ffmpeg -y -i " . $input_file . " -vcodec libx264 -b:v 250k -bt 50k -s 640x360 -acodec aac  -ab 56k -ac 2  -strict -2 " . $output_file . " </dev/null >/dev/null 2>> " . $log_file . ".mp4 &");
                    $response["after_mp4_exec"]=time();
                }
            }

         }
*/

    }

    public function saveCurrentVideoUploaded($videomodel, $videoUploadPath, $thumbFileName, $thumbExtension)
    {

    }


    public function getWebRootPath()
    {

        $server_root_dir = dirname($_SERVER['SCRIPT_NAME']);
        return $server_root_dir . '/';
    }

    /**
     * Clean the file name for security
     *
     * @param    string
     * @return    string
     */
    public function clean_file_name($filename)
    {
        $bad = array(
            "<!--",
            "-->",
            "'",
            "<",
            ">",
            '"',
            '&',
            '$',
            '=',
            ';',
            '?',
            '/',
            "%20",
            "%22",
            "%3c", // <
            "%253c", // <
            "%3e", // >
            "%0e", // >
            "%28", // (
            "%29", // )
            "%2528", // (
            "%26", // &
            "%24", // $
            "%3f", // ?
            "%3b", // ;
            "%3d" // =
        );

        $filename = str_replace($bad, '', $filename);

        return stripslashes($filename);
    }

    /**
     * Limit the File Name Length
     *
     * @param    string
     * @return    string
     */
    public function limit_filename_length($filename, $length)
    {
        if (strlen($filename) < $length) {
            return $filename;
        }

        $ext = '';
        if (strpos($filename, '.') !== FALSE) {
            $parts = explode('.', $filename);
            $ext = '.' . array_pop($parts);
            $filename = implode('.', $parts);
        }

        return substr($filename, 0, ($length - strlen($ext))) . $ext;
    }

    /**
     * XSS Clean
     *
     * Sanitizes data so that Cross Site Scripting Hacks can be
     * prevented.  This function does a fair amount of work but
     * it is extremely thorough, designed to prevent even the
     * most obscure XSS attempts.  Nothing is ever 100% foolproof,
     * of course, but I haven't been able to get anything passed
     * the filter.
     *
     * Note: This function should only be used to deal with data
     * upon submission.  It's not something that should
     * be used for general runtime processing.
     *
     * This function was based in part on some code and ideas I
     * got from Bitflux: http://channel.bitflux.ch/wiki/XSS_Prevention
     *
     * To help develop this script I used this great list of
     * vulnerabilities along with a few other hacks I've
     * harvested from examining vulnerabilities in other programs:
     * http://ha.ckers.org/xss.html
     *
     * @param    mixed    string or array
     * @param    bool
     * @return    string
     */
    public function xss_clean($str, $is_image = FALSE)
    {
        /*
         * Is the string an array?
         *
         */
        if (is_array($str)) {
            while (list($key) = each($str)) {
                $str[$key] = $this->xss_clean($str[$key]);
            }

            return $str;
        }

    }

    /**
     * Runs the file through the XSS clean function
     *
     * This prevents people from embedding malicious code in their files.
     * I'm not sure that it won't negatively affect certain files in unexpected ways,
     * but so far I haven't found that it causes trouble.
     *
     * @return    void
     */
    public function do_xss_clean()
    {
        $file = $this->postFileTempName;

        if (filesize($file) == 0) {
            return FALSE;
        }

        if (function_exists('memory_get_usage') && memory_get_usage() && ini_get('memory_limit') != '') {
            $current = ini_get('memory_limit') * 1024 * 1024;

            // There was a bug/behavioural change in PHP 5.2, where numbers over one million get output
            // into scientific notation.  number_format() ensures this number is an integer
            // http://bugs.php.net/bug.php?id=43053

            $new_memory = number_format(ceil(filesize($file) + $current), 0, '.', '');

            ini_set('memory_limit', $new_memory); // When an integer is used, the value is measured in bytes. - PHP.net
        }

        // If the file being uploaded is an image, then we should have no problem with XSS attacks (in theory), but
        // IE can be fooled into mime-type detecting a malformed image as an html file, thus executing an XSS attack on anyone
        // using IE who looks at the image.  It does this by inspecting the first 255 bytes of an image.  To get around this
        // CI will itself look at the first 255 bytes of an image to determine its relative safety.  This can save a lot of
        // processor power and time if it is actually a clean image, as it will be in nearly all instances _except_ an
        // attempted XSS attack.

        if (function_exists('getimagesize') && @getimagesize($file) !== FALSE) {
            if (($file = @fopen($file, 'rb')) === FALSE) // "b" to force binary
            {
                return FALSE; // Couldn't open the file, return FALSE
            }

            $opening_bytes = fread($file, 256);
            fclose($file);

            // These are known to throw IE into mime-type detection chaos
            // <a, <body, <head, <html, <img, <plaintext, <pre, <script, <table, <title
            // title is basically just in SVG, but we filter it anyhow

            if (!preg_match('/<(a|body|head|html|img|plaintext|pre|script|table|title)[\s>]/i', $opening_bytes)) {
                return TRUE; // its an image, no "triggers" detected in the first 256 bytes, we're good
            } else {
                return FALSE;
            }
        }

        if (($data = @file_get_contents($file)) === FALSE) {
            return FALSE;
        }

        return $this->xss_clean($data, TRUE);

    }

}