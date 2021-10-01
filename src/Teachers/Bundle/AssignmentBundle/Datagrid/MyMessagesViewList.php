<?php

namespace Teachers\Bundle\AssignmentBundle\Datagrid;

use Oro\Bundle\DataGridBundle\Entity\AbstractGridView;
use Oro\Bundle\DataGridBundle\Extension\GridViews\AbstractViewsList;
use Oro\Bundle\FilterBundle\Form\Type\Filter\EnumFilterType;

class MyMessagesViewList extends AbstractViewsList
{
    protected $systemViews = [
        [
            'name' => 'my_messages.inbox',
            'label' => 'teachers.assignment.grid.views.inbox',
            'is_default' => true,
            'grid_name' => 'teachers-assignment-my-messages-grid',
            'type' => AbstractGridView::TYPE_PUBLIC,
            'filters' => [
                'statusLabel' => [
                    'type' => EnumFilterType::TYPE_IN,
                    'value' => ['pending']
                ]
            ],
            'sorters' => [],
            'columns' => []
        ],
        [
            'name' => 'my_messages.sent',
            'label' => 'teachers.assignment.grid.views.sent',
            'is_default' => false,
            'grid_name' => 'teachers-assignment-my-messages-grid',
            'type' => AbstractGridView::TYPE_PUBLIC,
            'filters' => [
                'statusLabel' => [
                    'type' => EnumFilterType::TYPE_IN,
                    'value' => ['not_approved']
                ]
            ],
            'sorters' => [],
            'columns' => []
        ]
    ];

    /**
     * {@inheritDoc}
     */
    protected function getViewsList(): array
    {
        return $this->getSystemViewsList();
    }
}
