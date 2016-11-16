<?php

/**
 * Data object containing the SQL and PHP code to migrate the database
 * up to version 1479297981.
 * Generated on 2016-11-16 12:06:21 by sacredskull
 */
class PropelMigration_1479297981
{
    public $comment = '';

    public function preUp($manager)
    {
        // add the pre-migration code here
    }

    public function postUp($manager)
    {
        // add the post-migration code here
    }

    public function preDown($manager)
    {
        // add the pre-migration code here
    }

    public function postDown($manager)
    {
        // add the post-migration code here
    }

    /**
     * Get the SQL statements for the Up migration
     *
     * @return array list of the SQL strings to execute for the Up migration
     *               the keys being the datasources
     */
    public function getUpSQL()
    {
        return array (
  'assemble' => '
# This is a fix for InnoDB in MySQL >= 4.1.x
# It "suspends judgement" for fkey relationships until are tables are set.
SET FOREIGN_KEY_CHECKS = 0;

DROP INDEX `unique_name` ON `assemblegroup`;

CREATE UNIQUE INDEX `unique_name` ON `assemblegroup` (`name`(100));

DROP INDEX `unique_name` ON `interest`;

CREATE UNIQUE INDEX `unique_name` ON `interest` (`name`(60));

DROP INDEX `unique_username` ON `person`;

ALTER TABLE `person`

  CHANGE `password` `password` VARCHAR(255) NOT NULL,

  ADD `email` VARCHAR(120) NOT NULL AFTER `privilege`;

CREATE UNIQUE INDEX `unique_username` ON `person` (`username`(30));

CREATE UNIQUE INDEX `unique_email` ON `person` (`email`(120));

ALTER TABLE `person_group`

  ADD `privilege` TINYINT DEFAULT 0 AFTER `hidden`;

# This restores the fkey checks, after having unset them earlier
SET FOREIGN_KEY_CHECKS = 1;
',
);
    }

    /**
     * Get the SQL statements for the Down migration
     *
     * @return array list of the SQL strings to execute for the Down migration
     *               the keys being the datasources
     */
    public function getDownSQL()
    {
        return array (
  'assemble' => '
# This is a fix for InnoDB in MySQL >= 4.1.x
# It "suspends judgement" for fkey relationships until are tables are set.
SET FOREIGN_KEY_CHECKS = 0;

DROP INDEX `unique_name` ON `assemblegroup`;

CREATE UNIQUE INDEX `unique_name` ON `assemblegroup` (`name`);

DROP INDEX `unique_name` ON `interest`;

CREATE UNIQUE INDEX `unique_name` ON `interest` (`name`);

DROP INDEX `unique_email` ON `person`;

DROP INDEX `unique_username` ON `person`;

ALTER TABLE `person`

  CHANGE `password` `password` VARCHAR(100) NOT NULL,

  DROP `email`;

CREATE UNIQUE INDEX `unique_username` ON `person` (`username`);

ALTER TABLE `person_group`

  DROP `privilege`;

# This restores the fkey checks, after having unset them earlier
SET FOREIGN_KEY_CHECKS = 1;
',
);
    }

}