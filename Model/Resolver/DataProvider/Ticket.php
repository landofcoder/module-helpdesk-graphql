<?php
/**
 * Copyright © LandOfCoder All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Lof\HelpDeskGraphQl\Model\Resolver\DataProvider;

use Lof\HelpDesk\Api\CategoryRepositoryInterface;
use Lof\HelpDesk\Api\Data\TicketInterface;
use Lof\HelpDesk\Api\Data\TicketSearchResultsInterfaceFactory;
use Lof\HelpDesk\Api\DepartmentRepositoryInterface;
use Lof\HelpDesk\Api\TicketRepositoryInterface;
use Lof\HelpDesk\Helper\Data;
use Lof\HelpDesk\Model\ResourceModel\Spam\Collection as SpamCollection;
use Lof\HelpDesk\Model\ResourceModel\Ticket\CollectionFactory;
use Lof\HelpDesk\Model\Sender;
use Lof\HelpDesk\Model\TicketFactory;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
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
        Sender $sender
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
     */
    public function createTicket($data) {

        if ($data) {
            $ticketModel = $this->ticketFactory->create();
            $category = $this->categoryRepository->get($data['category_id']);

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

            $department = $this->departmentRepository->get($data['department_id']);
            $data['email_to'] = [];
            if (count($department) > 0) {
                foreach ($department['users'] as $key => $_user) {
                    $user->load($_user, 'user_id');
                    $data['email_to'][] = $user->getEmail();
                }
            }

            foreach ($this->spamCollection->addFieldToFilter('is_active', 1) as $key => $spam) {

                if ($this->helper->checkSpam($spam, $data)) {
                    return false;
                }
            }
            $ticketModel->setData($data)->save();
            if (count($data['email_to'])) {
                $this->sender->newTicket($data);
            }
            return $ticketModel;
        }
    }
}

