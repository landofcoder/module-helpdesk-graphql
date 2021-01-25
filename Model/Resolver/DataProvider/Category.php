<?php
/**
 * Copyright © LandOfCoder All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Lof\HelpDeskGraphQl\Model\Resolver\DataProvider;

use Lof\HelpDesk\Api\CategoryRepositoryInterface;
use Lof\HelpDesk\Api\Data\CategoryMessageSearchResultsInterfaceFactory;
use Lof\HelpDesk\Helper\Data;
use Lof\HelpDesk\Model\CategoryFactory;
use Lof\HelpDesk\Model\ResourceModel\Category\CollectionFactory;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\User\Model\UserFactory;

/**
 * Class Category
 * @package Lof\HelpDeskGraphQl\Model\Resolver\DataProvider
 */
class Category
{

    /**
     * @var ChatRepositoryInterface
     */
    private $chatRepository;
    /**
     * @var CollectionFactory
     */
    private $categoryCollection;
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


    /**
     * Category constructor.
     * @param CollectionFactory $categoryCollection
     * @param CollectionProcessorInterface $collectionProcessor
     * @param CategorySearchResultsInterfaceFactory $searchResultsFactory
     * @param CategoryFactory $chatFactory
     * @param CategoryRepositoryInterface $categoryRepository
     * @param UserFactory $userFactory
     * @param StoreManagerInterface $storeManagement
     * @param Data $helper
     */
    public function __construct(
        CollectionFactory $categoryCollection,
        CollectionProcessorInterface $collectionProcessor,
        CategorySearchResultsInterfaceFactory $searchResultsFactory,
        CategoryFactory $chatFactory,
        CategoryRepositoryInterface $categoryRepository,
        UserFactory $userFactory,
        StoreManagerInterface $storeManagement,
        Data $helper

    )
    {
        $this->chatCollection = $categoryCollection;
        $this->collectionProcessor = $collectionProcessor;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->chatFactory = $chatFactory;
        $this->categoryRepository = $categoryRepository;
        $this->userFactory = $userFactory;
        $this->storeManagement = $storeManagement;
        $this->helper = $helper;
    }


    /**
     * @param $criteria
     * @return mixed
     */
    public function getListCategories($criteria) {
        $collection = $this->categoryCollection->create();
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
}

