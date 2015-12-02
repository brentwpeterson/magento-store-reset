<?php

class Wage_Datareset_Adminhtml_Datareset_DataresetController extends Mage_Adminhtml_Controller_action
{

	/**
     * Backup list action
     */
    public function indexAction()
    {
        $this->_title($this->__('System'))->_title($this->__('Tools'))->_title($this->__('Reset Database'));

        if($this->getRequest()->getParam('ajax')) {
            $this->_forward('grid');
            return;
        }

        $this->loadLayout();
        $this->_setActiveMenu('system');
        $this->_addBreadcrumb(Mage::helper('adminhtml')->__('System'), Mage::helper('adminhtml')->__('System'));
        $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Tools'), Mage::helper('adminhtml')->__('Tools'));
        $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Reset Database'), Mage::helper('adminhtml')->__('Reset Database'));

        $this->_addContent($this->getLayout()->createBlock('datareset/adminhtml_datareset', 'datareset'));

        $this->renderLayout();
    }

    /**
     * Backup list action
     */
    public function gridAction()
    {
        $this->getResponse()->setBody($this->getLayout()->createBlock('datareset/adminhtml_datareset_grid')->toHtml());
    }

    /**
     * Download backup action
     *
     * @return Mage_Adminhtml_Controller_Action
     */
    public function downloadAction()
    {
        /* @var $backup Mage_Backup_Model_Backup */
        $backup = Mage::getModel('backup/backup')->loadByTimeAndType(
            $this->getRequest()->getParam('time'),
            $this->getRequest()->getParam('type')
        );

        if (!$backup->getTime() || !$backup->exists()) {
            return $this->_redirect('*/*');
        }

        $fileName = Mage::helper('backup')->generateBackupDownloadName($backup);

        $this->_prepareDownloadResponse($fileName, null, 'application/octet-stream', $backup->getSize());

        $this->getResponse()->sendHeaders();

        $backup->output();
        exit();
    }

    /**
     * Rollback Action
     *
     * @return Mage_Adminhtml_Controller_Action
     */
    public function rollbackAction()
    {
        if (!Mage::helper('datareset')->isRollbackAllowed()){
            return $this->_forward('denied');
        }

        if (!$this->getRequest()->isAjax()) {
            return $this->getUrl('*/*/index');
        }

        $helper = Mage::helper('backup');
        $response = new Varien_Object();

        try {
            /* @var $backup Mage_Backup_Model_Backup */
            $backup = Mage::getModel('backup/backup')->loadByTimeAndType(
                $this->getRequest()->getParam('time'),
                $this->getRequest()->getParam('type')
            );

            if (!$backup->getTime() || !$backup->exists()) {
                return $this->_redirect('*/*');
            }

            if (!$backup->getTime()) {
                throw new Mage_Backup_Exception_CantLoadSnapshot();
            }

            $type = $backup->getType();

            $backupManager = Mage::getModel('datareset/rollback') //Mage_Backup::getBackupInstance($type)
                ->setBackupExtension($helper->getExtensionByType($type))
                ->setTime($backup->getTime())
                ->setBackupsDir($helper->getBackupsDir())
                ->setName($backup->getName(), false)
                ->setResourceModel(Mage::getResourceModel('backup/db'));

            Mage::register('backup_manager', $backupManager);

            $passwordValid = Mage::getModel('backup/backup')->validateUserPassword(
                $this->getRequest()->getParam('password')
            );

            if (!$passwordValid) {
                $response->setError(Mage::helper('backup')->__('Invalid Password.'));
                $backupManager->setErrorMessage(Mage::helper('backup')->__('Invalid Password.'));
                return $this->getResponse()->setBody($response->toJson());
            }

            if ($this->getRequest()->getParam('maintenance_mode')) {
                $turnedOn = $helper->turnOnMaintenanceMode();

                if (!$turnedOn) {
                    $response->setError(
                        Mage::helper('backup')->__('You do not have sufficient permissions to enable Maintenance Mode during this operation.')
                            . ' ' . Mage::helper('backup')->__('Please either unselect the "Put store on the maintenance mode" checkbox or update your permissions to proceed with the rollback."')
                    );
                    $backupManager->setErrorMessage(Mage::helper('backup')->__("System couldn't put store on the maintenance mode"));
                    return $this->getResponse()->setBody($response->toJson());
                }
            }

            if ($type != Mage_Backup_Helper_Data::TYPE_DB) {

                $backupManager->setRootDir(Mage::getBaseDir())
                    ->addIgnorePaths($helper->getRollbackIgnorePaths());

                if ($this->getRequest()->getParam('use_ftp', false)) {
                    $backupManager->setUseFtp(
                        $this->getRequest()->getParam('ftp_host', ''),
                        $this->getRequest()->getParam('ftp_user', ''),
                        $this->getRequest()->getParam('ftp_pass', ''),
                        $this->getRequest()->getParam('ftp_path', '')
                    );
                }
            }

            $backupManager->rollback();

            $helper->invalidateCache()->invalidateIndexer();

            $adminSession = $this->_getSession();
            $adminSession->unsetAll();
            $adminSession->getCookie()->delete($adminSession->getSessionName());

            $response->setRedirectUrl($this->getUrl('*'));
        } catch (Mage_Backup_Exception_CantLoadSnapshot $e) {
            $errorMsg = Mage::helper('backup')->__('Backup file not found');
        } catch (Mage_Backup_Exception_FtpConnectionFailed $e) {
            $errorMsg = Mage::helper('backup')->__('Failed to connect to FTP');
        } catch (Mage_Backup_Exception_FtpValidationFailed $e) {
            $errorMsg = Mage::helper('backup')->__('Failed to validate FTP');
        } catch (Mage_Backup_Exception_NotEnoughPermissions $e) {
            Mage::log($e->getMessage());
            $errorMsg = Mage::helper('backup')->__('Not enough permissions to perform rollback');
        } catch (Exception $e) {
            Mage::log($e->getMessage());
            $errorMsg = Mage::helper('backup')->__('Failed to rollback');
        }

        if (!empty($errorMsg)) {
            $response->setError($errorMsg);
            $backupManager->setErrorMessage($errorMsg);
        }

        if ($this->getRequest()->getParam('maintenance_mode')) {
            $helper->turnOffMaintenanceMode();
        }

        $this->getResponse()->setBody($response->toJson());
    }

    /**
     * Check Permissions for all actions
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('system/datareset' );
    }

    /**
     * Retrive adminhtml session model
     *
     * @return Mage_Adminhtml_Model_Session
     */
    protected function _getSession()
    {
        return Mage::getSingleton('adminhtml/session');
    }
}