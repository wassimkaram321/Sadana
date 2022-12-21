<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Brian2694\Toastr\Facades\Toastr;


class DatabaseSettingController extends Controller
{
    public function db_index()
    {
        // $tables = DB::connection()->getDoctrineSchemaManager()->listTableNames();
        // $filter_tables = array(
        //     'admin_roles', 'admins', 'business_settings', 'colors', 'currencies',
        //     'failed_jobs', 'migrations', 'oauth_access_tokens', 'oauth_auth_codes',
        //     'oauth_clients', 'oauth_personal_access_clients', 'oauth_refresh_tokens',
        //     'password_resets', 'personal_access_tokens', 'phone_or_email_verifications',
        //     'social_medias', 'soft_credentials', 'users', 'carts', 'admin_wallet_histories',
        //     'admin_wallets', 'attributes', 'bags_setting', 'billing_addresses', 'cart_shippings',
        //     'category_shipping_costs', 'chattings', 'contacts', 'coupons', 'customer_wallet_histories',
        //     'customer_wallets', 'deal_of_the_days', 'delivery_histories', 'feature_deals', 'flash_deal_products',
        //     'help_topics', 'order_transactions', 'orders_alameen', 'paytabs_invoices', 'pharmacies_points', 'pharmacies_plan_details',
        //     'products_bag', 'products_keys', 'products_points', 'refund_requests', 'refund_statuses', 'refund_transactions',
        //     'salers_teams', 'salers_work_plans', 'sales_area', 'sales_group', 'sales_pharmacy', 'search_functions', 'seller_wallet_histories',
        //     'seller_wallets', 'sellers', 'shipping_addresses', 'shipping_methods', 'shipping_types', 'shops', 'transactions', 'translations', 'withdraw_requests',
        //     'work_plan_details_archive', 'work_plan_tasks', 'flash_deals', 'bags_orders_details'
        // );
        //  $tables = array_values(array_diff($tables, $filter_tables));

         $tables = [
            0 => "brands",
            1 => "products",
            2 => "stores",
            3 => "banners",
            4 => "orders",
        ];
        $rows = [];
        foreach ($tables as $table) {
            $count = DB::table($table)->count();
            array_push($rows, $count);
        }

        return view('admin-views.business-settings.db-index', compact('tables', 'rows'));
    }
    public function clean_db(Request $request)
    {
        $tables = (array)$request->tables;

        if (count($tables) == 0) {
            Toastr::error('No Table Updated');
            return back();
        }

        try {
            DB::transaction(function () use ($tables) {
                foreach ($tables as $table) {
                    if($table=="brands")
                        DB::table("products")->delete();
                    if($table=="orders")
                    {
                        DB::table("order_details")->delete();
                        DB::table("bags_orders_details")->delete();
                    }
                    DB::table($table)->delete();
                }
            });
        } catch (\Exception $exception) {
            Toastr::error('Failed to update!');
            return back();
        }


        Toastr::success('Updated successfully!');
        return back();
    }
}
