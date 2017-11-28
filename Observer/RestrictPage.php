<?php
namespace DavidRobert\RestrictPage\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\UrlInterface;
use Magento\Cms\Helper\Page;
use \Magento\Customer\Model\Session;
use \Magento\Framework\App\Response\Http;
use \Magento\Framework\Message\ManagerInterface;
use \DavidRobert\RestrictPage\Helper\Data;

class RestrictPage implements ObserverInterface
{

	/**
	 * @var UrlInterface
	 */
	protected $_urlInterface;

	/**
	 * @var Page
	 */
	protected $_cmsPage;

	/**
	 * @var Session
	 */
	protected $_customerSession;

	/**
	 * @var Http
	 */
	protected $_http;

	/**
	 * @var ManagerInterface
	 */
	protected $_messageManager;

	/**
	 * @var Data
	 */
	protected $_dataHelper;

	/**
	 * @param \Magento\Framework\UrlInterface $urlInterface
	 * @param \Magento\Cms\Helper\Page $page
	 * @param \Magento\Customer\Model\Session $customerSession
	 * @param \Magento\Framework\App\Response\Http $http
	 * @param \Magento\Framework\Message\ManagerInterface $messageManager
	 */
	public function __construct(
		UrlInterface $urlInterface,
		Page $cmsPage,
		Session $customerSession,
		Http $http,
		ManagerInterface $messageManager,
		Data $dataHelper
	) {
		$this->_urlInterface = $urlInterface;
		$this->_cmsPage =  $cmsPage;
		$this->_customerSession = $customerSession;
		$this->_http = $http;
		$this->_messageManager = $messageManager;
		$this->_dataHelper = $dataHelper;
	}

	/**
	 *
	 * @param \Magento\Framework\Event\Observer $observer
	 * @return void
	 */
	public function execute(Observer $observer)
	{
		$pageId = $this->getCmsPage();
		$urlToCheck = $this->_cmsPage->getPageUrl($pageId);
		$currentUrl = $this->_urlInterface->getCurrentUrl();
		$message = 'Veuillez vous identifier pour voir cette page !';
		$loginUrl = $this->_urlInterface->getUrl(
			'customer/account/login',
					array('referer' => base64_encode($currentUrl))
		);

		if ($urlToCheck === $currentUrl) {
			if(!$this->_customerSession->isLoggedIn()) {
				$this->_messageManager->addWarning(__($message));
				$this->_http->setRedirect($loginUrl);
			}
		}
	}

	/**
	 *
	 * @return string
	 */
	public function getCmsPage()
	{
		return $this->_dataHelper->getGeneralConfig('cms_page');
	}
}