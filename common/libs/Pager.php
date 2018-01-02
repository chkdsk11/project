<?php
/**
 * Created by PhpStorm.
 * User: lw
 * Date: 2016/8/15
 * Time: 16:28
 *
 * 分页处理
 * 使用前提：已注入di服务中
 * 
 * 参数：
 *  $param['page'] = int ,//当前页(必须)
 *  $param['counts'] = int ,//数据总条数(必须)
 *  $param['psize'] = int ,//每页显示条数,默认为15(非必须)
 *  $param['url'] = string ,//跳转链接前缀(必须)
 *  $param['url_back'] = string ,//跳转链接后缀(必须)
 *  $param['home_page'] = string ,//首页链接(必须)
 *  $param['size'] = int ,//中间显示页数,默认为5(非必须)
 * 
 * 使用方法：
 *  $this->page->pageDetail($param);
 *    
 * 
 *
 */

namespace Shop\Libs;
use Shop\Libs\LibraryBase;

/**
 * Description of Pager
 *
 * @author Administrator
 */
class Pager extends LibraryBase
{

    //put your code here

    public $AbsolutePage = 1;
    public $PageCount = 1;
    public $Size = 5;
    public $Prefix = "?p=";
    public $Suffix = "";
    public $FirstText = "首页";
    public $LastText = "末页";
    public $PrevText = "上一页";
    public $NextText = "下一页";
    public $FirstPageLink = false;
    public $Psize = 15;
    public $SelectValue = [10,15,20,30,50];
    public $CountRow = 0;

    /**
     * 分页数部分
     * @param string $cssNormal 其他页数样式
     * @param string $cssSelected 当前页数样式
     * @return string
     */
    public function GetPageCodes($cssNormal = "normal", $cssSelected = "selected") {
        if ($cssNormal) {
            $cssNormal = ' class="' . $cssNormal . '"';
        }
        if ($cssSelected) {
            $cssSelected = ' class="' . $cssSelected . '"';
        }
        //计算出开始页码和结束页码
        $sNumber = 1;
        $eNumber = 1;
        if ($this->PageCount <= $this->Size) {
            $sNumber = 1;
			$eNumber = $this->PageCount;
        } else {
            $mNumber = $this->Size % 2 == 0 ? $this->Size / 2 : ($this->Size - 1) / 2 + 1;
            $sNumber = $this->AbsolutePage - $mNumber + 1;
            if ($sNumber < 1)
                $sNumber = 1;
            $eNumber = $sNumber + $this->Size - 1;
            if ($this->PageCount + 1 <= $eNumber) {
                $eNumber = $this->PageCount;
            }
        }
        $htmlString = '';
        for ($p = $sNumber; $p <= $eNumber; $p++) {
            if ($p == $this->AbsolutePage) {
                $htmlString.='<strong' . $cssSelected . '>' . $p . '</strong>'."\r\n";
            } else {
                $href = $this->Prefix . $p . $this->Suffix;
                if ($p == 1 && $this->FirstPageLink) {
                    $href = $this->FirstPageLink;
                }
                $htmlString .= '<a' . $cssNormal . ' href="' . $href . '">' . $p . '</a>'."\r\n";
            }
        }
        return $htmlString;
    }

    /**
     * 首页
     * @param string $cssClass 样式
     * @return string
     */
    public function GetFirst($cssClass = 'first') {
		if($this->AbsolutePage == 1){
			return '';
		}
        if ($cssClass) {
            $cssClass = ' class="' . $cssClass . '"';
        }
        $href = $this->Prefix . 1 . $this->Suffix;
        if ($this->PageCount > $this->Size && $this->FirstPageLink) {

            $href = $this->FirstPageLink;
        }
        return '<a' . $cssClass . ' href="' . $href . '">' . $this->FirstText . '</a>'."\r\n";
    }

    /**
     * 尾页
     * @param string $cssClass 样式
     * @return string
     */
    public function GetLast($cssClass = 'last') {
		if($this->AbsolutePage == $this->PageCount){
			return '';
		}
        if ($cssClass) {
            $cssClass = ' class="' . $cssClass . '"';
        }
        $href = $this->Prefix . $this->PageCount . $this->Suffix;
        return '<a' . $cssClass . ' href="' . $href . '">' . $this->LastText . '</a>'."\r\n";
    }

    /**
     * 下一页
     * @param string $cssClass 样式
     * @return string
     */
    public function GetNext($cssClass = 'next') {
        if ($cssClass) {
            $cssClass = ' class="' . $cssClass . '"';
        }
        if ($this->AbsolutePage < $this->PageCount) {
            $href = $this->Prefix . ($this->AbsolutePage + 1) . $this->Suffix;
            return '<a' . $cssClass . ' href="' . $href . '">' . $this->NextText . '</a>'."\r\n";
        }
    }

    /**
     * 上一页
     * @param string $cssClass 样式
     * @return string
     */
    public function GetPrev($cssClass = 'prev') {
        if ($cssClass) {
            $cssClass = ' class="' . $cssClass . '"';
        }
        if ($this->AbsolutePage > 1) {
            $href = $this->Prefix . ($this->AbsolutePage - 1) . $this->Suffix;
            if ($this->AbsolutePage == 2 && $this->FirstPageLink) {
                $href = $this->FirstPageLink;
            }
            return '<a' . $cssClass . ' href="' . $href . '">' . $this->PrevText . '</a>'."\r\n";
        }
    }

    /**
     * 当前页/总页数
     * @param string $cssClass 样式
     * @return string
     */
    public function GetTotal($cssClass = 'total') {

        if ($cssClass) {
            $cssClass = ' class="' . $cssClass . '"';
        }

        return '<span' . $cssClass . '>' . $this->AbsolutePage . '/' . $this->PageCount . '</span>'."\r\n";
    }

    /**
     * 页数跳转部分
     * @param string $cssClass 样式
     * @return string
     */
    public function GetTiao($cssClass = 'tiao') {
        if ($cssClass) {
            $cssClass = ' class="' . $cssClass . '"';
        }
        return '<input id="tiao" value=""><a '.$cssClass.' href="javascript:void(0)" onclick="tiaozhuan()">跳转</a>
            <script>function tiaozhuan(){var p = document.getElementById("tiao").value;if(!/^[0-9]*$/.test(p)){alert("页码只能输入数字");}else{if(parseInt(p)>0){if(parseInt(p) > '.$this->PageCount.'){
window.location.href="'. $this->FirstPageLink.'"
}else{window.location.href="'.$this->Prefix.'"+p+"'.$this->Suffix.'";}}else{alert("请输入要跳转页面");}}}</script>';
    }

    /**
     * 总页数部分
     * @param string $cssClass 样式
     * @return string
     */
    public function GetSpan($cssClass = 'span') {
        if ($cssClass) {
            $cssClass = ' class="' . $cssClass . '"';
        }
        return '<span '.$cssClass.'>共 '.$this->CountRow.' 条 '.$this->PageCount.' 页</span>';
    }

    /**
     * 分页部分拼接
     * @return string
     */
    public function ToString1() {
//         return $this->GetFirst() . $this->GetPrev() . $this->GetPageCodes()
//                 . $this->GetNext() . $this->GetLast() . $this->GetTotal();
        return '<div class="page">' . $this->GetFirst() . $this->GetPrev() . $this->GetPageCodes()
                . $this->GetNext() . $this->GetLast() . $this->GetSpan() . $this->GetTiao()
                . '</div><link rel="stylesheet" href="http://'.$this->config->domain->static.'/assets/css/page.css" />';
    }

    /**
     * 含每页选择条数分页部分拼接
     * @param array $selectValue  可选条数值
     * @return string
     */
    public function ToString2($selectValue)
    {
        return '<div class="page">' . $this->selectPageSize($selectValue) . $this->GetFirst() . $this->GetPrev() . $this->GetPageCodes()
                . $this->GetNext() . $this->GetLast() . $this->GetSpan() . $this->GetTiao()
                . '</div><link rel="stylesheet" href="http://'.$this->config->domain->static.'/assets/css/page.css" />';
    }

    /**
     * 每页显示条数选择
     * @param array $value 可选条数值
     * @return string
     */
    public function selectPageSize($value = [])
    {
        if (!$value) {
            $value = $this->SelectValue;
        }
        $select = '<select id="selectPage" onchange= "selectChange()" style="height: 34px;margin: 0 10px;">';
        foreach ($value as $item) {
            $select .= '<option value="'.$item.'" ';
            $select .= ($item == $this->Psize) ? 'selected>'.$item.'</option>' : '>'.$item.'</option>';
        }
        $select .= '</select>';
        $url = $this->Prefix.$this->AbsolutePage.$this->Suffix."&psize=";
        return '<span style="margin-right: 50px;">每页显示：'.$select.'条</span>'
                . '<script>function selectChange(){var selVal = document.getElementById("selectPage").value;'
        . 'document.getElementById("psize").value = selVal;document.getElementById("submit").click();}</script>';
    }
    
    /**
     * 分页样式1
     * $data=array(
     *      ['page'] => int ,//当前页
     *      ['count'] => int ,//总页数
     *      ['url'] => string ,//跳转链接前缀
     *      ['url_back'] => string ,//跳转链接前缀
     *      ['home_page'] => string ,//首页链接
     *      ['size'] => int ,//中间显示页数,默认为5
     *      ['isShow'] = blood ,//时候显示选择每页条数部分
     *      ['selectValue'] = array ,//每页选择条数选项值
     * )
     * return $page string 分页字符串
     */
    public function page1($data){        
        $pag = $this->page;
        $pag->AbsolutePage = $data['page']; //当前锁定页
        $pag->PageCount = $data['count'];   //总页数量
        $pag->Prefix=$data['url'];  //链接前缀
        $pag->Suffix=$data['url_back'];   //链接后缀
        $pag->FirstPageLink = $data['home_page']; //首页链接
        $pag->Size = $data['size'];   //尺寸
        $pag->CountRow = $data['countRow'];//数据总条数

        $pageCodeHtml = !$data['isShow'] ? $pag->ToString1() : $pag->ToString2($data['selectValue']); //获得分页过后的html
        
        return $pageCodeHtml; //将输出以下内容
    }
    
    /**
     *      分页详情
     *      $param['page'] = int ,//当前页(非必须)
     *      $param['counts'] = int ,//数据总条数(非必须)
     *      $param['psize'] = int ,//每页显示条数,默认显示15条(非必须)
     *      $param['url'] = string ,//跳转链接前缀(必须)
     *      $param['url_back'] = string ,//跳转链接后缀(非必须)
     *      $param['size'] = int ,//中间显示页数,默认为5(非必须)
     *      $param['isShow'] = blood ,//时候显示选择每页条数部分
     *      $param['selectValue'] = array ,//每页选择条数选项值
     *
     *      @return array('page' => '分页字符串','record' => '数据库起始查询条数','psize' => '每页显示条数');
     */
    public function pageDetail($param) {
        $param['page'] = ($param['page'] > 0)?(int)$param['page']:1;
        $param['counts'] = ($param['counts'] > 0)?(int)$param['counts']:0;
        //每页选择条数选项值
        $this->SelectValue = isset($param['selectValue'])?(int)$param['selectValue']:$this->SelectValue;
        //每页显示条数
        $this->Psize = isset($param['psize'])?(int)$param['psize']:$this->Psize;
        //总页数
        $pages = ceil($param['counts']/$this->Psize);
        //当前页数
        $page = ($param['page'] <= 0)?1:(($param['page'] > $pages)?$pages:$param['page']);
        //数据库起始查询条数
        $record = ($page - 1)*$this->Psize;
        //跳转url前缀
        $url = $param['url'];
        //中间间隔
        $size = (isset($param['size']) && $param['size'] > 0)?$param['size']:5;
        //组织分页信息
        $data['page'] = $page;//当前页
        $data['countRow'] = $param['counts'];//数据总条数
        $data['count'] = $pages;//总页数
        $data['url'] = $url;//跳转链接前缀
        $data['url_back'] = isset($param['url_back'])?$param['url_back']:'';//跳转链接后缀
        $data['home_page'] = $url . 1;//首页链接
        $data['size'] = $size;//中间显示页数,默认为5
        $data['isShow'] = isset($param['isShow']) ? $param['isShow'] : false;
        $data['selectValue'] = isset($param['selectValue']) ? $param['selectValue'] : [];
        $arr['page'] = $this->page1($data);
        $arr['record'] = $record >= 0 ? $record : 0;
        $arr['psize'] = $this->Psize;
        return $arr;
    }

}



