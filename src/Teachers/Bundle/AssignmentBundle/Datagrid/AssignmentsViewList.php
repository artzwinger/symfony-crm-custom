<?php

namespace Teachers\Bundle\AssignmentBundle\Datagrid;

use Oro\Bundle\DataGridBundle\Entity\AbstractGridView;
use Oro\Bundle\DataGridBundle\Extension\GridViews\AbstractViewsList;
use Oro\Bundle\WorkflowBundle\Exception\WorkflowException;
use Oro\Bundle\WorkflowBundle\Model\WorkflowManager;
use Symfony\Contracts\Translation\TranslatorInterface;
use Teachers\Bundle\UsersBundle\Helper\Role;

class AssignmentsViewList extends AbstractViewsList
{
    /**
     * @var Role
     */
    private $roleHelper;
    /**
     * @var WorkflowManager
     */
    private $workflowManager;

    public function __construct(TranslatorInterface $translator, Role $roleHelper, WorkflowManager $workflowManager)
    {
        $this->roleHelper = $roleHelper;
        $this->workflowManager = $workflowManager;
        parent::__construct($translator);
    }

    /**
     * {@inheritDoc}
     * @throws WorkflowException
     */
    protected function getViewsList(): array
    {
        if ($this->roleHelper->isCurrentUserAdmin() || $this->roleHelper->isCurrentUserCourseManager()) {
            $this->systemViews = array_merge($this->systemViews, $this->getAdminCourseManagerViews());
        }
        return $this->getSystemViewsList();
    }

    /**
     * @throws WorkflowException
     */
    protected function getAdminCourseManagerViews(): array
    {
        $wf = $this->workflowManager->getWorkflow('assignment_flow');
        $def = $wf->getDefinition();

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
                    'workflowStepLabelByWorkflowStep' => [
                        'value' => [$def->getStepByName('new')->getId()]
                    ]
                ],
                'sorters' => [],
                'columns' => []
            ],
            [
                'name' => 'assignments.up_for_bid',
                'label' => 'teachers.assignment.grid.views.up_for_bid',
                'is_default' => false,
                'grid_name' => 'teachers-assignments-grid',
                'type' => AbstractGridView::TYPE_PUBLIC,
                'filters' => [
                    'workflowStepLabelByWorkflowStep' => [
                        'value' => [$def->getStepByName('up_for_bid')->getId()]
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
                    'workflowStepLabelByWorkflowStep' => [
                        'value' => [$def->getStepByName('assigned')->getId()]
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
                    'workflowStepLabelByWorkflowStep' => [
                        'value' => [$def->getStepByName('complete')->getId()]
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
                    'workflowStepLabelByWorkflowStep' => [
                        'value' => [$def->getStepByName('paused_due_nonpayment')->getId()]
                    ]
                ],
                'sorters' => [],
                'columns' => []
            ],
        ];
    }
}
