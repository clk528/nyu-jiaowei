<?php


namespace clk528\NyuJiaoWei\Controllers;


use clk528\NyuJiaoWei\Exporters\SurverExportor;
use clk528\NyuJiaoWei\Models\NyuSurvey;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Grid;

class SurveryController extends AdminController
{
    protected $title = "Survery";

    public function grid()
    {
        $grid = new Grid(new NyuSurvey(), function (Grid $grid) {
            $grid->column('netId', 'NetId');

            $grid->column('email');

            $grid->disableColumnSelector();
            $grid->disableBatchActions();
//            $grid->disableExport();
            $grid->disableCreateButton();
            $grid->disableActions();

            $grid->filter(function (Grid\Filter $filter) {
                $filter->disableIdFilter();

                $filter->like('netid', 'NetId');
                $filter->like('email');

            });
            return $grid;
        });


        $exporter = new SurverExportor();

        $exporter->setFileName("survers-".date("Y-m-d-H-i") . ".xlsx");

        $grid->exporter($exporter);

        return $grid;
    }
}
