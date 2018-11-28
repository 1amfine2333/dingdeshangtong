
/**设置图标层的高度及添加图片层的背景图**/
/**
 obj  需设置图片层的class
 h    需设置图片层高的比例(比例的计算方式为UI图片的 高度/宽度)
 **/
$.toast.prototype.defaults.duration=3000;
function bgImg(obj, h) {
    var width = $('.' + obj).width();
    $('.' + obj).height(width * h);
    $('.' + obj).each(function() {
        var src = $(this).find('img').attr('src');
        $(this).css('background', 'url(' + src + ') center no-repeat');
        $(this).css('background-size', 'cover');
    });
}

function testTel(num){
	var reg=/^1[3|4|5|6|7|8|9]\d{9}$/;
	 return reg.test(num);
}

//拨打电话
function call(obj,tel){
	$('body').append('<div class="callBox meng"><div class="content"><p>确认拨打 '+tel+' ?</p><div class="btnBox disbox"><a class="cancel disflex" onclick="cancel(this)">取消</a><a class="sure disflex" href="tel:'+tel+'">确认</a></div></div></div>');
}

//点击取消
function cancel(obj){
	$(obj).parents('.meng').hide();
}


//获取验证码
var bj = 0;
var countdown = 60;
function setTime(val) {
	if(countdown == 0) {
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
	setTimeout(function() {
		setTime(val)
	}, 1000)
}
function getCode(obj,phone){
	console.log(phone);
	if($(obj).hasClass("on")){
        var phone=$('#telInput').val();
        setTime(countdown);
		$(obj).removeClass('on').addClass('active');
    }
}

$(function(){
	
	//监听输入框变化事件
	$('.inputBox').bind('input propertychange', function() { 
	    if($(this).val().length<1){
			$('.cancel').hide();
		}
		else{
			$('.cancel').show();
		}
	});  
	$('.cancel').click(function(){
		$('.inputBox').val('').focus();
		$(this).hide();
	});
	
	//点击头部返回
	$('.back').click(function(){
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
    if(typeof(option) !== 'object') {
        console.warn("option is not a 'object'");
        return false;
    }
    if(typeof(option.loading) !== 'boolean') {
        $.showLoading();
    }
    option.url=option.url?option.url:$(".layui-form").attr("action");
    $.ajax({
        url: option.url || location.pathname,
        data: option.data || null,
        dataType: option.dataType || 'JSON',
        type: option.type || 'post',
        async: typeof(option.async) === 'boolean' ? option.async : true,
        success: option.success || function(res) {
            let url = res.url;
            url && (setTimeout(function() {
                location = url;
            }, 1000));
            let code = res.code===0?'cancel':null;
            setTimeout(function() {
                res.msg && $.toast(res.msg,code);
            },100);
            option.done && option.done(res);
        },
        complete: function() {
            if($('.weui_loading').hasClass('weui_loading')  && typeof(option.loading) !== 'boolean') {
                 $.hideLoading()
            }
            setTimeout(function() {
                let ret = option.reload || false;
                if(ret) {
                    ret = (typeof(ret === 'number')) ? ret : 0;
                    setTimeout(function() {
                        location.reload();
                    }, ret * 1000);
                }
            }, 10);
        },
        error: option.error || function(e) {
            $.toast('error:' + e.statusText || e.statusMessage,'cancel');
        }
    });
}