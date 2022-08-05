<?php
/**
 * @category  Webkul
 * @package   Webkul_AdvancedLayeredNavigation
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\AdvancedLayeredNavigation\Controller\Adminhtml\CarouselFilter;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Json\Helper\Data as JsonHelperData;
use Webkul\AdvancedLayeredNavigation\Logger\Logger;
use Webkul\AdvancedLayeredNavigation\Helper\Data as DataHelper;

class Options extends \Magento\Backend\App\Action
{
    /**
     * @var JsonHelperData
     */
    private $jsonHelperData;

    /**
     * @param Context $context
     * @param JsonHelperData $jsonHelperData
     * @param Logger $logger
     * @param DataHelper $dataHelper
     */
    public function __construct(
        Context $context,
        JsonHelperData $jsonHelperData,
        Logger $logger,
        DataHelper $dataHelper
    ) {
        parent::__construct($context);
        $this->jsonHelperData = $jsonHelperData;
        $this->logger = $logger;
        $this->dataHelper = $dataHelper;
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        try {
            $params = $this->getRequest()->getParams();
            $result = $this->dataHelper->getOptions($params['attribute']);
            $this->getResponse()->representJson($this->jsonHelperData->jsonEncode($result));
        } catch (\Exception $e) {
            $this->logger->info('controller=Options : '. $e->getMessage());
        }
    }
}
