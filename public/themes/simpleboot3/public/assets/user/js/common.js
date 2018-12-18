/**设置图标层的高度及添加图片层的背景图**/
/**
 obj  需设置图片层的class
 h    需设置图片层高的比例(比例的计算方式为UI图片的 高度/宽度)
 **/
$.toast.prototype.defaults.duration = 3000;

function bgImg(obj, h) {
    var width = $('.' + obj).width();
    $('.' + obj).height(width * h);
    $('.' + obj).each(function () {
        var src = $(this).find('img').attr('src');
        $(this).css('background', 'url(' + src + ') center no-repeat');
        $(this).css('background-size', 'cover');
    });
}

const api = {
    baidu_map: 'http://api.map.baidu.com/location/ip?ak=cLx2ByWCm7OxxO65d8jKLp7iudhVNbtB&coor=bd09ll&ip=',
};

function testTel(num) {
    var reg = /^1[3|4|5|6|7|8|9]\d{9}$/;
    return reg.test(num);
}

//拨打电话
function call(obj, tel) {
    $('body').append('<div class="callBox meng"><div class="content"><p>确认拨打 ' + tel + ' ?</p><div class="btnBox disbox"><a class="cancel disflex" onclick="cancel(this)">取消</a><a class="sure disflex" href="tel:' + tel + '">确认</a></div></div></div>');
}

//点击取消
function cancel(obj) {
    $(obj).parents('.meng').hide();
}


//获取验证码
var bj = 0;
var countdown = 60;

function setTime(val) {
    if (countdown == 0) {
        $('.getCode').text('获取验证码').addClass('on');
        $('.getCode').removeClass("active");
        countdown = 60;
        bj = 0;
        return;
    } else {
        $('.getCode').text(countdown + 's后再获取');
        $('.getCode').addClass("active");
        countdown--;
    }
    setTimeout(function () {
        setTime(val)
    }, 1000)
}

function getCode(obj, phone) {
    console.log(phone);
    if ($(obj).hasClass("on")) {
        var phone = $('#telInput').val();
        setTime(countdown);
        $(obj).removeClass('on').addClass('active');
    }
}

$(function () {

    //监听输入框变化事件
    $('.inputBox').bind('input propertychange', function () {
        if ($(this).val().length < 1) {
            $('.cancel').hide();
        }
        else {
            $('.cancel').show();
        }
    });
    $('.cancel').click(function () {
        $('.inputBox').val('').focus();
        $(this).hide();
    });

    //点击头部返回
    $('.back').click(function () {
        back();
    })
});


$.fn.formData = function () {
    let arr_data = $(this).serializeArray();
    let data = {};
    if (arr_data.length > 0) {
        arr_data.forEach(function (item) {
            data[item.name] = item.value;
        });
    }
    return data;
};

/**
 * @param option
 * @returns {boolean}
 */
function request(option) {
    if (typeof(option) !== 'object') {
        console.warn("option is not a 'object'");
        return false;
    }
    if (typeof(option.loading) !== 'boolean') {
        $.showLoading();
    }
    option.url = option.url || $("form[action]").attr("action");
    $.ajax({
        url: option.url || location.pathname,
        data: option.data || null,
        dataType: option.dataType || 'JSON',
        type: option.type || 'post',
        async: typeof(option.async) === 'boolean' ? option.async : true,
        success: option.success || function (res) {
            let url = res.url;
            url && (setTimeout(function () {
                window.location = url;
            }, 1000));
            let code = res.code === 0 ? 'cancel' : null;
            setTimeout(function () {
                $.toast(res.msg, code);
            }, 200);
            option.done && option.done(res);
        },
        complete: function () {
            if ($('.weui_loading').hasClass('weui_loading') && typeof(option.loading) !== 'boolean') {
                $.hideLoading();
            }
            let ret = option.reload || false;
            if (ret) {
                ret = (typeof(ret === 'number')) ? ret : 0;
                setTimeout(function () {
                    location.reload();
                }, ret * 1000);
            }
        },
        error: option.error || function (e) {
            $.toast('error:' + e.statusText || e.statusMessage, 'cancel');
        }
    });
}

/**
 * 本地浏览器缓存
 * @param key
 * @param val
 * @returns {string}
 */
function cache(key, val) {
    let l = window.localStorage;
    if (key) {
        if (typeof val === 'undefined') {
            let data = l.getItem(key);
            try {
                data = JSON.parse(data);
            } catch (e) {
                try {
                    data = eval(data);
                } catch (e) {
                }
            }
            return data;
        } else if (typeof val === 'object') {

            let data = JSON.stringify(val);
            l.setItem(key, data);

        } else if (val === null) {
            l.removeItem(key);
        } else {
            l.setItem(key, val);
        }
    }
}

$(function () {
    //清除内容
    $(".input i").on("click",function () {
       var that_input = $(this).hide().parents("div.input").find("input");
        if (app){
            let name = that_input.attr("name");
            app[name]="";
        }
        that_input.val("");
    });
//监听输入
    $(".input input").keyup(function () {
        let i = $(this).parents(".input").find("i");
        if ($(this).val().length){
            i.show();
        }else{
            i.hide();
        }
    });

    $(".input input").each(function (i,v) {

          let ti = $(v).parents(".input").find("i");
        if ($(this).val().length){
            ti.show();
        }else{
            ti.hide();
        }
    })

});

/**
 * 手机事件封装
 */
/*
;(function (w, d, factory) {
    var fn = factory(w, d);
    w.DJ = w.DJ || {};
    w.DJ.toucher = w.DJ.toucher || fn;
})(this, document, function (w, d) {
    "use strict";

    function Djswipe(el) {
        this.elem = d.querySelector(el) || {};
        // 自定义事件库
        this._events = this._events || {};
        // 记录垂直或水平方向
        this.dir = false;
        // 处理长按的定时器
        this.ltimer = null;
        this.istouched = false;
    }

    Djswipe.prototype = {
        events: ['touchStart', 'swipeLeft', 'swipeRight', 'swipeUp', 'swipeDown', 'dragVertical', 'dragHorizontal', 'touchEnd', 'drag', 'tag', 'longTag'],
        init: function () {
            this.touch();
            return this;
        },
        //模拟on自定义事件绑定，多事件空格隔开
        on: function (evt, handler) {
            var arrevts = evt.split(' '),
                len = arrevts.length,
                isFunction = typeof(handler) === 'function';
            for (var i = 0; i < len; i++) {
                this._events[arrevts[i]] = this._events[arrevts[i]] || [];
                isFunction && this._events[arrevts[i]].push(handler);
            }
            return this;
        },
        //自定义事件触发器
        trigger: function (evt, e, s) {
            if (!!evt)
                for (var i = 0; evt[i]; evt[i++].call(this.elem, e, s)) ;
        },
        touch: function () {
            var _t = this,
                sx, sy,
                disX, disY;
            this.elem.addEventListener('touchstart', function (e) {
                _t.dir = false;
                _t.istouched = true;
                _t.islongtag = false;
                _t.startTime = new Date();
                sx = e.targetTouches[0].pageX, sy = e.targetTouches[0].pageY;
                // 开始触摸
                _t.trigger(_t._events.touchStart, e);

                _t.ltimer = setTimeout(function () {
                    // 长按
                    _t.istouched && _t.dir === false && (_t.trigger(_t._events.longTag, e), _t.islongtag = true);
                }, 500);
            }, false);
            this.elem.addEventListener('touchmove', function (e) {
                var mx = e.targetTouches[0].pageX,
                    my = e.targetTouches[0].pageY,
                    x, y;
                disX = mx - sx, disY = my - sy;

                x = Math.abs(disX), y = Math.abs(disY);
                //坐标和位移值等，方便外部直接拿到，做其他操作
                var status = {
                    startx: sx,
                    starty: sy,
                    movex: mx,
                    movey: my,
                    disx: disX,
                    disy: disY
                }

                // 水平
                if (x !== 0 && x >= 4 && x > y && _t.dir === false)
                    _t.dir = 0;
                // 垂直
                if (y !== 0 && y >= 4 && x < y && _t.dir === false)
                    _t.dir = 1;
                // 拖拽
                _t.dir !== false && _t.trigger(_t._events.drag, e, status);
                // 水平和垂直滑动
                _t.trigger(_t._events[['dragHorizontal', 'dragVertical'][_t.dir]], e, status);
            }, false);
            this.elem.addEventListener('touchend', function (e) {
                _t.istouched = false;
                _t.disTime = new Date() - _t.startTime;
                clearInterval(_t.ltimer);
                if (_t.disTime < 150) {
                    if (_t.dir === 0) {
                        // 左右
                        disX < 0 && _t.trigger(_t._events.swipeLeft, e);
                        disX > 0 && _t.trigger(_t._events.swipeRight, e);
                    }
                    if (_t.dir === 1) {
                        // 上下
                        disY < 0 && _t.trigger(_t._events.swipeUp, e);
                        disY > 0 && _t.trigger(_t._events.swipeDown, e);
                    }
                    // 轻击
                    _t.dir === false && _t.trigger(_t._events.tag, e);
                } else {
                    // 处理tag、longtag多余的间隙
                    (_t.dir !== false || _t.dir === false && _t.islongtag === false) && _t.trigger(_t._events.touchEnd, e);
                }
            }, false);
        }
    };

    return function (el) {
        return new Djswipe(el).init();
    }
});
*/




