function request(option) {
    if(typeof(option) !== 'object') {
        console.warn("option is not a 'object'");
        return false;
    }
    if(typeof(option.loading) !== 'boolean') {
        var loading = layer.load(1);
    }

    option.url=option.url?option.url:$(".layui-form").attr("action");
    $.ajax({
        url: option.url || location.pathname,
        data: option.data || null,
        dataType: option.dataType || 'JSON',
        type: option.type || 'post',
        async: typeof(option.async) === 'boolean' ? option.async : true,
        success: option.success || function(res) {
            if(res.data) {
                var wait = res.wait || 0;
                wait && (wait *= 1000);
                let url = res.url;
                url && (setTimeout(function() {
                    location = url;
                }, wait));
                res.data.reload && (option.reload = parseFloat(res.data.reload));
                if(res.data.alert) {
                    res.msg && layer.open({
                        type: 0,
                        shadeClose: true,
                        shade: ["0.6", "#7186a5"],
                        skin: 'Skin',
                        content: res.msg
                    });
                }
            }
            if(!res.data || !res.data.alert) {
                var code = res.code===0?2:1;
                var cfg = typeof(res.data.icon) !== "boolean" ? {
                    icon: code,
                    offset: '80%',
                   // time: 0
                } : {};
                res.msg && layer.msg(res.msg, cfg);
            }
            option.done && option.done(res);
        },
        complete: function() {
            if(typeof(option.loading) !== 'boolean') {
                layer.close(loading);
            }
            setTimeout(function() {
                var ret = option.reload || false;
                if(ret) {
                    ret = (typeof(ret === 'number')) ? ret : 0;
                    setTimeout(function() {
                        location.reload();
                    }, ret * 1000);
                }
            }, 10);
        },
        error: option.error || function(e) {
            layer.msg('error:' + e.statusText || e.statusMessage);
        }
    });
}
