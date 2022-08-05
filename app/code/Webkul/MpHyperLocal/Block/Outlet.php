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
namespace Webkul\MpHyperLocal\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Webkul\Marketplace\Helper\Data as Mphelper;
use Magento\Directory\Model\ResourceModel\Country\CollectionFactory;
use Magento\InventoryApi\Api\Data\SourceInterfaceFactory;
use Magento\Framework\App\Request\DataPersistorInterface;

class Outlet extends Template
{
    /**
     * @var Mphelper
     */
    protected $mpHelper;

    /**
     * @var \Webkul\MpHyperLocal\Model\OutletFactory
     */
    protected $outletFactory;

    /**
     * @var \Webkul\MpHyperLocal\Helper\Data
     */
    protected $helper;

    /**
     * @var CollectionFactory
     */
    protected $_countryCollection;

    /**
     * @var SourceInterfaceFactory
     */
    protected $sourceFactory;

    /**
     * @var DataPersistorInterface
     */
    protected $dataPersistor;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param Mphelper $mpHelper
     * @param \Webkul\MpHyperLocal\Model\OutletFactory $outletFactory
     * @param \Webkul\MpHyperLocal\Helper\Data $helper
     * @param CollectionFactory $countryCollection
     * @param SourceInterfaceFactory $sourceFactory
     * @param DataPersistorInterface $dataPersistor
     * @param array $data
     */
    public function __construct(
        Context $context,
        Mphelper $mpHelper,
        \Webkul\MpHyperLocal\Model\OutletFactory $outletFactory,
        \Webkul\MpHyperLocal\Helper\Data $helper,
        CollectionFactory $countryCollection,
        SourceInterfaceFactory $sourceFactory,
        DataPersistorInterface $dataPersistor,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->mpHelper = $mpHelper;
        $this->outletFactory = $outletFactory;
        $this->helper = $helper;
        $this->_countryCollection = $countryCollection;
        $this->sourceFactory = $sourceFactory;
        $this->dataPersistor = $dataPersistor;
    }
    /**
     * Get Marketplace Helper
     *
     * @return \Webkul\Marketplace\Helper\Data
     */
    public function getMpHelper()
    {
        return $this->mpHelper;
    }
    /**
     * getHelper function
     *
     * @return \Webkul\MpHyperLocal\Helper\Data
     */
    public function getHelper()
    {
        return $this->helper;
    }
    /**
     * get outlet data by id
     *
     * @return array
     */
    public function getOutletDataById($id)
    {
        $outletCollection = $this->outletFactory->create()
                                 ->getCollection()
                                 ->addFieldToFilter('source_code', $id);
        $address = $outletCollection->getFirstItem()->getAddress();
        $sourceModel = $this->sourceFactory->create()->load($id)->getData();
        $sourceModel['address'] = $address;
        return $sourceModel;
    }
    /**
     * get country list
     * @return array $countryList
     */
    public function getCountryList()
    {
        $countryList = $this->_countryCollection
                        ->create()->loadByStore()
                        ->toOptionArray(true);
        return $countryList;
    }

    /**
     * Get Persistent Data for outlet
     *
     * @return array
     */
    public function getPersistentData()
    {
        $fields = (array)$this->dataPersistor->get('seller_source_data');
        if (empty($fields)) {
            $fields = [
                "source_code" => "",
                "name" => "",
                "enabled" => "",
                "description" => "",
                "latitude" => "",
                "longitude" => "",
                "country_id" => "",
                "region_id" => "",
                "region" => "",
                "city" => "",
                "street" => "",
                "postcode" => "",
                "contact_name" => "",
                "email" => "",
                "phone" => "",
                "fax" => "",
                "use_default_carrier_config" => "",
                "address" => ""
            ];
        }
        $this->dataPersistor->clear('seller_source_data');
        return $fields;
    }
}
