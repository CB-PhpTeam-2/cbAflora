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
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Webkul\AdvancedLayeredNavigation\Helper\Data as DataHelper;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
use Magento\Framework\DB\Helper as FrameworkDbHelper;
use Magento\Framework\App\CacheInterface;
use Magento\Catalog\Model\Category;
use Webkul\AdvancedLayeredNavigation\Model\CarouselFilterAttributesFactory;

class Main extends Generic implements TabInterface
{
    /**
     * @var string
     */
    protected $_template = 'Webkul_AdvancedLayeredNavigation::options.phtml';

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
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param Attribute $attributeFactory
     * @param DataHelper $dataHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        Attribute $attributeFactory,
        DataHelper $dataHelper,
        CarouselFilterAttributesFactory $carouselAttributesFactory,
        CollectionFactory $categoryCollectionFactory,
        FrameworkDbHelper $frameworkDbHelper,
        array $data = []
    ) {
        $this->carouselAttributesFactory = $carouselAttributesFactory;
        $this->attributeFactory = $attributeFactory;
        $this->dataHelper = $dataHelper;
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->frameworkDbHelper = $frameworkDbHelper;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Init form
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('badges_form');
        $this->setTitle(__('Badge Information'));
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
        return __('General');
    }
 
    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return __('General');
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

    /**
     * get attributes
     *
     * @return array
     */
    public function getAttributes()
    {
        $attributeArray = [];
        $attributeInfo = $this->attributeFactory
                              ->getCollection()
                              ->addFieldToFilter('frontend_input', 'select');
        $attributeArray[''] = __("Select");
        foreach ($attributeInfo as $attributes) {
            if ($this->attributeFactory->load($attributes->getAttributeId())->getIsFilterable() == 1) {
                $attributeArray[$attributes->getAttributeCode()] = $attributes->getFrontendLabel();
            }
        }
        return $attributeArray;
    }

    public function getCarouselDetail($id)
    {
        $detail = $this->carouselAttributesFactory->create()->load($id);
        return $detail;
    }

    /**
     * Retrieve categories tree
     *
     * @param string|null $filter
     * @return array
     */
    public function getCategoriesTree($filter = null)
    {
        $helper = $this->dataHelper;
        $categoryCollection = $this->categoryCollectionFactory->create();
        if ($filter !== null) {
            $categoryCollection->addAttributeToFilter(
                'name',
                ['like' => $this->frameworkDbHelper->addLikeEscape($filter, ['position' => 'any'])]
            );
        }

        $categoryCollection->addAttributeToSelect('path')
            ->addAttributeToFilter('entity_id', ['neq' => Category::TREE_ROOT_ID]);
        $shownCategoriesIds = [];

        /** @var \Magento\Catalog\Model\Category $category */
        foreach ($categoryCollection as $category) {
            foreach (explode('/', $category['path']) as $parentId) {
                $shownCategoriesIds[$parentId] = 1;
            }
        }
        $collection = $this->categoryCollectionFactory->create();
        $collection->addAttributeToFilter('entity_id', ['in' => array_keys($shownCategoriesIds)])
            ->addAttributeToSelect(['name', 'is_active', 'parent_id']);
        $sellerCategory = [
            Category::TREE_ROOT_ID => [
                'value' => Category::TREE_ROOT_ID,
                'optgroup' => null,
            ],
        ];

        foreach ($collection as $category) {
            $catId = $category->getId();
            $catParentId = $category->getParentId();
            foreach ([$catId, $catParentId] as $categoryId) {
                if (!isset($sellerCategory[$categoryId])) {
                    $sellerCategory[$categoryId] = ['value' => $categoryId];
                }
            }

            $sellerCategory[$catId]['is_active'] = $category->getIsActive();
            $sellerCategory[$catId]['label'] = $category->getName();
            $sellerCategory[$catParentId]['optgroup'][] = &$sellerCategory[$catId];
        }
       
        return json_encode($sellerCategory[Category::TREE_ROOT_ID]['optgroup']);
    }
}
