<?php

class Wage_Datareset_Helper_Data extends Mage_Core_Helper_Abstract
{
	public function isRollbackAllowed(){
        return Mage::getSingleton('admin/session')->isAllowed('system/datareset');
    }
}