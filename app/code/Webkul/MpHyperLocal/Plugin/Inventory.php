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
namespace Webkul\MpHyperLocal\Plugin;

use Webkul\Marketplace\Helper\Data as MpHelper;
use Magento\InventoryApi\Api\SourceRepositoryInterface;

class Inventory
{
    /**
     * @var MpHelper
     */
    protected $mpHelper;

    /**
     * @var \Webkul\MpHyperLocal\Helper\Data
     */
    protected $helper;

    /**
     * @var SourceRepositoryInterface
     */
    protected $sourceRepository;

    /**
     * @param MpHelper $mpHelper
     * @param \Webkul\MpHyperLocal\Helper\Data $helper
     * @param \Webkul\MpHyperLocal\Model\OutletFactory $outletModel
     * @param SourceRepositoryInterface $sourceRepository
     * @param \Magento\Framework\Webapi\ServiceOutputProcessor $serviceOutputProcessor
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     */
    public function __construct(
        MpHelper $mpHelper,
        \Webkul\MpHyperLocal\Helper\Data $helper,
        \Webkul\MpHyperLocal\Model\OutletFactory $outletModel,
        SourceRepositoryInterface $sourceRepository,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Framework\Webapi\ServiceOutputProcessor $serviceOutputProcessor,
        \Magento\Framework\Json\Helper\Data $jsonHelper
    ) {
        $this->mpHelper = $mpHelper;
        $this->helper = $helper;
        $this->outletModel = $outletModel;
        $this->sourceRepository = $sourceRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->serviceOutputProcessor = $serviceOutputProcessor;
        $this->jsonHelper = $jsonHelper;
    }

    public function aroundGetSources(
        \Webkul\MpMSI\Block\Inventory $subject,
        callable $proceed
    ) {
        if ($this->helper->isEnabled()) {
            try {
                $sources = ['default'];
                $sellerId = $this->mpHelper->getCustomerId();
                $outletModel = $this->outletModel->create()
                                    ->getCollection()
                                    ->addFieldToFilter('seller_id', $sellerId);
                foreach ($outletModel as $outlet) {
                    $sources[] = $outlet->getSourceCode();
                }
                $searchCriteria = $this->searchCriteriaBuilder
                                        ->addFilter('source_code', $sources, 'in')
                                        ->create();
                $source = $this->sourceRepository->getList($searchCriteria);
                $sourceItems = $this->serviceOutputProcessor->convertValue(
                    $source,
                    \Magento\InventoryApi\Api\Data\SourceSearchResultsInterface::class
                );
                return $this->jsonHelper->jsonEncode($sourceItems);
            } catch (\Exception $e) {
                $this->mpHelper->logDataInLogger(
                    "Inventory Plugin aroundGetSources : ".$e->getMessage()
                );
            }
        }
        return $proceed();
    }
}
