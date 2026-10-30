<?php

namespace Modules\GlowPoints\Sidebar;

use Maatwebsite\Sidebar\Item;
use Maatwebsite\Sidebar\Menu;
use Maatwebsite\Sidebar\Group;
use Modules\Admin\Sidebar\BaseSidebarExtender;
use Modules\GlowPoints\Services\GlowPoints;

class SidebarExtender extends BaseSidebarExtender
{
    public function extend(Menu $menu)
    {
        $menu->group(trans('admin::sidebar.content'), function (Group $group) {
            $group->item(GlowPoints::name(), function (Item $item) {
                $item->icon('fa fa-star');
                $item->weight(25);
                $item->route('admin.glowpoints.index');

                $item->item('Transactions', function (Item $item) {
                    $item->weight(1);
                    $item->route('admin.glowpoints.index');
                });

                $item->item('Settings', function (Item $item) {
                    $item->weight(2);
                    $item->route('admin.glowpoints.settings');
                });
            });
        });
    }
}
