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

    protected static $_table_name = 'videokes';

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
    public $title;

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

    /** video type
     * @var string
     */
    public $video_type;

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
    public static $connectionInstance;




    public function getByID($id){

        try{
            $output = DB::query("SELECT * FROM videokes WHERE id='".intval($id)."'")->execute()->as_array();

            return $output[0];

        }catch(Exception $err){
            return null;
        }
    }

    public function getVideokes($user_id=0, $category_id=0){

        try{
            VideoModel::getConnectionSingelton();
            $query = mysql_query("SELECT * FROM videokes WHERE 1".
                (($user_id > 0)?" AND user_id='".intval($user_id)."' ":"").
                (($category_id > 0)?" AND category_id='".".intval($user_id)."."' ":"")
                 );
            $result = mysql_fetch_array($query);

            return $result;

        }catch(Exception $err){
            return null;
        }


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

    public static function get_html5_video_formats(){
        return array(".mp4", ".webm", ".ogg" );
    }

    public function get_video($format) {
        return Uri::create("uploads" . DS . Model_User::clean_name($this->user->username) . DS . "videokes" . DS . $this->video . $format);
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

    public static function getConnectionSingelton() {

      if( VideoModel::$connectionInstance == null ){

           VideoModel::$connectionInstance = mysql_connect(DB_HOST,DB_USERNAME,DB_PASSWORD) or die('Unable to connect to database');
      }
        return VideoModel::$connectionInstance ;
}

   public function generateThumbnails(){
  //TODO:Return generated thumbnail file

  }

    public function checkIf_thumbnail_Exists($thumbnailPath) {

    //TODO:check if thumbnails exist or not
   }

  public function uploadVideoFile($srcvideoFilePath,$destinationPath) {
  //TODO:retrun a thumbnail based on a give video url
 }

 public function get_thumbnail($videoFileurl) {
 //TODO:return a thumbnail based on a give video url
 }

 public function findVideoByName($videoFileName) {

    if( !isset($videoFileName) ) {
        exit('video file name is empty. on findByVideoName');

    } else {

       $this->getConnectionSingelton();
       mysql_select_db(DB_DATABASE);
       $query = 'select * from video_library where video_filename =`'.$videoFileName.'`';
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

  public function mapVideoModelFromDatabase($resultObject) {

      $videoModelMapper = new VideoModel();
      $videoModelMapper->video_id = $resultObject['video_id'];
      $videoModelMapper->video_title = $resultObject['video_title'];
      $videoModelMapper->video_url = $ $resultObject['video_filename'];
      $videoModelMapper->video_file_extension = $resultObject['video_extension'];
      $videoModelMapper->thumbnail_file_extension = $resultObject['video_extension'];

  }



}
