<?php

namespace Teachers\Bundle\AssignmentBundle\Datagrid;

use Oro\Bundle\DataGridBundle\Entity\AbstractGridView;
use Oro\Bundle\DataGridBundle\Extension\GridViews\AbstractViewsList;

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
                'recipientOfLatestMessage' => [
                    'value' => '1'
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
                'isLatestMessageSentByMe' => [
                    'value' => '1'
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
