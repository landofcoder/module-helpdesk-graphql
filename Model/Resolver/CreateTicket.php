<?php
declare(strict_types=1);

namespace Lof\HelpDeskGraphQl\Model\Resolver;

use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\GraphQl\Model\Query\ContextInterface;

/**
 * Class CreateTicket
 * @package Lof\HelpDeskGraphQl\Model\Resolver
 */
class CreateTicket implements ResolverInterface
{


    /**
     * @var DataProvider\Ticket
     */
    private $ticketProvider;

    /**
     * CreateTicket constructor.
     * @param DataProvider\Ticket $ticket
     */
    public function __construct(
        DataProvider\Ticket $ticket
    ) {
        $this->ticketProvider = $ticket;
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
        /** @var ContextInterface $context */
        if (false === $context->getExtensionAttributes()->getIsCustomer()) {
            throw new GraphQlAuthorizationException(__('The current customer isn\'t authorized.'));
        }
        $args = $args['input'];
        if (!($args['subject']) || !isset($args['subject'])) {
            throw new GraphQlInputException(__('"input" value should be specified'));
        }

        $customer = $this->getCustomer->execute($context);
        $data['customer_id'] = $customer->getId();
        $data['customer_name'] = $customer->getName();
        $data['customer_email'] = $customer->getEmail();
        $ticket = $this->ticketProvider->createTicket($data);
        if (!$ticket) {
            throw new GraphQlInputException(__('You are Spam!'));
        }
        return $ticket;
    }


}
