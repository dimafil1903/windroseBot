<?php


namespace App\Http\Controllers;




use App\MenuItem;
use App\Translation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use TCG\Voyager\Facades\Voyager;
use App\TelegramConfig;
class TelegramSettingsController extends \TCG\Voyager\Http\Controllers\Controller
{

    public function index()
    {
        // Check permission


        $data = TelegramConfig::orderBy('order', 'ASC')->get();

        $settings = [];
        $settings[__('voyager::settings.group_general')] = [];
        foreach ($data as $d) {
            if ($d->group == '' || $d->group == __('voyager::settings.group_general')) {
                $settings[__('voyager::settings.group_general')][] = $d;
            } else {
                $settings[$d->group][] = $d;
            }
        }
        if (count($settings[__('voyager::settings.group_general')]) == 0) {
            unset($settings[__('voyager::settings.group_general')]);
        }

        $menu_items=MenuItem::where('menu_id',2)->get();
        $translations=Translation::where('table_name','telegram_configs')->get();
        $groups_data = TelegramConfig::select('group')->distinct()->get();
        $groups = [];
        foreach ($groups_data as $group) {
            if ($group->group != '') {
                $groups[] = $group->group;
            }
        }



        return view('admin.telegram-settings', compact('settings', 'groups','translations','menu_items'));
    }

    public function store(Request $request)
    {
        // Check permission


        $key = implode('.', [Str::slug($request->input('group')), $request->input('key')]);
        $key_check = TelegramConfig::where('key', $key)->get()->count();

        if ($key_check > 0) {
            return back()->with([
                'message'    => __('voyager::settings.key_already_exists', ['key' => $key]),
                'alert-type' => 'error',
            ]);
        }

        $lastSetting = TelegramConfig::orderBy('order', 'DESC')->first();

        if (is_null($lastSetting)) {
            $order = 0;
        } else {
            $order = intval($lastSetting->order) + 1;
        }

        $request->merge(['order' => $order]);
        $request->merge(['value' => '']);
        $request->merge(['key' => $key]);

        TelegramConfig::create($request->except('setting_tab'));

        request()->flashOnly('setting_tab');

        return back()->with([
            'message'    => __('voyager::settings.successfully_created'),
            'alert-type' => 'success',
        ]);
    }

    public function update(Request $request,$id)
    {


        $setting = TelegramConfig::find($id);


            $content = $this->getContentBasedOnType($request, 'telegram-config', (object) [
                'type'    => $setting->type,
                'field'   => config('voyager.multilingual.default').'-'.str_replace('.', '_', $setting->key),
                'group'   => $setting->group,
            ]);




            $setting->value = $content;
            $setting->save();

        foreach(config('voyager.multilingual.locales') as $lang){
            $content = $this->getContentBasedOnType($request, 'telegram-config', (object) [
                'type'    => $setting->type,
                'field'   => $lang.'-'.str_replace('.', '_', $setting->key),
                'group'   => $setting->group,
            ]);
            if($content) {
                Translation::updateOrInsert(
                    ['table_name' => 'telegram_configs', 'column_name' => 'value', 'foreign_key' => $setting->id, 'locale' => $lang],
                    ['value' => $content]
                );
            }
        }


        return back()->with([
            'message'    => __('voyager::settings.successfully_saved'.$content.';'),
            'alert-type' => 'success',
        ]);
    }

    public function delete($id)
    {
        // Check permission


        $setting = TelegramConfig::find($id);

        TelegramConfig::destroy($id);

        request()->session()->flash('setting_tab', $setting->group);

        return back()->with([
            'message'    => __('voyager::settings.successfully_deleted'),
            'alert-type' => 'success',
        ]);
    }

    public function move_up($id)
    {
        // Check permission


        $setting = TelegramConfig::find($id);

        // Check permission


        $swapOrder = $setting->order;
        $previousSetting = TelegramConfig::
              where('order', '<', $swapOrder)
            ->where('group', $setting->group)
            ->orderBy('order', 'DESC')->first();
        $data = [
            'message'    => __('voyager::settings.already_at_top'),
            'alert-type' => 'error',
        ];

        if (isset($previousSetting->order)) {
            $setting->order = $previousSetting->order;
            $setting->save();
            $previousSetting->order = $swapOrder;
            $previousSetting->save();

            $data = [
                'message'    => __('voyager::settings.moved_order_up', ['name' => $setting->display_name]),
                'alert-type' => 'success',
            ];
        }

        request()->session()->flash('setting_tab', $setting->group);

        return back()->with($data);
    }

    public function delete_value($id)
    {
        $setting = TelegramConfig::find($id);

        // Check permission


        if (isset($setting->id)) {
            // If the type is an image... Then delete it
            if ($setting->type == 'image') {
                if (Storage::disk(config('voyager.storage.disk'))->exists($setting->value)) {
                    Storage::disk(config('voyager.storage.disk'))->delete($setting->value);
                }
            }
            $setting->value = '';
            $setting->save();
        }

        request()->session()->flash('setting_tab', $setting->group);

        return back()->with([
            'message'    => __('voyager::settings.successfully_removed', ['name' => $setting->display_name]),
            'alert-type' => 'success',
        ]);
    }

    public function move_down($id)
    {
        // Check permission


        $setting = TelegramConfig::find($id);

        // Check permission


        $swapOrder = $setting->order;

        $previousSetting = TelegramConfig::
            where('order', '>', $swapOrder)
            ->where('group', $setting->group)
            ->orderBy('order', 'ASC')->first();
        $data = [
            'message'    => __('voyager::settings.already_at_bottom'),
            'alert-type' => 'error',
        ];

        if (isset($previousSetting->order)) {
            $setting->order = $previousSetting->order;
            $setting->save();
            $previousSetting->order = $swapOrder;
            $previousSetting->save();

            $data = [
                'message'    => __('voyager::settings.moved_order_down', ['name' => $setting->display_name]),
                'alert-type' => 'success',
            ];
        }

        request()->session()->flash('setting_tab', $setting->group);

        return back()->with($data);
    }
}
