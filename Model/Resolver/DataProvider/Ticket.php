<?php
/**
 * Copyright © LandOfCoder All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Lof\HelpDeskGraphQl\Model\Resolver\DataProvider;

use Exception;
use Lof\HelpDesk\Api\CategoryRepositoryInterface;
use Lof\HelpDesk\Api\Data\TicketInterface;
use Lof\HelpDesk\Api\Data\TicketSearchResultsInterfaceFactory;
use Lof\HelpDesk\Api\DepartmentRepositoryInterface;
use Lof\HelpDesk\Api\TicketRepositoryInterface;
use Lof\HelpDesk\Helper\Data;
use Lof\HelpDesk\Model\Attachment;
use Lof\HelpDesk\Model\DepartmentFactory;
use Lof\HelpDesk\Model\Like;
use Lof\HelpDesk\Model\MessageFactory;
use Lof\HelpDesk\Model\ResourceModel\Spam\Collection as SpamCollection;
use Lof\HelpDesk\Model\ResourceModel\Ticket\CollectionFactory;
use Lof\HelpDesk\Model\Sender;
use Lof\HelpDesk\Model\TicketFactory;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Store\Model\StoreManagerInterface;
use Magento\User\Model\UserFactory;

/**
 * Class Ticket
 * @package Lof\HelpDeskGraphQl\Model\Resolver\DataProvider
 */
class Ticket
{

    /**
     * @var TicketRepositoryInterface
     */
    private $ticketRepository;
    /**
     * @var CollectionFactory
     */
    private $ticketCollection;
    /**
     * @var CollectionProcessorInterface
     */
    private $collectionProcessor;
    /**
     * @var TicketSearchResultsInterfaceFactory
     */
    private $searchResultsFactory;
    /**
     * @var TicketFactory
     */
    private $ticketFactory;
    /**
     * @var CategoryRepositoryInterface
     */
    private $categoryRepository;
    /**
     * @var UserFactory
     */
    private $userFactory;
    /**
     * @var StoreManagerInterface
     */
    private $storeManagement;
    /**
     * @var Data
     */
    private $helper;
    /**
     * @var DepartmentRepositoryInterface
     */
    private $departmentRepository;
    /**
     * @var SpamCollection
     */
    private $spamCollection;
    /**
     * @var Sender
     */
    private $sender;
    /**
     * @var MessageFactory
     */
    private $messageFactory;
    /**
     * @var Attachment
     */
    private $attachment;
    /**
     * @var DepartmentFactory
     */
    private $departmentFactory;
    /**
     * @var Like
     */
    private $_like;

    /**
     * Ticket constructor.
     * @param TicketRepositoryInterface $ticketRepository
     * @param CollectionFactory $ticketCollection
     * @param CollectionProcessorInterface $collectionProcessor
     * @param TicketSearchResultsInterfaceFactory $searchResultsFactory
     * @param TicketFactory $ticketFactory
     * @param CategoryRepositoryInterface $categoryRepository
     * @param UserFactory $userFactory
     * @param StoreManagerInterface $storeManagement
     * @param Data $helper
     * @param DepartmentRepositoryInterface $departmentRepository
     * @param SpamCollection $spamCollection
     * @param Sender $sender
     * @param MessageFactory $messageFactory
     * @param Attachment $attachment
     * @param DepartmentFactory $departmentFactory
     * @param Like $like
     */
    public function __construct(
        TicketRepositoryInterface $ticketRepository,
        CollectionFactory $ticketCollection,
        CollectionProcessorInterface $collectionProcessor,
        TicketSearchResultsInterfaceFactory $searchResultsFactory,
        TicketFactory $ticketFactory,
        CategoryRepositoryInterface $categoryRepository,
        UserFactory $userFactory,
        StoreManagerInterface $storeManagement,
        Data $helper,
        DepartmentRepositoryInterface $departmentRepository,
        SpamCollection $spamCollection,
        Sender $sender,
        MessageFactory $messageFactory,
        Attachment $attachment,
        DepartmentFactory $departmentFactory,
        Like $like
    )
    {
        $this->ticketRepository = $ticketRepository;
        $this->ticketCollection = $ticketCollection;
        $this->collectionProcessor = $collectionProcessor;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->ticketFactory = $ticketFactory;
        $this->categoryRepository = $categoryRepository;
        $this->userFactory = $userFactory;
        $this->storeManagement = $storeManagement;
        $this->helper = $helper;
        $this->departmentRepository = $departmentRepository;
        $this->spamCollection = $spamCollection;
        $this->sender = $sender;
        $this->messageFactory = $messageFactory;
        $this->attachment = $attachment;
        $this->departmentFactory = $departmentFactory;
        $this->_like = $like;
    }

    /**
     * @param $ticket_id
     * @return TicketInterface
     */
    public function getTicketById($ticket_id)
    {
        try {
            return $this->ticketRepository->get($ticket_id);
        } catch (LocalizedException $e) {
        }
    }

    /**
     * @param $criteria
     * @return \Lof\HelpDesk\Api\Data\TicketSearchResultsInterface
     */
    public function getListTickets($criteria) {
        $collection = $this->ticketCollection->create();

        $this->collectionProcessor->process($criteria, $collection);

        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($criteria);

        $items = [];
        foreach ($collection as $key => $model) {
            $model->load($model->getTicketId());
            $items[$key] = $model->getData();
        }

        $searchResults->setItems($items);
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults;
    }

    /**
     * @param $data
     * @return false|\Lof\HelpDesk\Model\Ticket
     * @throws LocalizedException
     * @throws NoSuchEntityException
     * @throws Exception
     */
    public function createTicket($data) {

        if ($data) {
            $ticketModel = $this->ticketFactory->create();
            $category = $this->categoryRepository->get($data['category_id']);
            if (!$category) {
                throw new GraphQlInputException(__('Category Id does not exists'));
            }
            $user = $this->userFactory->create();
            $store = $this->storeManagement;
            $data['store_id'] = $store->getStore()->getId();
            $data['status_id'] = 1;
            $data['last_reply_name'] = $data['customer_name'];
            $data['reply_cnt'] = 0;
            $data['category'] = $category['title'];
            $data['namestore'] = $this->helper->getStoreName();
            $data['urllogin'] = $this->helper->getCustomerLoginUrl();
            $data['department_id'] = $this->helper->getDepartmentByCategory($data['category_id']);
            $data['status'] = $this->getStatus($data['status_id']);
            $department = $this->departmentRepository->get($data['department_id']);
            $data['department'] = $department->getTitle();
            $data['email_to'] = [];
            if (count($department) > 0) {
                foreach ($department['users'] as $key => $_user) {
                    $user->load($_user, 'user_id');
                    $data['email_to'][] = $user->getEmail();
                }
            }

            if($this->isSpam($data)) return false;

            $ticketModel->setData($data)->save();
            if (count($data['email_to'])) {
                $this->sender->newTicket($data);
            }
            return $ticketModel;
        }
    }

    /**
     * @param $status_id
     * @return \Magento\Framework\Phrase|string
     */
    protected function getStatus($status_id){
        $data = '';
        if ($status_id == 0) {
            $data = __('Close');
        } elseif ($status_id == 1) {
            $data = __('Open');
        } elseif ($status_id == 2) {
            $data = __('Processing');
        } elseif ($status_id == 3) {
            $data = __('Done');
        }
        return $data;
    }

    /**
     * @param $data
     * @return bool|\Lof\HelpDesk\Model\Message
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function SendMessageTicket($data)
    {
        if ($data) {
            $messageModel = $this->messageFactory->create();
            if($this->isSpam($data)) return false;
            $messageModel->setData($data)->save();
            $data = $this->updateData($data);
            if ($this->helper->getConfig('email_settings/enable_testmode')) {
                $this->sender->newMessage($data);
            }

            $attachmentData = [];
            $attachmentData['message_id'] = $messageModel->getId();
            $attachmentData['body'] = $data['attachment'];
            $attachmentData['name'] = $data['attachment_name'];
            $this->attachment->setData($attachmentData)->save();
            return $messageModel;
        }
    }

    /**
     * @param $data
     * @return Like
     * @throws Exception
     */
    public function LikeTicket($data)
    {
        if($data) {
            $like = $this->_like->load($data['message_id'], 'message_id');
            $like->setData('customer_id', $data['customer_id'])->setData('message_id', $data['message_id'])->save();
            return $like;
        }
    }

    /**
     * @param $data
     * @return \Lof\HelpDesk\Model\Ticket
     */
    public function RateTicket($data)
    {
        if($data) {
            $ticket = $this->ticketFactory->create()->load($data['ticket_id']);
            $ticket->setRating($data['rating'])->save();
            return $ticket;
        }
    }

    /**
     * @param $data
     * @return bool
     */
    public function isSpam($data){
        foreach ($this->spamCollection->addFieldToFilter('is_active', 1) as $key => $spam) {
            if ($this->helper->checkSpam($spam, $data)) {
                return true;
            }
        }
    }

    /**
     * @param $data
     * @return mixed
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function updateData($data) {
        $store = $this->storeManagement;
        $ticket = $this->ticketRepository->get($data['ticket_id']);
        $category = $this->categoryRepository->get($ticket['category_id']);
        $data['nameticket'] = $ticket['subject'];
        $data['category'] = $category['title'];
        $data['store_id'] = $store->getStore()->getId();
        $data['namestore'] = $this->helper->getStoreName();
        $data['urllogin'] = $this->helper->getCustomerLoginUrl();
        $user = $this->userFactory->create();
        $department = $this->departmentFactory->create();
        foreach ($department->getCollection() as $key => $_department) {
            $dataDepartment = $department->load($_department->getDepartmentId())->getData();
            if (in_array($ticket['category_id'], $dataDepartment['category_id']) && $dataDepartment['is_active'] == 1 && (in_array($data['store_id'], $dataDepartment['store_id']) || in_array(0, $dataDepartment['store_id']))) {
                $data['email_to'] = [];
                foreach ($dataDepartment['users'] as $key => $_user) {
                    $user->load($_user, 'user_id');
                    $data['email_to'][] = $user->getEmail();
                }
            }
        }
        return $data;
    }
}

