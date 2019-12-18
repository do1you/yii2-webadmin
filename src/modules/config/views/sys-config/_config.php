<?php
if(in_array($item->config_type,['selectmult','selectajaxmult','ddmulti'])){
    $item->value = $item->value ? explode(',',$item->value) : [];
}
$field = $form->field($item, 'value')->label($item['label_name']);
!empty($item['label_note']) && $field->hint($item['label_note']); 
$arr = Yii::$app->controller->action->id=='config' ? ['readonly'=>'readonly', 'disabled'=>'disabled'] : [];
switch($item['config_type'])
{
    case 'textarea': //  多行文本框
        echo $field->textarea(['rows' => 3, 'id' => "SysConfig_{$k}", 'name' => "SysConfig[{$k}]"]+$arr);
        break;
    case 'mask': //  格式化文本
        echo $field->mask($item['config_params'], ['id' => "SysConfig_{$k}", 'name' => "SysConfig[{$k}]"]+$arr);
        break;
    case 'checkbox': // 复选框
        echo $field->checkbox(['id' => "SysConfig_{$k}", 'name' => "SysConfig[{$k}]"]+$arr);
        break;
    case 'dd': // 数据字典
        echo $field->dropDownList(\webadmin\modules\config\models\SysLdItem::dd($item['config_params']), ['id' => "SysConfig_{$k}", 'name' => "SysConfig[{$k}]", 'prompt'=>'请选择']+$arr);
        break;
    case 'ddmulti': // 数据字典多选
        echo $field->duallistbox(\webadmin\modules\config\models\SysLdItem::dd($item['config_params']), ['id' => "SysConfig_{$k}", 'name' => "SysConfig[{$k}]"]+$arr);
        break;
    case 'select': // 下拉框
        echo $field->dropDownList($item['v_config_params'], ['id' => "SysConfig_{$k}", 'name' => "SysConfig[{$k}]", 'prompt'=>'请选择']+$arr);
        break;
    case 'selectmult': // 下拉多选框
        echo $field->duallistbox($item['v_config_params'], ['id' => "SysConfig_{$k}", 'name' => "SysConfig[{$k}]"]+$arr);
        break;
    case 'selectajax': // 下拉异步
    case 'selectajaxmult': // 下拉异步多选框
        echo $item['v_config_ajax']
        ? $field->{$item['config_type']}($item['v_config_ajax'], ['id' => "SysConfig_{$k}", 'name' => "SysConfig[{$k}]"]+$arr)
        : $field->dropDownList([], ['id' => "SysConfig_{$k}", 'name' => "SysConfig[{$k}]", 'prompt'=>'请选择']+$arr);
        break;
    case 'date': // 日期
        echo $field->date(['id' => "SysConfig_{$k}", 'name' => "SysConfig[{$k}]"]+$arr);
        break;
    case 'time': // 时间
        echo $field->time(['id' => "SysConfig_{$k}", 'name' => "SysConfig[{$k}]"]+$arr);
        break;
    case 'datetime': // 日期时间
        echo $field->datetime(['id' => "SysConfig_{$k}", 'name' => "SysConfig[{$k}]"]+$arr);
        break;
    case 'daterange': // 日期范围
        echo $field->daterange(['id' => "SysConfig_{$k}", 'name' => "SysConfig[{$k}]"]+$arr);
        break;
    case 'datetimerange': // 日期时间范围
        echo $field->datetimerange(['id' => "SysConfig_{$k}", 'name' => "SysConfig[{$k}]"]+$arr);
        break;
    case 'text': // 文本框
    default: // 默认文本框
        echo $field->textInput(['maxlength' => true, 'id' => "SysConfig_{$k}", 'name' => "SysConfig[{$k}]"]+$arr);
        break;
}
