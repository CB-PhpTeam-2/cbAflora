<?php
/**
 * @category  Webkul
 * @package   Webkul_AdvancedLayeredNavigation
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\AdvancedLayeredNavigation\Controller\Adminhtml\CarouselFilter;

use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Webkul\AdvancedLayeredNavigation\Model\ResourceModel\CarouselFilterAttributes\CollectionFactory;
use Webkul\AdvancedLayeredNavigation\Model\CarouselFilter;
use Webkul\AdvancedLayeredNavigation\Helper\Data as DataHelper;

class MassDelete extends \Magento\Backend\App\Action
{
    /**
     * Massactions filter.
     *
     * @var Filter
     */
    private $filter;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;
    /**
     * @var CarouselFilter
     */
    private $carouselFilter;

    /**
     * @param Context           $context
     * @param Filter            $filter
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        Context $context,
        DataHelper $helper,
        Filter $filter,
        CarouselFilter $carouselFilter,
        CollectionFactory $collectionFactory
    ) {
        $this->helper =  $helper;
        $this->carouselFilter = $carouselFilter;
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $collection = $this->filter->getCollection($this->collectionFactory->create());
        $collectionSize = $collection->getSize();
        foreach ($collection as $item) {
            $item->delete();
            $option = [];
            $option =  $this->carouselFilter->getCollection()
            ->addFieldToFilter('carousel_id', ['eq' =>$item->getId()]);
            if ($option->getSize()) {
                foreach ($option as $opt) {
                    $opt->delete();
                }
            }
        }
        $this->messageManager->addSuccess(__('A total of %1 element(s) have been deleted.', $collectionSize));
        $this->helper->cacheFlush();
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('*/*/');
    }

    /**
     * delete object.
     *
     * @return void
     */
    private function deleteObj($object)
    {
        $object->delete();
    }
}
