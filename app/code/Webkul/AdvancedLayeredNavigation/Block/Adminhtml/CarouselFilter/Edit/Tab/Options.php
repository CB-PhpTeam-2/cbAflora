<?php
/**
 * @category  Webkul
 * @package   Webkul_AdvancedLayeredNavigation
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\AdvancedLayeredNavigation\Block\Adminhtml\CarouselFilter\Edit\Tab;

use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Webkul\AdvancedLayeredNavigation\Model\CarouselFilterAttributesFactory;
use Webkul\AdvancedLayeredNavigation\Model\CarouselFilterFactory;
use Magento\Framework\DB\Helper as FrameworkDbHelper;
use Magento\Framework\App\CacheInterface;
use Webkul\AdvancedLayeredNavigation\Helper\Data as DataHelper;

class Options extends Generic implements TabInterface
{
    /**
     * @var string
     */
    protected $_template = 'Webkul_AdvancedLayeredNavigation::attributeOptions.phtml';

    /**
     * @var Attribute
     */
    private $attributeFactory;

    /**
     * @var DataHelper
     */
    private $dataHelper;

    /**
     * @var CollectionFactory
     */
    private $categoryCollectionFactory;

    /**
     * @var FrameworkDbHelper
     */
    private $frameworkDbHelper;

    /**
     * @var CacheInterface
     */
    private $cacheInterface;

    /**
     * @var CarouselFilter
     */
    private $carouselFilter;

    /**
     * @var eavAttribute
     */
    
    private $eavAttribute;
    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param Attribute $attributeFactory
     * @param DataHelper $dataHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Eav\Model\ResourceModel\Entity\Attribute $eavAttribute,
        CarouselFilterFactory $carouselFilter,
        CarouselFilterAttributesFactory $carouselAttributesFactory,
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        DataHelper $dataHelper,
        array $data = []
    ) {
        $this->eavAttribute = $eavAttribute;
        $this->carouselFilter = $carouselFilter;
        $this->dataHelper = $dataHelper;
        $this->carouselAttributesFactory = $carouselAttributesFactory;
        parent::__construct($context, $registry, $formFactory, $data);
    }
    
    /**
     * Prepare form fields
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @return \Magento\Backend\Block\Widget\Form
     */
    protected function _prepareForm()
    {
        $model = $this->_coreRegistry->registry('carouselFilter');
        $form = $this->_formFactory->create(
            ['data' => ['id' => 'edit_form',
                        'enctype' => 'multipart/form-data',
                        'action' => $this->getData('action'),
                        'method' => 'post'
                        ]
                    ]
        );
        $this->setForm($form);
        return parent::_prepareForm();
    }

    /**
     * Prepare label for tab
     *
     * @return string
     */
    public function getTabLabel()
    {
        return __('Options');
    }
 
    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return __('Options');
    }
 
    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return true;
    }
 
    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return false;
    }
    
    public function options($id)
    {
        $carousel = $this->carouselAttributesFactory->create()->load($id);
        $allOptions = [];
        $allOptions = $this->dataHelper->getOptions($carousel->getAttributeCode());
        return $allOptions;
    }

    public function getAttributeId($attributeCode)
    {
        $attributeId = $this->eavAttribute->getIdByCode('product', $attributeCode);
        return $attributeId;
    }

    public function selectedOption($id)
    {
        $optionId = [];
        $allSelectedOption = $this->carouselFilter->create()
                            ->getCollection()
                            ->addFieldToFilter('carousel_id', $id);
        foreach ($allSelectedOption as $selectedOption) {
            $optionId[] = $selectedOption->getAttributeOptionId();
        }
        return $optionId;
    }

    public function optionData($id, $carouselId)
    {
        $allSelectedOption = $this->carouselFilter->create()->getCollection()
        ->addFieldToFilter('attribute_option_id', $id)
        ->addFieldToFilter('carousel_id', $carouselId)->getFirstItem();
         return $allSelectedOption;
    }
}
