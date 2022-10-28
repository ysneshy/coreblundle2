<?php

namespace Claroline\CoreBundle\Migrations\pdo_oci;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2013/11/20 01:25:49
 */
class Version20131120132545 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_badge_rule
            ADD (
                resource_id NUMBER(10) DEFAULT NULL
            )
        ");
        $this->addSql("
            ALTER TABLE claro_badge_rule
            ADD CONSTRAINT FK_805FCB8F89329D25 FOREIGN KEY (resource_id)
            REFERENCES claro_resource_node (id)
        ");
        $this->addSql("
            CREATE INDEX IDX_805FCB8F89329D25 ON claro_badge_rule (resource_id)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_badge_rule
            DROP (resource_id)
        ");
        $this->addSql("
            ALTER TABLE claro_badge_rule
            DROP CONSTRAINT FK_805FCB8F89329D25
        ");
        $this->addSql("
            DROP INDEX IDX_805FCB8F89329D25
        ");
    }
}
