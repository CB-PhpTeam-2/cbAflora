<?php
/**
 * @author Aitoc Team
 * @copyright Copyright (c) 2019 Aitoc (https://www.aitoc.com)
 * @package Aitoc_DeleteOrders
 */

/**
 * Copyright © 2018 Aitoc. All rights reserved.
 */

namespace Aitoc\DeleteOrders\Block\Adminhtml\Rules\Edit;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;
use Magento\Backend\Block\Widget\Context;

/**
 * Class DeleteButton
 *
 * @package Aitoc\DeleteOrders\Block\Adminhtml\Rules\Edit
 */
class DeleteButton implements ButtonProviderInterface
{
    /**
     * Url Builder
     *
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $request;

    /**
     * DeleteButton constructor.
     *
     * @param Context $context
     */
    public function __construct(
        Context $context
    ) {
        $this->request = $context->getRequest();
        $this->urlBuilder = $context->getUrlBuilder();
    }

    /**
     * @return array
     */
    public function getButtonData()
    {
        $data      = [];
        $ruleId = $this->getRuleId();
        if ($ruleId) {
            $data = [
                'label'      => __('Delete'),
                'class'      => 'delete',
                'on_click'   => 'deleteConfirm(\'' .
                    __('Are you sure you want to delete the rule?') .
                    '\', \'' .
                    $this->urlBuilder->getUrl('deleteorders/rules/delete', ['entity_id' => $ruleId]) . '\')',
                'sort_order' => 20
            ];
        }

        return $data;
    }

    public function getRuleId()
    {
        if ($this->request->getParam('entity_id')) {
            return $this->request->getParam('entity_id');
        } else {
            return null;
        }
    }

    public function getUrl($route = '', $params = [])
    {
        return  $this->urlBuilder->getUrl($route, $params);
    }
}
