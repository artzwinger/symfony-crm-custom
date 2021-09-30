<?php

namespace Teachers\Bundle\ApplicationBundle\Datagrid;

use Oro\Bundle\DataGridBundle\Entity\AbstractGridView;
use Oro\Bundle\DataGridBundle\Extension\GridViews\AbstractViewsList;
use Oro\Bundle\WorkflowBundle\Exception\WorkflowException;
use Oro\Bundle\WorkflowBundle\Model\WorkflowManager;
use Symfony\Contracts\Translation\TranslatorInterface;
use Teachers\Bundle\UsersBundle\Helper\Role;

class ApplicationsViewList extends AbstractViewsList
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
            $this->systemViews = array_merge($this->systemViews, $this->getAdminViews());
        }
        return $this->getSystemViewsList();
    }

    /**
     * @throws WorkflowException
     */
    protected function getAdminViews(): array
    {
        $wf = $this->workflowManager->getWorkflow('application_flow');
        $def = $wf->getDefinition();

        return [
            [
                'name' => 'applications.new',
                'label' => 'teachers.application.grid.views.new',
                'is_default' => true,
                'grid_name' => 'teachers-applications-grid',
                'type' => AbstractGridView::TYPE_PUBLIC,
                'filters' => [
                    'workflowStepLabelByWorkflowStep' => [
                        'value' => [$def->getStepByName('new')->getId()]
                    ]
                ],
                'sorters' => [],
                'columns' => []
            ]
        ];
    }
}
