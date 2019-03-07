$(function(){
/* 
    $.ajax({
        type:'POST',
        url:'php/handle/.php',
        data:{
            act:"",
        },
        success:function(data){

        }
    });
 */


    // 全局环境
    var g = {
        method:null,
        id:-1,
    };
    // 全局提示语
    g.tip = function (words, condition){
        var words = words || '警告';
        var global_tip = $('#global_tip');
        if (!global_tip[0]) {
            global_tip = $('<p id="global_tip"></p>');
            $('.content').append(global_tip);
        }
        global_tip.html(words);

        global_tip.css('left', ($(window).width() - global_tip.width())/2 );
        global_tip.css('top', ($(window).height() - global_tip.height())*.15);

        global_tip.show();
        TweenMax.from(global_tip, .2, { opacity: 0, top: global_tip.offset().top-20});
        TweenMax.to(global_tip, .2, { opacity: 1, top: global_tip.offset().top+30});
        
        if (condition !== false) {
            TweenMax.to(global_tip, .2, {
                opacity: 0, top: global_tip.offset().top - 10, onComplete: function () {
                    global_tip.hide();
                }
            }).delay(1);
        }
    };
    // 全局确认删除对话框
    g.dialog = function (callback) {
        var words = words || '警告';
        var global_dialog = $('#global_dialog');
        // 第一次则生成对话框
        if (!global_dialog[0]) {
            global_dialog = $('\
                <div id="global_dialog">\
                    <div class="dialog">\
                        <div class="head">\
                            <h3>确认删除</h3>\<a class="close" href="javascript:;"><i class="iconfont">&#xeb2c;</i></a>\
                        </div>\
                        <p>确认要把所选文件放入回收站吗？<br>删除的文件可在10天内通过回收站还原</p>\
                        <div class="btns">\
                            <a class="confirm" href="javascript:;">确定</a>\
                            <a class="cancel" href="javascript:;">取消</a>\
                        </div>\
                    </div>\
                </div>\
            ');
            $('.content').append(global_dialog);
        };
        global_dialog.show();
        var confirm_btn = global_dialog.find('.confirm'),
            cancel_btn = global_dialog.find('.cancel'),
            close_btn = global_dialog.find('.close'),
            head = global_dialog.find('.head'),
            dialog = global_dialog.find('.dialog');
        // 点击确认
        confirm_btn.on('click',function(){
            global_dialog.hide();
            callback && callback();
        });
        // 点击取消
        cancel_btn.on('click',function(){ global_dialog.hide(); });
        // 点击关闭按钮
        close_btn.on('click',function(){ global_dialog.hide(); });
        // 按住对话框顶部进行拖动
        head.on('mousedown',function(e){
            var dis_x = e.pageX - dialog.offset().left;
            var dis_y = e.pageY - dialog.offset().top;
            $(document).on('mousemove',function(e){
                dialog.css('margin','0');
                dialog.css('position','absolute');
                var left = e.pageX - dis_x;
                var top = e.pageY - dis_y;
                left = left <= 0 ? 0 : left; 
                left = left >= $(window).width() - dialog.width() ? $(window).width() - dialog.width() : left; 
                top = top <= 0 ? 0 : top; 
                top = top >= $(window).height() - dialog.height() ? $(window).height() - dialog.height() : top; 

                dialog.css('left', left);
                dialog.css('top', top);
            });
            $(document).on('mouseup', function(){
                $(this).off('mousemove');
                $(this).off('mouseup');
            });
        });
    };
    // 全局执行列表，同一时刻只允许执行一件事
    g.task_list = [];
    g.init = function(){}
    g.init();



    /**
     * 用户信息对象
     */
    var usi = {};
    usi.user_info = $('.user_info');
    usi.profile_wrap = usi.user_info.find('.profile_wrap');
    usi.name = usi.user_info.find('#name');
    usi.u = {}; // 存放获取到的用户相关信息
    usi.get_user_info = function(){
        $.ajax({
            type:'POST',
            url:'php/handle/user.php',
            data:{
                act:"userInfo"
            },
            success:function(data){
                // if(!data){}
                var _obj = JSON.parse(data);
                usi.u = _obj;
                if(_obj){
                    TweenMax.from(usi.name,.5,{ opacity:0 });
                    TweenMax.to(usi.name,.5,{ opacity:1 });
                    usi.name.html(_obj.username);
                }
            }
        });
    };
    usi.init = function(){
        usi.get_user_info();
    };
    usi.init();



    /**
     * 目录信息对象
     */
    var cat = {};
    cat.files_con = $('.files_con');
    cat.files_loading = $('.files_loading');
    cat.file = cat.files_con.find('.file');
    cat.pre_files = cat.files_con.find('.pre_files');
    cat.cur_files = cat.files_con.find('.cur_files');
    cat.file_checkbox = cat.files_con.find('.file_checkbox');
    cat.breadcrumbs = $('#breadcrumbs');
    // loading图标
    cat.svg_loading = function(style){

        var style = style || {};
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

    // 暂存区，存放刚刚点击过的目录的html
    cat.ts = {};

    // 存放目录的icon图标
    cat.ii_path = {
        folder: 'lib/coloursIcon/wenjian.png',
        txt: 'lib/coloursIcon/txt.png',
        unknown: 'lib/coloursIcon/unknown.png',
    };

    // 返回文件或文件夹的html字符串
    cat.file_html_str = function(argu){
        return  '\
        <div class="file" act="user" type="' + argu.type + '" files_id="' + argu.id + '" title="' + argu.name + '">\
            <div class="icon_img"><img src="'+ argu.icon + '" alt=""></div>\
            <div class="filename"><a href="javascript:;">'+ argu.name + '</a></div>\
            <div class="file_checkbox"><i class="iconfont">&#xeb26;</i></div>\
        </div>';
    }

    // 生成节点
    cat.build_nodes = function(target){

        if(typeof target == 'object'){
            var act = target.attr('act');
            var fid = target.attr('files_id');
            var sign = act+'_'+fid;
            g.method = act;
            g.id = fid;
        }

        cat.files_loading.show();
        cat.files_con.html('');

        if(!cat.ts[sign]){
            // 限于后台代码没预备，暂时手动载入
            if(target == 'all_-1'){
                cat.ts['all_-1'] = 
                '<div class="file" type="folder" act="user" files_id="0" title="私有目录">\
                    <div class="icon_img"><img src="lib/coloursIcon/wenjian.png" alt=""></div>\
                    <div class="filename"><a href="javascript:;">私有目录</a></div>\
                    <div class="file_checkbox"><i class="iconfont">&#xeb26;</i></div>\
                </div>\
                <div class="file" type="folder" act="lesson" files_id="0" title="班级目录">\
                    <div class="icon_img"><img src="lib/coloursIcon/wenjian.png" alt=""></div>\
                    <div class="filename"><a href="javascript:;">班级目录</a></div>\
                    <div class="file_checkbox"><i class="iconfont">&#xeb26;</i></div>\
                </div>';
                cat.files_con.append(cat.ts['all_-1']);
                cat.files_con.html(cat.ts['all_-1']);
                cat.files_loading.hide();
            }else{
                // 请求数据
                $.ajax({type:'POST', url:'php/handle/data.php', data:{ act:act, fid:fid }, success:function(data){
                    var obj = JSON.parse(data),
                        files = [obj.folder,obj.file],      // 这条语句谨慎更改
                        ii_path = cat.ii_path;
                    var nodes = '';

                    // 遍历生成节点
                    if(obj.folder || obj.file){
                        $.each(files,function(i,list){
                            $.each(list,function(j,item){
                                var id = item.id,
                                    name = item.name,
                                    ext = name.substr(name.lastIndexOf('.')+1),
                                    icon = ii_path.folder,
                                    type = i == 0 ? 'folder' : 'file';

                                if(i == 0){
                                    icon = ii_path.folder;
                                }else{
                                    icon = ii_path[ext] ? ii_path[ext] : ii_path.unknown;
                                }
                                nodes += cat.file_html_str({type:type, id:id, name:name, icon:icon });
                            });
                        });
                        
                    }else{
                        // 空
                        nodes = 
                        '\
                            <p class="files_empty">\
                                <img src="img/empty.png">\
                                您还没上传过文件哦，点击<a href="javascript:;">上传</a>按钮~\
                            </p>\
                        '; 
                    }

                    // 记录到暂存区
                    cat.ts[sign] = nodes;
                    cat.files_con.html(nodes);
                    cat.files_loading.hide();
                }});
            }
        }else{
            cat.files_con.html(cat.ts[sign]);
            cat.files_loading.hide();
        }
    }

    // 生成面包屑
    cat.build_breadcrumbs = function(target,back){
        var name = target.attr('title');
        var id = target.attr('files_id');
        var act = target.attr('act');

        if(back == 'back'){
            if(cat.breadcrumbs.find('a:last').get()[0] != target.get()[0]){
                target.nextAll().remove();
                target.removeAttr('href');
                target.toggleClass('path_active');
            }
        }else{
            // 暂时手动做
            if( (act == 'user' && id == 0) || (act == 'lesson' && id == 0) ){
                cat.breadcrumbs.html('\
                    <a act="all" files_id="-1" class="path_active" href="javascript:;">全部文件</a>\
                    <span>&gt;</span>\
                    <a act="'+act+'" files_id="'+id+'">'+name+'</a>\
                ');
            }else if( (act == 'all' && id == -1) ){
                cat.breadcrumbs.html('<a act="all" files_id="-1">全部文件</a>');
            }else{
                var last_a = cat.breadcrumbs.find('a:last');
                last_a.attr('href','javascript:;');
                last_a.toggleClass('path_active');
                cat.breadcrumbs.append('\
                    <span>&gt;</span>\
                    <a act="'+act+'" files_id="'+id+'" >'+name+'</a>\
                ');
            }
        }
    };
    

    // 存放被选中的文件的id及相关信息，以对象为单位
    cat.checked_files = {
        length:0
    };

    // 各种事件
    cat.event = function(){
        // 文件双击事件
        cat.files_con.on('dblclick',function(e){

            // 如果有正在处理的事务，禁止单击
            if (g.task_list.length) return false;

            var target = $(e.target).parents('.file');

            // 如果当前文件夹被操作则取消双击事件
            if (target[0].is_working == true) return false;

            if(target){
                cat.build_nodes(target);
                cat.build_breadcrumbs(target);
                cat.checked_files = {
                    length:0
                };
            }
        });


        // 文件点击事件
        cat.files_con.on('click',function(e){
            console.log(g.task_list.length);
            // 如果有正在处理的事务，禁止单击
            if (g.task_list.length) return false;

            var file = $(e.target).parents('.file')[0] ? $(e.target).parents('.file') : $(e.target);

            var checkbox = $(e.target).parent('.file_checkbox');
            file.toggleClass('file_active');
            if(checkbox[0]){
                checkbox.toggleClass('file_checkbox_ed');
            }else{
                file.find('.file_checkbox').toggleClass('file_checkbox_ed');
            }

            var key = file.attr('act')+'_'+file.attr('files_id');

            
            if (cat.checked_files[key]){
                delete cat.checked_files[key];
                if (cat.checked_files.length > 0) cat.checked_files.length--;
            }else{
                cat.checked_files[key] = {
                    target:file,
                    title:file.attr('title'),
                    id:file.attr('files_id'),
                    type:file.attr('type')
                };
                cat.checked_files.length++;
            }

        });

        // 面包屑事件
        cat.breadcrumbs.on('click',function(e){
            cat.build_nodes($(e.target));
            cat.build_breadcrumbs($(e.target),'back');
        });

    };

    // 初始化
    cat.init = function(){
        if(!cat.files_loading.html()){
            cat.files_loading.append(cat.svg_loading({size:40,color:'#8bc34a'}));
        }
        cat.build_nodes('all_-1');
        cat.event();
    };
    cat.init();


    
    var panel = {};
    panel.panel_btns = $('.panel_btns');
    panel.panel_btns_small = $('.panel_btns_small');
    panel.panel_btns_other = $('.panel_btns_other');
    panel.build_rename_nodes = function(parent,callback){
        parent.append('\
                <div class="filename_input">\
                    <input type="text" spellcheck="false">\
                    <i class="iconfont confirm" title="确认">&#xeb29;</i>\
                    <i class="iconfont cancel" title="取消">&#xeb2c;</i>\
                </div>\
            ');
        var o_filename_input = parent.find('.filename_input'),
            o_input = o_filename_input.find('input'),
            o_confirm_btn = o_filename_input.find('.confirm'),
            o_cancel_btn = o_filename_input.find('.cancel');

        o_confirm_btn.off('click'); // 防止重复绑定事件
        o_confirm_btn.on('click', function(e){
            callback.comfirm && callback.comfirm(e);
        });

        o_cancel_btn.on('click', function (e) {
            callback.cancel && callback.cancel(e);
        });
        
        return {
            o_filename_input: o_filename_input,
            o_input: o_input,
            o_confirm_btn: o_confirm_btn,
            o_cancel_btn: o_cancel_btn
        }
    };
    panel.rechristen = function(){
        
        var arr = [];
        for (var key in cat.checked_files) {
            if (key == 'length') continue;
            arr.push(cat.checked_files[key]);
            if(arr.length == 1) break;
        }

        // 有事件正在执行，返回false
        if(g.task_list.length) return false;

        // 设置先决条件,暂时这么写
        if (cat.checked_files.length > 1){
            g.tip('一次只能重命名一个文件或文件夹');
            return false;
        } else if (cat.checked_files.length == 0) {
            g.tip('请选择修改对象');
            return false;
        }

        if (arr[0].id == 0 && usi.u.lv == 0) {
            g.tip('您的权限不足，无法做出修改');
            return false;
        }

        var target = arr[0];

        // 占位
        g.task_list.push({ name: 'rename', target: target.target });

        var e_filename = target.target.find('.filename');
        var e_filename_a = target.target.find('a');

        var o_filename_input = e_filename.find('.filename_input'),
            o_input = null,
            o_confirm_btn = null,
            o_cancel_btn = null;

        if (o_filename_input[0]) {
            o_input = o_filename_input.find('input');
            o_confirm_btn = o_filename_input.find('.confirm');
            o_cancel_btn = o_filename_input.find('.cancel');
        } else {
            var obj = panel.build_rename_nodes(e_filename,{
                comfirm:function(){
                    now_name = o_input.val();
                    if (pre_name != now_name) {
                        g.tip('正在重命名...', false);
                        $.ajax({ type: 'POST', url: 'php/handle/file.php', 
                            data: {
                                act: "rechristen",
                                fid: g.id,
                                id: target.id,
                                method: g.method,
                                type: target.type,
                                name: now_name
                            },
                            success: function (data) {
                                var _obj = JSON.parse(data);
                                if (_obj.status == 1) {
                                    e_filename_a.html(now_name);
                                    e_filename_a.show();
                                    o_filename_input.hide();
                                    g.tip('更改成功');
                                    // 离位
                                    g.task_list.pop();
                                } else {
                                    g.tip('更改失败');
                                    o_input.val(pre_name);
                                }
                            }
                        });

                    } else {
                        e_filename_a.html(now_name);
                        e_filename_a.show();
                        o_filename_input.hide();
                        // 离位
                        g.task_list.pop();
                    }
                },
                cancel:function(){
                    e_filename_a.show();
                    o_filename_input.hide();
                    // 离位
                    g.task_list.pop();
                }
            });
            o_filename_input = obj.o_filename_input;
            o_input = obj.o_input;
        }

        e_filename_a.hide();
        o_filename_input.show();
        var pre_name = e_filename_a.html();
        var now_name = '';
        o_input.val(pre_name);
        o_input.focus();
        o_input[0].selectionStart = 0;
        o_input[0].selectionEnd = o_input.val().lastIndexOf('.');
        
    };
    panel.del = function(){
        var obj = cat.checked_files;
        var arr = [];
        for (var key in obj){
            if (key == 'length') continue;
            var _arr = [
                obj[key].type,
                obj[key].id,
            ];
            arr.push(_arr);
        }
        if (obj.length != 0) {
            // 占位
            g.task_list.push({ name:'del', target: arr });

            g.dialog(function () {
                g.tip('正在删除...,注意是硬删除', false);
                $.ajax({type: 'POST', url: 'php/handle/file.php', data:{ act:"del",method:1,func:'del',data:arr},success:function(data){
                    var obj = JSON.parse(data);
                    if (obj.status == 1) {
                        g.tip('删除成功');
                        for (var key in cat.checked_files) {
                            if (key == 'length') continue;
                            cat.checked_files[key].target.remove();
                        }
                    } else {
                        g.tip('删除失败');
                    }
                }});
            });
        }
    };
    panel.create = function(){
        
        if (g.task_list.length) {
            console.log(g.task_list);
            // 双闪提示
            TweenMax.to(g.task_list[0].target, .1, { opacity: 0 });
            TweenMax.to(g.task_list[0].target, .1, { opacity: 1 }).delay(.1);
            return false;

        } else if (g.method != null && g.id != -1) { // 全局环境下的暂时无人可操作
            
            // 默认文件名，i作为区别后缀
            var name = '新建文件夹', i = 0;

            // 生成新的文件或文件夹节点
            var file = $(
                cat.file_html_str({
                    type:'folder',  // 暂时只能新建文件夹
                    id:'underfined',
                    name: name,
                    icon:cat.ii_path.folder
                })
            );

            // 占位
            g.task_list.push({ name: 'create', target: file });

            // 清除当前被选中的节点
            var clecked_files = cat.files_con.find('.file_active');
            clecked_files.find('.file_checkbox_ed').removeClass('file_checkbox_ed');
            clecked_files.removeClass('file_active');
            // 清除存储的属性
            cat.checked_files = { length: 0 };

            cat.files_con.prepend(file);
            var e_filename = file.find('.filename');

            // 新建文件时特有的确认事件回调和取消事件回调
            var res_obj = panel.build_rename_nodes(e_filename,{
                comfirm: function (e) {
                    // 防止按钮被确定按钮被重复点击
                    if($(this).data('lock')) return false;
                    $(this).data('lock',true);

                    // 调用提示语，发起请求
                    g.tip('正在创建...',false);
                    _tmp(name);
                },
                cancel: function (e) {
                    // 防止在请求新建文件时候被点击取消
                    if ($(this).data('lock')) return false;
                    file.remove();
                    // 离位
                    g.task_list.pop();
                }
            });

            // 让文本框文字被选中
            var o_input = res_obj.o_input;
            o_input.val(name);
            o_input.focus();
            o_input.select();

            function _tmp(name) {
                $.ajax({type: 'POST', url: 'php/handle/file.php', data: {act: "create", fid: g.id, method: g.method, type: 'folder', name: name },
                    success: function (data) {
                        var obj = JSON.parse(data);
                        if (obj.status == 1){
                            // 调用提示语
                            g.tip('创建成功');
                            // 填入成功后的文件名，隐藏文本框，显示a标签
                            e_filename.find('a').html(name);
                            e_filename.find('a').show();
                            e_filename.find('.filename_input').hide();
                            // 清除相关事务
                            g.task_list.pop();
                        } else if (obj.status == 2 && i <= 20){ // 不允许连续超过20次的ajax请求
                            // 自动生成新的名称并再次发起请求
                            i = i+1;
                            name = '新建文件夹'+i;
                            _tmp(name);
                        } else if (obj.status == 3) {
                            g.tip('用户权限不足');
                            $(this).data('lock',false);
                        } else if (obj.status == 4){
                            g.tip('创建失败');
                            $(this).data('lock',false);
                        }
                    }
                });
            }
        }
    };

    panel.event = function(){
        panel.panel_btns.on('click',function(e){
            var _target = $(e.target).parents('a')[0] || $(e.target)[0];
            var target = $(_target);
            var act = target.attr('act');
            switch (act){
                case 'rechristen':
                    panel.rechristen();
                    break;
                case 'del':
                    panel.del();
                    break;
                case 'create':
                    panel.create();
                    break;
            }
        });

        panel.panel_btns_small.hover(function(){
            TweenMax.to($(this),.2,{width:116});
            TweenMax.to($(this),.2,{height:170}).delay(.1);
        },function(){
            TweenMax.to($(this),.2,{height:33});
            TweenMax.to($(this),.2,{width:66}).delay(.1);
        });
    };

    panel.init = function(){
        this.event();
    };
    panel.init();



    var side = {};
    side.side_large = $('.side_large');
    side.side_small = $('.side_small');
    side.main_menu = side.side_large.find('#main_menu');
    side.event = function(){
        side.main_menu.on('click',function(e){
            var target = $(e.target);
            if(target[0].tagName != 'A' && target[0].tagName != 'I') return false;
            cat.build_nodes(target);
            cat.build_breadcrumbs(target);
        });
        side.side_small.on('click',function(e){
            var target = $(e.target)[0].tagName != 'A' ? $(e.target).parent() : $(e.target);
            cat.build_nodes(target);
            cat.build_breadcrumbs(target);
        });
    };
    side.init = function(){
        this.event();
    };
    side.init();



    var footer = {};
    footer.footer_menu = $('.footer_menu');
    footer.event = function(){
        footer.footer_menu.on('click',function(e){
            var target = $(e.target)[0].tagName != 'A' ? $(e.target).parent() : $(e.target);
            cat.build_nodes(target);
            cat.build_breadcrumbs(target);
        });
    };
    footer.init = function(){
        this.event();
    };
    footer.init();

});