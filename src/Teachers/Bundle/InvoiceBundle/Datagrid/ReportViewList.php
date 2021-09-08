<?php

namespace Teachers\Bundle\InvoiceBundle\Datagrid;

use Oro\Bundle\DataGridBundle\Entity\AbstractGridView;
use Oro\Bundle\DataGridBundle\Extension\GridViews\AbstractViewsList;

class ReportViewList extends AbstractViewsList
{
    const FULLY_PAID_DATE = 'fullyPaidDate';
    protected $systemViews = [];

    /**
     * {@inheritDoc}
     */
    protected function getViewsList(): array
    {
        $this->systemViews = array_merge($this->systemViews, [
            $this->getThisMonthView(),
            $this->getLastMonthView(),
            $this->getThisYearView(),
            $this->getLastYearView()
        ]);
        return $this->getSystemViewsList();
    }

    private function getThisMonthView(): array
    {
        $startDate = date('Y-m') . '-01 00:00';
        $endDate = date('Y-m-t') . ' 23:59';

        return [
            'name' => 'invoice.this_month',
            'label' => 'teachers.invoice.grid.views.this_month',
            'is_default' => true,
            'grid_name' => 'oro_reportcrm-invoices-base',
            'type' => AbstractGridView::TYPE_PUBLIC,
            'filters' => [
                self::FULLY_PAID_DATE => [
                    'value' => [
                        'start' => $startDate,
                        'end' => $endDate
                    ]
                ]
            ],
            'sorters' => [],
            'columns' => []
        ];
    }

    private function getLastMonthView(): array
    {
        $lastMonthTimeStamp = strtotime('-1 month');
        $startDate = date('Y-m', $lastMonthTimeStamp) . '-01 00:00';
        $endDate = date('Y-m-t', $lastMonthTimeStamp) . ' 23:59';

        return [
            'name' => 'invoice.last_month',
            'label' => 'teachers.invoice.grid.views.last_month',
            'is_default' => false,
            'grid_name' => 'oro_reportcrm-invoices-base',
            'type' => AbstractGridView::TYPE_PUBLIC,
            'filters' => [
                self::FULLY_PAID_DATE => [
                    'value' => [
                        'start' => $startDate,
                        'end' => $endDate
                    ]
                ]
            ],
            'sorters' => [],
            'columns' => []
        ];
    }

    private function getLastYearView(): array
    {
        $lastYear = date('Y', strtotime('-1 year'));
        $startDate = $lastYear . '-01-01 00:00';
        $endDate = $lastYear . '-12-31 23:59';

        return [
            'name' => 'invoice.last_year',
            'label' => 'teachers.invoice.grid.views.last_year',
            'is_default' => false,
            'grid_name' => 'oro_reportcrm-invoices-base',
            'type' => AbstractGridView::TYPE_PUBLIC,
            'filters' => [
                self::FULLY_PAID_DATE => [
                    'value' => [
                        'start' => $startDate,
                        'end' => $endDate
                    ]
                ]
            ],
            'sorters' => [],
            'columns' => []
        ];
    }

    private function getThisYearView(): array
    {
        $thisYear = date('Y');
        $startDate = $thisYear . '-01-01 00:00';
        $endDate = $thisYear . '-12-31 23:59';

        return [
            'name' => 'invoice.this_year',
            'label' => 'teachers.invoice.grid.views.this_year',
            'is_default' => false,
            'grid_name' => 'oro_reportcrm-invoices-base',
            'type' => AbstractGridView::TYPE_PUBLIC,
            'filters' => [
                self::FULLY_PAID_DATE => [
                    'value' => [
                        'start' => $startDate,
                        'end' => $endDate
                    ]
                ]
            ],
            'sorters' => [],
            'columns' => []
        ];
    }
}
