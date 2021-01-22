<?php
/**
 * Copyright © LandOfCoder All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Lof\HelpDeskGraphQl\Model\Resolver;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

class Ticket implements ResolverInterface
{

    /**
     * @var DataProvider\Ticket
     */
    private $ticketProvider;

    /**
     * @param DataProvider\Ticket $TicketRepository
     */
    public function __construct(
        DataProvider\Ticket $TicketRepository
    ) {
        $this->ticketProvider = $TicketRepository;
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
        $ticket = $this->ticketProvider->getTicketById($args['ticket_id']);
        return $ticket;
    }
}

