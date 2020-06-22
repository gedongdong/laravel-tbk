<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <title>内购福利</title>
    <link rel="stylesheet" href="/js/layui/css/layui.css">
</head>
<body class="layui-layout-body">
<div class="layui-container">
    <form class="layui-form" action="" style="margin-top: 10%;margin-bottom: 3rem;">
        <h3 style="text-align: center;margin-bottom: 1rem;color:#666;">内 购 福 利</h3>
        <div class="layui-form-item" style="margin-bottom: 15px;">
            <div class="layui-input-block" style="margin-left: 0;">
                <input type="text" name="title" placeholder="请输入或粘贴淘宝、天猫商品名称" autocomplete="off" class="layui-input" style="border-radius: 0.5rem;border-color:#09bb07;">
            </div>
        </div>
        <div class="layui-form-item">
            <div class="layui-input-block" style="margin-left:0;text-align: center;">
                <button class="layui-btn layui-btn-normal" lay-submit lay-filter="formDemo" style="background-color: #09bb07;border-radius:0.5rem;">立即搜索</button>
                <button type="reset" class="layui-btn layui-btn-primary" style="border-radius:0.5rem;">清空</button>
            </div>
        </div>
    </form>
    <fieldset class="layui-elem-field">
        <legend style="font-size: 14px;"><span class="layui-badge-dot"></span> 使用方式</legend>
        <div class="layui-field-box">
            <ul style="color:#666;">
                <li>1. 直接搜索商品关键词</li>
                <li>2. 复制淘宝或天猫的商品名称，粘贴后搜索</li>
                <li>3. 点击商品复制淘口令，打开淘宝或天猫领券下单</li>
            </ul>
        </div>
    </fieldset>
</div>
<script src="/js/layui/layui.js"></script>
<script>
    //JavaScript代码区域
    layui.use(['element','form','layer'], function(){
        var element = layui.element;
        var form = layui.form;
        var layer = layui.layer;

        //监听提交
        form.on('submit(formDemo)', function(data){
            var title = data.field.title
            if(!title){
                layer.msg('请输入商品名称', {
                    icon: 3,
                    time: 2000 //2秒关闭（如果不配置，默认是3秒）
                })
            }else {
                window.location.href = '/search?q=' + title;
            }
            return false;
        });

        // form.verify({
        //     title: function(value, item){ //value：表单的值、item：表单的DOM对象
        //         if (!value){
        //             layer.tips('查询内容不能为空', '#search_input', {
        //                 tips: [3, '#09bb07'] //还可配置颜色
        //             });
        //             return false;
        //         }
        //     }
        // });
    });
</script>
</body>
</html>