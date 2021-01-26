<?php
declare(strict_types=1);

namespace Lof\HelpDeskGraphQl\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\GraphQl\Model\Query\ContextInterface;
use Magento\CustomerGraphQl\Model\Customer\GetCustomer;

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
     * @var GetCustomer
     */
    private $getCustomer;

    /**
     * CreateTicket constructor.
     * @param DataProvider\Ticket $ticket
     * @param GetCustomer $getCustomer
     */
    public function __construct(
        DataProvider\Ticket $ticket,
        GetCustomer $getCustomer
    ) {
        $this->ticketProvider = $ticket;
        $this->getCustomer = $getCustomer;
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
        if (!$context->getExtensionAttributes()->getIsCustomer()) {
            throw new GraphQlAuthorizationException(__('The current customer isn\'t authorized.'));
        }
        $args = $args['input'];
        if (!($args['subject']) || !isset($args['subject'])) {
            throw new GraphQlInputException(__('"input" value should be specified'));
        }

        $customer = $this->getCustomer->execute($context);
        $args['customer_id'] = $customer->getId();
        $args['customer_name'] = $customer->getFirstname().' '.$customer->getLastname();
        $args['customer_email'] = $customer->getEmail();
        $ticket = $this->ticketProvider->createTicket($args);
        if (!$ticket) {
            throw new GraphQlInputException(__('You are Spam!'));
        }
        return $ticket;
    }


}
