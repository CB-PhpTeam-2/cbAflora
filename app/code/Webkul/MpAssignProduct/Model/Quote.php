<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpAssignProduct
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpAssignProduct\Model;

use Webkul\MpAssignProduct\Api\Data\QuoteInterface;
use Magento\Framework\DataObject\IdentityInterface;

class Quote extends \Magento\Framework\Model\AbstractModel implements QuoteInterface, IdentityInterface
{
    /**
     * No route page id.
     */
    const NOROUTE_ENTITY_ID = 'no-route';

    /**
     * Assign Product Quote cache tag.
     */
    const CACHE_TAG = 'mpassignproduct_quote';

    /**
     * @var string
     */
    protected $_cacheTag = 'mpassignproduct_quote';

    /**
     * Prefix of model events names.
     *
     * @var string
     */
    protected $_eventPrefix = 'mpassignproduct_quote';

    /**
     * Initialize resource model.
     */
    protected function _construct()
    {
        $this->_init(\Webkul\MpAssignProduct\Model\ResourceModel\Quote::class);
    }

    /**
     * Load object data.
     *
     * @param int|null $id
     * @param string   $field
     *
     * @return $this
     */
    public function load($id, $field = null)
    {
        if ($id === null) {
            return $this->noRouteQuote();
        }

        return parent::load($id, $field);
    }

    /**
     * Load No-Route Quote.
     *
     * @return \Webkul\MpAssignProduct\Model\Quote
     */
    public function noRouteQuote()
    {
        return $this->load(self::NOROUTE_ENTITY_ID, $this->getIdFieldName());
    }

    /**
     * Get identities.
     *
     * @return array
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG.'_'.$this->getId()];
    }

    /**
     * Get ID.
     *
     * @return int
     */
    public function getId()
    {
        return parent::getData(self::ENTITY_ID);
    }

    /**
     * Set ID.
     *
     * @param int $id
     *
     * @return \Webkul\MpAssignProduct\Api\Data\QuoteInterface
     */
    public function setId($id)
    {
        return $this->setData(self::ENTITY_ID, $id);
    }
}
