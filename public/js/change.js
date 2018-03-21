/**
 * Created by chenglh on 2016/9/5.
 */
//feasibleform
//消息提示
function lalert(txt) {
    if (txt != '')
        layer.alert(txt, {icon: 2, closeBtn: false, area: '100px'});
}

$().ready(function() {
    $("#changeform").validate({

    });
    $("#feasibleform").validate({});

    $('#changeCategory').change(function () {
    $.ajax({
        type: "GET",
        url: "/change/changerefer?gate=" + $('#changeCategory').val(),
        success: function (msg) {
            $('#changeSubCategory').html('<option value="">-请选择-</option>');
            console.log(msg);
            for (var key in msg) {
                var option = document.createElement("option");
                $('#changeSubCategory').append(option);
                option.text = msg[key].Means;
                option.value = msg[key].Code;
            }
        }
    });
});

$('#createSubmit').click(function () {//验证提交内容
    $.ajax({
        data: $('#changeform').serialize(),
        type: "POST",
        url: "/change/changepush",
        success: function (msg) {
            if (msg.status) {//提交成功
                layer.alert(msg.statusMsg, {icon: 1, closeBtn: false, area: '100px'}, function () {
                    closeFrame();
                });
            } else {//提交失败
                submitLock = false;
                validateAlert(msg.statusMsg);
            }
        }
    });
});
})
