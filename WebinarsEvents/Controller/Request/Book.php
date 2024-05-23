<?php

namespace Inclusive\WebinarsEvents\Controller\Request;

class Book extends \Magento\Framework\App\Action\Action
{
    const XML_PATH_RECIPIENT_EMAIL = 'trans_email/ident_general/email';
    const XML_PATH_RECIPIENT_NAME  = 'trans_email/ident_general/name';
    const XML_PATH_SENDER_EMAIL    = 'trans_email/ident_sales/email';
    const XML_PATH_SENDER_NAME     = 'trans_email/ident_sales/name';

    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilder
     */
    protected $_transportBuilder;

    /**
     * @var \Magento\Framework\Translate\Inline\StateInterface
     */
    protected $_inlineTranslation;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Framework\Escaper
     */
    protected $_escaper;

    /**
     * @var \Magento\Framework\Data\Form\FormKey
     */
    protected $_formKey;

    /**
     * @var \Magento\Catalog\Model\Product
     */
    protected $_product;

    /**
     * @var \Inclusive\WebinarsEvents\Helper\Event
     */
    protected $_event;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Data\Form\FormKey $formKey,
        \Magento\Framework\Escaper $escaper,
        \Magento\Catalog\Model\Product $product,
        \Inclusive\WebinarsEvents\Helper\Event $event
    )
    {
        parent::__construct($context);
        $this->_transportBuilder = $transportBuilder;
        $this->_inlineTranslation = $inlineTranslation;
        $this->_scopeConfig = $scopeConfig;
        $this->_storeManager = $storeManager;
        $this->_formKey = $formKey;
        $this->_escaper = $escaper;
        $this->_product = $product;
        $this->_event = $event;
    }

    /**
     * Post user question
     *
     * @return void
     * @throws \Exception
     */
    public function execute()
    {
        try {
            $post = $this->getRequest()->getPostValue();
            /** @var \Inclusive\WebinarsEvents\Model\Event\Schedule $bookingDate */
            $bookingDate = null;

            $productId = isset($post['product']) ? (int) $post['product'] : 0;
            if ($productId > 0) {
                $this->_product->load($productId);
            }

            if (!isset($post['form_key']) || $post['form_key'] != $this->_formKey->getFormKey()) {
                throw new \Exception("Invalid form key");
            }

            if ($this->_product->getId() && $this->_event->isEvent($this->_product)) {
                if (!\Zend_Validate::is(trim($post['email']), 'EmailAddress')) {
                    throw new \Exception("Error Booking date, invalid email");
                }

                if (!\Zend_Validate::is(trim($post['event-date']), 'NotEmpty')) {
                    throw new \Exception("Error Booking date, invalid booking date");
                }

                $dateKey = trim($post['event-date']);
                $schedule = $this->_product->getData('event_schedule');

                if (empty($schedule)) {
                    throw new \Exception("No Booking dates available");
                }

                foreach ($schedule as $item) {
                    /** @var \Inclusive\WebinarsEvents\Model\Event\Schedule $item */
                    if ($dateKey == $item->getValue()) {
                        $bookingDate = $item;
                    }
                }
            } else {
                throw new \Exception("No booking product found");
            }

            if (!$bookingDate) {
                throw new \Exception("Error Booking date");
            }
        } catch (\Exception $exception) {
            $this->messageManager->addError($exception->getMessage());
            $this->redirectBack();

            return;
        }

        $this->_inlineTranslation->suspend();

        try {
            $postObject = new \Magento\Framework\DataObject();

            $date = $bookingDate->getDate(true);
            if ($date instanceof \DateTime) {
                $date = $date->format('l d M Y');
            }

            $postObject
                ->setData('customerEmail', $post['email'])
                ->setData('eventDate', $date)
                ->setData('eventLocation', $bookingDate->getLocation())
                ->setData('eventName', $this->_product->getName());

            $transport = $this->_transportBuilder
                ->setTemplateIdentifier('new_event_book_request')
                ->setTemplateOptions(
                    [
                        'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                        'store' => \Magento\Store\Model\Store::DEFAULT_STORE_ID,
                    ]
                )
                ->setTemplateVars(['data' => $postObject])
                ->setFrom([
                    'name' => $this->getSenderName(),
                    'email' => $this->getSenderEmail(),
                ])
                ->addTo($this->getRecipientEmail(), $this->getRecipientName())
                ->getTransport();

            $transport->sendMessage();
            $this->_inlineTranslation->resume();
            $this->messageManager->addSuccess(
                __('Thanks for contacting us, after your request was processed we will contact you with more details.')
            );
            $this->redirectBack();

            return;
        } catch (\Exception $e) {
            $this->_inlineTranslation->resume();
            $this->messageManager->addError(__('We can\'t process your request right now. Sorry, that\'s all we know.'));
            $this->redirectBack();

            return;
        }
    }

    protected function redirectBack()
    {
        $referer = $this->_redirect->getRefererUrl();

        if ($this->_product->getId() && $this->_event->isEvent($this->_product) && $referer) {
            $this->getResponse()->setRedirect($referer);
        } else {
            $this->_redirect('home');
        }
    }

    protected function getSenderName()
    {
        return $this->_scopeConfig->getValue(static::XML_PATH_SENDER_NAME);
    }

    protected function getSenderEmail()
    {
        return $this->_scopeConfig->getValue(static::XML_PATH_SENDER_EMAIL);
    }

    protected function getRecipientName()
    {
        return $this->_scopeConfig->getValue(static::XML_PATH_RECIPIENT_NAME);
    }

    protected function getRecipientEmail()
    {
        return $this->_scopeConfig->getValue(static::XML_PATH_RECIPIENT_EMAIL);
    }
}