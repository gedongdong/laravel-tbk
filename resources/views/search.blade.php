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
                <span>输入商品名称</span>
            </label>
        </form>
        <a href="javascript:" class="weui-search-bar__cancel-btn" id="searchCancel">取消</a>
    </div>

    {{--列表--}}
    <div class="weui-panel weui-panel_access">
        <div class="weui-panel__bd" id="list_item">

        </div>
        <div class="weui-loadmore" id="more">
            <span id="page" style="display: none;">{{ $page }}</span>
            <i class="weui-loading"></i>
            <span class="weui-loadmore__tips">正在加载</span>
        </div>
        <div class="weui-footer" id="footer" style="display: none;margin: 0.5rem;">
            <p class="weui-footer__text">~已经到底了~</p>
        </div>
        <div class="weui-footer" id="nomore" style="display: none;margin: 0.5rem;">
            <p class="weui-footer__text">~没有搜索到商品~</p>
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

        $(document).ready(function () {
            var page = {{ $page }};
            $.get("/search/more?page={{ $page }}&q={{ $q }}", function (data) {
                if (!data.html) {
                    $('#more').hide();
                    if(page == 1){
                        $('#nomore').show();
                    }else{
                        $('#footer').show();
                    }
                } else {
                    $('#page').html(parseInt(data.page) + 1);
                    $('#list_item').append(data.html);
                    loading = false;
                    if(data.flag === 'no'){
                        $('#more').hide();
                        $('#nomore').hide();
                        $('#footer').show();
                    }
                }
            });
        });

        $(document.body).infinite(200);
        var loading = false;  //状态标记
        $(document.body).infinite().on("infinite", function () {
            var page = parseInt($('#page').html());
            if (loading) return;
            loading = true;
            $.get("/search/more?q={{ $q }}&page=" + page, function (data) {
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

        $("form").on("submit", function (event) {
            var q = $('#searchInput').val();
            window.location.href = '/search?q=' + q;
            return false;
        });

    </script>
@endsection