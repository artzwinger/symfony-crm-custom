<?php

namespace Teachers\Bundle\AssignmentBundle\Datagrid;

use Oro\Bundle\DataGridBundle\Entity\GridView;
use Oro\Bundle\DataGridBundle\Extension\GridViews\AbstractViewsList;
use Oro\Bundle\FilterBundle\Form\Type\Filter\EnumFilterType;

class MessagesViewList extends AbstractViewsList
{
    protected $systemViews = [
        [
            'name' => 'message.pending',
            'label' => 'teachers.assignment.grid.views.pending',
            'is_default' => true,
            'grid_name' => 'assignment-messages-grid',
            'type' => GridView::TYPE_PUBLIC,
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
            'name' => 'message.declined',
            'label' => 'teachers.assignment.grid.views.declined',
            'is_default' => false,
            'grid_name' => 'assignment-messages-grid',
            'type' => GridView::TYPE_PUBLIC,
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
