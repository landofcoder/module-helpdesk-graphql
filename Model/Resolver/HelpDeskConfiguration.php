<?php

declare(strict_types=1);

namespace Lof\HelpDeskGraphQl\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Lof\HelpDesk\Helper\Data as HelpDeskHelper;

/**
 * Resolve Store Config information for SendFriend
 */
class HelpDeskConfiguration implements ResolverInterface
{
    /**
     * @var HelpDeskHelper
     */
    private $helpDeskHelper;

    /**
     * @param HelpDeskHelper $helpDeskHelper
     */
    public function __construct(HelpDeskHelper $helpDeskHelper)
    {
        $this->helpDeskHelper = $helpDeskHelper;
    }

    /**
     * @inheritDoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        $store = $context->getExtensionAttributes()->getStore();
        $storeId = $store->getId();

        return [
            'enabled' => $this->helpDeskHelper->getConfig('general_settings/enable', $storeId),
            'enable_testmode' => $this->helpDeskHelper->getConfig('email_settings/enable_testmode', $storeId),
            'email_receive' => $this->helpDeskHelper->getConfig('email_settings/email_receive', $storeId),
            'sender_email_identity' => $this->helpDeskHelper->getConfig('email_settings/sender_email_identity', $storeId),
            'new_ticket_template' => $this->helpDeskHelper->getConfig('email_settings/new_ticket_template', $storeId),
            'new_message_template' => $this->helpDeskHelper->getConfig('email_settings/new_message_template', $storeId),
            'status_ticket_template' => $this->helpDeskHelper->getConfig('email_settings/status_ticket_template', $storeId),
            'reminder_template' => $this->helpDeskHelper->getConfig('email_settings/reminder_template', $storeId),
            'assign_ticket_template' => $this->helpDeskHelper->getConfig('email_settings/assign_ticket_template', $storeId),
            'auto_close_ticket' => $this->helpDeskHelper->getConfig('automation/auto_close_ticket', $storeId),
            'auto_reminder_ticket' => $this->helpDeskHelper->getConfig('automation/auto_reminder_ticket', $storeId),
            'enable_chat' => $this->helpDeskHelper->getConfig('chat/enable', $storeId),
            'text_label_chat' => $this->helpDeskHelper->getConfig('chat/text_label', $storeId),
            'background_color_chat' => $this->helpDeskHelper->getConfig('chat/background_color', $storeId),
            'store' => $this->helpDeskHelper->getConfig('chat/store', $storeId),
        ];
    }
}
