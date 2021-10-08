<?php

namespace Teachers\Bundle\ApplicationBundle\Datagrid;

use Oro\Bundle\DataGridBundle\Entity\AbstractGridView;
use Oro\Bundle\DataGridBundle\Extension\GridViews\AbstractViewsList;
use Oro\Bundle\FilterBundle\Form\Type\Filter\EnumFilterType;
use Symfony\Contracts\Translation\TranslatorInterface;
use Teachers\Bundle\ApplicationBundle\Entity\Application;
use Teachers\Bundle\UsersBundle\Helper\Role;

class ApplicationsViewList extends AbstractViewsList
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
            $this->systemViews = array_merge($this->systemViews, $this->getAdminViews());
        }
        return $this->getSystemViewsList();
    }

    /**
     * @return array
     */
    protected function getAdminViews(): array
    {
        return [
            [
                'name' => 'applications.new',
                'label' => 'teachers.application.grid.views.new',
                'is_default' => true,
                'grid_name' => 'teachers-applications-grid',
                'type' => AbstractGridView::TYPE_PUBLIC,
                'filters' => [
                    'statusLabel' => [
                        'type' => EnumFilterType::TYPE_IN,
                        'value' => [Application::STATUS_NEW]
                    ]
                ],
                'sorters' => [],
                'columns' => []
            ]
        ];
    }
}
