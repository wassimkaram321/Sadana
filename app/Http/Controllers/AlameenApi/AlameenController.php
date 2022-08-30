<?php

namespace App\Http\Controllers\AlameenApi;

use App\Http\Controllers\Controller;
use App\Model\Product;
use App\Model\Brand;
use App\Model\Store;
use App\User;
use App\Pharmacy;
use App\Http\Traits\GeneralTrait;
use App\Http\Requests\ProductRequest;
use App\Http\Requests\BrandsRequest;
use App\Http\Requests\PharmicesRequest;
use App\Http\Requests\StoresRequest;
use App\CPU\BackEndHelper;
use Illuminate\Support\Str;
use App\CPU\Helpers;
use App\CPU\ImageManager;

class AlameenController extends Controller
{
    use GeneralTrait;

    //Stores
    public function StoreProducts(ProductRequest $request)
    {
        try {

            foreach ($request->products as $product) {

                if ($product['discount_type'] == 'percent') {
                    $dis = ($product['unit_price'] / 100) * $product['discount'];
                } else {
                    $dis = $product['discount'];
                }

                if ($product['unit_price'] <= $dis) {
                    return $this->returnError('unit_price', 'Discount can not be more or equal to the price!');
                }

                $p = new Product();
                $p->user_id = 9999999;
                $p->added_by = "admin";
                $p->name = $product['name'];
                $p->slug = Str::slug($product['name'], '-') . '-' . Str::random(6);
                $p->store_id = $product['store_id'];

                $p->production_date = $product['production_date'];
                $p->expiry_date = $product['expiry_date'];


                $category = [];
                array_push($category, [
                    'id' => 9999999,
                    'position' => 10,
                ]);
                $p->category_ids = json_encode($category);


                $choice_options = [];
                $p->choice_options = json_encode($choice_options);


                $colors = [];
                $p->colors = json_encode($colors);


                //combinations start
                $options = [];
                $combinations = Helpers::combinations($options);


                $variations = [];
                $p->variation = json_encode($variations);

                $p->attributes = json_encode($request->choice_attributes);

                $p->brand_id = $product['brand_id'];
                $p->unit = $product['unit'];
                $p->details = $product['description'];


                $stock_count = (int)$product['current_stock'];
                $p->current_stock = abs($stock_count);


                $p->unit_price = BackEndHelper::currency_to_usd($product['unit_price']);
                $p->purchase_price = BackEndHelper::currency_to_usd($product['purchase_price']);
                $p->tax = $product['tax_type'] == 'flat' ? BackEndHelper::currency_to_usd($product['tax']) : $product['tax'];
                $p->tax_type = $product['tax_type'];
                $p->discount = $product['discount_type'] == 'flat' ? BackEndHelper::currency_to_usd($product['discount']) : $product['discount'];
                $p->discount_type = $product['discount_type'];

                $p->num_id = $product['num_id'];

                $p->shipping_cost = BackEndHelper::currency_to_usd($product['shipping_cost']);

                if ($request->file('image')) {
                    $product_images[] = ImageManager::upload('product/', 'png', $request->file('image'));
                    $p->images = json_encode($product_images);
                }

                if ($request->file('thumbnail_image')) {
                    $p->thumbnail = ImageManager::upload('product/thumbnail/', 'png', $request->thumbnail_image);
                }
                $p->save();
            }
            return $this->returnSuccessMessage(' products details stored successfully');

        } catch (\Exception $e) {
            return $this->returnError($e);
        }
    }

    public function StoreBrands(BrandsRequest $request)
    {
        try {
            foreach ($request->brands as $brand) {

                $b = new Brand();
                $b->id=$brand['brand_id'];
                $b->name = $brand['brand_name'];
                if ($request->file('brand_image')) {
                    $b->image = ImageManager::upload('brand/', 'png',$request->file('brand_image'));
                }
                $b->status = 1;
                $b->save();
            }
            return $this->returnSuccessMessage(' brands details stored successfully');
        } catch (\Exception $e) {
            return $this->returnError($e);
        }
    }


    public function SaveStores(StoresRequest $request)
    {
        try {
            foreach ($request->stores as $store) {
                $s = new Store();
                $s->id=$store['store_id'];
                $s->store_name = $store['store_name'];
                if ($request->file('store_image')) {
                    $s->store_image = ImageManager::upload('store/', 'png', $request->file('store_image'));
                }
                $s->store_status = 1;
                $s->save();
            }
            return $this->returnSuccessMessage('stores details stored successfully');
        } catch (\Exception $e) {
            return $this->returnError($e);
        }
    }


    public function SavePharmices(PharmicesRequest $request)
    {
        try {

            foreach ($request->Pharmacies as $pharmacy) {

                //user information
                $u = new User();
                $u->f_name = $pharmacy['f_name'];
                $u->l_name = $pharmacy['l_name'];
                $u->phone = $pharmacy['phone'];
                $u->is_active = 1;
                $u->is_phone_verified = 1;

                if ($pharmacy['email'])
                    $u->email = $pharmacy['email'];
                if ($pharmacy['password'])
                    $u->password = bcrypt($pharmacy['password']);



                //Pharmacy information
                $p = new Pharmacy();
                $p->name = $pharmacy['name'];
                $p->land_number = $pharmacy['land_number'];
                $p->from = $pharmacy['from'];
                $p->to = $pharmacy['to'];
                $p->statusToday = $pharmacy['statusToday'];
                $p->Address = $pharmacy['Address'];
                $p->city = $pharmacy['city'];
                $p->user_type_id ="pharmacist";
                $p->lat = $pharmacy['lat'];
                $p->lan = $pharmacy['lan'];
                $p->region = $pharmacy['region'];


                $u->save();
                $p->user_id = $u->id;
                $p->save();
            }

            return $this->returnSuccessMessage(' Pharmacies details stored successfully');
        } catch (\Exception $e) {
            return $this->returnError($e);
        }
    }


    //Export

    // public function ExportOrders()
    // {
    //     try {
    //         $orders = Order::with('details')->select('customer_id')->get();
    //         //$orders = Order::with('details')->get();
    //         return $this->returnData('Details', $orders ,'Orders details');
    //     } catch (\Exception $e) {
    //         return $this->returnError($e);
    //     }
    // }


    // public function ExportPharmices()
    // {
    //     try {

    //         return $this->returnData('Details', " ", 'Pharmacies details');
    //     } catch (\Exception $e) {
    //         return $this->returnError($e);
    //     }
    // }


}
