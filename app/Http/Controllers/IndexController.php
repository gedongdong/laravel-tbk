<?php
/**
 * User: gedongdong
 * Date: 2020-03-21 21:20
 */

namespace App\Http\Controllers;


use App\Models\Product;
use Illuminate\Http\Request;

class IndexController extends Controller
{
    public function index()
    {
        $products = Product::where('status', 1)->whereNotNull('coupon_info')->where('coupon_end_time', '>', date('Y-m-d'))->orderBy('volume', 'desc')->paginate(20);
        return view('index', ['products' => $products]);
    }

    public function more(Request $request)
    {
        $page = $request->get('page', 2);

        $products = Product::where('status', 1)->whereNotNull('coupon_info')->where('coupon_end_time', '>', date('Y-m-d'))->orderBy('volume', 'desc')->paginate(20)->toArray();

        $flag = 'yes';
        if ($page >= $products['last_page']) {
            $flag = 'no';
        }

        $html = '';
        foreach ($products['data'] as $pro) {
            $html .= '<a href="javascript:void(0)" onclick="show('.$pro['id'].')" class="weui-media-box weui-media-box_appmsg">';
            $html .= '<div class="weui-media-box__hd" style="height: 5.3rem;width: 5.3rem;line-height: 5.3rem;">';
            $html .= '<img class="weui-media-box__thumb" src="' . $pro['pict_url'] . '">';
            $html .= '</div>';
            $html .= '<div class="weui-media-box__bd" style="height: 5.3rem;">';
            $html .= '<p class="weui-media-box__desc" style="font-weight: 400;font-size: 0.7rem;color:#333;line-height: 1rem;">' . $pro['title'] . '</p>';
            if ($pro['nick']) {
                $html .= '<p class="weui-media-box__desc" style="margin-top: 0.2rem;">';
                if ($pro['user_type'] == 0) {
                    $html .= '<p style="width: 0.6rem;height: 0.6rem;display: block;float: left;"><img src="/img/taobao.jpg" alt="" style="height: 0.6rem;width: 0.6rem;"></p>';
                } else {
                    $html .= '<p style="width: 0.6rem;height: 0.6rem;display: block;float: left;"><img src="/img/tmail.jpeg" alt="" style="height: 0.6rem;width: 0.6rem;"></p>';
                }
                $html .= '<p style="display: block;float: left;line-height: 0.6rem; padding-left: 0.3rem;color:#999;font-size: 0.5rem;">' . $pro['nick'] . '</p> </p>';
            }
            $html .= '<p style="clear: both;"></p>';
            $html .= '<p class="weui-media-box__desc" style="margin-top: 0.2rem;">';
            $html .= '<p style="width: 1rem;height: 0.9rem;display: block;float: left;background-image: url(\'/img/quan1.png\');background-size: 1rem 0.9rem;background-repeat:no-repeat;color:#fff;font-size: 0.7rem;text-align: center;border-radius: 0.1rem 0 0 0.1rem;line-height: 0.9rem;"></p>';
            $html .= '<p style="display: block;float: left;line-height: 1rem; padding:0 0.1rem;color:#f06060;font-size: 0.6rem;">' . $pro['coupon_price'] . '元</p>';
            $html .= '<p style="display: block;float: right;line-height: 0.8rem; padding:0 0.2rem;color:#999;font-size: 0.6rem;">销量 ' . $pro['volume'] . '</p>';
            $html .= '<p style="clear: both;"></p>';
            $html .= '<p class="weui-media-box__desc" style="margin-top: 0.2rem;">';
            $html .= '<p style="display: block;float: left;line-height: 0.8rem; padding:0 0.2rem 0 0;color:#999;font-size: 0.6rem;">券后 <span style="color:#666;font-weight: bold;font-size: 0.8rem;">¥' . ($pro['zk_final_price'] - $pro['coupon_price']) . '</span></p>';
            $html .= '<p style="display: block;float: left;line-height: 0.8rem; padding:0 0.2rem;color:#999;font-size: 0.6rem;text-decoration: line-through;">￥' . $pro['zk_final_price'] . '</p></p></div></a>';
            $html .= '<div id="tkl_'.$pro['id'].'" style="background-color: #eee;width: 100%;height: auto;font-size: 0.5rem;color:#666;display: none;">';
            $html .= '<div style="padding: 0.6rem;">';
            $html .= '<div style="padding: 0.5rem;">';
            $html .= '<div>'.$pro['title'].'</div>';
            $html .= '<div>【在售价】'.$pro['zk_final_price'].'</div>';
            $html .= '<div>【券后价】'.($pro['zk_final_price']-$pro['coupon_price']).'</div>';
            $html .= '<div>------------</div>';
            $html .= '<div>注意：请完整复制这条信息，'.$pro['tkpwd'].'，到【手机淘宝】即可查看或分享给好友</div>';
            $html .= '<input id="txt_'.$pro['id'].'" type="hidden" value="'.$pro['title'].'----【在售价】'.$pro['zk_final_price'].'元----【券后价】'.($pro['zk_final_price']-$pro['coupon_price']).'元----注意：请完整复制这条信息，'.$pro['tkpwd'].'，到【手机淘宝】即可查看或分享给好友"></div>';
            $html .= '<div style="text-align: right;"  onclick="copyText('.$pro['id'].')">';
            $html .= '<a href="javascript:void(0);" class="weui-btn weui-btn_mini weui-btn_primary">点击复制淘口令</a></div></div></div>';
        }

        $res = [
            'page' => $page,
            'flag' => $flag,
            'html' => $html
        ];
        return response()->json($res);
    }
}