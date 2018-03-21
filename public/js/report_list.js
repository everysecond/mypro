/**
 * Created by chenglh on 2016/12/22.
 */

$("#report1").click(function () {
    var info1 = $("#evaTable");
    var year=$("#year").val();
    var priority=$("#priority").val();
    var charge=$("#charge").val();
    var supportType=$("#supportType").val();
    var supportSource=$("#supportSource").val();
    var need = '?year='+year+'&priority='+priority+'&charge='+charge+'&supportType='+supportType+'&supportSource='+ supportSource;
    $.ajax({
        type: 'get',
        url: 'getEvaList'+need,
        headers: {'X-CSRF-Token': $('meta[name=csrf-token]').attr('content')},
        dataType: 'json',
        success: function (data) {
            info1.empty();
            for (var i in data.evaList) {
                var str = "";
                str +=  '<tr class="tr1" align="center" height="22">' + '<td class="td2">' + i +
                    '</td><td class="td2">' + data.evaList[i][0]+'</td>';
                for(var j =1;j<=12;j++){
                    str += '<td class="td2">' + '<a href="supportKZList'+ need +'&evaluate='+
                        data.evaList[i][1]+'&month='+j+'">' + data.evaList[i][2][j] + '</a>' + '</td>';
                }
                info1.append(str);
            }
        }
    })
})
$("#report2").click(function () {
    var info2 = $("#comTable");
    var year=$("#year").val();
    var priority=$("#priority").val();
    var charge=$("#charge").val();
    var supportType=$("#supportType").val();
    var supportSource=$("#supportSource").val();
    var need = '?year='+year+'&priority='+priority+'&charge='+charge+'&supportType='+supportType+'&supportSource='+ supportSource;
    $.ajax({
        type: 'get',
        url: 'getComList'+need ,
        headers: {'X-CSRF-Token': $('meta[name=csrf-token]').attr('content')},
        dataType: 'json',
        success: function (data) {
            info2.empty();
            for (var i in data.comList) {
                var str = "";
                str +=  '<tr class="tr1" align="center" height="22">' + '<td class="td2">' + i +
                    '</td><td class="td2">' + data.comList[i][0]+'</td>';
                for(var j =1;j<=12;j++){
                    str += '<td class="td2">' + '<a href="supportKZList'+ need +'&timeOut='+
                        data.comList[i][1]+'&month='+j+'">' + data.comList[i][2][j] + '</a>' + '</td>';
                }
                info2.append(str);
            }
        }
    })
})
$("#report3").click(function () {
    var info3 = $("#repTable");
    var year=$("#year").val();
    var priority=$("#priority").val();
    var charge=$("#charge").val();
    var supportType=$("#supportType").val();
    var supportSource=$("#supportSource").val();
    var need = '?year='+year+'&priority='+priority+'&charge='+charge+'&supportType='+supportType+'&supportSource='+ supportSource;
    $.ajax({
        type: 'get',
        url:  'getRepList'+need ,
        headers: {'X-CSRF-Token': $('meta[name=csrf-token]').attr('content')},
        dataType: 'json',
        success: function (data) {
            info3.empty();
            for (var i in data.repList) {
                var str = "";
                str +=  '<tr class="tr1" align="center" height="22">' + '<td class="td2">' + i +
                    '</td><td class="td2">' + data.repList[i][0]+'</td>';
                for(var j =1;j<=12;j++){
                    str += '<td class="td2">' + '<a href="supportKZList'+ need +'&responseTime='+
                        data.repList[i][1]+'&month='+j+'">' + data.repList[i][2][j] + '</a>' + '</td>';
                }
                info3.append(str);
            }
        }
    })
})
$("#report4").click(function () {
    var info4 = $("#sucTable");
    var year=$("#year").val();
    var priority=$("#priority").val();
    var charge=$("#charge").val();
    var supportType=$("#supportType").val();
    var supportSource=$("#supportSource").val();
    var need = '?year='+year+'&priority='+priority+'&charge='+charge+'&supportType='+supportType+'&supportSource='+ supportSource;
    $.ajax({
        type: 'get',
        url: 'getSucList'+need ,
        headers: {'X-CSRF-Token': $('meta[name=csrf-token]').attr('content')},
        dataType: 'json',
        success: function (data) {
            info4.empty();
            for (var i in data.sucList) {
                var str = "";
                str +=  '<tr class="tr1" align="center" height="22">' + '<td class="td2">' + i +
                    '</td><td class="td2">' + data.sucList[i][0]+'</td>';
                for(var j =1;j<=12;j++){
                    str += '<td class="td2">' + '<a href="supportKZList'+ need +'&successNum='+
                        data.sucList[i][1]+'&month='+j+'">' + data.sucList[i][2][j] + '</a>' + '</td>';
                }
                info4.append(str);
            }
        }
    })
})


