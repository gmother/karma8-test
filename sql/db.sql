DROP TABLE IF EXISTS `users`;
DROP PROCEDURE IF EXISTS filler;

CREATE TABLE `users` (
    `id` bigint NOT NULL AUTO_INCREMENT COMMENT 'User ID',
    `username` varchar(32) NOT NULL COMMENT 'User name',
    `email` varchar(255) NOT NULL COMMENT 'User email',
    `valid_ts` timestamp NULL DEFAULT NULL COMMENT 'Subscription is valid until',
    `confirmed` tinyint NOT NULL DEFAULT '0' COMMENT 'Is user email confirmed',
    `checked` tinyint NOT NULL DEFAULT '0' COMMENT 'Is user email checked',
    `valid` tinyint NOT NULL DEFAULT '0' COMMENT 'Is user email valid',
    `sent_ts` timestamp NULL DEFAULT NULL COMMENT 'Last notification was sent at',
    `sent_num` int NOT NULL DEFAULT 0 COMMENT 'Number of notifications was sent',
    `locked_ts` timestamp NULL DEFAULT NULL COMMENT 'User locked by processor at',
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DELIMITER $$
CREATE PROCEDURE filler(n1 INT)
BEGIN
    DECLARE generatedName VARCHAR(255) DEFAULT '';
    WHILE n1 > 0 DO
        SET generatedName = MD5(UUID());
        INSERT IGNORE INTO `users` (username, email, valid_ts, confirmed) VALUES (
            generatedName,
            CONCAT(generatedName, '@fake-mail.com'),
            IF(RAND() > 0.8, NOW() + INTERVAL FLOOR(86000 + RAND() * 86000 * 5) SECOND, NULL),
            IF(RAND() > 0.85, 1, 0)
        );
        SET n1 = n1 - 1;
    END WHILE;
END$$
DELIMITER ;

CALL filler(10000);
