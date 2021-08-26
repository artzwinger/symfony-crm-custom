<?php

namespace Teachers\Bundle\AssignmentBundle\Datagrid;

use Oro\Bundle\DataGridBundle\Entity\AbstractGridView;
use Oro\Bundle\DataGridBundle\Extension\GridViews\AbstractViewsList;
use Symfony\Contracts\Translation\TranslatorInterface;
use Teachers\Bundle\UsersBundle\Helper\Role;

class AssignmentsViewList extends AbstractViewsList
{
    protected $systemViews = [];
    protected $adminCourseManagerViews = [
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
        ]
    ];
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
     */
    protected function getViewsList(): array
    {
        if ($this->roleHelper->isCurrentUserAdmin() || $this->roleHelper->isCurrentUserCourseManager()) {
            $this->systemViews = array_merge($this->systemViews, $this->adminCourseManagerViews);
        }
        return $this->getSystemViewsList();
    }
}
