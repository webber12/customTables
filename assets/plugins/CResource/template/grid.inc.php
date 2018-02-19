<link rel="stylesheet" type="text/css" href="<?=$dir;?>easyui/themes/bootstrap/easyui.css">
<link rel="stylesheet" type="text/css" href="<?=$dir;?>easyui/themes/icon.css">
<link rel="stylesheet" type="text/css" href="<?=$dir;?>easyui/demo/demo.css">
<script type="text/javascript" src="<?=$dir;?>easyui/jquery.easyui.min.js"></script>
<script type="text/javascript" src="<?=$dir;?>easyui/plugins/jquery.edatagrid.js"></script>
<script type="text/javascript">
    (function($){
        $.extend($.fn.datebox.defaults,{
            formatter:function(date){
                var y = date.getFullYear();
                var m = date.getMonth()+1;
                var d = date.getDate();
                return y + '-' + (m<10?('0'+m):m) + '-' + (d<10?('0'+d):d);
            },
            parser:function(s){
                if (!s) return new Date();
                var ss = s.split('-');
                var d = parseInt(ss[2],10);
                var m = parseInt(ss[1],10);
                var y = parseInt(ss[0],10);
                if (!isNaN(y) && !isNaN(m) && !isNaN(d)){
                    return new Date(y,m-1,d);
                } else {
                    return new Date();
                }
            }
        });

        $.extend($.fn.pagination.defaults,{
            pageList:[10,20,30,50,100,200,500,1000]
        });
    })(jQuery);
</script>
<style>
    .datagrid-toolbar{background:#ffffff;}
    .datagrid-cell{color:#000;}
    .panel-body{padding:20px 0 !important;}
    a.l-btn span span.l-btn-icon-left{padding-left:0;}
    a.easyui-linkbutton{vertical-align:top;}
    a.easyui-linkbutton i.fa{}
    .filters{padding:10px;background:#f5f5f5;text-align:right;}
    .filters label{margin-right:10px;}
    .filters label input{border:solid 1px #D4D4D4;background:#ffffff;padding:3px;}
    .tabs-header{border:none;background:none;}
    #tt .tabs{padding-left:0;height:auto;border-bottom:solid 1px rgb(221, 221, 221);}
    #tt .tabs:after{display:table;content:'';float:none;clear:both;}
    #tt .tabs>li{padding: 0 14px;border-radius:0;height:40px;line-height:40px;}
    #tt .tabs>li a{border:none;}
    #tt .tabs>li.tabs-selected{border:solid 1px rgb(221, 221, 221);border-bottom:solid 1px #ffffff;background:#ffffff;}
</style>
<div id="tt" class="easyui-tabs" style="height:auto;">
    <div title="<?=$gridTabTitle;?>" style="padding:10px" data-options="closable:false" >
        <table class="easyui-datagrid" id="dataGrid" title="<?=$gridTitle;?>"
               data-options="idField:'<?=$idField;?>',toolbar:'#tbar',singleSelect:true,pagination:true,pageSize:<?=$display;?>">
            <thead>
            <tr>
                <?=$header;?>
            </tr>
            </thead>
        </table>
    </div>

</div>

<div id="tbar" style="padding:5px;height:auto">
    <div style="margin-bottom:10px">
        <a href="#" class="easyui-linkbutton" onclick="addBtn()"><i class="fa fa-plus-square" aria-hidden="true"></i> Добавить</a>
        <a href="#" class="easyui-linkbutton" onclick="editBtn()"><i class="fa fa-pencil-square-o" aria-hidden="true"></i> Редактировать</a>
        <a href="#" class="easyui-linkbutton" onclick="javascript:<?=$jqname;?>('#dataGrid').edatagrid('saveRow')"><i class="fa fa-floppy-o" aria-hidden="true"></i> Сохранить</a>
        <a href="#" class="easyui-linkbutton" onclick="javascript:<?=$jqname;?>('#dataGrid').edatagrid('destroyRow')"><i class="fa fa-trash-o" aria-hidden="true"></i> Удалить</a>
    </div>
    <div class="filters">
        <?=$searchData['fields'];?>
    </div>
</div>

<script type="text/javascript">
    var index = 0;
    function addPanel(id){
        index++;
        var url = window.location.href.replace(/#[^#]*$/, "");
        if(id>0){
            title = 'Документ #' + id;
            url = '<?=$docEditURL;?>' + id;
        }else{
            title = 'Новый документ';
            url = '<?=$docNewURL;?>'+'<?=$pid;?>';
        }
        <?=$jqname;?>('#tt').tabs('add',{
            title: title,
            content: '<iframe scrolling="yes" frameborder="0"  src="'+ url +'" style="width:100%;height:800px"></iframe>',
            height: '500px',
            closable: true
        });
    }
    function removePanel(){
        var tab = <?=$jqname;?>('#tt').tabs('getSelected');
        if (tab){
            var index = <?=$jqname;?>('#tt').tabs('getTabIndex', tab);
            <?=$jqname;?>('#tt').tabs('close', index);
        }
    }

    var pager = <?=$jqname;?>('#dataGrid').datagrid().datagrid('getPager');
    var editIndex = undefined;
    <?=$jqname;?>('#dataGrid').edatagrid({
            url: '<?=$listURL;?>',
            saveUrl: '<?=$saveURL;?>',
            updateUrl: '<?=$updateURL;?>',
            destroyUrl: '<?=$delURL;?>',
            errorMSG:{
                title:"Ошибка"
            },
            destroyMsg:{
                norecord:{
                    title:'Ошибка',
                    msg:'Необходимо выбрать запись для удаления'
                },
                confirm:{
                    title:'Подтверждение удаления',
                    msg:'Вы действительно хотите удалить запись?'
                }
            },
            onSave: function(index, row){
                <?=$jqname;?>(this).datagrid('reload');
            }
        });

    function addBtn(){
        addPanel(0);
    }
    function delBtn(){
        alert('Тут нужно удалить строку');

    }
    function editBtn(){
        var row = <?=$jqname;?>('#dataGrid').datagrid('getSelected');
        if (row){
            addPanel(row.<?=$idField;?>);
        }else{
            <?=$jqname;?>.messager.show({
                title: "Ошибка",
                msg: "Необходимо выбрать документ для редактирования"
            });
        }
    }
/*
    function findBtn(){
        alert('Запустить поиск LIKE');
    } */
	function findBtn(){
        <?=$searchData['scripts'];?>
    }
</script>

