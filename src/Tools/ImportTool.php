<?php

namespace clk528\NyuJiaoWei\Tools;

use Encore\Admin\Grid;
use Encore\Admin\Grid\Tools\AbstractTool;

class ImportTool extends AbstractTool
{
    /**
     * @var Grid
     */
    protected $grid;

    /**
     * Create a new CreateButton instance.
     *
     * @param Grid $grid
     */
    public function __construct(Grid $grid)
    {
        $this->grid = $grid;
    }

    public function render()
    {
        return <<<EOT
<div class="btn-group pull-right" style="margin-right: 10px">
    <a href="{$this->getImportUrl()}" class="btn btn-sm btn-success" title="Import Data">
        <i class="fa fa-upload"></i><span class="hidden-xs">&nbsp;&nbsp;Import Data</span>
    </a>
</div>
EOT;
    }

    /**
     * Get create url.
     *
     * @return string
     */
    public function getImportUrl()
    {
        $queryString = '';

        if ($constraints = $this->grid->model()->getConstraints()) {
            $queryString = http_build_query($constraints);
        }

        return sprintf(
            '%s/import%s',
            $this->grid->resource(),
            $queryString ? ('?' . $queryString) : ''
        );
    }

}
