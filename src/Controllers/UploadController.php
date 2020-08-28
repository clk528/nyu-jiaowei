<?php


namespace clk528\NyuJiaoWei\Controllers;

use clk528\NyuJiaoWei\Tools\ImportTool;
use clk528\NyuJiaoWei\Imports\UploadImport;
use clk528\NyuJiaoWei\Models\UploadData;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Layout\Content;
use Encore\Admin\Widgets\Box;
use Encore\Admin\Widgets\Form;
use Encore\Admin\Grid;
use Illuminate\Http\Request;

class UploadController extends AdminController
{
    private $titles = [
        'admin.student-undergraduate.index' => 'Student Undergraduate',
        'admin.student-go-local.index' => 'Student Go Local',
        'admin.master-or-doctoral.index' => 'Master Or Doctoral',
        'admin.student-other.index' => 'Student Other',
        'admin.teacher.index' => 'Teacher',
        'admin.staff.index' => 'Staff',


        'admin.student-undergraduate.import' => 'Student Undergraduate Import',
        'admin.student-go-local.import' => 'Student Go Local Import',
        'admin.master-or-doctoral.import' => 'Student Master Or Doctoral Import',
        'admin.student-other.import' => 'Student Other Import',
        'admin.teacher.import' => 'Teacher Import',
        'admin.staff.import' => 'Staff Import'
    ];

    private $routes = [
        'admin.student-undergraduate.import' => 'admin.student-undergraduate.index',
        'admin.student-go-local.import' => 'admin.student-go-local.index',
        'admin.master-or-doctoral.import' => 'admin.master-or-doctoral.index',
        'admin.student-other.import' => 'admin.student-other.index',
        'admin.teacher.import' => 'admin.teacher.index',
        'admin.staff.import' => 'admin.staff.index'
    ];

    protected $description = [
        'create' => 'Import Excel'
    ];


    protected function title()
    {
        $routerName = \Route::currentRouteName();
        return $this->titles[$routerName] ?? $routerName;
    }

    protected function grid()
    {
        return new Grid(new UploadData(), function (Grid $grid) {
            $grid->column('ID', 'ID');

            $grid->column('NetID');
            $grid->column('XM', 'Name');
            $grid->column('ID_Type', 'ID Type')->display(function ($value) {
                if($value == 0){
                    return '身份证';
                }
                if($value == 1){
                    return '港澳台通行证';
                }
                if($value == 2){
                    return '护照';
                }
            });
            $grid->column('ZJHM', 'Id Number');
            $grid->column('SJH', 'Mobile');
            $grid->column('SFLX', 'Person Type');
            $grid->column('Subtype', 'Sub Type');
            $grid->column('XXMC', 'School Name');
            $grid->column('Last_Update_By', 'Last Modify');

            $grid->tools(function (Grid\Tools $tools) use ($grid) {
                $tools->append(new ImportTool($grid));
            });

            $grid->disableCreateButton();
            $grid->disableExport();
        });
    }

    protected function form()
    {
        $form = new Form([
            'route' => $this->routes[\Route::currentRouteName()] ?? 'admin'
        ]);

        $form->file("excel", "Excel File")->rules('mimes:xlsx')->required();

        $form->t("template", "Excel Template")->with(function () {
            return "<a href='/upload-template.xlsx'>Download Template</a>";
        });

        $form->hidden('route');

        $form->action(route(\Route::currentRouteName()));

        $box = new Box("Import Excel", $form);

        $box->solid();
        $box->style('success');

        return $box;
    }

    /**
     * @param Request $request
     * @param Content $content
     * @return Content|\Illuminate\Http\RedirectResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    function import(Request $request, Content $content)
    {
        \Validator::make($request->all(), [
            'excel' => [
                'required',
                'file',
                'mimes:xlsx'
            ]
        ])->validate();

        $import = new UploadImport();
        $import->setOperator(\Auth::user()->netId)->import($request->file('excel'), null, \Maatwebsite\Excel\Excel::XLSX);

        if (count($import->getFailed()) > 0) {
            return $content
                ->title($this->title())
                ->description($this->description['create'] ?? trans('admin.create'))
                ->body($this->failed($import->getFailed()));
        }

        $route = $request->input('route') ?? 'admin.home';
        return redirect()->route($route);
    }


    private function failed(array $data = [])
    {
        return view("upload.failed")->with('data', $data);
    }
}
