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
namespace Webkul\MpHyperLocal\Block\Product;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Entity\Collection\AbstractCollection;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Catalog\Block\Product\ListProduct as BaseListProduct;
use Webkul\MpHyperLocal\Helper\Data as HyperLocalHelperData;

/**
 * Product list
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ListProduct extends BaseListProduct
{
    /**
     * @var \Webkul\MpHyperLocal\Helper\Data
     */
    private $helperData;

    /**
     * @param Context $context
     * @param \Magento\Framework\Data\Helper\PostHelper $postDataHelper
     * @param \Magento\Catalog\Model\Layer\Resolver $layerResolver
     * @param CategoryRepositoryInterface $categoryRepository
     * @param \Magento\Framework\Url\Helper\Data $urlHelper
     * @param HyperLocalHelperData $helperData,
     * @param ShipAreaFactory $shipArea,
     * @param MpProductFactory $mpProduct,
     * @param array $data
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Framework\Data\Helper\PostHelper $postDataHelper,
        \Magento\Catalog\Model\Layer\Resolver $layerResolver,
        CategoryRepositoryInterface $categoryRepository,
        \Magento\Framework\Url\Helper\Data $urlHelper,
        HyperLocalHelperData $helperData,
        array $data = []
    ) {
        $this->helperData = $helperData;
        parent::__construct(
            $context,
            $postDataHelper,
            $layerResolver,
            $categoryRepository,
            $urlHelper,
            $data
        );
    }

    /**
     * Retrieve loaded category collection
     *
     * @return AbstractCollection
     */
    protected function _getProductCollection()
    {
        $proCollection = parent::_getProductCollection();
        $savedAddress = $this->helperData->getSavedAddress();
        if ($savedAddress) {
            $sellerIds = $this->helperData->getNearestSellers();
            $allowedProList = $this->helperData->getNearestProducts($sellerIds);
            $proCollection->addAttributeToFilter('entity_id', ['in' => $allowedProList]);
        }
        $this->_productCollection = $proCollection;
        $this->_productCollection->getSize();
        return $proCollection;
    }
}
