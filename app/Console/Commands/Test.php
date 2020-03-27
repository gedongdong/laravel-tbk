<?php

namespace App\Console\Commands;

use App\Models\Favorites;
use App\Models\Product;
use ETaobao\Factory;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class Test extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:taobaoke';

    protected $secretKey = 'de2b0f56cd0e164dfef62c656c699ac1';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $config = [
            'appkey'    => '28240849',
            'secretKey' => $this->secretKey,
            'format'    => 'json',
            'simplify'  => true,
        ];

        $app = Factory::Tbk($config);

        //选品库
        echo '处理选品库。。。' . PHP_EOL;
        $param = [
            'fields' => 'favorites_title,favorites_id,type',
        ];
        $res   = $app->uatm->getFavorites($param);

        $results = $res->results ?? '';
        if ($results) {
            foreach ($res->results->tbk_favorites as $item) {
                Favorites::updateOrCreate(
                    ['fav_id' => $item->favorites_id],
                    [
                        'title' => $item->favorites_title,
                        'type'  => $item->type,
                    ]
                );
            }
        }

        $favids = Favorites::pluck('fav_id')->toArray();
        if ($favids) {
            $param = [
                'fields'    => 'num_iid,title,pict_url,small_images,reserve_price,zk_final_price,user_type,provcity,item_url,click_url,nick,seller_id,volume,tk_rate,zk_final_price_wap,shop_title,type,status,category,coupon_click_url,coupon_end_time,coupon_info,coupon_start_time,coupon_total_count,coupon_remain_count',
                'adzone_id' => 110070750232,
                'page_size' => 100,
            ];
            foreach ($favids as $favid) {
                echo '处理选品库中的商品。。。' . $favid . PHP_EOL;
                $param['favorites_id'] = $favid;

                $param['page_no'] = 1;

                $res1 = $app->uatm->getItemFavorites($param);
                $this->handleProduct($res1, $favid);

                $param['page_no'] = 2;

                $res2 = $app->uatm->getItemFavorites($param);
                $this->handleProduct($res2, $favid);
            }

            echo '开始生成淘口令...'.PHP_EOL;
            $products = Product::where('coupon_click_url','!=','')->select('id','coupon_click_url','tkpwd')->get();
            foreach ($products as $product){
                $param = [
                    'text' => '淘宝天猫优惠券',
                    'url' => $product->coupon_click_url
                ];
                $res3 = $app->tpwd->create($param);
                var_dump($res3);
                $result = $res3->data->model ?? '';
                if ($results) {
                    $product->tkpwd = $result;
                    $product->save();
                }
            }
        }

    }

    protected function handleProduct($res, $favid)
    {
        $results = $res->results->uatm_tbk_item ?? '';
        if ($results) {
            foreach ($results as $item) {
                print_r($item);
                $small_images = (array)$item->small_images ? implode(',', array_values((array)$item->small_images)[0]) : '';

                $coupon_price = $item->coupon_info ?? null;
                if ($coupon_price) {
                    $coupon_price = explode('减', $coupon_price);
                    $coupon_price = str_replace('元', '', $coupon_price[1]);
                }
                Product::updateOrCreate(
                    ['num_iid' => $item->num_iid],
                    [
                        'title'               => $item->title,
                        'favorites_id'        => $favid,
                        'pict_url'            => $item->pict_url,
                        'small_images'        => $small_images,
                        'reserve_price'       => $item->reserve_price ?? '',
                        'zk_final_price'      => $item->zk_final_price ?? '',
                        'user_type'           => $item->user_type,
                        'provcity'            => $item->provcity ?? '',
                        'item_url'            => $item->item_url,
                        'click_url'           => $item->click_url,
                        'nick'                => $item->nick ?? '',
                        'seller_id'           => $item->seller_id,
                        'volume'              => $item->volume ?? 0,
                        'tk_rate'             => $item->tk_rate,
                        'zk_final_price_wap'  => $item->zk_final_price_wap ?? '',
                        'shop_title'          => $item->shop_title ?? '',
                        'type'                => $item->type,
                        'status'              => $item->status,
                        'category'            => $item->category ?? '',
                        'coupon_click_url'    => $item->coupon_click_url ?? '',
                        'coupon_end_time'     => $item->coupon_end_time ?? '',
                        'coupon_info'         => $item->coupon_info ?? null,
                        'coupon_price'        => $coupon_price,
                        'coupon_start_time'   => $item->coupon_start_time ?? 0,
                        'coupon_total_count'  => $item->coupon_total_count ?? '',
                        'coupon_remain_count' => $item->coupon_remain_count ?? '',
                    ]
                );
            }
        }
    }

    protected function generateSign($params)
    {
        ksort($params);

        $stringToBeSigned = $this->secretKey;
        foreach ($params as $k => $v) {
            if (!is_array($v) && "@" != substr($v, 0, 1)) {
                $stringToBeSigned .= "$k$v";
            }
        }
        unset($k, $v);
        $stringToBeSigned .= $this->secretKey;

        return strtoupper(md5($stringToBeSigned));
    }
}
