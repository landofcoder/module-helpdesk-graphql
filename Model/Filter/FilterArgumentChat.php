<?php

declare(strict_types=1);

namespace Lof\HelpDeskGraphQl\Model\Filter;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\ConfigInterface;
use Magento\Framework\GraphQl\Query\Resolver\Argument\FieldEntityAttributesInterface;

/**
 * Class FilterArgumentChat
 * @package Lof\BlogGraphQl\Model\Resolver
 */
class FilterArgumentChat implements FieldEntityAttributesInterface
{
    /** @var ConfigInterface */
    private $config;

    /**
     * FilterArgumentSeller constructor.
     * @param ConfigInterface $config
     */
    public function __construct(ConfigInterface $config)
    {
        $this->config = $config;
    }

    /**
     * @return array
     */
    public function getEntityAttributes(): array
    {
        $fields = [];
        /** @var Field $field */
        foreach ($this->config->getConfigElement('Chat')->getFields() as $field) {
            $fields[$field->getName()] = [
                'type' => 'String',
                'fieldName' => $field->getName(),
            ];
        }
        return $fields;
    }
}
