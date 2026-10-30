<?php

namespace Modules\SkinAnalysis\Sidebar;

use Maatwebsite\Sidebar\Item;
use Maatwebsite\Sidebar\Menu;
use Maatwebsite\Sidebar\Group;
use Modules\Admin\Sidebar\BaseSidebarExtender;

class SidebarExtender extends BaseSidebarExtender
{
    public function extend(Menu $menu)
    {
        $menu->group(trans('admin::sidebar.content'), function (Group $group) {
            $group->item('Skin Analysis', function (Item $item) {
                $item->icon('fa fa-smile-o');
                $item->weight(26);
                $item->route('admin.skin_analysis.index');
            });
        });
    }
}
