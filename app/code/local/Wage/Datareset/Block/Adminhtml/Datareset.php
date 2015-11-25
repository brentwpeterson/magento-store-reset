<?php
class Wage_Datareset_Block_Adminhtml_Datareset extends Mage_Adminhtml_Block_Template
{
    /**
     * Block's template
     *
     * @var string
     */
    protected $_template = 'datareset/list.phtml';

    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        $this->setChild('dataresetGrid',
            $this->getLayout()->createBlock('datareset/adminhtml_datareset_grid')
        );

        $this->setChild('dataresetDialogs', $this->getLayout()->createBlock('datareset/adminhtml_datareset_dialogs'));
    }

    public function getGridHtml()
    {
        return $this->getChildHtml('dataresetGrid');
    }

    /**
     * Generate html code for pop-up messages that will appear when user click on "Rollback" link
     *
     * @return string
     */
    public function getDialogsHtml()
    {
        return $this->getChildHtml('dataresetDialogs');
    }
}
