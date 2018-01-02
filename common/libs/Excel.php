<?php
/**
 * Created by PhpStorm.
 * User: WuJunhua
 * Date: 2016/8/10
 * Time: 15:03
 *
 * 导入导出excel
 * 使用前提：已经在di中注入该Library
 * 使用方法：
 *          $this->excel->exportExcel(excel表的一维标题数组,导出的二维数据数组,导出文件名,excel里的table名,导出excel文件类型);  //eg:$this->excel->exportExcel($titleArr,$data,$fileName,$tableName,'xls');
 *          $this->excel->importExcel(文件路径,文件后缀); //eg:$this->excel->importExcel($filePath,'xls')
 * 返回结果：导出的结果将直接在浏览器进行下载，导入的结果把excel表结构变换成数组:array(array('a','b'),array('c','d'))
 *
 */

namespace Shop\Libs;
require APP_PATH."/vendor/autoload.php";
use Shop\Libs\LibraryBase;

class Excel extends LibraryBase
{
    /**
     * @var $_instance 对象
     */
    public static $_instance;

    public static $objExcel;

    //表格字母坐标
    public static $excelArray = array(
        '1'  => 'A',
        '2'  => 'B',
        '3'  => 'C',
        '4'  => 'D',
        '5'  => 'E',
        '6'  => 'F',
        '7'  => 'G',
        '8'  => 'H',
        '9'  => 'I',
        '10' => 'J',
        '11' => 'K',
        '12' => 'L'
    );

    //文件后缀
    public static $fileExtension = array(
        'xls'  => 'xls',
        'xlsx' => 'xlsx'
    );

    //文件类型
    public static $writerType = array(
        'xls'  => 'Excel5',
        'xlsx' => 'Excel2007'
    );

    //应用程序
    public static $application = array(
        'xls'  => 'vnd.ms-excel;',
        'xlsx' => 'vnd.openxmlformats-officedocument.spreadsheetml.sheet;'
    );

    //是否存在图片
    public static $_exist_photo = false;

    public function __construct(){
        self::$objExcel = new \PHPExcel();
    }

    /**
     * @desc 用双冒号::操作符访问静态方法获取实例
     * @author WuJunhua
     * @return Excel
     */
    public static function getInstance()
    {
        if(!self::$_instance instanceof self){
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * @desc 生成excel表
     * @param array $headArray 设置标题栏
     * @param array $conArray 订单内容
     * @param string $fileName excel表格文件名
     * @param string $tableName excel表格的sheet名
     * @param string $fileExtension excel表的文件后缀(xls或xlsx)
     * @param boolean $isZip 是否压缩包
     * @author WuJunhua
     */
    public function exportExcel($headArray,$conArray,$fileName,$tableName,$fileExtension = 'xls',$isZip = false)
    {
        if(empty($headArray) || empty($conArray)){
            return false;
        }
        if(empty($fileName)){
            $fileName = time();
        }
        if(empty($tableName)){
            $tableName = 'test';
        }
        self::$objExcel = new \PHPExcel();
        //设置文件属性
        self::$objExcel->getProperties()->setCreator("Maarten Balliauw")
            ->setLastModifiedBy("Maarten Balliauw")
            ->setTitle("Office 2007 XLSX Test Document")
            ->setSubject("Office 2007 XLSX Test Document")
            ->setDescription("Test document for Office 2007 XLSX, generated using PHP classes.")
            ->setKeywords("office 2007 openxml php")
            ->setCategory("Test result file");
        //设置打开文档默认为第一张表
        self::$objExcel->setActiveSheetIndex(0);
        //生成头部标题
        $this->createExcelHeader($headArray);
        //写入excel表格内容
        $this->createExcelContent($conArray);
        // 给当前活动的表设置名称
        self::$objExcel->getActiveSheet()->setTitle($tableName);

        //$fileName = urlencode(utf8_encode($fileName));
        if($fileExtension == self::$fileExtension['xlsx']){
            //导出xlsx文件
            $application = self::$application['xlsx'];
            $writerType = self::$writerType['xlsx'];
        }else{
            //导出xls文件
            $application = self::$application['xls'];
            $writerType = self::$writerType['xls'];
        }

        if($isZip){
            //把生成的多个Excel文件压缩成zip
            //$fileName = $fileName.date("YmdHis");
            $objWriter = \PHPExcel_IOFactory::createWriter(self::$objExcel, $writerType);
            $objWriter->save($this->config->application->uploadDir.'excel/'.$fileName.'.'.$fileExtension);
            return $this->config->application->uploadDir.'excel/'.$fileName.'.'.$fileExtension;
        }else{
            //下载单个Excel
            // Redirect output to a client’s web browser (Excel2007)
            ob_end_clean();//清除缓冲区,避免乱码
            //iconv("utf-8", "gb2312", $filename);  //文件改编码

            header("Content-Type: application/".$application." charset=UTF-8");
            header('Content-Disposition: attachment;filename="'.$fileName.'.'.$fileExtension.'"');
            header('Cache-Control: max-age=0');
            // If you're serving to IE 9, then the following may be needed
            header('Cache-Control: max-age=1');

            // If you're serving to IE over SSL, then the following may be needed
            header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
            header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
            header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
            header ('Pragma: public'); // HTTP/1.0

            $objWriter = \PHPExcel_IOFactory::createWriter(self::$objExcel, $writerType);
            $objWriter->save($this->config->application->uploadDir.'excel/'.$fileName.'.'.$fileExtension);
            $objWriter->save('php://output');
        }
    }

    /**
     * @desc 生成excel标题栏
     * @param array $paramArray 设置标题栏
     * @author WuJunhua
     */
    public function createExcelHeader($paramArray = array())
    {
        if(empty($paramArray)){
            return false;
        }
        //设置头部
        $strExcel = self::$objExcel->setActiveSheetIndex(0);
        $i = 0;
        foreach($paramArray as $key => $param){
            if($param == '图片'){
                self::$_exist_photo=true;
                $i++;
                break;
            }
        }

        if(self::$_exist_photo){
            $strExcel = $strExcel->setCellValue('A1', '图片');
        }

        foreach($paramArray as $key => $param)
        {
            if($param == '图片'){
                $mark=1;
                continue;
            }
            $fieldName = $this->createCoords($i);
            $strExcel = $strExcel->setCellValue($fieldName.'1', $param);
            $i++;
        }
        return $strExcel;
    }

    /**
     * @desc excel写入内容
     * @param array $contentArray 数据信息
     * @author WuJunhua
     */
    public function createExcelContent($contentArray = array())
    {

        if(empty($contentArray)){
            return false;
        }
        $newExcel = self::$objExcel;  //实例化一个PHPExcel对象
        $newExcel->getDefaultStyle()->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
        $newExcel->getDefaultStyle()->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
        $strExcel = $newExcel->setActiveSheetIndex(0);   //添加数据
        $activeSheet = $newExcel->getActiveSheet();	//获取当前活动的PHPExcel表格
        //处理单条记录的
        $keyV = 0;
        $single = 2;
        $offsetY = 0;  //纵坐标每次位移记录

        foreach($contentArray as $key => $content)
        {
            $num = $key + 2;
            $k = 0;
            $con = isset($content['picture'])?$content['picture']:null;
            if(is_array($content)){
                if(self::$_exist_photo){
                    $k++;
                    if(!file_exists($con)){
                        $strExcel = $strExcel->setCellValue(self::$excelArray[1].(2 + $offsetY),'');
                    }else{
                        $objDrawing = new \PHPExcel_Worksheet_Drawing();
                        $objDrawing->setName('ZealImg');
                        $objDrawing->setDescription('Image inserted by Zeal');
                        $objDrawing->setPath($con);
                        $objDrawing->setHeight(80);
                        $objDrawing->setWidth(80);
                        $objDrawing->setCoordinates(self::$excelArray[1].(2 + $offsetY));
                        $objDrawing->setOffsetX(10);
                        $objDrawing->setOffsetY(10);
                        $objDrawing->getShadow()->setVisible(true);
                        $objDrawing->getShadow()->setDirection(45);
                        $objDrawing->setWorksheet($activeSheet);
                    }
                }

                foreach($content as $kk => $con){
                    if($kk === 'picture'){
                        continue;
                    }
                    $strExcel = $strExcel->setCellValue($this->createCoords($k).$num,$con."\t");
                    $k++;
                }
                $offsetY++;
            }else{
                $strExcel = $strExcel->setCellValue($this->createCoords($keyV).$single,$content."\t");
                $keyV++;
            }
        }
        return $strExcel;
    }

    /**
     * @desc 生成excel表格坐标
     * @param int $num
     * @return $fieldName 单元格坐标
     * @author WuJunhua
     */
    public function createCoords($num)
    {
        $fieldArray = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z');
        if($num < 26){
            $fieldName = $fieldArray[$num];
        }else{
            $re = intval($num / 26);
            $remaind = $num % 26;
            $fieldName = $fieldArray[$re-1].$fieldArray[$remaind];
        }
        return $fieldName;
    }

    /**
     * @desc 把内容填充到指定的模
     * @param array $contentArr 数据内容
     * @param string $fileName 文件名称
     * @param string $filePath 模板文件路径
     * @param boolean $isBool
     * @author WuJunhua
     */
    public function fillContent($contentArr,$fileName,$filePath,$isBool = false)
    {
        $objReader = \PHPExcel_IOFactory::createReader('Excel5');


        $objPHPExcel = $objReader->load($filePath);
        $strExcel = $objPHPExcel->getActiveSheet();
        $fileName = $fileName.date("YmdHis");
        //判断是否下拉框
        if($isBool){
            $count = count($contentArr)+100;
            for($i=2;$i<$count;++$i)
            {
                $objValidation = $strExcel->getCell("B".$i)->getDataValidation(); //这一句为要设置数据有效性的单元格
                $objValidation -> setType(\PHPExcel_Cell_DataValidation::TYPE_LIST)
                    -> setErrorStyle(\PHPExcel_Cell_DataValidation::STYLE_INFORMATION)
                    -> setAllowBlank(true)
                    -> setShowInputMessage(true)
                    -> setShowErrorMessage(true)
                    -> setShowDropDown(true)
                    -> setFormula1('"Part Shipment,Full Shipment"');

                $objValidation = $strExcel->getCell("C".$i)->getDataValidation(); //这一句为要设置数据有效性的单元格
                $objValidation -> setType(\PHPExcel_Cell_DataValidation::TYPE_LIST)
                    -> setErrorStyle(\PHPExcel_Cell_DataValidation::STYLE_INFORMATION)
                    -> setAllowBlank(true)
                    -> setShowInputMessage(true)
                    -> setShowErrorMessage(true)
                    -> setShowDropDown(true)
                    -> setFormula1('"EMS,ePacket,DHL Global Mail,DHL,UPS Express Saver,UPS Expedited,FedEx,TNT,SF Express,China Post Air Mail,China Post Air Parcel,Hongkong Post Air Mail,Hongkong Post Air Parcel"');
            }
        }
        $strExcel->fromArray($contentArr, NULL, 'A2');
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');

        $objWriter->save($this->config->application->uploadDir.'excel/'.$fileName.'.xls');
        unset($objWriter);
        return $this->config->application->uploadDir.'excel/'.$fileName.'.xls';
    }

    /**
     * @desc 获取excel表格内容
     * @param string $filePath	文件路径
     * @author WuJunhua
     * @return array excel文件数据
     */
    public function importExcel($filePath,$fileExtension = 'xls')
    {
        if(empty($filePath) || empty($fileExtension) || $fileExtension != pathinfo($filePath)['extension']){
            return false;
        }
        $writerType = self::$writerType['xls'];
        if($fileExtension == 'xlsx'){
            $writerType = self::$writerType['xlsx'];
        }

        /*创建对象,针对Excel2003*/
        $objExcel = \PHPExcel_IOFactory::createReader($writerType);
        $objExcel->setReadDataOnly(true);
        /*加载对象路径*/
        $objPHPExcel = $objExcel->load($filePath);
        /*获取工作表*/
        $objWorksheet = $objPHPExcel->getActiveSheet();
        /*得到总行数*/
        $highestRow = $objWorksheet->getHighestRow();
        /*得到总列数*/
        $highestColumn = $objWorksheet->getHighestColumn();
        $highestColumnIndex = \PHPExcel_Cell::columnIndexFromString($highestColumn);
        $excelData = array();
        for($row = 2;$row <= $highestRow; ++$row){
            $rowValue = [];
            for($col = 0;$col <= $highestColumnIndex; ++$col){
                $rowValue[] = trim($objWorksheet->getCellByColumnAndRow($col,$row)->getValue());
            }
            if (!array_filter($rowValue)) {
                continue;
            } else {
                $excelData[$row] = $rowValue;
            }
        }
        return $excelData;
    }

    /**
     * @desc 获取excel商品表格内容
     * @param string $filePath	文件路径
     * @author luoyiting
     * @return array excel文件数据
     */
    public function importGoodsExcel($filePath,$fileExtension = 'xls')
    {
        if(empty($filePath) || empty($fileExtension) || $fileExtension != pathinfo($filePath)['extension']){
            return false;
        }
        $writerType = self::$writerType['xls'];
        if($fileExtension == 'xlsx'){
            $writerType = self::$writerType['xlsx'];
        }

        /*创建对象,针对Excel2003*/
        $objExcel = \PHPExcel_IOFactory::createReader($writerType);
        $objExcel->setReadDataOnly(true);
        /*加载对象路径*/
        $objPHPExcel = $objExcel->load($filePath);
        /*获取工作表*/
        $objWorksheet = $objPHPExcel->getActiveSheet();
        /*得到总行数*/
        $highestRow = $objWorksheet->getHighestRow();
        /*得到总列数*/
        $highestColumn = $objWorksheet->getHighestColumn();
        $highestColumnIndex = \PHPExcel_Cell::columnIndexFromString($highestColumn);
        $excelTitle = array();
        for($col = 0;$col < $highestColumnIndex; ++$col){
            $excelTitle[] = $objWorksheet->getCellByColumnAndRow($col,1)->getValue();
        }
        $excelData = array();
        for($row = 2;$row <= $highestRow; ++$row){
            for($col = 0;$col < $highestColumnIndex; ++$col){
                $excelData[$row][$excelTitle[$col]] = $objWorksheet->getCellByColumnAndRow($col,$row)->getValue();
            }
        }
        return $excelData;
    }
    
}