@extends('layouts.web')

@section('content')
    {{--搜索--}}
    <div class="weui-search-bar" id="searchBar">
        <form class="weui-search-bar__form">
            <div class="weui-search-bar__box">
                <i class="weui-icon-search"></i>
                <input type="search" class="weui-search-bar__input" id="searchInput" placeholder="输入商品名称" required="">
                <a href="javascript:" class="weui-icon-clear" id="searchClear"></a>
            </div>
            <label class="weui-search-bar__label" id="searchText">
                <i class="weui-icon-search"></i>
                <span>输入商品标题</span>
            </label>
        </form>
        <a href="javascript:" class="weui-search-bar__cancel-btn" id="searchCancel">取消</a>
    </div>

    <div style="width: 100%;"><img style="width: 100%;" src="/img/buzhou.jpg" alt=""></div>

    {{--列表--}}
    <div class="weui-panel weui-panel_access">
        <div class="weui-panel__bd" id="list_item">
            @foreach($products as $pro)
                <a href="javascript:void(0)" onclick="show({{ $pro->id }})"
                   class="weui-media-box weui-media-box_appmsg">
                    <div class="weui-media-box__hd" style="height: 5.3rem;width: 5.3rem;line-height: 5.3rem;">
                        <img class="weui-media-box__thumb" src="{{ $pro->pict_url }}">
                    </div>
                    <div class="weui-media-box__bd" style="height: 5.3rem;">
                        <p class="weui-media-box__desc"
                           style="font-weight: 400;font-size: 0.7rem;color:#333;line-height: 1rem;">{{ $pro->title }}</p>
                        @if($pro->nick)
                            <p class="weui-media-box__desc" style="margin-top: 0.2rem;">
                            <p style="width: 0.6rem;height: 0.6rem;display: block;float: left;"><img
                                        src=@if($pro->user_type==1)"/img/tmail.jpeg"@else"/img/taobao.jpg"@endif alt=""
                                style="height: 0.6rem;width: 0.6rem;"></p>
                            <p style="display: block;float: left;line-height: 0.6rem; padding-left: 0.3rem;color:#999;font-size: 0.5rem;">{{ $pro->nick }}</p>
                            </p>
                        @endif
                        <p style="clear: both;"></p>
                        <p class="weui-media-box__desc" style="margin-top: 0.2rem;">
                        <p style="width: 1rem;height: 0.9rem;display: block;float: left;background-image: url('/img/quan1.png');background-size: 1rem 0.9rem;background-repeat:no-repeat;color:#fff;font-size: 0.7rem;text-align: center;border-radius: 0.1rem 0 0 0.1rem;line-height: 0.9rem;"></p>
                        <p style="display: block;float: left;line-height: 1rem; padding:0 0.1rem;color:#f06060;font-size: 0.6rem;">{{ $pro->coupon_price }}
                            元</p>
                        <p style="display: block;float: right;line-height: 0.8rem; padding:0 0.2rem;color:#999;font-size: 0.6rem;">
                            销量 {{ $pro->volume }}</p>
                        <p style="clear: both;"></p>
                        <p class="weui-media-box__desc" style="margin-top: 0.2rem;">
                        <p style="display: block;float: left;line-height: 0.8rem; padding:0 0.2rem 0 0;color:#999;font-size: 0.6rem;">
                            券后 <span
                                    style="color:#666;font-weight: bold;font-size: 0.8rem;">¥{{ $pro->zk_final_price-$pro->coupon_price }}</span>
                        </p>
                        <p style="display: block;float: left;line-height: 0.8rem; padding:0 0.2rem;color:#999;font-size: 0.6rem;text-decoration: line-through;">
                            ￥{{ $pro->zk_final_price }}</p>
                        </p>
                    </div>
                </a>
                <div id="tkl_{{ $pro->id }}"
                     style="background-color: #eee;width: 100%;height: auto;font-size: 0.5rem;color:#666;display: none;">
                    <div style="padding: 0.6rem;">
                        <div style="padding: 0.5rem;">
                            <div>{{ $pro->title }}</div>
                            <div>【在售价】{{ $pro->zk_final_price }}</div>
                            <div>【券后价】{{ $pro->zk_final_price-$pro->coupon_price }}</div>
                            <div>------------</div>
                            <div>注意：请完整复制这条信息，{{ $pro->tkpwd }}，到【手机淘宝】即可查看或分享给好友</div>
                            <input id="txt_{{ $pro->id }}" type="hidden"
                                   value="{{ $pro->title }}----【在售价】{{ $pro->zk_final_price }}元----【券后价】{{ $pro->zk_final_price-$pro->coupon_price }}元----注意：请完整复制这条信息，{{ $pro->tkpwd }}，到【手机淘宝】即可查看或分享给好友">
                        </div>
                        <div style="text-align: right;" onclick="copyText({{ $pro->id }})">
                            <a href="javascript:void(0);" class="weui-btn weui-btn_mini weui-btn_primary">点击复制淘口令</a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        <div class="weui-loadmore" id="more">
            <span id="page" style="display: none;">2</span>
            <i class="weui-loading"></i>
            <span class="weui-loadmore__tips">正在加载</span>
        </div>
        <div class="weui-footer" id="footer" style="display: none;margin: 0.5rem;">
            <p class="weui-footer__text">~已经到底了~</p>
        </div>
    </div>
@endsection

@section('script')
    <script src='/weui/js/swiper.js'></script>
    <script src='/zclip/jquery.zclip.js'></script>
    <script>
        $(".swiper-container").swiper({
            loop: true,
            autoplay: 3000
        });

        $(document.body).infinite(200);
        var loading = false;  //状态标记
        $(document.body).infinite().on("infinite", function () {
            var page = parseInt($('#page').html());
            if (loading) return;
            loading = true;
            $.get("/more?page=" + page, function (data) {
                $('#page').html(parseInt(data.page) + 1);
                if (data.html) {
                    $('#list_item').append(data.html);
                    loading = false;
                }
                if (data.flag === 'no') {
                    $(document.body).destroyInfinite();//没数据了销毁
                    $('#more').hide();
                    $('#footer').show();
                }
            });
        });

        function show(id) {
            if ($('#tkl_' + id).css('display') === 'none') {
                $('#tkl_' + id).css({'display': 'block'});
            } else {
                $('#tkl_' + id).hide();
            }
        }

        function copyText(id, message) {
            var content = $('#txt_' + id).val();
            var aux = document.createElement("input");
            aux.setAttribute("value", content);
            document.body.appendChild(aux);
            aux.select();
            document.execCommand("copy");
            document.body.removeChild(aux);
            if (message == null) {
                $.toast("复制成功，分享好友或直接打开淘宝领券", "text");
            } else {
                $.toast(message, "text");
            }
        }

        $("form").on("submit",function(event){
            var q = $('#searchInput').val();
            window.location.href = '/search?q=' + q;
            return false;
        });

    </script>
@endsection