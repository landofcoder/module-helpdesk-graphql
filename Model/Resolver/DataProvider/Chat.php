<?php
/**
 * Copyright © LandOfCoder All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Lof\HelpDeskGraphQl\Model\Resolver\DataProvider;

use Lof\HelpDesk\Api\CategoryRepositoryInterface;
use Lof\HelpDesk\Api\Data\ChatMessageSearchResultsInterfaceFactory;
use Lof\HelpDesk\Api\ChatRepositoryInterface;
use Lof\HelpDesk\Helper\Data;
use Lof\HelpDesk\Model\ChatMessageFactory;
use Lof\HelpDesk\Model\ResourceModel\Chat\CollectionFactory;
use Lof\HelpDesk\Model\ChatFactory;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\User\Model\UserFactory;
use Lof\HelpDesk\Model\ResourceModel\ChatMessage\CollectionFactory as ChatMessageCollection;

/**
 * Class Chat
 * @package Lof\HelpDeskGraphQl\Model\Resolver\DataProvider
 */
class Chat
{

    /**
     * @var ChatRepositoryInterface
     */
    private $chatRepository;
    /**
     * @var CollectionFactory
     */
    private $chatCollection;
    /**
     * @var CollectionProcessorInterface
     */
    private $collectionProcessor;
    /**
     * @var ChatMessageSearchResultsInterfaceFactory
     */
    private $searchResultsFactory;
    /**
     * @var ChatFactory
     */
    private $chatFactory;
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
     * @var ChatMessageFactory
     */
    private $messageFactory;
    /**
     * @var ChatMessageCollection
     */
    private $chatMessageCollection;


    public function __construct(
        ChatRepositoryInterface $chatRepository,
        CollectionFactory $chatCollection,
        CollectionProcessorInterface $collectionProcessor,
        ChatMessageSearchResultsInterfaceFactory $searchResultsFactory,
        ChatFactory $chatFactory,
        CategoryRepositoryInterface $categoryRepository,
        UserFactory $userFactory,
        StoreManagerInterface $storeManagement,
        Data $helper,
        ChatMessageFactory $messageFactory,
        ChatMessageCollection $chatMessageCollection

    )
    {
        $this->chatRepository = $chatRepository;
        $this->chatCollection = $chatCollection;
        $this->collectionProcessor = $collectionProcessor;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->chatFactory = $chatFactory;
        $this->categoryRepository = $categoryRepository;
        $this->userFactory = $userFactory;
        $this->storeManagement = $storeManagement;
        $this->helper = $helper;
        $this->messageFactory = $messageFactory;
        $this->chatMessageCollection = $chatMessageCollection;
    }

    /**
     * @param $data
     * @return \Lof\HelpDesk\Model\ChatMessage
     * @throws \Exception
     */
    public function chat($data)
    {
        $data['chat_id'] = $this->getChatId($data);
        $chatMessage = $this->messageFactory->create()->setData($data)->save();
        $chat = $this->chatFactory->create()->load($data['chat_id']);
        $number_message = $chat->getData('number_message') + 1;
        $chat->setData('is_read', 1)->setData('number_message', $number_message)->save();
        return $chatMessage;
    }


    /**
     * @param $criteria
     * @return \Lof\HelpDesk\Api\Data\ChatSearchResultsInterface
     */
    public function getListChatMessages($criteria) {
        $collection = $this->chatMessageCollection->create();
        $data = [];
        $chatId = $this->getChatId($data);
        $collection->addFieldToFilter('chat_id',$chatId);
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
     * @return array|mixed|null
     * @throws \Exception
     */
    public function getChatId($data)
    {
        if (isset($data['customer_email']) && $data['customer_email']) {
            $chat = $this->chatCollection->create()->addFieldToFilter('customer_email', $data['customer_email']);
            if (count($chat) > 0) {
                $chat_id = $chat->getFirstItem()->getData('chat_id');
            } else {
                $chatModel = $this->chatFactory->create();
                $chatModel->setCustomerId($data['customer_id'])->setCustomerName($data['customer_name'])->setCustomerEmail($data['customer_email']);
                $chatModel->save();
                $chat_id = $chatModel->getData('chat_id');
            }
        } else {
            $chat = $this->chatCollection->create()->addFieldToFilter('ip', $this->helper->getIp());
            if (count($chat) > 0) {
                $chat_id = $chat->getFirstItem()->getData('chat_id');
            } else {
                $chatModel = $this->chatFactory->create();
                $chatModel->setIp($this->helper->getIp());
                $chatModel->save();
                $chat_id = $chatModel->getData('chat_id');
            }
        }
        return $chat_id;
    }
}

