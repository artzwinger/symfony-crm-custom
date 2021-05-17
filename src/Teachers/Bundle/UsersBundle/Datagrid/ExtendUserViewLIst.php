<?php

namespace Teachers\Bundle\UsersBundle\Datagrid;

use Oro\Bundle\DataGridBundle\Entity\GridView;
use Oro\Bundle\FilterBundle\Form\Type\Filter\ChoiceFilterType;
use Oro\Bundle\UserBundle\Datagrid\UserViewList;
use Symfony\Contracts\Translation\TranslatorInterface;
use Teachers\Bundle\UsersBundle\Helper\Role;

class ExtendUserViewLIst extends UserViewList
{
    protected $extendSystemViews = [
        [
            'name' => 'user.students',
            'label' => 'teachers.users.grid.views.students',
            'is_default' => false,
            'grid_name' => self::GRID_NAME,
            'type' => GridView::TYPE_PUBLIC,
            'filters' => [
                'roleLabel' => [
                    'type' => ChoiceFilterType::TYPE_CONTAINS,
                    'value' => Role::ROLE_STUDENT
                ]
            ],
            'sorters' => [],
            'columns' => []
        ],
        [
            'name' => 'user.teachers',
            'label' => 'teachers.users.grid.views.teachers',
            'is_default' => false,
            'grid_name' => self::GRID_NAME,
            'type' => GridView::TYPE_PUBLIC,
            'filters' => [
                'roleLabel' => [
                    'type' => ChoiceFilterType::TYPE_CONTAINS,
                    'value' => Role::ROLE_TEACHER
                ]
            ],
            'sorters' => [],
            'columns' => []
        ],
        [
            'name' => 'user.course_managers',
            'label' => 'teachers.users.grid.views.course_managers',
            'is_default' => false,
            'grid_name' => self::GRID_NAME,
            'type' => GridView::TYPE_PUBLIC,
            'filters' => [
                'roleLabel' => [
                    'type' => ChoiceFilterType::TYPE_CONTAINS,
                    'value' => Role::ROLE_COURSE_MANAGER
                ]
            ],
            'sorters' => [],
            'columns' => []
        ]
    ];

    public function __construct(TranslatorInterface $translator)
    {
        parent::__construct($translator);
        $this->systemViews = array_merge($this->systemViews, $this->extendSystemViews);
    }
}
