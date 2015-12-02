<?php

class Wage_Datareset_Model_Rollback extends Mage_Backup_Db
{
    public function rollback()
    {
        set_time_limit(0);
        ignore_user_abort(true);

        $this->_lastOperationSucceed = false;
        $unsecureBaseUrl = Mage::getStoreConfig('web/unsecure/base_url');
        $secureBaseUrl = Mage::getStoreConfig('web/secure/base_url');
        $archiveManager = new Mage_Archive();
        $source = $archiveManager->unpack($this->getBackupPath(), $this->getBackupsDir());

        $file = new Mage_Backup_Filesystem_Iterator_File($source);
        foreach ($file as $statement) {


            $updateStatement = '';
            if(strpos($statement, '`admin_assert`')){ continue; }
            if(strpos($statement, '`admin_role`')){ continue; }
            if(strpos($statement, '`admin_rule`')){ continue; }
            if(strpos($statement, '`admin_user`')){ continue; }
            if(strpos($statement, '`adminnotification_inbox`')){ continue; }
            if(strpos($statement, '`api2_acl_attribute`')){ continue; }
            if(strpos($statement, '`api2_acl_role`')){ continue; }
            if(strpos($statement, '`api2_acl_rule`')){ continue; }
            if(strpos($statement, '`api2_acl_user`')){ continue; }
            if(strpos($statement, '`api_assert`')){ continue; }
            if(strpos($statement, '`api_role`')){ continue; }
            if(strpos($statement, '`api_rule`')){ continue; }
            if(strpos($statement, '`api_session`')){ continue; }
            if(strpos($statement, '`api_user`')){ continue; }
            if(strpos($statement, '`core_cache_option`')){ continue; }
            if(strpos($statement, '`oauth_consumer`')){ continue; }
            if(strpos($statement, '`oauth_nonce`')){ continue; }
            if(strpos($statement, '`oauth_token`')){ continue; }
            if(strpos($statement, '`core_resource`')){ continue; }
            
            $this->getResourceModel()->runCommand($statement);

            if(strpos($statement, '`core_config_data`')){
                if(strpos($statement, 'INSERT INTO `core_config_data`') === 0){
                    $updateStatement = "UPDATE `core_config_data` set value = '$unsecureBaseUrl' where path = 'web/unsecure/base_url';"; 
                    $updateStatement .= "UPDATE `core_config_data` set value = '$secureBaseUrl' where path = 'web/secure/base_url';"; 
                    $this->getResourceModel()->runCommand($updateStatement);
                }            
            } 
        }
        @unlink($source);

        $this->_lastOperationSucceed = true;

        return true;
    }

}
