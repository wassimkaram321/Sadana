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
use Illuminate\Support\Facades\DB;

class AlameenController extends Controller
{
    use GeneralTrait;

    //Stores
    public function StoreProducts(ProductRequest $request)
    {
        try {

            foreach ($request->products as $product) {


                if (isset($product['store_id'])) {
                    $store = Store::where('id', 'LIKE', '%' . $product['store_id'] . '%')->get()->first();
                    if (isset($store)) {
                        $store_id = $store->id;
                    } else {
                        return $this->returnError("store_id not found please insert stores Records ");
                    }
                } else {
                    if (isset($product['store_name'])) {
                        $store = Store::where('store_name', 'LIKE', '%' . $product['store_name'] . '%')->get()->first();
                        if (isset($store)) {
                            $store_id = $store->id;
                        } else {
                            $NewStore = new Store();
                            $NewStore->store_name = $product['store_name'];
                            $NewStore->store_image = 'def.png';
                            $NewStore->store_status = 1;
                            $NewStore->save();
                            $store_id = $NewStore->id;
                        }
                    } else {
                        $store = Store::take(1)->first();
                        if (isset($store)) {
                            $store_id = $store->id;
                        } else {
                            $NewStore = new Store();
                            $NewStore->store_name = "Hiba Store";
                            $NewStore->store_image = 'def.png';
                            $NewStore->store_status = 1;
                            $NewStore->save();
                            $store_id = $NewStore->id;
                        }
                    }
                }




                if (isset($product['brand_id'])) {
                    $brand = Brand::where('id', $product['brand_id'])->get()->first();
                    if (isset($brand)) {
                        $brand_id = $brand->id;
                    } else {
                        return $this->returnError("brand_id not found please insert brands Records ");
                    }
                } else {
                    if (isset($product['brand'])) {
                        $brand = Brand::where('name', $product['brand'])->get()->first();
                        if (isset($brand)) {
                            $brand_id = $brand->id;
                        } else {
                            $NewBrand = new Brand();
                            $NewBrand->name = $product['brand'];
                            $NewBrand->image = 'def.png';
                            $NewBrand->status = 1;
                            $NewBrand->save();
                            $brand_id = $NewBrand->id;
                        }
                    }
                }

                $category = [];
                array_push($category, [
                    'id' => 9999999,
                    'position' => 10,
                ]);
                $data = [];
                // $productOld = Product::where('name',$product['name'])->get()->first();
                $productOld = Product::where('num_id', $product['num_id'])->get()->first();

                if (isset($productOld)) {

                    $productOld->num_id = $product['num_id'];
                    $productOld->unit_price = $product['unit_price'];

                    $productOld->purchase_price = $product['purchase_price'];

                    $productOld->current_stock = $product['quantity'];


                    $productOld->details = $product['notes'];
                    $productOld->scientific_formula = $product['Scientific_formula'];

                    $productOld->q_normal_offer = $product['q_normal_offer'];
                    $productOld->normal_offer = $product['normal_offer'];
                    $productOld->q_featured_offer = $product['q_featured_offer'];
                    $productOld->featured_offer = $product['featured_offer'];

                    $productOld->expiry_date = $product['expiry_date'];
                    $productOld->production_date = $product['production_date'];
                    $productOld->demand_limit = $product['demand_limit'];
                    $productOld->store_id = $store_id;

                    $productOld->save();
                } else {

                    array_push($data, [
                        'num_id' => $product['num_id'],
                        'name' => $product['name'],
                        'unit_price' => $product['unit_price'],
                        'purchase_price' => $product['purchase_price'],
                        'current_stock' => $product['quantity'],
                        'details' => $product['notes'],
                        'scientific_formula' => $product['Scientific_formula'],
                        'q_normal_offer' => $product['q_normal_offer'],
                        'q_featured_offer' => $product['q_featured_offer'],
                        'normal_offer' => $product['normal_offer'],
                        'featured_offer' => $product['featured_offer'],
                        'expiry_date' => $product['expiry_date'],
                        'production_date' => $product['production_date'],
                        'demand_limit' => $product['demand_limit'],
                        'brand_id' => $brand_id,
                        'store_id' => $store_id,


                        //By defult
                        'unit' => "pc",
                        'category_ids' => json_encode($category),
                        'refundable' => false,
                        'video_provider' => 'youtube',
                        'thumbnail' => 'def.png',
                        'images' => json_encode(['def.png']),
                        'slug' => Str::slug($product['name'], '-') . '-' . Str::random(6),
                        'status' => 1,
                        'request_status' => 1,
                        'colors' => json_encode([]),
                        'attributes' => json_encode([]),
                        'choice_options' => json_encode([]),
                        'variation' => json_encode([]),
                        'featured_status' => 1,
                        'added_by' => 'admin',
                        'user_id' => 1,
                    ]);
                }

                if (count($data) > 0) {
                    DB::table('products')->insert($data);
                }
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
                $b->id = $brand['brand_id'];
                $b->name = $brand['brand_name'];
                if ($request->file('brand_image')) {
                    $b->image = ImageManager::upload('brand/', 'png', $request->file('brand_image'));
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
                $s->id = $store['store_id'];
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


    /*
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
                $p->user_type_id = "pharmacist";
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
    */
}
