<?php
/**
 * User: gedongdong
 * Date: 2020-03-26 21:49
 */

namespace App\Http\Controllers;


use App\Models\SearchLog;
use ETaobao\Factory;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function index(Request $request)
    {
        $q    = $request->get('q');
        $page = $request->get('page', 1);
        if (!$q) {
            return redirect('/');
        }

        return view('search', ['q' => $q, 'page' => $page]);
    }

    public function search(Request $request)
    {
        $secretKey = env('tbk_secret', '');

        $config = [
            'appkey'    => '28240849',
            'secretKey' => $secretKey,
            'format'    => 'json',
            'simplify'  => true,
        ];

        $app = Factory::Tbk($config);

        $result = [
            'error_code' => 0,
            'message'    => '',
            'data'       => '',
            'q'          => '',
            'page'       => 1,
            'flag'       => 'yes'
        ];

        $q = $request->get('q');
        if (!$q) {
            $result['error_code'] = 1;
            $result['message']    = '请输入名称';
            return response($result);
        }

        $page     = $request->get('page', 1);
        $per_page = $request->get('per_page', 20);

        $searchLog    = new SearchLog();
        $searchLog->q = $q;
        $searchLog->save();

        $html = '';
        $num  = 0;
        while (true) {
            $res            = $this->getPro($app, $q, $page, $per_page);
            $result['flag'] = $res['flag'];
            $html           .= $res['html'];
            $num            += $res['num'];
            if ($num >= 20) {
                break;
            }
            $page++;
        }

        $result['q']    = $q;
        $result['page'] = $page;
        $result['html'] = $html;

        return response($result);
    }

    protected function getPro($app, $q, $page, $per_page)
    {
        $result = [
            'html' => '',
            'num'  => 0,
            'flag' => 'yes'
        ];

        $param = [
            'sort'      => 'total_sales_des',
            'q'         => $q,
            'adzone_id' => 110070750232,
            'page_no'   => $page
        ];

        $res = $app->dg->materialOptional($param);

        $products = $res->result_list->map_data ?? [];
        if (!$products || (ceil($res->total_results / $per_page) < $page)) {
            $result['flag'] = 'no';
            return $result;
        }

        $html = '';
        $num  = 0;
        foreach ($products as $pro) {
            $pict_url    = $pro->pict_url ?? '';
            $coupon_info = $pro->coupon_info ?? '';
            $coupon_url  = $pro->coupon_share_url ?? '';
            if (!$pict_url) {
                continue;
            }
            if (!$coupon_info) {
                continue;
            }
            if (!$coupon_url) {
                continue;
            }
            $coupon_price = explode('减', $coupon_info);
            $coupon_price = str_replace('元', '', $coupon_price[1]);

            $param = [
                'text' => '淘宝天猫优惠券',
                'url'  => 'https:' . $coupon_url
            ];
            $res3  = $app->tpwd->create($param);
            $tkpwd = $res3->data->model ?? '';
            if (!$tkpwd) {
                continue;
            }

            $num++;

            $html .= '<a href="javascript:void(0)" onclick="show(' . $pro->num_iid . ')" class="weui-media-box weui-media-box_appmsg">';
            $html .= '<div class="weui-media-box__hd" style="height: 5.3rem;width: 5.3rem;line-height: 5.3rem;">';
            $html .= '<img class="weui-media-box__thumb" src="' . $pro->pict_url . '">';
            $html .= '</div>';
            $html .= '<div class="weui-media-box__bd" style="height: 5.3rem;">';
            $html .= '<p class="weui-media-box__desc" style="font-weight: 400;font-size: 0.7rem;color:#333;line-height: 1rem;">' . $pro->title . '</p>';
            if ($pro->nick) {
                $html .= '<p class="weui-media-box__desc" style="margin-top: 0.2rem;">';
                if ($pro->user_type == 0) {
                    $html .= '<p style="width: 0.6rem;height: 0.6rem;display: block;float: left;"><img src="/img/taobao.jpg" alt="" style="height: 0.6rem;width: 0.6rem;"></p>';
                } else {
                    $html .= '<p style="width: 0.6rem;height: 0.6rem;display: block;float: left;"><img src="/img/tmail.jpeg" alt="" style="height: 0.6rem;width: 0.6rem;"></p>';
                }
                $html .= '<p style="display: block;float: left;line-height: 0.6rem; padding-left: 0.3rem;color:#999;font-size: 0.5rem;">' . $pro->nick . '</p> </p>';
            }
            $html .= '<p style="clear: both;"></p>';
            $html .= '<p class="weui-media-box__desc" style="margin-top: 0.2rem;">';
            $html .= '<p style="width: 1rem;height: 0.9rem;display: block;float: left;background-image: url(\'/img/quan1.png\');background-size: 1rem 0.9rem;background-repeat:no-repeat;color:#fff;font-size: 0.7rem;text-align: center;border-radius: 0.1rem 0 0 0.1rem;line-height: 0.9rem;"></p>';
            $html .= '<p style="display: block;float: left;line-height: 1rem; padding:0 0.1rem;color:#f06060;font-size: 0.6rem;">' . $coupon_price . '元</p>';
            $html .= '<p style="display: block;float: right;line-height: 0.8rem; padding:0 0.2rem;color:#999;font-size: 0.6rem;">销量 ' . $pro->volume . '</p>';
            $html .= '<p style="clear: both;"></p>';
            $html .= '<p class="weui-media-box__desc" style="margin-top: 0.2rem;">';
            $html .= '<p style="display: block;float: left;line-height: 0.8rem; padding:0 0.2rem 0 0;color:#999;font-size: 0.6rem;">券后 <span style="color:#666;font-weight: bold;font-size: 0.8rem;">¥' . ($pro->zk_final_price - $coupon_price) . '</span></p>';
            $html .= '<p style="display: block;float: left;line-height: 0.8rem; padding:0 0.2rem;color:#999;font-size: 0.6rem;text-decoration: line-through;">￥' . $pro->zk_final_price . '</p></p></div></a>';
            $html .= '<div id="tkl_' . $pro->num_iid . '" style="background-color: #eee;width: 100%;height: auto;font-size: 0.5rem;color:#666;display: none;">';
            $html .= '<div style="padding: 0.6rem;">';
            $html .= '<div style="padding: 0.5rem;">';
            $html .= '<div>' . $pro->title . '</div>';
            $html .= '<div>【在售价】' . $pro->zk_final_price . '</div>';
            $html .= '<div>【券后价】' . ($pro->zk_final_price - $coupon_price) . '</div>';
            $html .= '<div>------------</div>';
            $html .= '<div>注意：请完整复制这条信息，' . $tkpwd . '，到【手机淘宝】即可查看或分享给好友</div>';
            $html .= '<input id="txt_' . $pro->num_iid . '" type="hidden" value="' . $pro->title . '----【在售价】' . $pro->zk_final_price . '元----【券后价】' . ($pro->zk_final_price - $coupon_price) . '元----注意：请完整复制这条信息，' . $tkpwd . '，到【手机淘宝】即可查看或分享给好友"></div>';
            $html .= '<div style="text-align: right;"  onclick="copyText(' . $pro->num_iid . ')">';
            $html .= '<a href="javascript:void(0);" class="weui-btn weui-btn_mini weui-btn_primary">点击复制淘口令</a></div></div></div>';
        }

        $result['num']  = $num;
        $result['html'] = $html;

        return $result;
    }
}