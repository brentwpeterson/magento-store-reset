<?php

class Wage_Datareset_Block_Adminhtml_Datareset_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    protected function _construct()
    {
        $this->setSaveParametersInSession(true);
        $this->setId('dataresetGrid');
        $this->setDefaultSort('time', 'desc');
    }

    /**
     * Init backups collection
     */
    protected function _prepareCollection()
    {
        $collection = Mage::getSingleton('backup/fs_collection');
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * Configuration of grid
     *
     * @return Mage_Adminhtml_Block_Backup_Grid
     */
    protected function _prepareColumns()
    {
        $url7zip = Mage::helper('adminhtml')->__('The archive can be uncompressed with <a href="%s">%s</a> on Windows systems', 'http://www.7-zip.org/', '7-Zip');

        $this->addColumn('time', array(
            'header'    => Mage::helper('backup')->__('Time'),
            'index'     => 'date_object',
            'type'      => 'datetime',
            'width'     => 200
        ));

        $this->addColumn('basename', array(
            'header'    => Mage::helper('backup')->__('Name'),
            'index'     => 'basename',
            'filter'    => false,
            'sortable'  => true,
            'width'     => 350
        ));

        $this->addColumn('size', array(
            'header'    => Mage::helper('backup')->__('Size, Bytes'),
            'index'     => 'size',
            'type'      => 'number',
            'sortable'  => true,
            'filter'    => false
        ));

        /*$this->addColumn('type', array(
            'header'    => Mage::helper('backup')->__('Type'),
            'type'      => 'options',
            'options'   => Mage::helper('backup')->getBackupTypes(),
            'index'     => 'type',
            'width'     => 300
        ));*/

        $this->addColumn('download', array(
            'header'    => Mage::helper('backup')->__('Download'),
            'format'    => '<a href="' . $this->getUrl('*/*/download', array('time' => '$time', 'type' => '$type'))
                . '">$extension</a> &nbsp; <small>('.$url7zip.')</small>',
            'index'     => 'type',
            'sortable'  => false,
            'filter'    => false
        ));

        if (Mage::helper('datareset')->isRollbackAllowed()){
            $this->addColumn('action', array(
                    'header'   => Mage::helper('backup')->__('Action'),
                    'type'     => 'action',
                    'width'    => '80px',
                    'filter'   => false,
                    'sortable' => false,
                    'actions'  => array(array(
                        'url'     => '#',
                        'caption' => Mage::helper('backup')->__('Rollback'),
                        'onclick' => 'return backup.rollback(\'$type\', \'$time\');'
                    )),
                    'index'    => 'type',
                    'sortable' => false
            ));
        }

        return $this;
    }

}
