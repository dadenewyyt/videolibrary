<?php
/**
 * Created by Daniel Adenew.
 * User: Daniel Adenew  dadenewyyt@gmail.com
 * Date: 11/25/14
 * All right reserved.
 * Time: 3:05 PM
 */

require_once('config/config.php');

class VideoModel {

    const THUMB_CONTENT = "151x89";
    const THUMB_HOME = "172x114";
    const THUMB_SIDEBAR = "124x72";

    static $THUMB_PART1 = array( "size"=>VideoModel::THUMB_HOME, "duration"=>"00:00:01");
    static $THUMB_PART2 = array( "size"=>VideoModel::THUMB_HOME, "duration"=>"00:00:30");
    static $THUMB_PART3 = array( "size"=>VideoModel::THUMB_HOME, "duration"=>"00:00:55");
    static $THUMB_PART4 = array( "size"=>VideoModel::THUMB_HOME, "duration"=>"00:01:00");



    protected static $_table_name = 'videokes'; //any table name you want

    /** video primary key
     * @var int
     */
    protected $id;

    /** video owner user id
     * @var int
     */
    protected $user_id;

    /** video category  id
     * @var int
     */
    protected $category_id;

    /** video title
     * @var string
     */
    public $title; // video

    /** video description
     * @var string
     */
    public $description;

    /** video key words
     * @var string
     */
    public $key_words;

    /**
     * Video views
     */
    public $views;

     /**
     * video likes
     * @var string
     */
    public $likes;

    /**
     * video dislikes
     * @var string
     */
    public $dislikes;

    /**
     * video created at
     * @var date
     */

    public $created_at;

    /**
     * video updated at
     * @var date
     */
    public $updated_at;

    /**
     * video is blocked or not
     * @var int
     */
    public $is_blocked;


    /** video name only with out extension
     * @var string
     */
    public $video_file_name;

    /** video file _ extension eg. .web , .flv , mp4 ..
     * @var string
     */
    public $video_file_extension;

    /** video thumbnail file name
     * @var string
     */
    public $thumbnail_name;

    /** video url if necessary full path of the server/where it has been uploaded
     * @var string
     */
    public $video_url;

    /** video thumbnail extension full path of the server/where it has been uploaded
     * @var string
     */
    public $thumbnail_file_extension;

    /** video thumbnail URL full path of the server/where it has been uploaded
     * @var string
     */
    public $thumbnail_url;

    /**
     * This a singleton pattern , doesn't create a connection object at every instance
     */
    public static $DBH;


    public static function getByID($id){

        try{

            $DBH = VideoModel::get_connection_singleton();

            $STH = $DBH->prepare("SELECT * FROM ". VideoModel::$_table_name ." WHERE id = ?");

            $STH->bindParam(1,intval($id));

            $STH->setFetchMode(PDO::FETCH_OBJ);

            $result_object = $STH->fetch();

            if($result_object != null) {

               return $result_object;
            }

            return null;

        } catch(Exception $err) {
            echo $err->getMessage();
        }
    }


    public static function get_video_objects(){

        $DBH = VideoModel::get_connection_singleton();

        $STH = $DBH->query("SELECT * FROM ". VideoModel::$_table_name);

        $STH->setFetchMode(PDO::FETCH_CLASS, 'VideoModel');

        $videoModelObject = $STH->fetch();

        return $videoModelObject ;

    }

    /**
     * DISABLING WEBM AND OGG FORMATS, BECAUSE mp4 PLAYS ON ALL SUPPORTED DEVICES
     * @return multitype:multitype:string
     */
    public static function get_formats() {
        return array(
            "webm" => array("extension" => '.webm', "type" => 'video/webm'),
            "mp4" => array("extension" => '.mp4', "type" => 'video/mp4'),
            "ogg" => array("extension" => '.ogg', "type" => 'video/ogg'),
        );
    }

    public static function get_thumbnail_sizes() {
        return array(VideoModel::THUMB_CONTENT, VideoModel::THUMB_HOME, VideoModel::THUMB_SIDEBAR);
    }

    public static function generate_thumbnails_of_four_size() {
        /* you can differ the sizes later */
        return array(VideoModel::$THUMB_PART1, VideoModel::$THUMB_PART2, VideoModel::$THUMB_PART3,VideoModel::$THUMB_PART4);
    }

    public static function get_html5_video_formats(){
        return array(".mp4", ".webm", ".ogg" );
    }

    public function get_video_from_upload_location($user_name , $format) {


        /***
         * Generate and return a custom video URL location
         * uses username to find video and its video name currently loaded and format of the video
         */

        $path = DEFAULT_VIDEO_STORE_LOCATION .'/'.'video_'.'/'. VideoModel::$_table_name . '/' . $user_name  . $format;

        $path = VideoModel::directory_sep_helper($path);
        return $path;
    }

    public function get_picture($user, $size) {

        $file = DOCROOT . "uploads/" . Model_User::clean_name($user->username) . DS . "videokes" . DS . "thumb_" . $size . "_" . $this->video . ".jpg";
        if (file_exists($file)) {
            return Uri::create("uploads" . DS . Model_User::clean_name($user->username) . DS . "videokes" . DS . "thumb_" . $size . "_" . $this->video . ".jpg");
        } else {
            return Uri::create("assets/img/defaults/" . "thumb_" . $size . "_video_picture.jpg");
        }
    }

    public function thumbs_up(){
        if(!$this->is_new()){
            $q = DB::query('SELECT (SELECT COUNT(`id`) FROM `videokes_ratings` WHERE `videoke_id` = '.$this->id.' AND `rating` = \'1\') + (SELECT COUNT(`id`) FROM `videokes_ratings` WHERE `videoke_id` = '.$this->id.' AND `rating` = \'2\')*2 AS `sum`')->execute()->as_array();
            if(!empty($q)){
                $q = array_pop($q);
                return $q['sum'];
            }
        }
        return 0;
    }

    public function thumbs_down(){
        if(!$this->is_new()){
            $q = DB::query('SELECT (SELECT COUNT(`id`) FROM `videokes_ratings` WHERE `videoke_id` = '.$this->id.' AND `rating` = \'-1\') + (SELECT COUNT(`id`) FROM `videokes_ratings` WHERE `videoke_id` = '.$this->id.' AND `rating` = \'-2\')*2 AS `sum`')->execute()->as_array();
            if(!empty($q)){
                $q = array_pop($q);
                return $q['sum'];
            }
        }
        return 0;
    }

    public function votes(){
        if(!$this->is_new()){
            return count(Model_Rating::find_by_videoke_id($this->id));

        }

    }

   public function _construct() {
   //TODO:DO some clean up instantiation work here
   }

    public static function get_connection_singleton() {

        try {

              if( VideoModel::$DBH == null ) {
                   //PDO:Based Database  connection
                   VideoModel::$DBH = new  PDO("mysql:host=".DB_HOST.";dbname=".DB_DATABASE, DB_USERNAME, DB_PASSWORD);
                  }
        }

        catch(PDOException $e) {

            echo $e->getMessage();
        }

        return VideoModel::$DBH ;
}


 public function findVideoByName($videoFileName) {

    if( !isset($videoFileName) ) {
        exit('video file name is empty. on findByVideoName');

    } else {

       $this->get_connection_singleton();
       mysql_select_db(DB_DATABASE);
       $query = "select * from ".VIDEOMODEL::$_table_name. "where video_filename =`'.$videoFileName.'`:";
       $result = mysql_query($query) ;
       $resultObject =  mysql_fetch_object($result);

        if($resultObject != null) {
          die('no record found!');
          return false;

        } else {

           $videoModelMapped = mapVideoModelFromDatabase($resultObject);
           return $videoModelMapped ;
        }
    }

  }

    public function generateThumbnails(){
        //TODO:Return generated thumbnail file

    }

    public function checkIf_thumbnail_Exists($thumbnailPath) {

        //TODO:check if thumbnails exist or not
    }


    public function get_thumbnail($videoFileurl) {
        //TODO:return a thumbnail based on a give video url
    }

    public static function directory_sep_helper ($path) {

    if (strtoupper(substr(PHP_OS, 0, 3) == 'WIN'))
        $path = str_replace('/', DIRECTORY_SEPARATOR, $path);
    else
        $path = str_replace('\\', DIRECTORY_SEPARATOR, $path);

    return $path;

    }

}
