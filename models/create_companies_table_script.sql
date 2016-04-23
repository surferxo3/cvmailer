/*
* Developer: Mohammad Sharaf Ali
* Description: SQL script to create companies table
* Date: 23-04-2016
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for `companies`
-- ----------------------------
DROP TABLE IF EXISTS `companies`;
CREATE TABLE `companies` (
  `ID` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `Name` varchar(300) DEFAULT '',
  `Website` varchar(300) DEFAULT '',
  `Email` varchar(300) DEFAULT '',
  `TrackingURL` text COMMENT 'Pixel Tracking Url (Base64 Encoded)',
  `TrackingExpiryDT` datetime DEFAULT NULL COMMENT 'Pixel Tracking Url after which it won''t be served',
  `IsOpened` char(1) DEFAULT '0' COMMENT '0 => Mail Not Viewed, 1 => Mail Viewed',
  `IsOpenedDT` datetime DEFAULT NULL COMMENT 'Mailed Viewd DateTime',
  `IsActive` char(1) DEFAULT '1' COMMENT '0 => Company Profile Not Active, 1 => Company Profile Active',
  `CreatedDT` datetime DEFAULT NULL,
  `ModifiedDT` datetime DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=233 DEFAULT CHARSET=latin1;
