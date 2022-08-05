<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_AdvancedLayeredNavigation
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\AdvancedLayeredNavigation\Block;

class Carousel extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Webkul\AdvancedLayeredNavigation\Helper\Data
     */
    private $dataHelper;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Webkul\AdvancedLayeredNavigation\Model\CarouselFilterFactory $carouselFactory
     * @param \Webkul\AdvancedLayeredNavigation\Logger\Logger $logger
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Webkul\AdvancedLayeredNavigation\Model\CarouselFilterFactory $carouselFactory,
        \Webkul\AdvancedLayeredNavigation\Logger\Logger $logger,
        \Webkul\AdvancedLayeredNavigation\Helper\Data $dataHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->carouselFactory = $carouselFactory;
        $this->logger = $logger;
        $this->dataHelper = $dataHelper;
    }

     /**
      * get carousel table data
      *
      * @return array
      */
    public function getCarouselItems()
    {
        try {
            $collection = $this->carouselFactory->create()->getCollection();
            return $collection;
        } catch (\Exception $e) {
            $this->logger->addError("Block=Carousel function=getCarouselItems Error= ".$e->getMessage());
        }
    }
    /**
     * get admin configuration values
     *
     * @return array
     */
    public function getConfigure()
    {
        $config = [];
        try {
            return $this->dataHelper->getConfigure();
        } catch (\Exception $e) {
            $this->logger->addError("Block=Carousel function= Error=getConfigure ".$e->getMessage());
        }
        return $config;
    }

    /**
     * enable feature
     *
     * @return boolean
     */
    public function enableChoiceAttribute()
    {
        return $this->_scopeConfig->getValue(
            'advancedlayerednavigation/multi_assign/choice_attribute_item',
            'websites'
        );
    }
}
