$(function(){
    
    var obj = {};
    obj.log_form = $('.log_form');
    obj.log_form_shade = $('.log_form_shade');
    obj.log_success = $('.log_success');
    obj.h = obj.log_form.height();
    obj.account = $('#account');
    obj.password = $('#password');
    obj.member_pass = $('#member_pass');
    obj.submit = $('#submit');
    obj.log_inputs_tip = $('.log_inputs_tip');
    // 登录框载入动效
    obj.load = function(){
        var ele = this.log_form,
            h = this.h;
        TweenLite.fromTo(
            ele, 
            .5, 
            {top:h-20,opacity:0}, 
            {top:h,opacity:1}
        );
    }
    // 验证文本框输入
    obj.testInput = function(){
        var account_val = this.account.val(),
            password_val = this.password.val(),
            res = '',
            re = /^[a-zA-Z0-9$]([a-zA-Z0-9$])*$/,
            re_no_chinese = /[\u4E00-\u9FA5]|[\uFE30-\uFFA0]/g;
    
        if(account_val.length < 7 || account_val.length > 16 ){
            res = (account_val.length == 0) ? '帐号或密码不能为空。' : '帐号长度必须大于7位且小于16位。';
        }else{
            if(!re.test(account_val)) res = '帐号只能包含数字、字母、美元符号。';
            if(re_no_chinese.test(account_val)) res = '帐号不能包含中文。';
        }
        if(!password_val.length){
            res = '帐号或密码不能为空。';
        }
        return res;
    };
    // 设置警告语
    obj.warn = function(txt){
        if(txt){
            obj.log_inputs_tip.show();
            obj.log_inputs_tip.find('span').html(txt);
            return false;
        }else{
            obj.log_inputs_tip.hide();
        }
    };
    // 允许/禁止文本框输入
    obj.kill_input = function(bool){
        if(bool){
            obj.account.on('focus',function(){ this.blur(); });
            obj.password.on('focus',function(){ this.blur(); });
        }else{
            obj.account.off('focus');
            obj.password.off('focus');
        }
    };
    // loading图标
    obj.svg_loading = function(style){

        var css_name = style.css_name ? style.css_name+' svg_loading' : 'svg_loading',
            size = style.size || 20,
            color = style.color;

        return $('\
        <svg \
           version="1.1" \
           class="'+css_name+'" \
           x="0px" y="0px" \
           width="'+size+'px" height="'+size+'px" \
           viewBox="0 0 50 50" \
           style="enable-background:new 0 0 50 50;" \
           xml:space="preserve"\
       >\
           <path \
               fill="'+color+'" \
               d="M25.251,6.461c-10.318,0-18.683,8.365-18.683,18.683h4.068c0-8.071,6.543-14.615,14.615-14.615V6.461z"\
           >\
               <animateTransform \
                   attributeType="xml" \
                   attributeName="transform" \
                   type="rotate" \
                   from="0 25 25" to="360 25 25" \
                   dur="0.6s" \
                   repeatCount="indefinite" \
               />\
           </path>\
       </svg>');
    }
    // 各种事件
    obj.event = function(){

        this.submit.on('click',function(){

            var txt = obj.testInput();
            obj.warn(txt);

            if(txt){
                return false;
            }else{
                obj.kill_input(true);
                obj.log_form_shade.fadeIn();
                if(!obj.log_form_shade.html()){
                    obj.log_form_shade.append(obj.svg_loading({size:40,color:"#adff4d"}));
                }
            }

            $.ajax({
                type:'POST',
                url:'php/handle/user.php',
                data:{
                    act:"login",
                    sid:obj.account.val(),
                    password:obj.password.val()
                },
                success:function(data){
                    var status = JSON.parse(data).status;
                    if(status == 1){
                        if(obj.member_pass.get()[0].checked){
                            $.cookie('ky_log_toggle', 1, { expires: 7 });
                            $.cookie('ky_account', obj.account.val(), { expires: 7 });
                            $.cookie('ky_password', obj.password.val(), { expires: 7 });
                        }
                        window.location.href = 'http://localhost/kuyun/src/index.html';
                    }else if(status == 0){
                        obj.warn('帐号或密码错误');
                        obj.kill_input(false);
                        obj.log_form_shade.fadeOut();
                    }
                }
            });
        });

    };
    // 自动登录
    obj.autoLogin = function(){

        // 避免重复登录
        $.ajax({
            type:'POST',
            url:'php/handle/user.php',
            data:{
                act:'checkLogged'
            },
            success:function(data){

                // 后台无SESSION记录，未登录
                if(JSON.parse(data).status == 0){
                    // 上一次登录是否选择了7天免登录
                    // 是：执行ajax登录操作
                    if($.cookie('ky_log_toggle') == 1){
                        $.ajax({
                            type:'POST',
                            url:'php/handle/user.php',
                            data:{
                                act:'login',
                                sid:$.cookie('ky_account'),
                                password:$.cookie('ky_password'),
                            },
                            success:function(data){
                                // 正常情况，成功跳转
                                if(JSON.parse(data).status == 1){
                                    window.location.href = 'http://localhost/kuyun/src/index.html';
                                }else{
                                // 失败调出登录界面
                                    obj.load();
                                    obj.event();
                                }
                            }
                        });
                    // 否：调出登录界面
                    }else{
                        obj.load();
                        obj.event();
                    }
                }else{
                    // 后台有记录，直接跳转
                    window.location.href = 'http://localhost/kuyun/src/index.html';
                }
            }
        });
    }

    // 初始化方法 
    obj.init = function(){
        this.autoLogin();
    }

    // 初始化
    obj.init();
    
});