<?php
declare(strict_types=1);

namespace Lof\HelpDeskGraphQl\Model\Resolver;

use Lof\HelpDesk\Model\TicketRepository;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\GraphQl\Model\Query\ContextInterface;
use Magento\CustomerGraphQl\Model\Customer\GetCustomer;

/**
 * Class SendMessageTicket
 * @package Lof\HelpDeskGraphQl\Model\Resolver
 */
class SendMessageTicket implements ResolverInterface
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
     * @var TicketRepository
     */
    private $ticketRepository;

    /**
     * CreateTicket constructor.
     * @param DataProvider\Ticket $ticket
     * @param GetCustomer $getCustomer
     * @param TicketRepository $ticketRepository
     */
    public function __construct(
        DataProvider\Ticket $ticket,
        GetCustomer $getCustomer,
        TicketRepository $ticketRepository
    ) {
        $this->ticketProvider = $ticket;
        $this->getCustomer = $getCustomer;
        $this->ticketRepository = $ticketRepository;
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

        if( !isset($args['input']) || !$args['input']) {
            throw new GraphQlInputException(__('"input" value should be specified'));
        }

        $args = $args['input'];

        if (!isset($args['ticket_id']) || !$args['ticket_id'] ) {
            throw new GraphQlInputException(__('Field "ticket_id" can\t be empty'));
        }

        if (!isset($args['body']) || !$args['body'] ) {
            throw new GraphQlInputException(__(' Field "body" can\t be empty'));
        }

        $customer = $this->getCustomer->execute($context);
        $ticket = $this->ticketRepository->get($args['ticket_id']);

        if(!isset($ticket['customer_id']) || $ticket['customer_id'] != $customer->getId()) {
            throw new GraphQlInputException(__('You don\'t have permission to send message for this ticket'));
        }

        $args['customer_id'] = $customer->getId();
        $args['customer_name'] = $customer->getFirstname().' '.$customer->getLastname();
        $args['customer_email'] = $customer->getEmail();
        $args['is_read'] = 0;
        $message = $this->ticketProvider->sendMessageTicket($args);
        if (!$message) {
            throw new GraphQlInputException(__('You are Spam!'));
        }
        return $message;
    }
}
