<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_SellerSubAccount
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\SellerSubAccount\Ui\DataProvider\SubAccount;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Session\SessionManagerInterface;
use Webkul\SellerSubAccount\Model\ResourceModel\SubAccount\Collection;
use Webkul\SellerSubAccount\Model\ResourceModel\SubAccount\CollectionFactory;
use Webkul\SellerSubAccount\Helper\Data as Helper;

/**
 * Class DataProvider data for subAccount
 *
 */
class DataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * @var Collection
     */
    public $collection;

    /**
     * @var array
     */
    public $loadedData;

    /**
     * @var Helper
     */
    public $helper;

    /**
     * @var SessionManagerInterface
     */
    public $session;

    /**
     * Constructor.
     *
     * @param string            $name
     * @param string            $primaryFieldName
     * @param string            $requestFieldName
     * @param CollectionFactory $collectionFactory
     * @param Helper            $helper
     * @param \Magento\Framework\Session\SessionManagerInterface $sessionManager
     * @param array             $meta
     * @param array             $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        Helper $helper,
        \Magento\Framework\Session\SessionManagerInterface $sessionManager,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->_helper = $helper;
        $this->sessionManager = $sessionManager;
        $this->collection = $collectionFactory->create();
        $this->collection->addFieldToSelect('*');
    }

    /**
     * Get session object.
     *
     * @return SessionManagerInterface
     */
    protected function getSession()
    {
        if ($this->session === null) {
            $this->session = $this->sessionManager;
        }

        return $this->session;
    }

    /**
     * Get data.
     *
     * @return array
     */
    public function getData()
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }
        $items = $this->collection->getItems();
        /** @var SubAccount $subAccount */
        foreach ($items as $subAccount) {
            $result['sub_account'] = $subAccount->getData();
            $this->loadedData[$subAccount->getId()] = $result;
            $id = $subAccount->getId();
            $customerData = $this->_helper->getCustomerById($subAccount->getCustomerId());
            $firstname = $customerData->getFirstname();
            $lastname = $customerData->getLastname();
            $email = $customerData->getEmail();
            $this->loadedData[$id]['sub_account']['firstname'] = $firstname;
            $this->loadedData[$id]['sub_account']['lastname'] = $lastname;
            $this->loadedData[$id]['sub_account']['email'] = $email;
        }
        return $this->loadedData;
    }
}
