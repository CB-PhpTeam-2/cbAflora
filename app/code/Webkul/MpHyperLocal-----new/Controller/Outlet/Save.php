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
declare(strict_types=1);
namespace Webkul\MpHyperLocal\Controller\Outlet;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Validation\ValidationException;
use Magento\InventoryApi\Api\Data\SourceInterface;
use Magento\InventoryApi\Api\Data\SourceInterfaceFactory;
use Magento\InventoryApi\Api\SourceRepositoryInterface;
use Magento\InventoryAdminUi\Model\Source\SourceHydrator;
use Magento\Framework\Controller\Result\Redirect;
use Exception;

class Save extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Webkul\Marketplace\Helper\Data
     */
    protected $helperData;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * @var \Webkul\MpHyperLocal\Model\OutletFactory
     */
    protected $outletFactory;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $_resultPageFactory;

    /**
     * @var \Magento\Framework\Data\Form\FormKey\Validator
     */
    protected $_formKeyValidator;

    /**
     * @var SourceInterfaceFactory
     */
    private $sourceFactory;

    /**
     * @var SourceRepositoryInterface
     */
    private $sourceRepository;

    /**
     * @var SourceHydrator
     */
    private $sourceHydrator;

    /**
     * @param FormKeyValidator $formKeyValidator
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Webkul\Marketplace\Helper\Data $helperData
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Webkul\MpHyperLocal\Model\OutletFactory $outletFactory
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Customer\Model\Url $modelUrl
     * @param SourceInterfaceFactory $sourceFactory
     * @param SourceRepositoryInterface $sourceRepository
     * @param SourceHydrator $sourceHydrator
     */
    public function __construct(
        \Magento\Framework\Registry $coreRegistry,
        \Webkul\Marketplace\Helper\Data $helperData,
        \Magento\Framework\App\Action\Context $context,
        \Webkul\MpHyperLocal\Model\OutletFactory $outletFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Customer\Model\Url $modelUrl,
        FormKeyValidator $formKeyValidator,
        SourceInterfaceFactory $sourceFactory,
        SourceRepositoryInterface $sourceRepository,
        SourceHydrator $sourceHydrator,
        \Magento\Framework\App\Request\DataPersistorInterface $dataPersistor
    ) {
        $this->helperData        = $helperData;
        $this->coreRegistry      = $coreRegistry;
        $this->outletFactory     = $outletFactory;
        $this->_customerSession  = $customerSession;
        $this->_resultPageFactory = $resultPageFactory;
        $this->modelUrl          = $modelUrl;
        $this->_formKeyValidator = $formKeyValidator;
        $this->sourceFactory = $sourceFactory;
        $this->sourceRepository = $sourceRepository;
        $this->sourceHydrator = $sourceHydrator;
        $this->dataPersistor = $dataPersistor;
        parent::__construct($context);
    }

    /**
     * Check customer authentication.
     *
     * @param RequestInterface $request
     *
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function dispatch(RequestInterface $request)
    {
        $loginUrl = $this->modelUrl->getLoginUrl();

        if (!$this->_customerSession->authenticate($loginUrl)) {
            $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);
        }
        return parent::dispatch($request);
    }

    /**
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        if (!$this->getRequest()->isPost() ||
            !$this->_formKeyValidator->validate(
                $this->getRequest()
            )
        ) {
            return $resultRedirect->setPath(
                '*/*/',
                ['_secure' => $this->getRequest()->isSecure()]
            );
        }
        $helper     = $this->helperData;
        $isPartner  = $helper->isSeller();
        if ($isPartner == 1) {
            $request = $this->getRequest();
            $requestData = $request->getPost()->toArray();
            $sourceCode = $requestData['general'][SourceInterface::SOURCE_CODE];
            $this->getDataPersistor()->set('seller_source_data', $requestData['general']);
            if (isset($requestData['id']) && !$requestData['id']) {
                try {
                    $outletModel = $this->outletFactory->create()
                    ->getCollection()
                    ->addFieldToFilter('source_code', $sourceCode)
                    ->addFieldToFilter('seller_id', ['neq' => $requestData['seller_id']]);
                    $sourceModel = $this->sourceFactory->create()->load($sourceCode);
                    if ($outletModel->getSize() && $sourceModel->getSourceCode()) {
                        $this->messageManager->addErrorMessage(__('Code is already exist!'));
                        return $resultRedirect->setPath(
                            '*/*/new',
                            ['_secure' => $this->getRequest()->isSecure()]
                        );
                    }
                } catch (Exception $e) {
                    $this->messageManager->addErrorMessage(__('Could not save Outlet.'));
                    return $resultRedirect->setPath(
                        '*/*/new',
                        ['_secure' => $this->getRequest()->isSecure()]
                    );
                }
            }
            try {
                $source = $this->sourceRepository->get($sourceCode);
                if ($source->getPostcode() !== $requestData['general'][SourceInterface::POSTCODE]) {
                    // unset($requestData['general'][SourceInterface::LATITUDE]);
                    // unset($requestData['general'][SourceInterface::LONGITUDE]);
                    $source->setLatitude(null);
                    $source->setLongitude(null);
                }
            } catch (NoSuchEntityException $e) {
                $source = $this->sourceFactory->create();
            }
            try {
                $this->processSave($source, $requestData);
                $this->messageManager->addSuccessMessage(__('The Outlet has been saved.'));
                $this->getDataPersistor()->clear('seller_source_data');
                $this->outletUpdate($requestData, $sourceCode);
                return $this->resultRedirectFactory->create()->setPath(
                    '*/*/',
                    ['_secure' => $this->getRequest()->isSecure()]
                );
            } catch (ValidationException $e) {
                foreach ($e->getErrors() as $localizedError) {
                    $this->messageManager->addErrorMessage($localizedError->getMessage());
                }
            } catch (CouldNotSaveException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (Exception $e) {
                $this->messageManager->addErrorMessage(__('Could not save Source.'));
            }
            return $this->resultRedirectFactory->create()->setPath(
                '*/*/new',
                ['_secure' => $this->getRequest()->isSecure()]
            );
        } else {
            return $this->resultRedirectFactory->create()->setPath(
                'marketplace/account/becomeseller',
                ['_secure' => $this->getRequest()->isSecure()]
            );
        }
    }

    protected function outletUpdate($requestData, $sourceCode)
    {
        $outletModel = $this->outletFactory->create()
        ->getCollection()
        ->addFieldToFilter('source_code', $sourceCode)
        ->addFieldToFilter('seller_id', ['eq' => $requestData['seller_id']]);
        if ($outletModel->getSize()) {
            foreach ($outletModel as $outlet) {
                $outlet->setAddress($requestData['general']['address'])
                        ->setLatitude($requestData['general']['latitude'])
                        ->setLongitude($requestData['general']['longitude'])
                        ->setStatus($requestData['general']['enabled'])
                        ->setOutletName($requestData['general']['name'])
                       ->save();
            }

        } else {
            $outletModel = $this->outletFactory->create();
            $outletModel->setAddress($requestData['general']['address'])
                        ->setLatitude($requestData['general']['latitude'])
                        ->setLongitude($requestData['general']['longitude'])
                        ->setStatus($requestData['general']['enabled'])
                        ->setOutletName($requestData['general']['name'])
                        ->setSellerId($requestData['seller_id'])
                        ->setSourceCode($sourceCode)
                        ->save();
        }
    }

    /**
     * Hydrate data from request and save source.
     *
     * @param SourceInterface $source
     * @param array $requestData
     * @return void
     * @throws CouldNotSaveException
     * @throws ValidationException
     */
    private function processSave(SourceInterface $source, array $requestData)
    {
        $source = $this->sourceHydrator->hydrate($source, $requestData);

        $this->_eventManager->dispatch(
            'controller_action_inventory_populate_source_with_data',
            [
                'request' => $this->getRequest(),
                'source' => $source,
            ]
        );

        $this->sourceRepository->save($source);

        $this->_eventManager->dispatch(
            'controller_action_inventory_source_save_after',
            [
                'request' => $this->getRequest(),
                'source' => $source,
            ]
        );
    }

    /**
     * Retrieve data persistor
     *
     * @return \Magento\Framework\App\Request\DataPersistorInterface|mixed
     */
    protected function getDataPersistor()
    {
        return $this->dataPersistor;
    }
}
