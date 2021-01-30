<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Lof\HelpDeskGraphQl\Model\Resolver;

use Magento\CustomerGraphQl\Model\Customer\GetCustomer;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\GraphQl\Model\Query\ContextInterface;
use Magento\Sales\Model\OrderFactory;

/**
 * Orders data reslover
 */
class Orders implements ResolverInterface
{


    /**
     * @var OrderFactory
     */
    private $orderFactory;
    /**
     * @var GetCustomer
     */
    private $getCustomer;

    /**
     * Order constructor.
     * @param OrderFactory $orderFactory
     * @param GetCustomer $getCustomer
     */
    public function __construct(
        OrderFactory $orderFactory,
        GetCustomer $getCustomer

    ) {
        $this->orderFactory = $orderFactory;
        $this->getCustomer = $getCustomer;
    }

    /**
     * @inheritDoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        /** @var ContextInterface $context */
        if (false === $context->getExtensionAttributes()->getIsCustomer()) {
            throw new GraphQlAuthorizationException(__('The current customer isn\'t authorized.'));
        }
        $customer = $this->getCustomer->execute($context);
        $orders = $this->orderFactory->create()->getCollection()->addFieldToFilter('customer_id', $customer->getId());
        $result = [];
        $data = [];
        foreach ($orders as $key=> $order) {
            $data[$key] = $order->getData();
            $data[$key]['order_number'] = $order->getIncrementId();
            $data[$key]['id'] = $order->getId();
            $data[$key]['model'] = $order;
        }
        $result['items'] = $data;
        $result['total_count'] = count($data);
        return $result;
    }
}
