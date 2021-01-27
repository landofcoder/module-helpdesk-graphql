<?php
/**
 * Copyright © LandOfCoder All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Lof\HelpDeskGraphQl\Model\Resolver\DataProvider;

use Lof\HelpDesk\Api\CategoryRepositoryInterface;
use Lof\HelpDesk\Api\Data\CategorySearchResultsInterfaceFactory;
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
     * @var CollectionFactory
     */
    private $categoryCollection;
    /**
     * @var CollectionProcessorInterface
     */
    private $collectionProcessor;
    /**
     * @var CategoryResultsInterfaceFactory
     */
    private $searchResultsFactory;
    /**
     * @var CategoryFactory
     */
    private $categoryFactory;
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
     * Category constructor.
     * @param CollectionFactory $categoryCollection
     * @param CollectionProcessorInterface $collectionProcessor
     * @param CategorySearchResultsInterfaceFactory $searchResultsFactory
     * @param CategoryFactory $categoryFactory
     * @param CategoryRepositoryInterface $categoryRepository
     * @param UserFactory $userFactory
     * @param StoreManagerInterface $storeManagement
     * @param Data $helper
     */
    public function __construct(
        CollectionFactory $categoryCollection,
        CollectionProcessorInterface $collectionProcessor,
        CategorySearchResultsInterfaceFactory $searchResultsFactory,
        CategoryFactory $categoryFactory,
        CategoryRepositoryInterface $categoryRepository,
        UserFactory $userFactory,
        StoreManagerInterface $storeManagement,
        Data $helper

    )
    {
        $this->categoryCollection = $categoryCollection;
        $this->collectionProcessor = $collectionProcessor;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->categoryFactory = $categoryFactory;
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

