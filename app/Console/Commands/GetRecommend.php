<?php

namespace App\Console\Commands;

use App\Models\Favorites;
use App\Models\Product;
use App\Models\ProductDetail;
use App\Models\Recommend;
use ETaobao\Factory;
use Illuminate\Console\Command;

class GetRecommend extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'get:recommend';

    protected $secretKey = '';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '获取商品关联推荐';

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
        $ids = Product::pluck("num_iid")->toArray();

        $bar = $this->output->createProgressBar(count($ids));
        echo '获取商品关联信息...' . PHP_EOL;
        foreach ($ids as $id) {
            $bar->advance();
            $param["item_id"]     = $id;
            $param["adzone_id"]   = 110070750232;
            $param["material_id"] = 13256;

            $res    = $app->dg->materialOptimus($param);
            $result = $res->result_list->map_data ?? '';
            if ($result) {
                foreach ($result as $item) {
                    $small_images = $item->small_images ?? '';
                    if ($small_images) {
                        $small_images = (array)$small_images ? implode(',', array_values((array)$small_images)[0]) : '';
                    }

                    Recommend::updateOrCreate(
                        ['main_id' => $id, 'recommend_id' => $item->item_id],
                        [
                            'title'          => $item->title,
                            'pict_url'       => $item->pict_url,
                            'small_images'   => $small_images,
                            'reserve_price'  => $item->reserve_price ?? null,
                            'zk_final_price' => $item->zk_final_price,
                            'user_type'      => $item->user_type,
                            'provcity'       => $item->provcity ?? null,
                            'item_url'       => $item->item_url ?? null,
                            'seller_id'      => $item->seller_id ?? null,
                            'volume'         => $item->volume ?? 0,
                            'nick'           => $item->nick ?? null,
                        ]
                    );
                }
            }
        }
    }
}
