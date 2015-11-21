<?php

/**
 * LICENSE: ##LICENSE##
 *
 * @package    Com_Anahita
 * @subpackage Schema_Migration
 */

/**
 * Schema Migration
 *
 * @package    Com_Anahita
 * @subpackage Schema_Migration
 */
class ComAnahitaSchemaMigration15 extends ComMigratorMigrationVersion
{
   /**
    * Called when migrating up
    */
    public function up()
    {
      //Create new fields for com_locations
      dbexec("ALTER TABLE jos_nodes ADD `address` VARCHAR(255) DEFAULT NULL AFTER `geo_longitude`");
      dbexec("ALTER TABLE jos_nodes ADD `city` VARCHAR(50) DEFAULT NULL AFTER `address`");
      dbexec("ALTER TABLE jos_nodes ADD `state_province` VARCHAR(50) DEFAULT NULL AFTER `city`");
      dbexec("ALTER TABLE jos_nodes ADD `country` VARCHAR(30) DEFAULT NULL AFTER `state_province`");
      dbexec("ALTER TABLE jos_nodes ADD `postalcode` VARCHAR(15) DEFAULT NULL AFTER `country`");

      //add extension records
      dbexec("INSERT INTO `#__components` (`name`, `link`, `admin_menu_link`, `admin_menu_alt`, `admin_menu_image`, `option`,`iscore`, `enabled`) VALUES ('Locations', 'option=com_locations', 'option=com_locations', 'Locations', 'js/ThemeOffice/component.png', 'com_locations', 1, 1)");
      dbexec("INSERT INTO `#__plugins` (`name`, `element`, `folder`, `published`, `iscore`) VALUES ('Content Filter - Location', 'location', 'contentfilter', 1, 1)");
    }

   /**
    * Called when rolling back a migration
    */
    public function down()
    {
        //add your migration here
    }
}
