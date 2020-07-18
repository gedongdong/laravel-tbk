<?php

namespace App\Console\Commands;

use App\Models\Favorites;
use App\Models\Product;
use App\Models\ProductDetail;
use App\Models\Recommend;
use ETaobao\Factory;
use Illuminate\Console\Command;

class GetDetail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'get:detail';

    protected $secretKey = '';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '获取商品详情';

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
        $this->secretKey = env('tbk_secret', '');

        $config = [
            'appkey'    => '28240849',
            'secretKey' => $this->secretKey,
            'format'    => 'json',
            'simplify'  => true,
        ];

        $app = Factory::Tbk($config);

        echo '获取本地商品id...' . PHP_EOL;
        $pro_ids       = Product::pluck("num_iid")->toArray();
        $recommend_ids = Recommend::pluck("recommend_id")->toArray();
        $ids           = array_unique(array_merge($pro_ids, $recommend_ids));

        $chunk_ids = array_chunk($ids, 40);
        $bar       = $this->output->createProgressBar(count($chunk_ids));
        echo '获取商品详情...' . PHP_EOL;
        foreach ($chunk_ids as $id) {
            $bar->advance();
            $param["num_iids"] = implode($id, ',');

            $res = $app->item->getInfo($param);
            if ($res->results->n_tbk_item) {
                foreach ($res->results->n_tbk_item as $item) {
                    $small_images = $item->small_images ?? '';
                    if ($small_images) {
                        $small_images = (array)$small_images ? implode(',', array_values((array)$small_images)[0]) : '';
                    }
                    ProductDetail::updateOrCreate(
                        ['num_iid' => $item->num_iid],
                        [
                            'cat_name'       => $item->cat_name,
                            'title'          => $item->title,
                            'pict_url'       => $item->pict_url,
                            'small_images'   => $small_images,
                            'reserve_price'  => $item->reserve_price,
                            'zk_final_price' => $item->zk_final_price,
                            'user_type'      => $item->user_type,
                            'provcity'       => $item->provcity,
                            'item_url'       => $item->item_url,
                            'seller_id'      => $item->seller_id,
                            'volume'         => $item->volume,
                            'nick'           => $item->nick,
                            'cat_leaf_name'  => $item->cat_leaf_name ?? null,
                        ]
                    );
                }
            }
        }

    }
}
