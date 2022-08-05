<?php
/**
 * @category  Webkul
 * @package   Webkul_AdvancedLayeredNavigation
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\AdvancedLayeredNavigation\Controller\Adminhtml\CarouselFilter;

use Webkul\AdvancedLayeredNavigation\Logger\Logger;
use Webkul\AdvancedLayeredNavigation\Helper\Data as DataHelper;
use Webkul\AdvancedLayeredNavigation\Model\CarouselFilterFactory;
use Webkul\AdvancedLayeredNavigation\Model\CarouselFilterAttributesFactory;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Filesystem;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\Filesystem\Driver\File;
use Webkul\AdvancedLayeredNavigation\Model\Storage\DbStorage;

class Save extends \Magento\Backend\App\Action
{

    /**
     * object of Filesystem
     * @var [type]
     */
    private $filesystem;

    /**
     * @var Webkul\Ebaymagentoconnect\Model\Storage\DbStorage
     */
    private $dbStorage;
    
    /**
     * @param Logger $logger
     * @param DataHelper $helper
     * @param CarouselFilterFactory $carouselFactory
     * @param CarouselFilterAttributesFactory $carouselAttributesFactory
     * @param Filesystem $filesystem
     * @param Context $context
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param TimezoneInterface $localeDate
     * @param File $file
     * @param DbStorage $dbStorage
     */
    public function __construct(
        Logger $logger,
        DataHelper $helper,
        CarouselFilterFactory $carouselFactory,
        CarouselFilterAttributesFactory $carouselAttributesFactory,
        Filesystem $filesystem,
        Context $context,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        TimezoneInterface $localeDate,
        File $file,
        DbStorage $dbStorage
    ) {
        $this->file = $file;
        $this->logger = $logger;
        $this->helper =  $helper;
        $this->carouselFactory = $carouselFactory;
        $this->carouselAttributesFactory = $carouselAttributesFactory;
        $this->date = $date;
        $this->filesystem = $filesystem;
        $this->fileUploaderFactory = $fileUploaderFactory;
        $this->localeDate = $localeDate;
        $this->storeManager = $storeManager;
        $this->dbStorage = $dbStorage;
        parent::__construct($context);
    }

    /**
     * @return void
     */
    public function execute()
    {
        try {
            $error = 0;
            $errorMsg = null;
            $resultRedirect = $this->resultRedirectFactory->create();
            $imageData = [];
            $isImage = 0;
            $data = $this->getRequest()->getParams();
            $id =  $this->getRequest()->getParam('id');
            $files = $this->getRequest()->getFiles();
            if ($id) {
                    $optionId = [];
                    $alreadOptionIds = [];
                    $alreadOptionIds = explode(',', $data['alreadOptionIds']);
                    $model = $this->carouselAttributesFactory->create()->load($id);
                    $model->setCategories($data['product']['category_ids']);
                    $model->setTitle($data['title']);
                    $model->setEnable($data['enable']);
                    $model->save();
                    $i = 0;
                if (!empty($data['ids'])) {
                    foreach ($data['ids'] as $checkboxId) {
                        $optionId[] = $checkboxId;
                        $this->checkAllreadyAdded($checkboxId, $id, $files, $data['attributeName'][$i]);
                        $i++;
                    }
                }
                if ($alreadOptionIds) {
                    $this->removeOption($alreadOptionIds, $optionId, $id);
                }
            } else {
                $uniqueCategoryCheck =  $this->carouselAttributesFactory->create()
                ->getCollection()
                ->addFieldToFilter('categories', $data['product']['category_ids']);
                if ($uniqueCategoryCheck->getSize()) {
                    $error = 1;
                } else {
                    $id = $this->carouselAdd($data);
                }
            }
            $this->messageShow($error, $id);
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
            $this->logger->addError("Controller=Save Error= ".$e->getMessage());
        }
        if ($id && isset($data['back'])) {
            return $resultRedirect->setPath('advancedlayerednavigation/carouselfilter/edit/id/'.$id);
        } else {
            return $resultRedirect->setPath('*/*/');
        }
    }

    public function removeOption($alreadOptionIds, $optionId, $id)
    {
        if ($optionId) {
            $deletedOption = array_diff($alreadOptionIds, $optionId);
        } else {
            $deletedOption = $alreadOptionIds;
        }
        foreach ($deletedOption as $del) {
             $this->carouselFactory->create()->getCollection()
                ->addFieldToFilter('attribute_option_id', $del)
                ->addFieldToFilter('carousel_id', $id)
                ->getFirstItem()
                ->delete();
        }
    }

    public function checkAllreadyAdded($checkbox, $id, $files, $optionName)
    {
        $time = $this->localeDate->date()->format('Y-m-d H:i:s');
        $checkInsert  = '';
        $checkInsert = $this->carouselFactory->create()->getCollection()
                        ->addFieldToFilter('attribute_option_id', $checkbox)
                        ->addFieldToFilter('carousel_id', $id)->getFirstItem();
        if (!empty($checkInsert->getData())) {
            $alreadyAdded = 1;
                $imageData =  $this->imageUpload($files, $checkbox, $alreadyAdded, $checkInsert->getId());
        } else {
            $imageData =  $this->imageUpload($files, $checkbox);
            $optionData = $this->carouselFactory->create();
            $optionData->setImagePath($imageData);
            $optionData->setCarouselId($id);
            $optionData->setAttributeOptionId($checkbox);
            $optionData->setOptionName($optionName);
            $optionData->setCreatedAt($time);
            $optionData->save();
        }
    }

    /**
     * function for carousel add
     */

    public function carouselAdd($data)
    {
        $uniqueCategoryCheck =  $this->carouselAttributesFactory->create()
        ->getCollection()
        ->addFieldToFilter('categories', $data['product']['category_ids']);
        if ($uniqueCategoryCheck->getSize()) {
             $error = 1;
        } else {
            $model = $this->carouselAttributesFactory->create();
            $model->setAttributeCode($data['attribute_code']);
            $model->setCategories($data['product']['category_ids']);
            $model->setTitle($data['title']);
            $model->setEnable($data['enable']);
            $model->save()->getId();
            return $model->getId();
        }
    }

    /**
     * function for messageShow
     */

    public function messageShow($error, $id)
    {
        if ($error) {
            $this->messageManager->addError(__('Please select another category.'));
        } else {
            if ($id) {
                $this->messageManager->addSuccess(__('Carousel item updated successfully.'));
            } else {
                $this->messageManager->addSuccess(__('Carousel item saved successfully.'));
            }
            $this->helper->cacheFlush();
        }
    }

    /**
     * function for image upload
     */

    public function imageUpload($files, $checkbox, $allreadyAdded = null, $checkId = null)
    {
        $imageData['file-'.$checkbox] = '';
        if ($files['image-'.$checkbox]['name']) {
            $imageUploadPath = $this->filesystem->getDirectoryRead(
                DirectoryList::MEDIA
            )->getAbsolutePath('advancedlayerednavigation/');
            if (!($this->file->isDirectory($imageUploadPath))) {
                $this->file->createDirectory($imageUploadPath, 0755, true);
            }
            $uploader = $this->fileUploaderFactory->create(['fileId' =>$files['image-'.$checkbox]]);
            $uploader->setAllowedExtensions(['jpg', 'jpeg', 'gif', 'png']);
            $uploader->setAllowRenameFiles(true);
            $uploader->setFilesDispersion(false);
            $imageData = $uploader->validateFile();
            $info = getimagesize($imageData['tmp_name']);
            if ($info) {
                $resultLogo = $uploader->save($imageUploadPath);
                if ($resultLogo['file']) {
                    $imageData['file-'.$checkbox] = 'advancedlayerednavigation/'.$resultLogo['file'];
                }
            } else {
                $errorMsg = __('Image file is not correct.');
            }
            if ($allreadyAdded) {
                $optionData = $this->carouselFactory->create()->load($checkId);
                $optionData->setImagePath($imageData['file-'.$checkbox]);
                $optionData->save();
            }
        }
        return $imageData['file-'.$checkbox];
    }
}
