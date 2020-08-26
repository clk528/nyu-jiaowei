<?php


namespace clk528\NyuJiaoWei\Controllers;


use clk528\NyuJiaoWei\Models\RealNameUser;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Grid;

class AuditController extends AdminController
{
    protected $title = "Audit";

    public function grid()
    {
        return new Grid(new RealNameUser(), function (Grid $grid) {
            $grid->column('netid', 'NetId');

            $grid->column('first_name',);
            $grid->column('last_name');

            $grid->column('health_code');
            $grid->column('tour_code');
            $grid->column('health');
            $grid->column('nationality');
            $grid->column('nationality_form_type');

            $grid->disableColumnSelector();
            $grid->disableBatchActions();
            $grid->disableExport();
            $grid->disableCreateButton();
            $grid->disableActions();

            $grid->filter(function (Grid\Filter $filter) {
                $filter->disableIdFilter();
                $filter->like('netid', 'NetId');
                $filter->like('health_code')->select([
                    1 => 'Yes',
                    0 => 'No'
                ]);
                $filter->like('tour_code')->select([
                    1 => 'Yes',
                    0 => 'No'
                ]);
                $filter->like('health')->select([
                    1 => 'Yes',
                    0 => 'No'
                ]);
            });
            return $grid;
        });
    }
}
