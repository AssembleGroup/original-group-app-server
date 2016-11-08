<?php

/**
 * Data object containing the SQL and PHP code to migrate the database
 * up to version 1477091426.
 * Generated on 2016-10-21 23:10:26 by sacredskull
 */
class PropelMigration_1477091426
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

ALTER TABLE `assemblegroup`

  ADD `hidden` TINYINT(1) DEFAULT 0 AFTER `closed`;

CREATE UNIQUE INDEX `unique_name` ON `assemblegroup` (`name`(100));

DROP INDEX `unique_username` ON `person`;

CREATE UNIQUE INDEX `unique_username` ON `person` (`username`(30));

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

ALTER TABLE `assemblegroup`

  DROP `hidden`;

DROP INDEX `unique_username` ON `person`;

CREATE UNIQUE INDEX `unique_username` ON `person` (`username`);

# This restores the fkey checks, after having unset them earlier
SET FOREIGN_KEY_CHECKS = 1;
',
);
    }

}
