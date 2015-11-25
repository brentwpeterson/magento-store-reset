<?php

class Wage_Datareset_Block_Adminhtml_Datareset_Dialogs extends Mage_Adminhtml_Block_Template
{
    /**
     * Block's template
     *
     * @var string
     */
    protected $_template = 'datareset/dialogs.phtml';

    /**
     * Include backup.js file in page before rendering
     *
     * @see Mage_Core_Block_Abstract::_prepareLayout()
     */
    protected function _prepareLayout()
    {
        $this->getLayout()->getBlock('head')->addJs('mage/adminhtml/backup.js');
        parent::_prepareLayout();
    }
}
