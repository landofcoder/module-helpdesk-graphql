<?php
/**
 * Copyright © LandOfCoder All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Lof\HelpDeskGraphQl\Model\Resolver\DataProvider;

use Lof\HelpDesk\Api\DepartmentRepositoryInterface;
use Lof\HelpDesk\Api\Data\DepartmentSearchResultsInterfaceFactory;
use Lof\HelpDesk\Helper\Data;
use Lof\HelpDesk\Model\DepartmentFactory;
use Lof\HelpDesk\Model\ResourceModel\Department\CollectionFactory;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\User\Model\UserFactory;

/**
 * Class Department
 * @package Lof\HelpDeskGraphQl\Model\Resolver\DataProvider
 */
class Department
{

    /**
     * @var CollectionFactory
     */
    private $departmentCollection;
    /**
     * @var CollectionProcessorInterface
     */
    private $collectionProcessor;
    /**
     * @var DepartmentResultsInterfaceFactory
     */
    private $searchResultsFactory;
    /**
     * @var DepartmentFactory
     */
    private $departmentFactory;
    /**
     * @var DepartmentRepositoryInterface
     */
    private $departmentRepository;
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
     * Department constructor.
     * @param CollectionFactory $departmentCollection
     * @param CollectionProcessorInterface $collectionProcessor
     * @param DepartmentSearchResultsInterfaceFactory $searchResultsFactory
     * @param DepartmentFactory $departmentFactory
     * @param DepartmentRepositoryInterface $departmentRepository
     * @param UserFactory $userFactory
     * @param StoreManagerInterface $storeManagement
     * @param Data $helper
     */
    public function __construct(
        CollectionFactory $departmentCollection,
        CollectionProcessorInterface $collectionProcessor,
        DepartmentSearchResultsInterfaceFactory $searchResultsFactory,
        DepartmentFactory $departmentFactory,
        DepartmentRepositoryInterface $departmentRepository,
        UserFactory $userFactory,
        StoreManagerInterface $storeManagement,
        Data $helper

    )
    {
        $this->departmentCollection = $departmentCollection;
        $this->collectionProcessor = $collectionProcessor;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->departmentFactory = $departmentFactory;
        $this->departmentRepository = $departmentRepository;
        $this->userFactory = $userFactory;
        $this->storeManagement = $storeManagement;
        $this->helper = $helper;
    }


    /**
     * @param $criteria
     * @return mixed
     */
    public function getListDepartments($criteria) {
        $collection = $this->departmentCollection->create();
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

