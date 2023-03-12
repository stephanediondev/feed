INSERT INTO `action` VALUES(1, NULL, 'read', '2016-09-28 12:25:57');
INSERT INTO `action` VALUES(2, NULL, 'star', '2016-09-28 12:25:57');
INSERT INTO `action` VALUES(3, NULL, 'subscribe', '2016-09-28 12:25:57');
INSERT INTO `action` VALUES(4, NULL, 'read_all', '2016-09-28 12:25:57');
INSERT INTO `action` VALUES(5, NULL, 'exclude', '2016-09-28 12:25:57');
INSERT INTO `action` VALUES(6, NULL, 'evernote', '2016-09-28 12:25:57');
INSERT INTO `action` VALUES(7, NULL, 'email', '2016-09-28 12:25:57');
INSERT INTO `action` VALUES(8, NULL, 'purge', '2016-09-28 12:25:57');
INSERT INTO `action` VALUES(11, NULL, 'elasticsearch', '2016-09-28 12:25:57');
INSERT INTO `action` VALUES(12, NULL, 'unread', '2016-09-28 12:25:57');
INSERT INTO `action` VALUES(13, NULL, 'unstar', '2016-09-28 00:00:00');
INSERT INTO `action` VALUES(14, NULL, 'include', '2016-09-28 00:00:00');
INSERT INTO `action` VALUES(15, NULL, 'unsubscribe', '2016-09-28 00:00:00');

UPDATE `action` SET reverse = 1 WHERE id = 12;
UPDATE `action` SET reverse = 2 WHERE id = 13;
UPDATE `action` SET reverse = 3 WHERE id = 15;
UPDATE `action` SET reverse = 5 WHERE id = 14;

UPDATE `action` SET reverse = 12 WHERE id = 1;
UPDATE `action` SET reverse = 13 WHERE id = 2;
UPDATE `action` SET reverse = 15 WHERE id = 3;
UPDATE `action` SET reverse = 14 WHERE id = 5;
