<?php

namespace Teachers\Bundle\AssignmentBundle\Datagrid;

use Oro\Bundle\DataGridBundle\Entity\AbstractGridView;
use Oro\Bundle\DataGridBundle\Extension\GridViews\AbstractViewsList;
use Oro\Bundle\FilterBundle\Form\Type\Filter\EnumFilterType;
use Symfony\Contracts\Translation\TranslatorInterface;
use Teachers\Bundle\AssignmentBundle\Entity\Assignment;
use Teachers\Bundle\UsersBundle\Helper\Role;

class AssignmentsViewList extends AbstractViewsList
{
    /**
     * @var Role
     */
    private $roleHelper;

    public function __construct(TranslatorInterface $translator, Role $roleHelper)
    {
        $this->roleHelper = $roleHelper;
        parent::__construct($translator);
    }

    /**
     * {@inheritDoc}
     * @return array
     */
    protected function getViewsList(): array
    {
        if ($this->roleHelper->isCurrentUserAdmin() || $this->roleHelper->isCurrentUserCourseManager()) {
            $this->systemViews = array_merge($this->systemViews, $this->getAdminCourseManagerViews());
        }
        return $this->getSystemViewsList();
    }

    /**
     * @return array
     */
    protected function getAdminCourseManagerViews(): array
    {
        return [
            [
                'name' => 'assignments.un_viewed_bids',
                'label' => 'teachers.assignment.grid.views.un_viewed_bids',
                'is_default' => false,
                'grid_name' => 'teachers-assignments-grid',
                'type' => AbstractGridView::TYPE_PUBLIC,
                'filters' => [
                    'hasUnViewedBids' => [
                        'value' => '1'
                    ]
                ],
                'sorters' => [],
                'columns' => []
            ],
            [
                'name' => 'assignments.new',
                'label' => 'teachers.assignment.grid.views.new',
                'is_default' => false,
                'grid_name' => 'teachers-assignments-grid',
                'type' => AbstractGridView::TYPE_PUBLIC,
                'filters' => [
                    'statusLabel' => [
                        'type' => EnumFilterType::TYPE_IN,
                        'value' => [Assignment::STATUS_NEW]
                    ]
                ],
                'sorters' => [],
                'columns' => []
            ],
            [
                'name' => 'assignments.up_for_bid',
                'label' => 'teachers.assignment.grid.views.up_for_bid',
                'is_default' => true,
                'grid_name' => 'teachers-assignments-grid',
                'type' => AbstractGridView::TYPE_PUBLIC,
                'filters' => [
                    'statusLabel' => [
                        'type' => EnumFilterType::TYPE_IN,
                        'value' => [Assignment::STATUS_UP_FOR_BID]
                    ]
                ],
                'sorters' => [],
                'columns' => []
            ],
            [
                'name' => 'assignments.assigned',
                'label' => 'teachers.assignment.grid.views.assigned',
                'is_default' => false,
                'grid_name' => 'teachers-assignments-grid',
                'type' => AbstractGridView::TYPE_PUBLIC,
                'filters' => [
                    'statusLabel' => [
                        'type' => EnumFilterType::TYPE_IN,
                        'value' => [Assignment::STATUS_ASSIGNED]
                    ]
                ],
                'sorters' => [],
                'columns' => []
            ],
            [
                'name' => 'assignments.complete',
                'label' => 'teachers.assignment.grid.views.complete',
                'is_default' => false,
                'grid_name' => 'teachers-assignments-grid',
                'type' => AbstractGridView::TYPE_PUBLIC,
                'filters' => [
                    'statusLabel' => [
                        'type' => EnumFilterType::TYPE_IN,
                        'value' => [Assignment::STATUS_COMPLETE]
                    ]
                ],
                'sorters' => [],
                'columns' => []
            ],
            [
                'name' => 'assignments.paused_due_nonpayment',
                'label' => 'teachers.assignment.grid.views.paused_due_nonpayment',
                'is_default' => false,
                'grid_name' => 'teachers-assignments-grid',
                'type' => AbstractGridView::TYPE_PUBLIC,
                'filters' => [
                    'statusLabel' => [
                        'type' => EnumFilterType::TYPE_IN,
                        'value' => [Assignment::STATUS_PAUSED_DUE_NONPAYMENT]
                    ]
                ],
                'sorters' => [],
                'columns' => []
            ],
        ];
    }
}
