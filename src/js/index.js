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

    var usi = {};
    usi.user_info = $('.user_info');
    usi.profile_wrap = usi.user_info.find('.profile_wrap');
    usi.name = usi.user_info.find('#name');
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
    cat.ts = {};
    cat.build_nodes = function(target){

        if(typeof target == 'object'){
            var act = target.attr('act');
            var fid = target.attr('files_id');
            var sign = act+'_'+fid;
        }

        cat.files_loading.show();
        cat.files_con.html('');

        if(!cat.ts[sign]){
            // 限于后台代码没预备，暂时手动载入
            if(target == 'all_-1'){
                cat.ts['all_-1'] = 
                '<div class="file" act="user" files_id="0" title="私有目录">\
                    <div class="icon_img"><img src="lib/coloursIcon/wenjian.png" alt=""></div>\
                    <div class="filename"><a href="javascript:;">私有目录</a></div>\
                    <div class="file_checkbox"><span class="un_check"></span><span class="checked"></span></div>\
                </div>\
                <div class="file" act="lesson" files_id="0" title="班级目录">\
                    <div class="icon_img"><img src="lib/coloursIcon/wenjian.png" alt=""></div>\
                    <div class="filename"><a href="javascript:;">班级目录</a></div>\
                    <div class="file_checkbox"><span class="un_check"></span><span class="checked"></span></div>\
                </div>';
                cat.files_con.append(cat.ts['all_-1']);
                cat.files_con.html(cat.ts['all_-1']);
                cat.files_loading.hide();
            }else{
                $.ajax({type:'POST', url:'php/handle/data.php', data:{ act:act, fid:fid }, success:function(data){
                    var obj = JSON.parse(data),
                        files = [obj.folder,obj.file],
                        ii_path = {
                            folder:'lib/coloursIcon/wenjian.png',
                            txt:'lib/coloursIcon/txt.png',
                            unknown:'lib/coloursIcon/unknown.png',
                        };
                    var nodes = '';
                    $.each(files,function(i,list){
                        $.each(list,function(j,item){
                            var id = item.id,
                                name = item.name,
                                ext = name.substr(name.lastIndexOf('.')+1),
                                icon = ii_path.folder;
                            if(i == 0){
                                icon = ii_path.folder;
                            }else{
                                icon = ii_path[ext] ? ii_path[ext] : ii_path.unknown;
                            }
                            nodes += 
                            '<div class="file" act="user" files_id="'+id+'" title="'+name+'">\
                                <div class="icon_img"><img src="'+icon+'" alt=""></div>\
                                <div class="filename"><a href="javascript:;">'+name+'</a></div>\
                                <div class="file_checkbox"><span class="un_check"></span><span class="checked"></span></div>\
                            </div>';
                        });
                    });
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
            var last_a = cat.breadcrumbs.find('a:last');
            last_a.attr('href','javascript:;');
            last_a.toggleClass('path_active');
            cat.breadcrumbs.append('\
                <span>></span>\
                <a act="'+act+'" files_id="'+id+'" >'+name+'</a>\
            ');
        }
    };
    cat.event = function(){
        cat.files_con.on('dblclick',function(e){
            if($(e.target).attr('class') != 'file') return;
            cat.build_nodes($(e.target));
            cat.build_breadcrumbs($(e.target));
        });

        cat.breadcrumbs.on('click',function(e){
            cat.build_nodes($(e.target));
            cat.build_breadcrumbs($(e.target),'back');
        });

        // cat.file_checkbox.on('click',function(e){
        //     console.log('cat.file_checkbox');
        //     e.stopPropagation();
        // });
    };
    cat.init = function(){

        if(!cat.files_loading.html()){
            cat.files_loading.append(cat.svg_loading({size:40,color:'#8bc34a'}));
        }
        cat.build_nodes('all_-1');
        cat.event();
    };

    cat.init();
});