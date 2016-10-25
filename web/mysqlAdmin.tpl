<!--
js依赖，调用方必须加载jquery插件
css依赖，按照bootstrap规范定义，所以可以随意加载一套bootstrap主题，或者自己按照bootstrap规范定义样式即可
-->
<style>
    @font-face {
        font-family: 'iconfont';
        src: url('//at.alicdn.com/t/font_1449982954_687468.eot'); /* IE9*/
        src: url('//at.alicdn.com/t/font_1449982954_687468.eot?#iefix') format('embedded-opentype'), /* IE6-IE8 */ url('//at.alicdn.com/t/font_1449982954_687468.woff') format('woff'), /* chrome、firefox */ url('//at.alicdn.com/t/font_1449982954_687468.ttf') format('truetype'), /* chrome、firefox、opera、Safari, Android, iOS 4.2+*/ url('//at.alicdn.com/t/font_1449982954_687468.svg#iconfont') format('svg'); /* iOS 4.1- */
    }

    .updateHtml {
        width: 100%;
        height: 100%;
        background-color: rgba(55, 55, 55, 0.62);
        position: fixed;
        z-index: 99;
        top: 0;
        left: 0;
    }

    .updateHtml .updateHtmlContent {
        position:relative;
        background-color: white;
        width: 90%;
        height: 100%;
        overflow-y: scroll;
        margin: 10px auto 0 auto;
        padding: 10px;
        border: solid 1px #d2d2d9;
    }

    .updateHtml .updateHtmlContent > table {
        width: 100%;
    }

    .columnValueArr {
        border: solid 1px #818181;
    }

    .columnValueArr > table {
        width: 100%;
    }

    #fastTableInfo .deleteAll {
        display: none;
        position: fixed;
        left: 50%;
        top: 50%;
        width: 300px;
        margin-left: -150px;
        margin-top: -50px;
        background-color: white;
        box-shadow: 0 0 40px black;
    }

    #fastTableInfo .updateAll {
        position: fixed;
        display: none;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(129, 129, 129, 0.48);
    }

    #fastTableInfo .updateAll .updateAllPanel {
        position: fixed;
        left: 50%;
        top: 50%;
        width: 500px;
        margin-left: -250px;
        margin-top: -100px;
        background-color: white;
        box-shadow: 0 0 40px black;
    }

    #fastTableInfo .deleteAll > div {
    }

    /*运行中动画*/
    .updateHtmlContentInserting:before{
        animation:contentLoading 6s linear infinite;
        content: '';
        background-color: #00A5E3;
        background-image: repeating-linear-gradient(-45deg, #14c3a2, #14c3a2 20px, #22e8c3 20px, #22e8c3 40px);
        position:absolute;
        left:0px;
        right:0px;
        top:0px;
        height:3px;
    }
    @keyframes contentLoading {
        from {
            background-position: 0% 0%;
        }

        to {
            background-position: 600px 0%;
        }
    }
</style>
<div id="fastTableInfo" class="container">
    <div class="deleteAll panel panel-default">
        <div class="panel-heading">
            <h3 class="panel-title">批量操作</h3>
        </div>
        <div class="panel-body">
            <div class="deleteCount">选择<span></span>条记录</div>
        </div>
        <div class="panel-footer">
            <input type="button" data-event="delete" class="btn btn-default" value="删除"/>
            <input type="button" data-event="update" class="btn btn-default" value="修改"
                   onclick="$('.updateAll').show()"/>
        </div>
    </div>
    <div class="updateAll">
        <div class="updateAllPanel panel panel-default">
            <div class="panel-heading" style="height: 38px;">
                <h3 class="panel-title" style="float: left;">批量修改</h3>
                <span onclick="$(this).parents('.updateAll').hide()" class="glyphicon glyphicon-remove"
                      aria-hidden="true" style="float:right;"></span>
            </div>
            <div class="panel-body">
                <div>
                    <select datatype="column">
                        <option value="">选择字段</option>
                        {foreach $column as $k=>$v}
                            <option value="{$k}">{$v.title}</option>
                        {/foreach}
                    </select>
                    <span>|</span>
                    <select datatype="replaceType">
                        <option value="replacePart">包含替换</option>
                    </select>

                    <div style="margin-top: 10px;">
                        <div style="width: 48%;float: left;height: 50px;">
                            <textarea datatype="searchText" style="width: 100%;height:100%;"></textarea>
                        </div>
                        <div style="width: 4%;float: left;text-align: center;">
                            <span class="glyphicon glyphicon-arrow-right" style="line-height: 50px;"
                                  aria-hidden="true"></span>
                        </div>
                        <div style="width: 48%;float: left;height: 50px;">
                            <textarea datatype="replaceText" style="width: 100%;height:100%;"></textarea>
                        </div>
                    </div>
                </div>
            </div>
            <div class="panel-footer">
                <input type="button" data-event="updateAll" class="btn btn-default" value="执行"/>
            </div>
            <script>
                $('#fastTableInfo .updateAll').on('click', '[data-event=updateAll]', function () {
                    var tableBody = $(this).parents('.updateAllPanel').find('.panel-body');
                    var column = tableBody.find('[datatype=column]').val();
                    var replaceType = tableBody.find('[datatype=replaceType]').val();
                    var searchText = tableBody.find('[datatype=searchText]').val();
                    var replaceText = tableBody.find('[datatype=replaceText]').val();
                    if (column != '' && replaceType != '' && searchText != '' && replaceText != '') {
                        function run(searchArr, runType) {
                            $.post('', {
                                function: 'updateAll',
                                where: searchArr,
                                runType: runType,
                                column: column,
                                replaceType: replaceType,
                                searchText: searchText,
                                replaceText: replaceText,
                            }, function (data) {
                                alert(data);
                                mysqlAJAXClass.getList();
                                $('#fastTableInfo .updateAll').hide();
                                $('#fastTableInfo .deleteAll').hide();
                                $('#fastTableInfo table>thead>tr>th:eq(0)>:checkbox').attr('checked', false);
                            });
                        }

                        if ($(this).parents('#fastTableInfo').find('.deleteAll .panel-body .deleteCount select').val() == 'allPage') {
                            var isHasSearch = false;
                            for (var i in searchArr) {
                                isHasSearch = true;
                            }
                            if (isHasSearch === false) {
                                if (window.confirm('是否确认,您正在尝试批量处理全部数据')) {
                                    run('', 'where');
                                }
                            } else {
                                run(searchArr, 'where');
                            }
                        } else {
                            var allKeys = [];
                            $(this).parents('#fastTableInfo').find('>table>tbody>tr>.select>:checked').each(function () {
                                allKeys.push($(this).attr('data-id'));
                            });
                            var tempSearchArr = {};
                            tempSearchArr[key] = allKeys;
                            run(tempSearchArr, 'keys');
                        }
                    }
                })
            </script>
        </div>
    </div>
    <div style="width:100%;height:30px;">
        <div id="fastTableInfoPage"></div>
        <button class="btn btn-default" style="float: right;" onclick="mysqlAJAXClass.initInsertHtml()">添加</button>
    </div>
    <table class="table table-striped table-hover table-bordered">
        <thead>
        <tr>
            <th style="width: 31px"><input type="checkbox"/></th>
            {foreach $column as $k=>$v}
                {if $v.listShowType!='hidden'}
                    <th column="{$k}">
                        <p>{$v.title}</p>
                        {if $v.listsearch}
                            {if count($v.listsearch)>1}
                                <select>
                                    <option value="">全部</option>
                                    {foreach $v.listsearch as $kk=>$vv}
                                        {if isset($vv.title)}
                                            <option value="{$vv.match|escape:"html"}">{$vv.title}</option>
                                        {else}
                                            <option value="{$vv.match|escape:"html"}">{$vv.match}</option>
                                        {/if}
                                    {/foreach}
                                </select>
                            {else}
                                {if isset($v.listsearch[0].title)}
                                    {$v.listsearch[0].title}
                                {/if}
                            {/if}
                            {foreach $v.listsearch as $num=>$search}
                                {if $v.dataType=='date'}
                                    {$class='date'}
                                {/if}
                                {if substr_count($search.match,'?')==0}
                                {elseif $num==0}
                                    {section name=loop loop=substr_count($search.match,'?')}
                                        <input type="text" {if $class!=''} class="{$class}"{/if}
                                               match="{$search.match|escape:"html"}"/>
                                    {/section}
                                {else}
                                    {section name=loop loop=substr_count($search.match,'?')}
                                        <input type="text" {if $class!=''} class="{$class}"{/if}
                                               tmp="{substr_count($search.match,'?')}"
                                               match="{$search.match|escape:"html"}" style="display: none;"/>
                                    {/section}
                                {/if}
                            {/foreach}
                        {/if}
                    </th>
                {/if}
            {/foreach}
            <th>操作</th>
        </tr>
        </thead>
        <tbody></tbody>
    </table>
    <script>
        if ($('.date').length > 0) {
            $('.date').datepicker({
                dateFormat: 'yy-mm-dd'
            });
        }
        var column = {json_encode($column)};
        var perPage ={$perPage};
        var page = 0;
        var key = "{$key}";
        var deleteOpenAllPageData = {if $deleteOpenAllPageData}true{else}false{/if};
        var searchArr = {};
        var htmlObject = {
            {foreach $allColumnDataType as $typeName}
            '{$typeName}':{include file="./mysqlAdminPlugIn/{$typeName}.tpl"},
            {/foreach}
        };
        var oHead = document.getElementsByTagName('HEAD').item(0);
        for (var i in htmlObject) {
            if (htmlObject[i].js) {
                for (var j = 0; j < htmlObject[i].js.length; j++) {
                    var oscript = document.createElement("script");
                    oscript.type = "text/javascript";
                    oscript.src = htmlObject[i].js[j];
                    oHead.appendChild(oscript);
                }
            }
            if (htmlObject[i].css) {
                for (var j = 0; j < htmlObject[i].css.length; j++) {
                    var oscript = document.createElement("link");
                    oscript.rel = "stylesheet";
                    oscript.href = htmlObject[i].css[j];
                    oHead.appendChild(oscript);
                }
            }
        }
        var mysqlAJAXClass = {
            getList: function () {
                $.post("", {
                    'function': 'getList',
                    'page': page,
                    'searchArr': searchArr
                }, function (data_) {
                    var data_ = JSON.parse(data_);
                    //分页
                    (function () {
                        var pageButtonsHtml = '';
                        for (var i = 0; i < data_.dataCount / perPage; i++) {
                            if (i <= page - 10) {
                                if (i == page - 10) {
                                    pageButtonsHtml += '<span data-page="' + i + '">更多</span>';
                                }
                            } else if (i >= page + 10) {
                                if (i == page + 10) {
                                    pageButtonsHtml += '<span data-page="' + i + '">更多</span>';
                                }
                            } else if (i == page) {
                                pageButtonsHtml += '<span class="select btn btn-link" style="padding: 5px 5px;" data-page="' + i + '">' + (i + 1) + '</span>';
                            } else {
                                pageButtonsHtml += '<span class="btn btn-link" style="padding: 5px 5px;" data-page="' + i + '">' + (i + 1) + '</span>';
                            }
                        }
                        pageButtonsHtml += '总共' + data_.dataCount + '条，' + Math.ceil(data_.dataCount / perPage) + '页';
                        $('#fastTableInfo #fastTableInfoPage').html(pageButtonsHtml);
                    })();
                    //当前页数据
                    var data = data_.data;
                    var tbody = $("#fastTableInfo table tbody");
                    tbody.html("");
                    for (var i = 0; i < data.length; i++) {
                        var tr = $("<tr data-id='" + data[i][key] + "'></tr>");
                        tr.append($("<td class='select'><input data-id='" + data[i][key] + "' type='checkbox'/></td>"));
                        for (var j in column) {
                            if (column[j].listShowType != 'hidden') {
                                if (data_.dataHtml[i] && data_.dataHtml[i][j]) {
                                    tr.append($("<td>" + (data_.dataHtml[i][j] ? data_.dataHtml[i][j] : '') + "</td>"));
                                } else if (column[j].dataList != undefined && column[j].dataList.type == 'Enum') {
                                    tr.append($("<td>" + column[j].dataList.data[data[i][j]] + "</td>"));
                                } else if (htmlObject[column[j].dataType] != undefined && htmlObject[column[j].dataType].list != undefined) {
                                    tr.append(htmlObject[column[j].dataType].list(data[i][j]));
                                } else {
                                    tr.append($("<td>" + (data[i][j] ? data[i][j] : '') + "</td>"));
                                }
                            }
                        }
                        tr.append($("<td style='text-align: center;'><button class='updateButton btn btn-default'>修改</button><button class='deleteButton btn btn-danger'>删除</button></td>"));
                        tbody.append(tr);
                    }
                })
            },
            initInsertHtml: function () {
                var updateHtmlDom = $('<div class="updateHtml"></div>');
                var insertDom = $('<div class="updateHtmlContent"></div>');
                insertDom.append($('<div class="updateHtmlContentText"></div>'));//用来增加一些提示文字
                mysqlAJAXClass.makeHtml(insertDom, column);
                var buttonsDiv = $('<div class="updateButtons"></div>');
                buttonsDiv.append($("<button class='btn btn-danger' onclick='mysqlAJAXClass.insertDataSent(this)'>保存</button>"));
                buttonsDiv.append($("<button class='btn btn-default' onclick=\"$(this).parents('.updateHtml').remove()\">取消</button>"));
                insertDom.append(buttonsDiv);
                updateHtmlDom.append(insertDom);
                $("body").append(updateHtmlDom);
            },
            getDataByTable: function (table) {
                var columnData = {};
                var allsolumnData = table.find(">tbody>tr>.columnValue");
                for (var i = 0; i < allsolumnData.length; i++) {
                    if ($(allsolumnData[i]).is('.columnValueObj')) {
                        columnData[$(allsolumnData[i]).parent().attr('data-name')] = arguments.callee($(allsolumnData[i]).find('>table'));
                    } else if ($(allsolumnData[i]).is('.columnValueArr')) {
                        var insertArr = [];
                        var allTables = $(allsolumnData[i]).find('>table');
                        for (var j = 0; j < allTables.length; j++) {
                            insertArr.push(arguments.callee($(allTables[j])));
                        }
                        columnData[$(allsolumnData[i]).parent().attr('data-name')] = insertArr;
                    } else if ($(allsolumnData[i]).find('[name]').attr('name') == '') {
                        if ($(allsolumnData[i]).find('[name]').attr('type') == 'number') {
                            columnData = parseInt($(allsolumnData[i]).find('[name]').val());
                        } else {
                            columnData = $(allsolumnData[i]).find('[name]').val();
                        }
                    } else {
                        if ($(allsolumnData[i]).find('[name]').attr('type') == 'number') {
                            columnData[$(allsolumnData[i]).find('[name]').attr('name')] = parseInt($(allsolumnData[i]).find('[name]').val());
                        } else {
                            columnData[$(allsolumnData[i]).find('[name]').attr('name')] = $(allsolumnData[i]).find('[name]').val();
                        }
                    }
                }
                return columnData;
            },
            insertDataSent: function (sentButton) {
                $(".updateHtmlContent .baiduEditor").each(function () {
                    $(this).find('textarea').val($(this).find(".edui-editor-iframeholder iframe")[0].contentDocument.body.innerHTML);
                });
                $(".updateHtmlContent").addClass('updateHtmlContentInserting');
                var dataOfColumn = this.getDataByTable($(sentButton).parents('.updateHtmlContent:eq(0)').find('>table'));
                var dataCent = {
                };
                for (var i in dataOfColumn) {
                    if (column[i]['noDBColumn'] == true) {
                        continue;
                    }
                    if (dataOfColumn[i] instanceof Array == true) {
                        var temp = JSON.stringify(dataOfColumn[i]);
                    } else if (typeof(dataOfColumn[i]) == 'object') {
                        var temp = JSON.stringify(dataOfColumn[i]);
                    } else {
                        temp = dataOfColumn[i];
                    }
                    dataCent[i] = temp;
                }
                var sendData = {
                    function: "insertData",
                    data: dataCent
                };
                $.post("", sendData, function (data) {
                    $(".updateHtmlContent").removeClass('updateHtmlContentInserting');
                    mysqlAJAXClass.getList();
                    $(sentButton).parents('.updateHtml').remove();
                });
            },
            makeHtml: function (dom, column, data, deleteButton) {
                if (deleteButton) {
                    var div = $("<table style='border: solid 1px;margin-bottom: 10px;'></table>");
                    div.append($('<tr style="background-color:#95A7BD">' +
                            '<td></td>' +
                            '<td></td>' +
                            '<td style="font-family:iconfont"><span class="deleteArrButton" style="float:right;color:#970000;">&#xe601;</span></td>' +
                            '</tr>'));
                } else {
                    var div = $("<table class='table table-striped table-hover table-bordered'><tbody></tbody></table>");
                }
                for (var i in column) {
                    if (column[i].noDBColumn == true) {
                        continue;
                    } else if (column[i].dataType instanceof Array == true) {
                        var thisFunction = arguments.callee;
                        (function () {
                            var newInsertDiv = $("<td class='columnValue columnValueArr'></td>");
                            //如果数组存的是直接的值
                            if (column[i].dataType[0]['title'] != undefined && typeof column[i].dataType[0]['title'] == 'string') {
                                if (data == undefined || data[i] == undefined) {
                                    thisFunction(newInsertDiv, {
                                        '': column[i].dataType[0]
                                    }, null, true);
                                } else {
                                    for (var j = 0; j < data[i].length; j++) {
                                        thisFunction(newInsertDiv, {
                                            '': column[i].dataType[0]
                                        }, {
                                            '': data[i][j]
                                        }, true);
                                    }
                                }
                            } else {
                                //数组存的是对象
                                if (data == undefined || data[i] == undefined) {
                                    thisFunction(newInsertDiv, column[i].dataType[0], null, true);
                                } else {
                                    for (var j = 0; j < data[i].length; j++) {
                                        thisFunction(newInsertDiv, column[i].dataType[0], data[i][j], true);
                                    }
                                }
                            }
                            newInsertDiv.append($('<button class="addArrDataButton">增加</button>'));
                            var temp = $("<tr class='column' data-name='" + i + "'>" +
                                    "<td class='columnName'>" + column[i].title + "</td><td></td></tr>");
                            temp.append(newInsertDiv);
                            div.find('tbody').append(temp);
                        })();
                    } else if (typeof(column[i].dataType) == 'object') {
                        var thisFunction = arguments.callee;
                        (function () {
                            var newInsertDiv = $("<td class='columnValue columnValueObj'></td>");
                            if (data == undefined) {
                                thisFunction(newInsertDiv, column[i].dataType);
                            } else {
                                thisFunction(newInsertDiv, column[i].dataType, data[i]);
                            }
                            var temp = $("<tr class='column' data-name='" + i + "'>" +
                                    "<td class='columnName'>" + column[i].title + "</td><td></td></tr>");
                            temp.append(newInsertDiv);
                            div.find('tbody').append(temp);
                        })();
                    } else {
                        if (column[i].AUTO_INCREMENT) {
                        } else {
                            var tempTd = $("<td class='columnValue'></td>");
                            if (column[i].dataList != undefined && column[i].dataList.type == 'Enum') {
                                (function () {
                                    var select = $('<select name="' + i + '"><select>');
                                    for (var j in column[i].dataList.data) {
                                        if (data && data[i] && data[i] == j) {
                                            select.append($('<option value="' + j + '" selected>' + column[i].dataList.data[j] + '</option>'));
                                        } else {
                                            select.append($('<option value="' + j + '">' + column[i].dataList.data[j] + '</option>'));
                                        }
                                    }
                                    tempTd.append(select);
                                })();
                            } else if (htmlObject[column[i].dataType] != undefined && htmlObject[column[i].dataType].writeHTML != undefined) {
                                tempTd.append(htmlObject[column[i].dataType].writeHTML(i, data ? data[i] : ''));
                            } else {
                                tempTd.append($("<input class='form-control' name='" + i + "' value='" + ((data && data[i]) ? data[i] : '') + "'/>"));
                            }
                            var tr = $("<tr class='column' data-name='" + i + "'></tr>");
                            tr.append($("<td class='columnName'>" + column[i].title + "</td><td>:</td>"));
                            tr.append(tempTd);
                            div.find('tbody').append(tr);
                        }
                    }
                }
                if (dom.find('>.addArrDataButton').length > 0) {
                    dom.find('>.addArrDataButton').before(div);
                } else {
                    dom.append(div);
                }
            },
            updateingData: {},
            initUpdateHtml: function () {
                $.getJSON('', {
                    'function': 'getOneData',
                    'id': $(this).parents('tr').attr('data-id')
                }, function (data) {
                    mysqlAJAXClass.updateingData = data;
                    var updateDom = $('<div class="updateHtml"></div>');
                    var tempDom = $('<div data-id="' + data[key] + '" class="updateHtmlContent"></div>');
                    for (var i in column) {
                        if (typeof(column[i].dataType) == 'object') {
                            if (typeof data[i] == 'string') {
                                data[i] = JSON.parse(data[i]);
                            }
                        }
                    }
                    mysqlAJAXClass.makeHtml(tempDom, column, data);
                    var buttonsDiv = $('<div class="updateButtons"></div>');
                    buttonsDiv.append($("<button class='btn btn-danger' onclick='mysqlAJAXClass.updateDataSent(\"" + data[key] + "\",this)'>保存</button>"));
                    buttonsDiv.append($("<button class='btn btn-default' onclick=\"$(this).parents('.updateHtml').remove()\">取消</button>"));
                    tempDom.append(buttonsDiv);
                    updateDom.append(tempDom);
                    $("body").append(updateDom);
                });
            },
            updateDataSent: function (id, buttonDom) {
                $(".updateHtmlContent .baiduEditor").each(function () {
                    $(this).find('textarea').val($(this).find(".edui-editor-iframeholder iframe")[0].contentDocument.body.innerHTML);
                });
                var dataOfColumn = this.getDataByTable($(buttonDom).parents('.updateHtmlContent:eq(0)').find('>table'));
                var dataCent = {};
                var isUpdate = false;
                for (var i in dataOfColumn) {
                    if (column[i]['noDBColumn'] == true) {
                        continue;
                    }
                    if (dataOfColumn[i] instanceof Array == true) {
                        var temp = JSON.stringify(dataOfColumn[i]);
                    } else if (typeof(dataOfColumn[i]) == 'object') {
                        var temp = JSON.stringify(dataOfColumn[i]);
                    } else {
                        var temp = dataOfColumn[i];
                    }
                    if (temp != this.updateingData[i]) {
                        isUpdate = true;
                        dataCent[i] = temp;
                    }
                }
                if (isUpdate) {
                    $.post('', {
                        function: 'update',
                        id: id,
                        data: dataCent
                    }, function (data) {
                        if (data == 1) {
                            $(buttonDom).parents('.updateHtml').remove();
                            mysqlAJAXClass.getList();
                        } else {
                            alert('更新失败');
                        }
                    });
                } else {
                    alert('未修改任何数据');
                }
            },
            addArrListHtml: function () {
                var columnOfThis = column;
                var allTrParents = $(this).parents('.updateHtml tr');
                for (var i = allTrParents.length - 1; i >= 0; i--) {
                    columnOfThis = columnOfThis[$(allTrParents[i]).attr('data-name')].dataType;
                    if (columnOfThis instanceof Array == true) {
                        columnOfThis = columnOfThis[0];
                    }
                }
                if (columnOfThis['title'] != undefined && typeof columnOfThis['title'] == "string") {
                    mysqlAJAXClass.makeHtml($(this).parent(), {
                        '': columnOfThis
                    }, null, true);
                } else {
                    mysqlAJAXClass.makeHtml($(this).parent(), columnOfThis, null, true);
                }
            },
            deleteSomeDataSent: function (allKeys, deleteAllCom) {
                function deleteAll() {
                    $.post('', {
                        function: 'deleteSomeData',
                        id: allKeys
                    }, function (data) {
                        alert('成功删除' + data + '条记录')
                        mysqlAJAXClass.getList();
                        deleteAllCom.hide();
                    });
                    deleteAllCom.parent('#fastTableInfo').find('table>thead>tr>th:eq(0)>:checkbox').attr('checked', false);
                }

                if (window.confirm('是否确认删除' + allKeys.length + '条数据么?')) {
                    if (allKeys.length > 10) {
                        if (window.confirm('数量较多请谨慎检查,以免误删')) {
                            deleteAll();
                        }
                    } else {
                        deleteAll();
                    }
                }
            },
            deleteDataSent: function () {
                if (window.confirm('是否确认删除')) {
                    var id = $(this).parents('tr').attr('data-id');
                    $.getJSON('', {
                        function: 'deleteData',
                        id: id
                    }, function (data) {
                        if (data == 1) {
                            alert('已经删除');
                            mysqlAJAXClass.getList();
                        } else {
                            alert('删除异常');
                        }
                    });
                }
            },
            deleteByWhere: function (searchArr) {
                $.post('', {
                    function: 'getCountByWhere',
                    where: searchArr,
                }, function (dataCount) {
                    if (window.confirm('是否确认删除' + dataCount + '条数据么?')) {
                        $.post('', {
                            function: 'deleteAllPageData',
                            where: searchArr,
                        }, function (result) {
                            alert('删除了' + result + '条数据');
                        });
                    }
                });
            },
            updateSomeDataReplace: function ($where, $search, $replace) {
            }
        };
        mysqlAJAXClass.getList();
        $('#fastTableInfo #fastTableInfoPage').on('click', 'span', function () {
            page = parseInt($(this).attr('data-page'));
            mysqlAJAXClass.getList();
        });
        $('#fastTableInfo').on('click', '.updateButton', mysqlAJAXClass.initUpdateHtml);
        $('#fastTableInfo').on('click', '.deleteButton', mysqlAJAXClass.deleteDataSent);
        $('.updateHtml').on('click', '.addArrDataButton', mysqlAJAXClass.addArrListHtml);
        $('.updateHtml').on('click', '.deleteArrButton', function () {
            $(this).parents('table').eq(0).remove();
        });
        $('#fastTableInfo table thead th').on('change', 'select', function () {
            $('#fastTableInfo table thead th input').val('');
            delete searchArr[$(this).parents('th').attr('column')];
            var allInput = $(this).parents('th').find('[match]');
            allInput.hide();
            var isNeedToInput = false;
            for (var i = 0; i < allInput.length; i++) {
                if ($(this).val() == allInput.eq(i).attr('match')) {
                    isNeedToInput = true;
                    allInput.eq(i).show();
                }
            }
            if (isNeedToInput == false) {
                if ($(this).val() !== '') {
                    searchArr[$(this).parents('th').attr('column')] = $(this).val();
                } else {
                    delete searchArr[$(this).parents('th').attr('column')];
                }
                mysqlAJAXClass.getList();
            }
        });
        $('#fastTableInfo table thead [column]').on('change', 'input', function () {
            var input = $(this);
            var searchVal = input.attr('match');
            var sameMatchCount = 0;
            var isAllWrite = 0;//是否所有字段都已经填写

            $('#fastTableInfo table thead th input').each(function () {
                if ($(this).attr('match') == input.attr('match')) {
                    sameMatchCount++;
                    if ($(this).val() != '') {
                        isAllWrite++;
                    }
                    searchVal = searchVal.replace('?', $(this).val());
                }
            });
            //要么都写了,要么都没写
            if (isAllWrite == 0) {
                delete searchArr[$(this).parents('th').attr('column')];
                page = 0;
                mysqlAJAXClass.getList();
            } else if (isAllWrite == sameMatchCount) {
                searchArr[$(this).parents('th').attr('column')] = searchVal;
                page = 0;
                mysqlAJAXClass.getList();
            } else {
                delete searchArr[$(this).parents('th').attr('column')];
            }
        });
        //整体删除
        $('#fastTableInfo .deleteAll [data-event=delete]').click(function () {
            var select = $(this).parents('.deleteAll').find('.panel-body .deleteCount select').val();
            if (select == 'allPage') {
                var isHasColumnInput = false;
                for (var i in searchArr) {
                    isHasColumnInput = true;
                }
                if (isHasColumnInput) {
                    mysqlAJAXClass.deleteByWhere(searchArr);
                } else {
                    alert('操作被禁止,您的操作尝试删除全部数据');
                }
            } else {
                var allKeys = [];
                $(this).parents('#fastTableInfo').find('>table>tbody>tr>.select>:checked').each(function () {
                    allKeys.push($(this).attr('data-id'));
                });
                mysqlAJAXClass.deleteSomeDataSent(allKeys, $(this).parents('.deleteAll'));
            }
        });
        //整体修改
        $('#fastTableInfo .deleteAll [data-event=update]').click(function () {
            //alert('开发中');
//			var select = $(this).parents('.deleteAll').find('.panel-body .deleteCount select').val();
//			if(select=='allPage'){
//				var isHasColumnInput = false;
//				for(var i in searchArr){
//					isHasColumnInput = true;
//				}
//				if(isHasColumnInput){
//					mysqlAJAXClass.deleteByWhere(searchArr);
//				}else{
//					alert('操作被禁止,您的操作尝试删除全部数据');
//				}
//			}else{
//				var allKeys = [];
//				$(this).parents('#fastTableInfo').find('>table>tbody>tr>.select>:checked').each(function(){
//					allKeys.push($(this).attr('data-id'));
//				});
//				mysqlAJAXClass.deleteSomeDataSent(allKeys, $(this).parents('.deleteAll') );
//			}
        });
        $('#fastTableInfo table').on('click', '>tbody>tr>.select :checkbox', function () {
            var allBrother = $(this).parents('table').find('>tbody>tr>.select>:checked');
            if (allBrother.length >= 2) {
                $(this).parents('table').parent().find('>.deleteAll').show();
            } else {
                $(this).parents('table').parent().find('>.deleteAll').hide();
            }
            if (allBrother.length == perPage && deleteOpenAllPageData) {
                $(this).parents('table').parent().find('>.deleteAll .deleteCount').html('选择<select><option value="thisPage">本页全部</option><option value="allPage">全部</option></select>记录');
            } else {
                $(this).parents('table').parent().find('>.deleteAll .deleteCount').html('选择<span>' + allBrother.length + '</span>条记录');
            }
        });
        $('#fastTableInfo table>thead>tr>th:eq(0)>:checkbox').click(function () {
            if (this.checked == true) {
                $(this).parents('table').parent().find('>.deleteAll').show();
            } else {
                $(this).parents('table').parent().find('>.deleteAll').hide();
            }
            $('#fastTableInfo table>tbody>tr>.select>:checkbox').attr('checked', this.checked);
            var allBrother = $(this).parents('table').find('>tbody>tr>.select>:checked');
            if (allBrother.length == perPage && deleteOpenAllPageData) {
                $(this).parents('table').parent().find('>.deleteAll .deleteCount').html('选择<select><option value="thisPage">本页全部</option><option value="allPage">全部</option></select>记录');
            } else {
                $(this).parents('table').parent().find('>.deleteAll .deleteCount').html('选择<span>' + allBrother.length + '</span>条记录');
            }
        });
    </script>
</div>