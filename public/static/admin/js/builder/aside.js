/*!
 *  Document   : aside.js
 *  Author     : pinbo <378184@qq.com>
 *  Description: 侧栏构建器
 */
jQuery(document).ready(function() {
    // 侧栏开关
    $('#aside .switch input:checkbox').on('click', function () {
        var $switch = $(this);
        var $data = {
            value: $switch.prop('checked'),
            table: $switch.data('table') || '',
            name: $switch.data('field') || '',
            type: 'switch',
            pk: $switch.data('id') || ''
        };

        // 发送ajax请求
        Ieasynet.loading();
        $.post(ieasynet.aside_edit_url, $data).success(function(res) {
            Ieasynet.loading('hide');
            if (1 != res.code) {
                Ieasynet.notify(res.msg, 'danger');
                $switch.prop('checked', !$data.status);
                return false;
            } else {
                Ieasynet.notify(res.msg, 'success');
            }
        }).fail(function () {
            Ieasynet.loading('hide');
            Ieasynet.notify('服务器发生错误~', 'danger');
        });
    });
});