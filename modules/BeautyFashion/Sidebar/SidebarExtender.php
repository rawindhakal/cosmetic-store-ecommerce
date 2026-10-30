<?php

namespace Modules\BeautyFashion\Sidebar;

use Maatwebsite\Sidebar\Item;
use Maatwebsite\Sidebar\Menu;
use Maatwebsite\Sidebar\Group;
use Modules\Admin\Sidebar\BaseSidebarExtender;

class SidebarExtender extends BaseSidebarExtender
{
    public function extend(Menu $menu)
    {
        $menu->group(trans('admin::sidebar.content'), function (Group $group) {
            $group->item('Beauty & Fashion', function (Item $item) {
                $item->icon('fa fa-magic');
                $item->weight(27);
                $item->route('admin.beauty.details');

                foreach ([['Product Details', 'admin.beauty.details'], ['Size Charts', 'admin.beauty.sizecharts'], ['Batches & Expiry', 'admin.beauty.batches']] as $i => [$label, $route]) {
                    $item->item($label, function (Item $sub) use ($i, $route) {
                        $sub->weight($i + 1);
                        $sub->route($route);
                    });
                }
            });
        });
    }
}
