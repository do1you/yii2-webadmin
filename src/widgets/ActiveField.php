<?php
/**
 * 继承系统默认的表单，根据现有模板修改内容
 */
namespace webadmin\widgets;

use Yii;
use yii\base\Component;
use yii\base\ErrorHandler;
use yii\base\Model;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\web\JsExpression;

class ActiveField extends \yii\widgets\ActiveField
{
    public $labelOptions = ['class' => 'col-sm-2 control-label no-padding-right'];
    
    public $inputOptions = ['class' => 'form-control'];
    
    public $template = "{label}\n<div class='col-sm-10'>{input}\n{hint}\n{error}</div>";
    
    public $enableAjaxValidation = true;
    
    protected $_inputId;
    
    /**
     * 初始化自定义规则
     */
    public function init()
    {
        if($this->isClientValidationEnabled()) $this->inputOptions = array_merge($this->inputOptions,$this->getInputClientOptions());
        return parent::init();
    }
    
    /**
     * 日期-年份
     */
    public function dateyear($options = [])
    {
        $options['data-date-format'] = 'yyyy'; 
        $this->textInput($options);
        if(!empty($this->parts['{input}'])){
            $this->parts['{input}'] = "<span class='input-icon icon-right'> {$this->parts['{input}']} <i class='fa fa-calendar'></i></span>";
        }
        $id = $this->getInputId($options);
        $view = $this->form->getView();
        $view->registerJsFile('@assetUrl/js/datetime/bootstrap-datepicker.js',['depends' => \webadmin\WebAdminAsset::className()]);
        $view->registerJs("$('#{$id}').datepicker({'format':'yyyy','startView':2,'minViewMode':2});");
        return $this;
    }
    
    /**
     * 日期
     */
    public function date($options = [])
    {
        $options['data-date-format'] = 'yyyy-mm-dd'; 
        $this->textInput($options);
        if(!empty($this->parts['{input}'])){
            $this->parts['{input}'] = "<span class='input-icon icon-right'> {$this->parts['{input}']} <i class='fa fa-calendar'></i></span>";
        }
        $id = $this->getInputId($options);
        $view = $this->form->getView();
        $view->registerJsFile('@assetUrl/js/datetime/bootstrap-datepicker.js',['depends' => \webadmin\WebAdminAsset::className()]);
        $view->registerJs("$('#{$id}').datepicker();");
        return $this;
    }
    
    /**
     * 日期时间
     */
    public function datetime($options = [])
    {
        $options['data-date-format'] = 'YYYY-MM-DD HH:mm:ss';
        $this->textInput($options);
        if(!empty($this->parts['{input}'])){
            $this->parts['{input}'] = "<span class='input-icon icon-right'> {$this->parts['{input}']} <i class='fa fa-clock-o'></i></span>";
        }
        $id = $this->getInputId($options);
        $view = $this->form->getView();
        $view->registerCssFile('@assetUrl/js/datetime/bootstrap-datetimepicker.css',['depends' => \webadmin\WebAdminAsset::className()]);
        $view->registerJsFile('@assetUrl/js/datetime/moment.js',['depends' => \webadmin\WebAdminAsset::className()]);
        $view->registerJsFile('@assetUrl/js/datetime/bootstrap-datetimepicker.js',['depends' => \webadmin\WebAdminAsset::className()]);
        $view->registerJs("$('#{$id}').datetimepicker();");
        return $this;
    }
    
    /**
     * 时间
     */
    public function time($options = [])
    {
        $this->textInput($options);
        if(!empty($this->parts['{input}'])){
            $this->parts['{input}'] = "<span class='input-icon icon-right'> {$this->parts['{input}']} <i class='fa fa-clock-o'></i></span>";
        }
        $id = $this->getInputId($options);
        $view = $this->form->getView();
        $view->registerJsFile('@assetUrl/js/datetime/bootstrap-timepicker.js',['depends' => \webadmin\WebAdminAsset::className()]);
        $view->registerJs("$('#{$id}').timepicker();");
        return $this;
    }
    
    /**
     * 日期范围
     */
    public function daterange($options = [])
    {
        $this->textInput($options);
        if(!empty($this->parts['{input}'])){
            $this->parts['{input}'] = "<span class='input-icon icon-right'> {$this->parts['{input}']} <i class='fa fa-calendar'></i></span>";
        }
        $id = $this->getInputId($options);
        $view = $this->form->getView();
        $view->registerJsFile('@assetUrl/js/datetime/moment.js',['depends' => \webadmin\WebAdminAsset::className()]);
        $view->registerJsFile('@assetUrl/js/datetime/daterangepicker.js',['depends' => \webadmin\WebAdminAsset::className()]);
        $view->registerJs(" $('#{$id}').daterangepicker({
                        		locale : { format: 'YYYY-MM-DD' },
                                ranges : {
                                    '今日': [moment().startOf('day'), moment()],
                                    '昨日': [moment().subtract('days', 1).startOf('day'), moment().subtract('days', 1).endOf('day')],
                                    '最近7日': [moment().subtract('days', 6), moment()],
                                    '最近30日': [moment().subtract('days', 29), moment()],
                                    '本月': [moment().startOf('month'),moment().endOf('month')],
                                    '上个月': [moment().subtract(1,'month').startOf('month'),moment().subtract(1,'month').endOf('month')]
                                },
                                autoUpdateInput : false, // 自动更新时间
                                timePicker: false
                            });");
        return $this;
    }
    
    /**
     * 日期时间范围
     */
    public function datetimerange($options = [])
    {
        $this->textInput($options);
        if(!empty($this->parts['{input}'])){
            $this->parts['{input}'] = "<span class='input-icon icon-right'> {$this->parts['{input}']} <i class='fa fa-calendar'></i></span>";
        }
        
        $id = $this->getInputId($options);
        $view = $this->form->getView();
        $view->registerJsFile('@assetUrl/js/datetime/moment.js',['depends' => \webadmin\WebAdminAsset::className()]);
        $view->registerJsFile('@assetUrl/js/datetime/daterangepicker.js',['depends' => \webadmin\WebAdminAsset::className()]);
        $view->registerJs(" $('#{$id}').daterangepicker({
                            locale : { format: 'YYYY-MM-DD HH:mm:ss' },
                            ranges : {
                                '今日': [moment().startOf('day'), moment()],
                                '昨日': [moment().subtract('days', 1).startOf('day'), moment().subtract('days', 1).endOf('day')],
                                '最近7日': [moment().subtract('days', 6), moment()],
                                '最近30日': [moment().subtract('days', 29), moment()],
                                '本月': [moment().startOf('month'),moment().endOf('month')],
                                '上个月': [moment().subtract(1,'month').startOf('month'),moment().subtract(1,'month').endOf('month')]
                            },
                            autoUpdateInput : false, // 自动更新时间
                            timePicker: true
                        });");
        return $this;
    }
    
    /**
     * 单文件上传控件
     */
    public function oneFile($acceptedFiles = "image/*", $options = [])
    {
        $this->textInput($options);
        
        $id = $this->getInputId($options);
        $plid = str_replace(["-","_"],"",$id);
        $url = \yii\helpers\Url::toRoute('/config/default/dropzone-upload',[]);
        $this->parts['{input}'] .= "<div id='{$plid}One' action='{$url}' class='dropzone'></div>";
        $acceptedFiles = !empty($acceptedFiles) ? $acceptedFiles : "image/*"; // 允许上传文件类型
        $file = trim($this->model[$this->attribute],'/');
        $src = $file ? Yii::getAlias('@webroot').'/'.$file : '';
        if(!empty($src) && file_exists($src)){
            $array = @getimagesize($src);
            $array && ($fileArr = [
                'name' => basename($src),
                'type' => $array['mime'],
                'size' => filesize($src),
                'accepted' => true,
                //'src' => \yii\helpers\Url::to('@web/'.$file),
                'src' => Yii::createObject('webadmin\ext\image\Imageer')->getpic($file,120,120),
            ]);
        }
        $fileVal = !empty($fileArr) ? json_encode($fileArr) : '""'; // 默认文件
        $view = $this->form->getView();
        $view->registerJsFile('@assetUrl/js/dropzone/dropzone.min.js',['depends' => \webadmin\WebAdminAsset::className()]);
        $view->registerJs("
            Dropzone.options.{$plid}One = {
                acceptedFiles: '{$acceptedFiles}',
                maxFilesize: 2,
                maxFiles: 1,
                parallelUploads: 1,
                uploadMultiple: false,
                maxfilesexceeded: function(a){
                    this.removeFile(a);
                    alert('只允许上传一个文件');
                },
                dictDefaultMessage: '拖拽文件或者点击',
                addRemoveLinks: true,
                dictRemoveFile: '移除',
                init: function(){
                    this.on('removedfile',function(a){
                        if(a.status!='error'){
                            $('#{$id}').val('');
                        }
                    });
                    
                    this.on('success',function(a){
                        a.xhr && a.xhr.status=='200' && $('#{$id}').val(a.xhr.response);
                        var elname = $('#{$id}').attr('name'),
                            fv = $('form.validate').data('bootstrapValidator');
                        fv && fv.options.fields[elname] && fv.updateStatus(elname,'NOT_VALIDATED', null);
                    });
                    
                    var mockFile = {$fileVal};
                    if(mockFile){ // 初始
                        this.emit('addedfile', mockFile);
                        this.emit('thumbnail', mockFile, mockFile.src);
                        this.emit('complete', mockFile);
                        this.emit('success', mockFile);
                        this.emit('processing', mockFile);
                        this.emit('complete', mockFile);
                        this.files.push( mockFile );
                    }
                }
            };
        ",\yii\web\View::POS_END);
        
        return $this;
    }
    
    /**
     * 多文件上传控件
     */
    public function manyFile($acceptedFiles = "image/*", $maxFiles = 10, $options = [])
    {
        $oldVal = $file = $this->model[$this->attribute];
        
        $this->model[$this->attribute] = null;
        $this->textInput($options);
        
        $id = $this->getInputId($options);
        $name = is_array($this->model) ? (isset($options['name']) ? $options['name'] : $this->attribute) : Html::getInputName($this->model, $this->attribute);
        $plid = str_replace(["-","_"],"",$id);
        $url = \yii\helpers\Url::toRoute('/config/default/dropzone-upload',[]);
        $this->parts['{input}'] = "<div id='{$plid}Many' action='{$url}' class='dropzone'></div>";
        $acceptedFiles = !empty($acceptedFiles) ? $acceptedFiles : "image/*"; // 允许上传文件类型
        
        $fileArr = [];
        if($file){
            $srcs = is_array($file) ? $file : array($file);
            foreach($srcs as $src){
                $src = $file = trim($src,'/');
                $src = Yii::getAlias('@webroot').'/'.$src;
                if(file_exists($src)){
                    $array = @getimagesize($src);
                    $array && ($fileArr[] = array(
                        'name' => basename($src),
                        'type' => $array['mime'],
                        'size' => filesize($src),
                        'accepted' => true,
                        'source' => $file,
                        //'src' => \yii\helpers\Url::to('@web/'.$file), // Helpfn::getpic($src,120,120)
                        'src' => Yii::createObject('webadmin\ext\image\Imageer')->getpic($file,120,120),
                    ));
                }
            }
        }
        
        $fileVal = !empty($fileArr) ? json_encode($fileArr) : '""'; // 默认文件
        $view = $this->form->getView();
        $view->registerJsFile('@assetUrl/js/dropzone/dropzone.min.js',['depends' => \webadmin\WebAdminAsset::className()]);
        $view->registerJs("
            Dropzone.options.{$plid}Many = {
                acceptedFiles: 'image/*',
                maxFilesize: 2,
                maxFiles: {$maxFiles},
                parallelUploads: 1,
                uploadMultiple: false,
                maxfilesexceeded: function(a){
                    this.removeFile(a);
                    alert('只允许上传{$maxFiles}个文件');
                },
                dictDefaultMessage: '拖拽图片或者点击',
                addRemoveLinks: true,
                dictRemoveFile: '移除',
                init: function(){
                    this.on('removedfile',function(a){
                        if(a.status!='error'){
                            a.inputEl && a.inputEl.remove();
                        }
                    });
                    
                    this.on('success',function(a){
                        if(a.xhr && a.xhr.status=='200' && a.xhr.response){
                            var el = $(\"<input type='hidden' name='{$name}[]' value='\"+a.xhr.response+\"'>\");
                            $('#{$plid}Many').append(el);
                            a.inputEl = el;
                        }
                    });
                    
                    var mockFiles = {$fileVal};
                    if(mockFiles && mockFiles.length){ // 初始图片
                        var v = this;
                        $.each(mockFiles,function(index){
                            mockFile = mockFiles[index];
                            v.emit('addedfile', mockFile);
                            v.emit('thumbnail', mockFile, mockFile.src);
                            v.emit('complete', mockFile);
                            v.emit('success', mockFile);
                            v.emit('processing', mockFile);
                            v.emit('complete', mockFile);
                            v.files.push( mockFile );
            
                            var el = $(\"<input type='hidden' name='{$name}[]' value='\"+mockFile.source+\"'>\");
                            $('#{$plid}Many').append(el);
                            mockFile.inputEl = el;
                        });
                    }
                }
            };
        ",\yii\web\View::POS_END);
        
        $this->model[$this->attribute] = $oldVal;
        return $this;
    }
    
    /**
     * 获取查询表单文本框
     */
    public function searchInput($options = [])
    {
        $this->template = '{label}{input}{hint}';
        $this->options = ['class' => 'form-group margin-right-10 margin-top-5 margin-bottom-5'];
        $this->labelOptions = ['class' => 'control-label padding-right-5'];
        
        return $this->textInput($options);
    }
    
    /**
     * 拼音首字母输入框
     */
    public function pinyin($to = '', $options = [])
    {
        $this->textInput();
        $id = $this->getInputId($options);
        $url = \yii\helpers\Url::toRoute('/config/default/pinyin');

        $view = $this->form->getView();
        $view->registerJs("$('#{$id}').on('keyup',function(){ $.get('{$url}', {zh: $(this).val()}, function(text) {
            $('{$to}').val(text);
        }); });");
        
        return $this;
    }
    
    /**
     * 左右栏多选框，调用duallistbox
     */
    public function duallistbox($items, $options = []){
        $options += ['multiple'=>'multiple','style'=>'height:280px;'];
        $id = $this->getInputId($options);
        $this->dropDownList($items,$options);
        
        $view = $this->form->getView();
        $view->registerCssFile('@assetUrl/css/bootstrap-duallistbox.css',['depends' => \webadmin\WebAdminAsset::className()]);
        $view->registerJsFile('@assetUrl/js/jquery.bootstrap-duallistbox.js',['depends' => \webadmin\WebAdminAsset::className()]);
        $view->registerJs("$('#{$id}').bootstrapDualListbox({
            infoText: '可选项 ({0})',
            infoTextEmpty: '暂无可选项',
            infoText2: '已选项 ({0})',
            infoTextEmpty2: '暂无已选项'
        }).bootstrapDualListbox('getContainer').find('.btn').addClass('btn-white btn-primary btn-bold');");
        
        return $this;
    }
    
    /**
     * 可搜索的下拉框
     */
    public function select2($items, $options = []){
        $id = $this->getInputId($options);
        $this->dropDownList($items, $options);
        
        $view = $this->form->getView();
        $view->registerJsFile('@assetUrl/js/select2/select2.js',['depends' => \webadmin\WebAdminAsset::className()]);
        if(!empty($options['multiple'])){
            $view->registerJs("$('#{$id}').select2({closeOnSelect:false});");
        }else{
            $view->registerJs("$('#{$id}').select2();");
        }
        
        return $this;
    }
    
    // 获取异步下拉的默认值
    private function _ajax_options($url='',$value=[]){
        if(empty($value)) return [];
        $mainConfig = require Yii::getAlias('@app/config/main.php');
        $class = isset($mainConfig['components']['request']) ? $mainConfig['components']['request'] : [];
        $class['class'] = '\yii\web\Request';
        $request = Yii::createObject($class);
        $request->setUrl($url);
        
        $parseArr = parse_url($url);
        !empty($parseArr['query']) ? parse_str($parseArr['query'],$get) : ($get=[]);
        $get['id'] = $value;
        $request->setQueryParams($get);
        list($route, $params) = Yii::$app->urlManager->parseRequest($request);
        
        $oldRequest = Yii::$app->request;
        Yii::$app->set('request', $request);
        $result = Yii::$app->runAction($route,$params);
        Yii::$app->set('request', $oldRequest);
        
        if(!empty($result['items'])){
            $result = \yii\helpers\ArrayHelper::map($result['items'], 'id', 'text');
        }else{
            $result = [];
        }

        return $result;
    }
    
    /**
     * 下拉异步单选
     */
    public function selectajax($url='', $options = [], $mult = false)
    {
        if($mult) $options['multiple'] = 'multiple';
        $id = $this->getInputId($options);
        $this->dropDownList($this->_ajax_options($url,$this->model[$this->attribute]), $options);
        
        $view = $this->form->getView();
        $view->registerJsFile('@assetUrl/js/select2/select2.js',['depends' => \webadmin\WebAdminAsset::className()]);
        $closeOnSelect = $mult ? 'false' : 'true';
        $view->registerJs("$('#{$id}').select2({
                                     ajax: {
                                         type:'GET',
                                         url: '{$url}',
                                         dataType: 'json',
                                         delay: 250,
                                         data: function (params) {
                                             return {q: params.term,page: params.page};
                                         },
                                         processResults: function (data, params) {
                                             params.page = params.page || 1;
                                             return {
                                                 results: data.items,
                                                 pagination: {
                                                     more: params.page < data.total_page
                                                 }
                                             };
                                         },
                                         cache: true
                                     },
                                     placeholder:'请选择',
                                     closeOnSelect:{$closeOnSelect},
                                     language: 'zh-CN',
                                     tags: false,
                                     allowClear: true,
                                     escapeMarkup: function (m){ return m; },
                                     minimumInputLength: 0,
                                     formatResult: function formatRepo(r){return r.text;},
                                     formatSelection: function formatRepoSelection(r){return r.text;}
                                });");
        
        return $this;
    }
    
    /**
     * 下拉异步多选
     */
    public function selectajaxmult($url='', $options = [])
    {
        return $this->selectajax($url, $options, true);
    }
    
    /**
     * 树型选择控件,第三参数是否多选
     */
    public function treeList($items, $options = [], $multiSelect=true){
        $id = $this->getInputId($options);
        $name = is_array($this->model) ? (isset($options['name']) ? $options['name'] : $this->attribute) : Html::getInputName($this->model, $this->attribute);
        $multiSelect = json_encode($multiSelect);
        $items = json_encode($items);
        $title = !isset($options['title'])?'&nbsp;':$options['title'];
        $this->parts['{input}'] = ' <div class="widget flat radius-bordered">
                                        <div class="widget-header bg-themeprimary">
                                            <span class="widget-caption">'.$title.'</span>
                                            <div class="widget-buttons">
                                                <a href="#" data-toggle="collapse"><i class="fa fa-minus"></i></a>
                                            </div>
                                        </div>
                                        <div class="widget-body" style="padding:0 12px 6px;max-height:400px;overflow-y:auto;">
                							<div id="'.$id.'_tree" class="tree tree-solid-line">
                                                <div class="tree-folder" style="display: none;">
                                                    <div class="tree-folder-header">
                                                        <i class="fa fa-folder"></i>
                                                        <div class="tree-folder-name"></div>
                                                    </div>
                                                    <div class="tree-folder-content">
                                                    </div>
                                                    <div class="tree-loader" style="display: none;"></div>
                                                </div>
                                                <div class="tree-item" style="display: none;">
                                                    <i class="tree-dot"></i>
                                                    <div class="tree-item-name"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>';
        
        $view = $this->form->getView();
        $view->registerJsFile('@assetUrl/js/fuelux/treeview/tree-custom.min.js',['depends' => \webadmin\WebAdminAsset::className()]);
        $view->registerJs("$('#{$id}_tree').tree({
            dataSource: {
                data : function(options, callback) {
        		    var data = null;
        		    if(! ('text' in options) && !('type' in options)) { // 根节点
                        data = {$items};
        		    } else if (('type' in options) && options.type=='folder') {
                        data = options.children || {};
        		    }
        		    
        		    if (data != null){
        		      callback({ data: data });
        		    }
        		}
            },
    	    multiSelect: {$multiSelect},
            loadingHTML: '<div class=\'tree-loading\'><i class=\'fa fa-rotate-right fa-spin\'></i></div>'
        }).bind('updated',function(e,data){
            $('#{$this->form->options['id']}').find('input[name=\'{$name}[]\']').remove();
            var acts = $('#{$id}_tree').tree('selectedItems');
            if(acts.length){
                $.each(acts,function(index,objs){
                    $('#{$this->form->options['id']}').append('<input type=\'hidden\' name=\'{$name}[]\' value=\''+objs.id+'\'>');
                });
            }
        });setTimeout(function(){ $('#{$id}_tree').tree('expand'); },50);");
        
        return $this;
    }
    
    /**
     * 开关按纽
     */
    public function switchs($options = [])
    {
        if(empty($options['class'])) $options['class'] = 'colored-primary slider-icon';
        $options['class'] .= ' checkbox-slider';
        $this->checkbox($options, false);

        return $this;
    }
    
    /**
     * 复选框
     */
    public function checkbox($options = [], $enclosedByLabel = true)
    {
        $options['label'] = null;
        if(empty($options['class'])) $options['class'] = 'colored-primary';
        parent::checkbox($options, $enclosedByLabel);
        
        if($enclosedByLabel){
            $this->parts['{label}'] = $this->model->getAttributeLabel($this->attribute);
            $this->template = "<div class='col-sm-10 col-sm-offset-2'><label style='margin:5px 0 0 0'>{input}<span class='text'></span> {label}</label>\n{hint}\n{error}</div>";
        }else{
            $this->template = "{label}\n<div class='col-sm-10'><label style='margin:5px 0 0 0'>{input}<span class='text'></span></label>\n{hint}\n{error}</div>";
        }
        
        return $this;
    }
    
    /**
     * 多个复选框
     */
    public function checkboxList($items, $options = [])
    {
        $options['label'] = null;
        $options['item'] = [$this,'_checkboxList'];
        $this->template = "{label}\n<div class='col-sm-10'><div style='margin:5px 0 0 0'>{input}</div>\n{hint}\n{error}</div>";
        return parent::checkboxList($items, $options);
    }
    
    /**
     * 格式化复选框元素
     */
    public function _checkboxList($index, $label, $name, $checked, $value)
    {
        $itemOptions = [ 'class' => 'colored-primary' ];
        return Html::checkbox($name, $checked, array_merge([
            'value' => $value,
            'label' => "<span class='text'></span>".Html::encode($label)." &nbsp; ",
        ], $itemOptions));
    }
    
    /**
     * 单选框
     */
    public function radio($options = [], $enclosedByLabel = true)
    {
        $options['label'] = null;
        if(empty($options['class'])) $options['class'] = 'colored-primary';
        parent::radio($options, $enclosedByLabel);
        
        if($enclosedByLabel){
            $this->parts['{label}'] = $this->model->getAttributeLabel($this->attribute);
            $this->template = "<div class='col-sm-10 col-sm-offset-2'><label style='margin:5px 0 0 0'>{input}<span class='text'></span> {label}</label>\n{hint}\n{error}</div>";
        }else{
            $this->template = "{label}\n<div class='col-sm-10'><label style='margin:5px 0 0 0'>{input}<span class='text'></span></label>\n{hint}\n{error}</div>";
        }
        
        return $this;
    }
    
    /**
     * 多个单选框
     */
    public function radioList($items, $options = [])
    {
        $options['label'] = null;
        $options['item'] = [$this,'_radioList'];
        $this->template = "{label}\n<div class='col-sm-10'><div style='margin:5px 0 0 0'>{input}</div>\n{hint}\n{error}</div>";
        return parent::radioList($items, $options);
    }
    
    /**
     * 格式化单选框元素
     */
    public function _radioList($index, $label, $name, $checked, $value)
    {
        $itemOptions = [ 'class' => 'colored-primary' ];
        return Html::radio($name, $checked, array_merge([
            'value' => $value,
            'label' => "<span class='text'></span>".Html::encode($label)." &nbsp; ",
        ], $itemOptions));
    }
    
    /**
     * 格式化文本
     */
    public function mask($mask='',$options = [])
    {
        $options['data-mask'] = $mask;
        $view = $this->form->getView();
        $view->registerJsFile('@assetUrl/js/inputmask/jasny-bootstrap.min.js',['depends' => \webadmin\WebAdminAsset::className()]);
        return $this->textInput($options);
    }
    
    /**
     * {@inheritDoc}
     * @see \yii\widgets\ActiveField::textInput()
     */
    public function textInput($options = [])
    {
        if(!is_array($this->model)){
            return parent::textInput($options);
        }
        
        $options = array_merge($this->inputOptions, $options);
        $name = (isset($options['name']) ? $options['name'] : $this->attribute);
        $value = (isset($options['value']) ? $options['value'] : (isset($this->model[$this->attribute]) ? $this->model[$this->attribute] : ''));
        
        if(isset($options['label'])){
            $this->labelOptions['label'] = $options['label'];
        }
        
        if(!isset($options['id'])){
            $options['id'] = $this->getInputId($options);
        }
        
        $this->parts['{input}'] = Html::textInput($name, $value, $options);
        
        return $this;
    }
    
    /**
     * {@inheritDoc}
     * @see \yii\widgets\ActiveField::dropDownList()
     */
    public function dropDownList($items, $options = [])
    {
        if(!is_array($this->model)){
            return parent::dropDownList($items, $options);
        }
        
        $options = array_merge($this->inputOptions, $options);
        $name = (isset($options['name']) ? $options['name'] : $this->attribute);
        $value = (isset($options['value']) ? $options['value'] : (isset($this->model[$this->attribute]) ? $this->model[$this->attribute] : ''));
        
        if(isset($options['label'])){
            $this->labelOptions['label'] = $options['label'];
        }
        
        if(!isset($options['id'])){
            $options['id'] = $this->getInputId($options);
        }
        
        $this->parts['{input}'] = Html::dropDownList($name, $value, $items, $options);
        
        return $this;
    }
    
    /**
     * {@inheritDoc}
     * @see \yii\widgets\ActiveField::label()
     */
    public function label($label = null, $options = [])
    {
        if(!is_array($this->model)){
            return parent::label($label, $options);
        }
        
        if ($label === false) {
            $this->parts['{label}'] = '';
            return $this;
        }
        
        $options = array_merge($this->labelOptions, $options);
        if ($label !== null) {
            $options['label'] = $label;
        }
        
        $label = (isset($options['label']) ? $options['label'] : $this->attribute);
        $for = (isset($options['for']) ? $options['for'] : $this->attribute);
        
        $this->parts['{label}'] = Html::label($label, $for, $options);
        
        return $this;
    }
    
    /**
     * {@inheritDoc}
     * @see \yii\widgets\ActiveField::error()
     */
    public function error($options = [])
    {
        if(!is_array($this->model)){
            return parent::error($options);
        }
        
        if ($options === false) {
            $this->parts['{error}'] = '';
            return $this;
        }
        $options = array_merge($this->errorOptions, $options);
        
        $tag = ArrayHelper::remove($options, 'tag', 'div');
        $encode = ArrayHelper::remove($options, 'encode', true);
        $error = ArrayHelper::remove($options, 'error', '');
        
        $this->parts['{error}'] = Html::tag($tag, $encode ? Html::encode($error) : $error, $options);
        return $this;
    }
    
    /**
     * {@inheritDoc}
     * @see \yii\widgets\ActiveField::hint()
     */
    public function hint($content, $options = [])
    {
        if(!is_array($this->model)){
            return parent::hint($content, $options);
        }
        
        if ($content === false) {
            $this->parts['{hint}'] = '';
            return $this;
        }
        
        $options = array_merge($this->hintOptions, $options);
        if ($content !== null) {
            $options['hint'] = $content;
        }
        
        $hint = isset($options['hint']) ? $options['hint'] : '';
        if (empty($hint)) {
            $this->parts['{hint}'] = '';
            return $this;
        }
        $tag = ArrayHelper::remove($options, 'tag', 'div');
        unset($options['hint']);

        $this->parts['{hint}'] = Html::tag($tag, $hint, $options);
        
        return $this;
    }
    
    /**
     * 获取表单元素的验证规则
     */
    public function getInputClientOptions()
    {
        $attribute = Html::getAttributeName($this->attribute);
        if (is_array($this->model) || !in_array($attribute, $this->model->activeAttributes(), true)) {
            return [];
        }
        
        $clientValidation = $this->isClientValidationEnabled(); // 是否开户客户端验证
        $ajaxValidation = $this->isAjaxValidationEnabled(); // 是否开启AJAX验证
        $oldOptions = $htmlOptions = $this->inputOptions;
        
        if($clientValidation || $ajaxValidation){
            foreach ($this->model->getActiveValidators($attribute) as $validator){
                if($validator->enableClientValidation) {
                    switch(basename(get_class($validator))){
                        case 'CompareValidator': // 校验字段一致性
                            if(!isset($htmlOptions['data-bv-identical'])){
                                $compareAttribute = $validator->compareAttribute===null ? $attribute.'_repeat' : $validator->compareAttribute;
                                if(in_array($validator->operator, ['==', '==='])){
                                    $htmlOptions['data-bv-identical'] = 'true';
                                    $htmlOptions['data-bv-identical-field'] = Html::getInputName($this->model,$compareAttribute);
                                }elseif(in_array($validator->operator, ['!=', '!=='])){
                                    $htmlOptions['data-bv-different'] = 'true';
                                    $htmlOptions['data-bv-different-field'] = Html::getInputName($this->model,$compareAttribute);
                                }
                            }
                            break;
                        case 'DateValidator': // 日期
                            $htmlOptions['data-bv-date'] = 'true'; // 或 dateISO
                            $htmlOptions['data-bv-date-format'] = 'YYYY-MM-DD';
                            break;
                        case 'EmailValidator': // 邮箱
                            $htmlOptions['data-bv-emailaddress'] = 'true';
                            break;
                        case 'NumberValidator': // 数字
                            if($validator->integerOnly){ // 整数
                                $htmlOptions['data-bv-digits'] = 'true';
                            }else{ // 数字类型
                                $htmlOptions['data-bv-numeric'] = 'true';
                            }
                            if($validator->min!==null) $htmlOptions['min'] = $validator->min; // 最小值
                            if($validator->max!==null) $htmlOptions['max'] = $validator->max; // 最大值
                            break;
                        case 'RequiredValidator': // 必填项
                            $htmlOptions['data-bv-notempty'] = 'true';
                            break;
                        case 'StringValidator': // 字符串长度
                            if($validator->min!==null) $htmlOptions['minlength'] = $validator->min; // 最小长度
                            if($validator->max!==null) $htmlOptions['maxlength'] = $validator->max; // 最大长度
                            if($validator->length!==null) $htmlOptions['minlength'] = $htmlOptions['maxlength'] = $validator->max; // 固定长度
                            break;
                        case 'UrlValidator': // 网址
                            $htmlOptions['data-bv-uri'] = 'true';
                            $htmlOptions['data-bv-uri-allowLocal'] = 'true'; // 允许本地地址
                            break;
                        case 'IpValidator': // IP类型
                            $htmlOptions['data-bv-ip'] = 'true';
                            break;
                        case 'BooleanValidator': // 枚举
                            $htmlOptions['data-bv-digits'] = 'true';
                            $htmlOptions['min'] = '0';
                            $htmlOptions['max'] = '1';
                            break;
                        case 'FileValidator': // 文件预留
                            break;
                        case 'ImageValidator': // 图片预留
                            break;
                        case 'RangeValidator': // 指定范围的值
                        case 'ExistValidator': // 检测是否已存在记录
                        case 'UniqueValidator': // 唯一值
                        case 'FilterValidator': // 过滤器
                        case 'EachValidator': // 校验数组
                        case 'RegularExpressionValidator': // 正则
                            if(!isset($htmlOptions['data-bv-remote'])){
                                $htmlOptions['data-bv-remote'] = 'true';
                                $htmlOptions['data-bv-remote-delay'] = 300;
                                $htmlOptions['data-bv-remote-url'] = Yii::$app->request->url;
                            }
                            break;                    
                    }
                    
                    // 自定义消息
                    if($validator->message && strpos($validator->message, '{')===false){
                        $params = [];
                        $params['{attribute}']=$this->model->getAttributeLabel($attribute);
                        $htmlOptions['data-bv-message'] = strtr($validator->message,$params);
                    }
                }
            }
            
            // 不进行AJAX校验
            if(!$ajaxValidation){
                unset($htmlOptions['data-bv-remote'],$htmlOptions['data-bv-remote-delay'],$htmlOptions['data-bv-remote-url']);
            }
            
            // 存在AJAX校验就去掉其他规则,避免重复校验
            if(!empty($htmlOptions['data-bv-remote'])){
                $oldOptions['data-bv-remote'] = 'true';
                $oldOptions['data-bv-remote-delay'] = 300;
                $oldOptions['data-bv-remote-url'] = Yii::$app->request->url;
                $htmlOptions = $oldOptions;
            }
        }
        
        // 只读属性
        if(Yii::$app->controller->action->id=='view'){
            $htmlOptions['disabled'] = 'disabled';
            $htmlOptions['readonly'] = 'readonly';
        }
        
        return $htmlOptions;
    }
    
    /**
     * 更新表单的attributes属性
     */
    protected function getClientOptions()
    {
    }
    
    /**
     * {@inheritDoc}
     * @see \yii\widgets\ActiveField::begin()
     */
    public function begin()
    {
        if ($this->form->enableClientScript) {
            $clientOptions = $this->getClientOptions();
            if (!empty($clientOptions)) {
                $this->form->attributes[] = $clientOptions;
            }
        }
        
        $inputID = $this->getInputId();
        $attribute = Html::getAttributeName($this->attribute);
        $options = $this->options;
        $class = isset($options['class']) ? (array) $options['class'] : [];
        $class[] = "field-$inputID";
        if (($this->form->enableClientValidation || $this->form->enableAjaxValidation) && (!is_array($this->model) && $this->model->isAttributeRequired($attribute))) {
            $class[] = $this->form->requiredCssClass;
        }
        $options['class'] = implode(' ', $class);
        if ($this->form->validationStateOn === ActiveForm::VALIDATION_STATE_ON_CONTAINER && !is_array($this->model)) {
            $this->addErrorClassIfNeeded($options);
        }
        $tag = ArrayHelper::remove($options, 'tag', 'div');
        
        return Html::beginTag($tag, $options);
    }
    
    /**
     * 获取元素ID
     */
    protected function getInputId($options=[])
    {
        if(isset($options['id'])) $this->_inputId = $options['id'];
        elseif(is_array($this->model)) $this->_inputId = (isset($options['name']) ? $options['name'] : $this->attribute);
        return $this->_inputId ? $this->_inputId : Html::getInputId($this->model, $this->attribute);
    }
}
