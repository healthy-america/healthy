<?php
namespace Aventi\CustomerRegister\Observer;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Message\ManagerInterface;

class ValidateNameLastNameObserver implements ObserverInterface
{
    /**
     * @var ManagerInterface
     */
    protected ManagerInterface $messageManager;

    /**
     * @var RequestInterface
     */
    protected RequestInterface $request;

    /**
     * @param ManagerInterface $messageManager
     * @param RequestInterface $request
     */
    public function __construct(
        ManagerInterface $messageManager,
        RequestInterface $request
    ) {
        $this->messageManager = $messageManager;
        $this->request = $request;
    }

    /**
     * Validate the name and last name fields.
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $post = $this->request->getPostValue();
        $firstName = isset($post['firstname']) ? trim($post['firstname']) : '';
        $lastName = isset($post['lastname']) ? trim($post['lastname']) : '';

        if (is_numeric($firstName) || is_numeric($lastName)) {
            $this->messageManager->addErrorMessage(__('The name and last name fields cannot be just numbers.'));
            $this->request->setPostValue('firstname', '');
            $this->request->setPostValue('lastname', '');
            $this->request->setParam('firstname', '');
            $this->request->setParam('lastname', '');
        }
    }
}
