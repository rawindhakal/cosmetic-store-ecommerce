<?php

namespace Modules\Pos\Sidebar;

use Maatwebsite\Sidebar\Item;
use Maatwebsite\Sidebar\Menu;
use Maatwebsite\Sidebar\Group;
use Modules\Admin\Sidebar\BaseSidebarExtender;

class SidebarExtender extends BaseSidebarExtender
{
    public function extend(Menu $menu)
    {
        $menu->group(trans('admin::sidebar.sales'), function (Group $group) {
            $group->item('POS', function (Item $item) {
                $item->icon('fa fa-calculator');
                $item->weight(1);
                $item->route('admin.pos.index');

                foreach ([['Open Terminal', 'admin.pos.index'], ['Sales & Returns', 'admin.pos.sales'], ['Registers', 'admin.pos.registers'], ['Barcodes & Labels', 'admin.pos.barcodes'], ['POS Settings', 'admin.pos.settings']] as $i => [$label, $route]) {
                    $item->item($label, function (Item $sub) use ($i, $route) {
                        $sub->weight($i + 1);
                        $sub->route($route);
                    });
                }
            });
        });
    }
}
