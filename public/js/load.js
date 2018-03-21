/**
 * Created by chenglh on 2016/12/14.
 */
//动态加载文件
function loadjscssfile(filename, filetype) {
    if (filetype == "js") { //判断文件类型
        var fileref = document.createElement('script'); //创建标签
        fileref.setAttribute("type", "text/javascript"); //定义属性type的值为text/javascript
        fileref.setAttribute("src", filename+"?date="+(Date.now())); //文件的地址
    }
    else if (filetype == "css") { //判断文件类型
        var fileref = document.createElement("link");
        fileref.setAttribute("rel", "stylesheet");
        fileref.setAttribute("type", "text/css");
        fileref.setAttribute("href", filename+"?date="+(Date.now()));
    }
    if (typeof fileref != "undefined")
        document.getElementsByTagName("head")[0].appendChild(fileref);
}

//防止重复加载
var filesadded = "" //保存已经绑定文件名字的数组变量
function checkloadjscssfile(filename, filetype) {
    if (filesadded.indexOf("[" + filename + "]") == -1) {// indexOf判断数组里是否有某一项
        loadjscssfile(filename, filetype);
        filesadded += "[" + filename + "]"; //把文件名字添加到filesadded
    }
    else {
        //alert("文件重复加载");
    }
}