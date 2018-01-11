<?php
/**
 * Created by PhpStorm.
 * User: 杨永坚
 * Date: 2016/9/1
 * Time: 9:33
 */

namespace Shop\Models;


class BaiyangCategoryEnum
{
    const TDK = array(
        '妈妈专区' => array(
            '1' => array(
                'title' => '母婴商城_母婴用品店_钙片_待产包-{{companyName}}',
                'keyword' => '母婴商城,母婴用品店,母婴用品',
                'description' => '母婴商城，{{companyName}}母婴用品频道提供各类母婴用品在线购买，钙片，孕妇营养，待产包，孕前产后用品等。母婴用品价格实惠，正品保障，无理由退换货，要健康，上{{companyName}}。'
            ),
            '2' => array(
                'title' => '孕妇xx_最新孕妇xx价格-{{companyName}}',
                'keyword' => '孕妇xx，孕妇xx价格',
                'description' => '孕妇xx，{{companyName}}提供孕妇xx在线购买，孕妇xx价格，孕妇xx图片，评价。正品保障，无理由退换货，要健康，上{{companyName}}。'
            ),
            '3' => array(
                'title' => '孕妇xx名_最新孕妇xx名价格_孕妇xx名哪个牌子好-{{companyName}}',
                'keyword' => '孕妇xx名，孕妇xx名价格',
                'description' => '孕妇xx名，{{companyName}}提供大牌孕妇xx名在线购买，孕妇xx名价格，孕妇xx名那个牌子好，图片，评价,。正品保障，无理由退换货，优化促销活动多。'
            )
        ),
        '宝宝专区' => array(
            '1' => array(
                'title' => '婴儿用品店_婴儿用品批发价_大牌婴儿奶粉_辅食_玩具-{{companyName}}',
                'keyword' => '婴儿用品,婴儿用品店,婴儿用品价格',
                'description' => '婴儿用品店，{{companyName}}婴儿用品类目提供婴儿奶粉，辅食，纸尿裤，玩具等婴儿用品批发价出售。正品保障，无理由退换货，{{companyName}}母婴用品店，百万妈妈的选择。要健康，上{{companyName}}。'
            ),
            '2' => array(
                'title' => '宝宝xx_最新婴儿xx价格-{{companyName}}',
                'keyword' => '宝宝xx，xx价格',
                'description' => '宝宝xx，{{companyName}}提供宝宝xx在线购买，宝宝xx价格，宝宝xx图片，评价。正品保障，无理由退换货，要健康，上{{companyName}}。'
            ),
            '3' => array(
                'title' => '宝宝xx_最新婴儿xx价格_宝宝xx哪个牌子好-{{companyName}}',
                'keyword' => '宝宝xx，xx价格',
                'description' => '宝宝xx，{{companyName}}提供大牌宝宝xx在线购买，宝宝xx价格，宝宝xx那个牌子好，图片，评价,。正品保障，无理由退换货，优化促销活动多。要健康，上{{companyName}}。'
            )
        ),
        '副食特产' => array(
            '1' => array(
                'title' => '青岛特产_好吃的青岛特产_青岛特产礼品_{{companyName}}',
                'keyword' => '青岛特产，青岛特产礼品',
                'description' => '青岛特产在线，{{companyName}}提供青岛特产在线购买，青岛啤酒，馒头，青岛樱桃，蓝莓，海鲜，小零食等青岛土特产分批供应。要健康的青岛特产礼品就上{{companyName}}。'
            ),
            '2' => array(
                'title' => '青岛xx_最新青岛xx价格-{{companyName}}',
                'keyword' => '青岛xx,最新青岛xx价格',
                'description' => '{{companyName}}提供青岛xx在线购买,最新青岛xx价格。{{companyName}}，本地企业，值得信赖。要健康，到{{companyName}}。'
            ),
            '3' => array(
                'title' => '青岛xx名_最新青岛xx名价格-{{companyName}}',
                'keyword' => '青岛xx名,最新青岛xx名价格',
                'description' => '{{companyName}}提供青岛xx名在线购买,最新青岛xx名价格。{{companyName}}，本地企业，值得信赖。要健康，到{{companyName}}。'
            )
        ),
        '健康生活' => array(
            '1' => array(
                'title' => '生活用品商城_家庭健康生活用品_—{{companyName}}',
                'keyword' => '生活用品',
                'description' => '生活用品商城，{{companyName}}健康生活频道为您提供美妆护肤品、洗刷用品，洗衣液，口腔护理等各种生活用品在线购买，网上查找护理药品，了解护理知识，就到{{companyName}}网上商城。'
            ),
            '2' => array(
                'title' => '【xx】_xx产品_xx用品价格_{{companyName}}',
                'keyword' => 'xx药品',
                'description' => '{{companyName}}在线销售xx,为您提供xx的最新价格、xx的副作用以及使用注意事项等内容，网上购买xx就上{{companyName}}。'
            ),
            '3' => array(
                'title' => '【xx】_xx产品大全_品牌xx价格_{{companyName}}',
                'keyword' => 'xx价格',
                'description' => '{{companyName}}在线销售品牌xx,为您提供xx药品的最新价格、xx药品的副作用以及使用注意事项等内容，网上购买xx药品就上{{companyName}}。'
            )
        ),
        '医疗器械' => array(
            '1' => array(
                'title' => '医疗器械网购_家用医疗器械价格_{{companyName}}',
                'keyword' => '医疗器械网购，医疗器械价格',
                'description' => '{{companyName}}网医疗器械栏目为您提供正规合法, ,价格实惠的家用医疗器械；哪里买医疗器械好？了解医疗器械的价格就到{{companyName}}网上药店。'
            ),
            '2' => array(
                'title' => 'xx网购_xx查询_xx价格-{{companyName}}',
                'keyword' => 'xx网购，xx查询',
                'description' => '{{companyName}}在线网购xx,为您提供各类xx的最新价格、xx使用注意事项等内容，网上购买xx就上{{companyName}}网上药店。'
            ),
            '3' => array(
                'title' => 'xx价格_xx使用说明-{{companyName}}',
                'keyword' => 'xx价格，xx使用说明',
                'description' => '{{companyName}}在线销售xx,为您提供xx的最新价格、使用方法以及注意事项等内容，了解更多xx的相关信息就到{{companyName}}网上药店。'
            )
        ),
        '中西成药' => array(
            '1' => array(
                'title' => '医药网_中国药品价格查询_药品零售网-{{companyName}}网上药店',
                'keyword' => '医药网,药品价格,药品购买',
                'description' => '医药网，{{companyName}}药品在线购买，男科、妇科、皮肤科、风湿骨科、精神科、五官科、感冒发烧、维生素钙剂等药品零售价格实惠，中国药品价格查询，了解最新药品价格，就到{{companyName}}医药网。'
            ),
            '2' => array(
                'title' => 'xx用药_xx药品查询_xx药品价格-{{companyName}}',
                'keyword' => 'xx用药，xx药品，xx药品价格',
                'description' => 'xx药品哪里买?{{companyName}}在线销售xx药品,为您提供xx药品最新价格，xx药品的说明书以及使用注意事项等内容，更多xx药品的相关信息尽在{{companyName}}网上药店。'
            ),
            '3' => array(
                'title' => 'xx用药_xx吃什么药好_xx药品价格-{{companyName}}',
                'keyword' => 'xx用药,xx药品价格, xx吃什么药',
                'description' => '{{companyName}}网上药店在线销售xx药品,为您提供xx用药的最新价格、国药准字查询。在线咨询执业药师xx吃什么药好，xx药品的说明书以及使用注意事项等内容，'
            )
        ),
        '保健养生' => array(
            '1' => array(
                'title' => '保健品网上商城_进口保健品排行榜—{{companyName}}网上药店',
                'keyword' => '保健品,进口保健品',
                'description' => '{{companyName}}保健品健频道为您提供男士保健、女性保养、营养补充剂、进口品牌等各种保健品，网上热销保健养生品排行榜，了解吃保健品的禁忌，就到{{companyName}}保健品网上商城。'
            ),
            '2' => array(
                'title' => 'xx_品牌xx_xx保健品价格-{{companyName}}',
                'keyword' => 'xx保健品，xx价格',
                'description' => '{{companyName}}在线销售xx药品,为您提供xx保健品的最新价格、xx的功效作用以及使用注意事项等内容，网上购买xx保健品就上{{companyName}}。'
            ),
            '3' => array(
                'title' => '【xx】xx保健品品牌_xx保健品价格-{{companyName}}',
                'keyword' => 'xx，xx保健品',
                'description' => '{{companyName}}网上药店在线销售xx保健品,并提供品牌xx保健品的最新价格多少钱,图片,使用说明书等内容, 网上购买xx保健品就上{{companyName}}。'
            )
        ),
        '养生中药' => array(
            '1' => array(
                'title' => '中药饮片大全_中药饮品价格-{{companyName}}',
                'keyword' => '中药饮片大全，中药饮片价格',
                'description' => '{{companyName}}为您提供中药饮片大全、中药饮片价格、中药饮片使用过程中的注意事项等内容，网上购买中药饮片就上{{companyName}}。'
            ),
            '2' => array(
                'title' => 'xx饮片_xx饮品价格_中药饮片—{{companyName}}',
                'keyword' => 'xx药品',
                'description' => '{{companyName}}在线销售xx药品,为您提供xx药品的最新价格、xx的效果与作用等内容，网上购买xx药品就上{{companyName}}。'
            ),
            '3' => array(
                'title' => 'xx饮品_xx饮品价格_中药饮品—{{companyName}}',
                'keyword' => 'xx，xx饮品价格',
                'description' => '{{companyName}}网上药店提供xx饮品品在线销售,为您提供艾滋的最新价格、功效与作用怎么样等内容，网上购买xx中药饮品就上{{companyName}}网上药店。'
            )
        ),
        '成人用品' => array(
            '1' => array(
                'title' => '成人用品_成人性用品商城_成人用品价格 - {{companyName}}',
                'keyword' => '计生用品分类，计生用品价格',
                'description' => '{{companyName}}为您提供各类成人用品在线购买，避孕套、男用器具、女用器具、情趣内衣、验孕检测等各种成人性用品，隐秘包装，了解各类成人用品价格和相关信息，就到{{companyName}}网上药店。'
            ),
            '2' => array(
                'title' => 'xx品牌_xx价格 - {{companyName}}',
                'keyword' => '避孕套价格，避孕套分类',
                'description' => '{{companyName}}在线销售xx,为您提供xx的最新价格、使用方法以及注意事项等内容，私密包装，了解更多xx的相关信息就到{{companyName}}网上药店。'
            ),
            '3' => array(
                'title' => 'xx价格_xx网购_xx-{{companyName}}',
                'keyword' => 'xx,xx价格',
                'description' => '{{companyName}}在线销售xx,并提供xx品牌,最新价格多少钱,图片,使用说明书等内容,买xx xx上国家药监局认证的专业药房网-{{companyName}}。'
            )
        ),
        '儿童用品' => array(
            '1' => array(
                'title' => '儿童商城_儿童用品网_钙片_玩具-{{companyName}}',
                'keyword' => '儿童商城,儿童用品店,儿童用品',
                'description' => '儿童商城，{{companyName}}儿童用品频道提供各类儿童用品在线购买，钙片，儿童营养，玩具等。儿童用品价格实惠，正品保障，无理由退换货，要健康，上{{companyName}}。'
            ),
            '2' => array(
                'title' => '儿童xx_最新小孩xx价格-{{companyName}}',
                'keyword' => '儿童xx，小孩xx价格',
                'description' => '儿童xx，{{companyName}}提供儿童xx在线购买，小孩xx价格，儿童xx图片，评价。正品保障，无理由退换货，要健康，上{{companyName}}。'
            ),
            '3' => array(
                'title' => '儿童xx_最新儿童xx价格_小孩xx哪个牌子好-{{companyName}}',
                'keyword' => '儿童xx，小孩xx价格',
                'description' => '儿童xx，{{companyName}}提供大牌儿童xx在线购买，小孩xx价格，儿童xx那个牌子好，图片，评价,。正品保障，无理由退换货，优化促销活动多。'
            )
        ),
        '默认' => array(
            '1' => array(
                'title' => 'xx_xx价格_xx网上商城-{{companyName}}',
                'keyword' => 'xx, xx价格, xx在线购买',
                'description' => '{{companyName}}网上药店在线销售xx, 并提供品牌xx的最新价格多少钱,图片,使用说明书等. {{companyName}}xx频道，价格实惠，正品保障，要健康，上{{companyName}}。'
            ),
            '2' => array(
                'title' => 'xx_xx价格_xx网上商城-{{companyName}}',
                'keyword' => 'xx, xx价格, xx在线购买',
                'description' => '{{companyName}}网上药店在线销售xx, 并提供品牌xx的最新价格多少钱,图片,使用说明书等. {{companyName}}xx频道，价格实惠，正品保障，要健康，上{{companyName}}。'
            ),
            '3' => array(
                'title' => 'xx_xx价格_xx网上商城-{{companyName}}',
                'keyword' => 'xx, xx价格, xx在线购买',
                'description' => '{{companyName}}网上药店在线销售xx, 并提供品牌xx的最新价格多少钱,图片,使用说明书等. {{companyName}}xx频道，价格实惠，正品保障，要健康，上{{companyName}}。'
            )
        ),
    );
}