

/**
 * Created by Daniel Adenew.
 * User: Daniel Adenew  dadenewyyt@gmail.com
 * Date: 11/25/14
 * All right reserved.
 * Time: 3:05 PM
 */


/***TODO:Video Library Table should be  updated **/

CREATE TABLE `video_library` (
  `video_id` int(11) NOT NULL AUTO_INCREMENT,
  `video_title` varchar(40) DEFAULT NULL,
  `video_filename` varchar(40) DEFAULT NULL,
  `video_type` varchar(40) DEFAULT NULL,
  `video_extension` varchar(40) DEFAULT NULL,
  `thumb_title` varchar(40) DEFAULT NULL,
  `thumb_path` varchar(40) DEFAULT NULL,
  `created_date` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_date` datetime DEFAULT CURRENT_TIMESTAMP,
  `created_by` varchar(40) DEFAULT NULL,
  PRIMARY KEY (`video_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1$$







