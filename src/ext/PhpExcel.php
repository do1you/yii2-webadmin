<?php
/**
 * 读取和导出excel
 * @author 统一
 *
 */
namespace webadmin\ext;

use Yii;
class PhpExcel
{
    /**
     * 读取excel
     */
    public static function readfile($file = '', $sheet = 0, $columnCnt = 0, &$options = [])
    {
        try {
            if (empty($file) || !file_exists($file)) {
                $file = iconv('UTF-8', 'GBK', $file);
                if(empty($file) || !file_exists($file)) {
                    throw new \yii\web\HttpException(200,Yii::t('common','文件不存在!'));
                }
            }
            
            $objRead = \PhpOffice\PhpSpreadsheet\IOFactory::createReader('Xlsx');
            
            if (!$objRead->canRead($file)) {
                $objRead = \PhpOffice\PhpSpreadsheet\IOFactory::createReader('Xls');
                
                if (!$objRead->canRead($file)) {
                    throw new \yii\web\HttpException(200,Yii::t('common','只支持导入Excel文件！'));
                }
            }
            
            /* 如果不需要获取特殊操作，则只读内容，可以大幅度提升读取Excel效率 */
            empty($options) && $objRead->setReadDataOnly(true);
            
            $obj = $objRead->load($file);
            $currSheet = $obj->getSheet($sheet);
            
            if (isset($options['mergeCells'])) {
                /* 读取合并行列 */
                $options['mergeCells'] = $currSheet->getMergeCells();
            }
            
            if (0 == $columnCnt) {
                /* 取得最大的列号 */
                $columnH = $currSheet->getHighestColumn();
                /* 兼容原逻辑，循环时使用的是小于等于 */
                $columnCnt = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($columnH);
            }
            
            /* 获取总行数 */
            $rowCnt = $currSheet->getHighestRow();
            $data   = [];
            
            /* 读取内容 */
            for ($_row = 1; $_row <= $rowCnt; $_row++) {
                $isNull = true;
                
                for ($_column = 1; $_column <= $columnCnt; $_column++) {
                    $cellName = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($_column);
                    $cellId   = $cellName . $_row;
                    $cell     = $currSheet->getCell($cellId);
                    
                    if (isset($options['format'])) {
                        /* 获取格式 */
                        $format = $cell->getStyle()->getNumberFormat()->getFormatCode();
                        /* 记录格式 */
                        $options['format'][$_row][$cellName] = $format;
                    }
                    
                    if (isset($options['formula'])) {
                        /* 获取公式，公式均为=号开头数据 */
                        $formula = $currSheet->getCell($cellId)->getValue();
                        
                        if (0 === strpos($formula, '=')) {
                            $options['formula'][$cellName . $_row] = $formula;
                        }
                    }
                    
                    if (isset($format) && 'm/d/yyyy' == $format) {
                        /* 日期格式翻转处理 */
                        $cell->getStyle()->getNumberFormat()->setFormatCode('yyyy/mm/dd');
                    }
                    
                    $data[$_row][$cellName] = trim($currSheet->getCell($cellId)->getFormattedValue());
                    
                    if (!empty($data[$_row][$cellName])) {
                        $isNull = false;
                    }
                }
                
                /* 判断是否整行数据为空，是的话删除该行数据 */
                if ($isNull) {
                    unset($data[$_row]);
                }
            }
            
            return $data;
        } catch (\Exception $e) {
            throw $e;
        }
    }
    
    /**
     * 导出excel
     */
    public static function export($model, \yii\data\ActiveDataProvider $dataProvider, $titles = [], $filename = null, $options = [])
    {
        set_time_limit(0);
        ini_set('memory_limit', '-1');
        
        $modelClass = $dataProvider->query->modelClass;
        $model = $modelClass ? $modelClass::model() : null; // 数据模型
        $titles = $titles ? $titles : ($model ? $model->attributeLabels() : array_keys($dataProvider->query->one()->attributes)); // 标题
        $count = $dataProvider->query->count(); // 总记录数
        
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $row = 1;
        $totalRow = [];
        
        // 父标题
        if(isset($options['parent_titles'])){
            // 预留
        }
        
        // 标题
        if($titles){
            $index = 0;
            foreach($titles as $tkey=>$tval){
                $attribute = is_array($tval) ? (isset($tval['attribute']) ? $tval['attribute'] : null) : $tval;
                $attribute = $attribute&&is_string($attribute) ? $attribute : $tkey;
                $label = $model ? $model->getAttributeLabel($attribute) : $attribute;
                $let = \webadmin\ext\Helpfn::intToChr($index++);
                $sheet->setCellValueExplicit($let.$row, $label, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                //$sheet->getColumnDimension($let)->setWidth(max(strlen($label)*2,20,$sheet->getColumnDimension($let)->getWidth()));
                $sheet->getColumnDimension($let)->setAutoSize(true);
                //echo $let.$row."=>".$label."\r\n";
            }
            $row++;
        }
        
        // 数据，分批量查询数据
        foreach($dataProvider->query->batch() as $data)
        {
            foreach($data as $item){
                $index = 0;
                foreach($titles as $tkey=>$tval){
                    $attribute = is_array($tval) ? (isset($tval['attribute']) ? $tval['attribute'] : null) : $tval;
                    $attribute = $attribute&&is_string($attribute) ? $attribute : $tkey;
                    if(!is_string($tval) && is_callable($tval)){
                        $value = call_user_func($tval->bindTo($item), $item, $index, $row);
                    }elseif(!empty($tval['value'])) {
                        if(is_string($tval['value'])) {
                            $value = \yii\helpers\ArrayHelper::getValue($item, $tval['value']);
                        }elseif(is_callable($tval['value'])){
                            $value = call_user_func($tval['value']->bindTo($item), $item, $index, $row);
                        }else{
                            $value = $tval['value'];
                        }
                    }elseif($attribute !== null) {
                        $value = \yii\helpers\ArrayHelper::getValue($item, $attribute);
                    }elseif($tkey !== null) {
                        $value = \yii\helpers\ArrayHelper::getValue($item, $tkey);
                    }
                    
                    if(is_numeric($value) && (empty($options['skip_total']) || (is_array($options['skip_total']) && !in_array($attribute,$options['skip_total'])))){ // 汇总
                        if(!isset($totalRow[$attribute])) $totalRow[$attribute] = 0;
                        $totalRow[$attribute] += $value;
                    }
                    
                    $let = \webadmin\ext\Helpfn::intToChr($index++);
                    
                    if(preg_match("/^\d{8,50}$/",$value) || (preg_match("/^\d{2,50}$/",$value) && substr($value,0,1)=='0')){
                        $sheet->setCellValueExplicit($let.$row, $value, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING); // setCellValueExplicit
                    }else{
                        $sheet->setCellValue($let.$row, $value);
                    }
                    //echo $let.$row."=>".$value."\r\n";
                }
                $row++;
            }
        }
        
        // 汇总
        if($totalRow){
            $index = 0;
            foreach($titles as $tkey=>$tval){
                $attribute = is_array($tval) ? (isset($tval['attribute']) ? $tval['attribute'] : null) : $tval;
                $attribute = $attribute&&is_string($attribute) ? $attribute : $tkey;
                $let = \webadmin\ext\Helpfn::intToChr($index++);
                if(isset($totalRow[$attribute])){
                    $totalRow[$attribute] = round($totalRow[$attribute]*1000)/1000;
                    $sheet->setCellValueExplicit($let.$row, $totalRow[$attribute], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                    //echo $let.$row."=>".$totalRow[$attribute]."\r\n";
                }
            }
            $row++;
        }
        
        // 输出文件
        if(!$filename) $filename = date('YmdHis');
        else $filename = str_replace(array(":","-"),"_",$filename);
        if(preg_match("/cli/i", php_sapi_name()) || !empty($options['return'])){ // cli模式，异步导出EXCEL
            $savePath = Yii::getAlias((isset($options['save_path']) ? trim($options['save_path'],'/').'/' : '@runtime/excels/').$filename.".xlsx");
            $encode = stristr(PHP_OS, 'WIN') ? 'GBK' : 'UTF-8';
            $savePath = iconv('UTF-8', $encode, $savePath); // 转码适应不同操作系统的编码规则
            \yii\helpers\FileHelper::createDirectory(dirname($savePath));
        }else{ // 正常模式
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition:attachment;filename="'.$filename.'.xlsx"');
            header("Content-Transfer-Encoding: binary");
            header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
            header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
            header("Pragma: no-cache");
            $savePath = 'php://output';
        }
        ob_clean();
        ob_start();
        $objWriter = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
        $objWriter->save($savePath);
        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);
        ob_end_flush();
        if(!empty($options['return'])){
            return $savePath;
        }else{
            exit;
        }        
    }
}