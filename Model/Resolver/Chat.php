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
 * Class Chat
 * @package Lof\HelpDeskGraphQl\Model\Resolver
 */
class Chat implements ResolverInterface
{

    /**
     * @var DataProvider\Chat
     */
    private $chatProvider;
    /**
     * @var GetCustomer
     */
    private $getCustomer;

    /**
     * CreateTicket constructor.
     * @param DataProvider\Chat $chatProvider
     * @param GetCustomer $getCustomer
     */
    public function __construct(
        DataProvider\Chat $chatProvider,
        GetCustomer $getCustomer
    ) {
        $this->chatProvider = $chatProvider;
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

        if( !isset($args['input']) || !$args['input']) {
            throw new GraphQlInputException(__('"input" value should be specified'));
        }
        $data = $args['input'];
        /** @var ContextInterface $context */
        if ($context->getExtensionAttributes()->getIsCustomer()) {
            $customer = $this->getCustomer->execute($context);
            $data['customer_id'] = $customer->getId();
            $data['customer_name'] = $customer->getFirstname().' '.$customer->getLastname();
            $data['customer_email'] = $customer->getEmail();
        } else {
            $data['customer_id'] = 0;
        }

        if (!($data['body_msg']) || !isset($data['body_msg'])) {
            throw new GraphQlInputException(__('"body_msg" value should be specified'));
        }

        return $this->chatProvider->Chat($data);
    }
}
