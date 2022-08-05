<?php

namespace Cb\GreenlineApi\Ui\Component\Listing\Column;

use Magento\Framework\UrlInterface;

class SellerNameById extends \Magento\Ui\Component\Listing\Columns\Column {

    protected $mpHelper;

    public function __construct(
        \Magento\Framework\View\Element\UiComponent\ContextInterface $context,
        \Magento\Framework\View\Element\UiComponentFactory $uiComponentFactory,
        \Webkul\Marketplace\Helper\Data $mpHelper,
        UrlInterface $urlBuilder,
        array $components = [],
        array $data = []
    ){
        $this->mpHelper = $mpHelper;
        $this->urlBuilder = $urlBuilder;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    public function prepareDataSource(array $dataSource) {
        
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {

                if (isset($item['seller_id'])) {
                    $sellerObj = $this->mpHelper->getSellerDataBySellerId($item['seller_id'])->getFirstItem();
                    $item['seller_id'] = $sellerObj->getName();
                    /*$item['seller_id'] = "<a href='".$this->urlBuilder->getUrl('customer/index/edit', ['id' => $item['seller_id']])."' target='blank' title='".$sellerObj->getName()."'>".$sellerObj->getName()."</a>";*/
                }
            }
        }

        return $dataSource;
    }
}