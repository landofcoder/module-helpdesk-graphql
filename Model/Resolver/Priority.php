<?php
/**
 * Copyright © LandOfCoder All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Lof\HelpDeskGraphQl\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

/**
 * Class Priority
 * @package Lof\HelpDeskGraphQl\Model\Resolver
 */
class Priority implements ResolverInterface
{

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
        $data = [];
        $data[0]['id'] = 0;
        $data[0]['title'] = __('Low');
        $data[1]['id'] = 1;
        $data[1]['title'] = __('Medium');
        $data[2]['id'] = 2;
        $data[2]['title'] = __('Height');
        $data[3]['id'] = 3;
        $data[3]['title'] = __('Ugent');

        $obj = new \Magento\Framework\DataObject();
        $obj->setItems($data);
        return $obj;
    }
}

