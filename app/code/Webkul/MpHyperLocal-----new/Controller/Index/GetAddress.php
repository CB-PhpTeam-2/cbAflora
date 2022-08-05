<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpHyperLocal
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpHyperLocal\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Webkul\MpHyperLocal\Helper\Data as HyperLocalHelper;

class GetAddress extends Action
{
    /**
     * @var HyperLocalHelper
     */
    private $helper;
    /**
     * @var JsonFactory
     */
    private $jsonFactor;
    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        HyperLocalHelper $helper,
        JsonFactory $jsonFactory
    ) {
        $this->helper = $helper;
        $this->jsonFactory = $jsonFactory;
        parent::__construct($context);
    }

    /**
     * Set Address
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $jsonFactory = $this->jsonFactory->create();
        $address = $this->helper->getSavedAddress();
        return $jsonFactory->setData($address);
    }
}
