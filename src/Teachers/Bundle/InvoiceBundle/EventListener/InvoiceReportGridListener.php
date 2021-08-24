<?php

namespace Teachers\Bundle\InvoiceBundle\EventListener;

use Oro\Bundle\DataGridBundle\Event\BuildBefore;
use Oro\Bundle\DataGridBundle\Provider\State\DatagridStateProviderInterface;
use Oro\Bundle\FilterBundle\Grid\Extension\Configuration as FilterConfiguration;

/**
 * Changes data name of "period" filter according to filter value.
 */
class InvoiceReportGridListener
{
    const FULLY_PAID_DATE = 'dueDate';
    const PERIOD_COLUMN_NAME = 'period';
    const PERIOD_FILTER_DEFAULT_VALUE = 'lastMonth';

    /** @var DatagridStateProviderInterface */
    private $filtersStateProvider;

    /**
     * @param DatagridStateProviderInterface $filtersStateProvider
     */
    public function __construct(DatagridStateProviderInterface $filtersStateProvider)
    {
        $this->filtersStateProvider = $filtersStateProvider;
    }

    /**
     * Event: oro_datagrid.datagrid.build.before.oro_reportcrm-invoices-base
     * @param BuildBefore $event
     */
    public function onBuildBefore(BuildBefore $event)
    {
        $config = $event->getConfig();

        $filtersState = $this->filtersStateProvider->getState($config, $event->getDatagrid()->getParameters());
        $period = $filtersState[self::PERIOD_COLUMN_NAME]['value'] ?? self::PERIOD_FILTER_DEFAULT_VALUE;


        var_dump((string)$config->getOrmQuery());
        die;
        if ($period === 'periodAll') {
            return;
        }

        $timestamp = $period === 'lastMonth' ? strtotime('-1 month') : time();
        $startDate = date('Y-m', $timestamp) . '-01 00:00';
        $endDate = date('Y-m-t', $timestamp) . ' 00:00';

        $path = sprintf(
            '%s[%s][%s]',
            FilterConfiguration::COLUMNS_PATH,
            self::FULLY_PAID_DATE,
            'value'
        );
        $config->offsetSetByPath(
            sprintf('%s[%s]', $path, 'start'),
            $startDate
        );
        $config->offsetSetByPath(
            sprintf('%s[%s]', $path, 'end'),
            $endDate
        );
    }
}
