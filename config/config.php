<?php
/**
 * Created by Daniel Adenew.
 * User: Daniel Adenew dadenewyyt@gmail.com
 * Date: 11/25/14
 * Time: 3:05 PM
 */


/**
 * You can Integrate your 
 */

define('MAX_FILE_SIZE', 0);
define('DEFAULT_VIDEO_NAME_PREFIX','video_');
// MySql data (in case you want to save uploads log)
define('DB_HOST','localhost'); // host, usually localhost
define('DB_DATABASE','tutorial'); // database name
define('DB_USERNAME','root'); // username
define('DB_PASSWORD',''); // password
define('DEFAULT_VIDEO_STORE_LOCATION','upload');
define('ALLOWED_FILE_EXTENSIONS',"mp4");
define('FILE_TYPES','mp4');
define('MAX_FILE_SIZE_LIMIT',256000000);
define('USER_FILE_UPLOAD_FIELD_NAME','file'); // example. <input type="file" name="file" id="file" />
define('BASE_PATH','upload_file.php');

