<?php

namespace Claroline\CoreBundle\Migrations\oci8;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/12/15 12:02:08
 */
class Version20141215120207 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_workspace 
            ADD (
                maxStorageSize VARCHAR2(255) NOT NULL, 
                maxUploadResources NUMBER(10) NOT NULL
            )
        ");
        $this->addSql("
            ALTER TABLE claro_directory 
            ADD (
                is_upload_destination NUMBER(1) NOT NULL
            )
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_directory 
            DROP (is_upload_destination)
        ");
        $this->addSql("
            ALTER TABLE claro_workspace 
            DROP (
                maxStorageSize, maxUploadResources
            )
        ");
    }
}