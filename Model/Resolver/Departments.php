<?php
/**
 * Copyright © Landofcoder All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Lof\HelpDeskGraphQl\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Query\Resolver\Argument\SearchCriteria\Builder as SearchCriteriaBuilder;

/**
 * Class Departments
 * @package Lof\HelpDeskGraphQl\Model\Resolver
 */
class Departments implements ResolverInterface
{

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;
    /**
     * @var DataProvider\Department
     */
    private $departmentProvider;

    /**
     * Departments constructor.
     * @param DataProvider\Department $departmentRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        DataProvider\Department $departmentRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder
    )
    {
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->departmentProvider = $departmentRepository;
    }

    /**
     * @inheritdoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        if ($args['currentPage'] < 1) {
            throw new GraphQlInputException(__('currentPage value must be greater than 0.'));
        }
        if ($args['pageSize'] < 1) {
            throw new GraphQlInputException(__('pageSize value must be greater than 0.'));
        }
        $searchCriteria = $this->searchCriteriaBuilder->build( 'lof_helpdesk_department', $args );
        $searchCriteria->setCurrentPage( $args['currentPage'] );
        $searchCriteria->setPageSize( $args['pageSize'] );

        $searchResult = $this->departmentProvider->getListDepartments( $searchCriteria );

        return [
            'total_count' => $searchResult->getTotalCount(),
            'items'       => $searchResult->getItems(),
        ];
    }
}
