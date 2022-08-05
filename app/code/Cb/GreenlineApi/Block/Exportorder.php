<?php

namespace Cb\GreenlineApi\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Webkul\Marketplace\Helper\Data as Mphelper;
use Magento\InventoryApi\Api\Data\SourceInterfaceFactory;
use Magento\Framework\App\Request\DataPersistorInterface;
use Cb\GreenlineApi\Model\GreenlineCredentialFactory;

class Exportorder extends Template
{
    /**
     * @var Mphelper
     */
    protected $mpHelper;

    protected $greenlineCredentialFactory;

    /**
     * @var \Cb\GreenlineApi\Helper\Data
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
     * @param \Cb\GreenlineApi\Helper\Data $helper
     * @param CollectionFactory $countryCollection
     * @param SourceInterfaceFactory $sourceFactory
     * @param DataPersistorInterface $dataPersistor
     * @param array $data
     */
    public function __construct(
        Context $context,
        Mphelper $mpHelper,
        GreenlineCredentialFactory $greenlineCredentialFactory,
        \Cb\GreenlineApi\Helper\Data $helper,
        SourceInterfaceFactory $sourceFactory,
        DataPersistorInterface $dataPersistor,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->mpHelper = $mpHelper;
        $this->greenlineCredentialFactory = $greenlineCredentialFactory;
        $this->helper = $helper;
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
     * @return \Cb\GreenlineApi\Helper\Data
     */
    public function getHelper()
    {
        return $this->helper;
    }
    /**
     * get Greenline data by seller id
     *
     * @return array
     */
    public function getGreenlineDataBySellerId($sellerId)
    {
        $greenlineCollection = $this->greenlineCredentialFactory->create()
                                 ->getCollection()
                                 ->addFieldToFilter('seller_id', $sellerId);
        return $greenlineCollection;
    }

}
