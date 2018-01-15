<?php
/**
 * Created by PhpStorm.
 * User: 吴俊华
 * Date: 2016/10/11 0011
 * Time: 下午 1:37
 */

namespace Shop\Models;

class HttpStatus
{
    /***** http状态码 *****/
    const SUCCESS                   = 200; //成功

    // 公共状态码(100001开始)
    const PARAM_ERROR               = 100001; //参数不完整或参数错误
    const VERIFY_FAILED             = 100002; //校验失败
    const SEND_FAILED               = 100003; //发送失败
    const PLATFORM_ERROR            = 100004; //平台标识不能为空
    const ILLEGAL_REQUEST           = 100005; //非法请求
    const NO_DATA                   = 100006; //暂无数据...
    const NOT_FOUND                 = 100007; //未找到
    const FAILED                    = 100008; //系统错误
    const SYSTEM_ERROR              = 100009; //系统参数有误
    const ADD_ERROR                 = 100010; //添加失败，请稍后再试
    const EDIT_ERROR                = 100011; //修改失败，请稍后再试
    const DELETE_ERROR              = 100012; //删除失败，请稍后再试
    const OPERATE_ERROR             = 100013; //操作失败，请稍后再试
    const CANCEL_ERROR              = 100014; //取消失败，请稍后再试
    const SYS_ERROR                 = 100015; //噢！系统抛出异常错误

    // 用户板块(200001开始)
    const NOT_LOGINED               = 200001; //未登录
    const NOT_SAME_PASSWORD         = 200002; //两个密码不一致
    const PHONE_ERROR               = 200003; //电话号码不正确
    const USERID_ERROR              = 200004; //用户id不能为空
    const PWD_CONTINUE_ERROR        = 200005; //密码连续输错
    const VERIFY_CODE_ERROR         = 200006; //验证码错误
    const USER_NOT_EXIST            = 200007; //用户不存在！
    const IDCARD_NAME_ERROR         = 200008; //姓名与身份证信息不一致!
    const CRAZY_OPERATE             = 200009; //操作过于频繁，请明天再试!
    const CRAZY_OPERATE_SOON        = 200010; //操作过于频繁，请稍候再试!

    // 商品板块(300001开始)
    const NOT_GOOD_INFO             = 300001; //商品已过期
    const NOT_ON_SALE               = 300002; //??已下架
    const GROUP_NOT_EXIST           = 300003; //套餐已过期
    const NOT_ENOUGH_STOCK          = 300004; //??库存不足
    const IS_COLLECT                = 300005; //已经收藏过该商品！
	const NOT_SAME_BOND             = 300006; //不同仓商品不允许提交
	const OVER_2000                 = 300007; //商品价格超过2000
	const GROUP_OVERDUE             = 300008; //??已过期
	const NOT_SAME_SHOP             = 300009; //??已过期

	// 购物车板块(400001开始)
    const SHOPPING_CART_ERROR       = 400001; //购物车商品信息不能为空
    const NOT_CART_GOODS            = 400002; //购物车商品不存在
    const GIFT_CANNOT_ADDTOCART     = 400003; //赠品不能加入购物车
    const OVER_SINGLE_BUY_NUM       = 400004; //最多购买200件！
    const RX_CANNOT_ADD_TO_CART     = 400005; //暂不支持购买处方药
    const NOT_SELECT_GOODS          = 400006; //请选择要结算的商品
    const ADD_TO_CART_FAILED        = 400007; //加入购物车失败，请稍后再试

    // 订单板块(500001开始)
    const NO_ORDER                     = 500001; //无订单！
    const O2O_REGION_NOT_EXIST         = 500002; //O2O配送地区不存在！
    const NOT_O2O_EXPRESS_TIME         = 500003; //没有O2O配送时间段！
    const O2O_EXPRESS_TIME_INVALID     = 500004; //配送时间已失效啦，请重新选择
    const ADDRESS_NOT_EXIST            = 500005; //请填写收货地址！
    const COMMENT_ADD_FAILED           = 500006; //评论添加失败！
    const COMMENT_IMG_UPLOAD_FAILED    = 500007; //图片上传失败！
    const COMMENT_ADD_SUCCESS          = 500008; //评论添加成功！
    const COMMENT_IMG_SIZE             = 500009; //图片超出可上传大小！
    const CALLBACKPHONE_IS_EMPTY       = 500010; //请填写回拨电话！
    const DUMMY_OEDER_NOT_SUPPORT_O2O  = 500011; //虚拟订单不支持极速配送哦~
    const SINCE_SHOP_NOT_EXIST         = 500012; //请选择自提门店
    const PAY_PASSWORD_IS_EMPTY        = 500013; //请输入支付密码
    const INVOICE_IS_EMPTY             = 500014; //请输入发票信息！
    const COMMENT_DRUG_TYPE            = 500015; //该商品是处方药，不允许评论，请见谅！
    const OVER_MAX_UPLOAD              = 500016; //上传不能超过??张!
    const NOT_TO_COMMENT               = 500017; //订单不是待评价状态
    const NOT_TIME_COMMENT             = 500018; //超过九十天不能再评价
    const NOT_SIGNIN_COMMENT           = 500019; //未签收不能评价
    const NO_GIFT_STOCK                = 500020; //赠品库存不足，是否继续提交？
    const NOT_ENOUGH_GIFT_STOCK        = 500021; //赠品库存不足，剩余??件，是否继续提交？
    const NOT_ENOUGH_KEYWORD_FILTER    = 500022; //评论中包含敏感词'??'，请重新提交
    const NOT_SUPPORT_O2O              = 510022; //商品不支持O2O配送
    const NOT_TO_SEND                  = 500023; //订单不是待发货状态
    const NOT_TO_SERVICE_STATUS        = 500024; //订单服务状态错误
    const PAY_MONEY_ERROR              = 500025; //支付金额不符
    const NOT_TO_ORDER_STATUS          = 500026; //订单状态错误
    const ORDER_SHIPPED                = 500027; //订单已发货
    const RX_CANNOT_BUY_NOW            = 500028; //处方药不支持立即购买

    // 退款相关(510001开始)
    const ORDER_HAVE_APPLIED_REFUND    = 510001; //订单已经申请了退款！
    const ORDER_NOT_APPLY_REFUND       = 510002; //该订单不能申请退款！
    const ORDER_NOT_REFUND_RECORD      = 510003; //没有该退款记录！
    const HAVE_REFUND_SERVICE_HANDLE   = 510004; //该订单还有服务单未处理完
    const NOT_REFUND_RULES             = 510005; //不符合退款规则
    const REFUND_VESION_UPGRADE        = 510006; //系统升级中，您可到{{companyName}}微信商城进行申请，带来不便深感歉意
    const REFUND_AUDITED               = 510007; // 退款申请已审核通过 不能撤销

    //收货地址(520001开始)
    const NO_THIS_ADDRESS              = 520001; //没有该地址
    const MAX_CONSIGNEE_ADDRESS        = 520002; //收货地址不能超过??个


    //拼团相关  (530001)

    const   GROUP_ID_ERROR = 530001; //拼团活动id有误
    const   GROUP_PARAM_ERROR = 534601; //参数有误
    const   GROUP_NOT_HAS = 534602; //拼团活动不存在
    const   GROUP_ACT_IS_CANCEL = 534603; //活动已取消
    const   GROUP_ACT_IS_OVER = 534604; //活动已结束
    const   GROUP_ACT_IS_SUCCESS = 534605; //已成团,不能参团啦
    const   GROUP_IS_FAIL = 534606; //已拼团失败,不能参团啦
    const   GROUP_IS_SUCCESS = 534607; //该团已结束,不能参团啦;
    const   GROUP_NOT_COD = 534610; //该订单不支持货到付款,给您带来的不便敬请谅解
    const   GROUP_GOODS_ERROR = 534613; //订单商品有误
    const   GROUP_NOT_GOODS = 534614;  //商品不存在或已下架
    const   GROUP_NOSTOCK_GOODS = 31059; //商品??库存不足，给您带来的不便敬请谅解;
    const   GROUP_NOT_YUE = 534615;  //您当前余额为 0 ,不能支付
    const   GROUP_GOODS_PRICE_ERROR = 534616;  //商品金额有误不能生成订单 , 给您带来的不便敬请谅解
    const   GROUP_JOIN_MAX_ERROR = 534617;  //该活动最多可参加 ?? 次, 不要太贪心
    const   GROUP_JOINED = 534631;     //对不起, 该团已参加过
    const   GROUP_IS_OVER = 534618;   //对不起,该团已结束
    const   GROUP_IS_CANCEL = 534619; //对不起,该团已取消
    const   GROUP_NOT_OPEN = 534620; //该团未开团成功,不能参团
    const   GROUP_IS_OLDUSER = 534621; //对不起，您已经是老用户了，请重新开团
    const   GROUP_OPEN_NEW = 534622; //该团已满, 给您开新团?
    const   GROUP_ORDER_SN_ERROR = 534623; //订单号有误
    const   GROUP_JOIN_MAX_PAYING_ERROR = 534624; //该活动每个用户可以参加 ?? 次, 您有 ?? 个未付款订单待支付
    const   GROUP_NOT_OPEN_ACT = 534628; //没有已开团活动
    const   GROUP_NOT_ORDER = 534629; //暂无新拼团订单
    const   GROUP_ACT_NOT_START = 534609; //活动未开始
    const   USER_DUMMY_ERROR = 534501;           //用户权限不足，请先绑定账号

    // 支付(800001开始)
    const PAY_FAIL_YUE = 800001; //余额支付失败

    // 促销活动(600001开始)
    const OVRER_BUY_NUM                     = 600001; //超过限定购买数量
    const NOT_GOODS_IN_PROMOTION            = 600002; //该活动没有商品参加
    const NOT_PROCESSING_PROMOTION          = 600003; //没有进行中的促销活动
    const NOT_PROCESSING_LIMIT_BUY          = 600004; //没有进行中的限购活动
    const NOT_PROCESSING_LIMIT_TIME         = 600005; //没有进行中的限时优惠活动
    const NOT_GOOD_PROMOTION                = 600006; //该商品没有参加促销活动
    const NOT_GOOD_LIMIT_BUY                = 600007; //该商品没有参加限购活动
    const PROMOTION_HAVE_ENDED              = 600008; //活动已结束
    const OVER_GOOD_LIMIT_BUY_NUM           = 600009; //已超出商品限购????

    // 优惠券(610001开始)
    const INVALID_COUPON                    = 610001; //优惠券无效~
    const COUPON_EXCHANGE_OVER_LIMIT        = 610002; //兑换优惠券时可兑换数量不足
    const COUPON_EXCHANGE_UPDATE_FAIL       = 610003; //领取券码失败，更新优惠券领取数量失败
    const COUPON_EXCHANGE_ADDTORECORD_FAIL  = 610004; //领取券码失败，添加到领取记录失败
    const COUPON_EXCHANGE_STATUS_FAIL       = 610005; //领取券码失败，修改状态失败
    const COUPON_EXCHANGE_UNDEFINDED        = 610006; //找不到对应的券码或者券码已经过期
    const COUPON_RECEIVE_FAIL               = 610007;  //优惠券领取失败
    const COUPON_MISSING                    = 610008; //对应优惠券列表不存在
    const COUPON_RECEIVE_OVER_QUANTITY      = 610009; //你已经领取过该优惠券了！
    const COUPON_IS_LIMMITOFF               = 610010; //优惠券已领取完！！
    const OVER_ACTIVATION_NUM               = 610011; //连续激活5次失败，请等待24小时后解锁。
    const COUPON_EXCHANGE_HADACTIVED        = 610012; //该兑换码已激活
    const COUPON_RECORD_CHANGE_FAIL         = 610013; //优惠券记录变更失败
    const COUPON_BRING_NUMBER_FAIL          = 610014; //优惠券领取数量更新失败
    const COUPON_OLD_USER_ONLY              = 610015; //该兑换码只允许兑换老用户优惠券
    const COUPON_NEW_USER_ONLY              = 610016; //该兑换码只允许兑换新用户优惠券
    const COUPON_PRESCRIBED_USER_ONLY       = 610017; //该兑换码只允许兑换指定用户优惠券
    const COUPON_OLD_USER_BRING_FAIL        = 610018; //该优惠券不属于老用户，领取失败
    const COUPON_NOT_ISSUED                 = 610019; //该优惠券还未开始发放，领取失败
    const COUPON_END_TO_ISSUE               = 610020; //该优惠券已经发放结束，领取失败

    // 优惠券大礼包(611001开始)
    const COUPON_RECORD_EXSIT               = 611011;//该优惠大礼包已领取，领取失败
    const PACKAGE_NOT_EXSIT                 = 611012;//该优惠大礼包不存在
    const PACKAGE_BIND_ERROR                = 611013;//该大礼包绑定失败
    const PACKAGE_BIND_EXIST               = 611014;//该大礼包已绑定

    // 辣妈(620001开始)
    const RECALL_DOC                        = 620001; //我们已收到你的信息，请十分钟之后再试
    const RECALL_DOC_ERROR                  = 620002; //数据错误，请刷新页面后重试
    const RECALL_DOC_SUCCESS                = 620003; //信息发送成功，请耐心等待
    const PRESCRIPTION_CANNOT_ADDTOCART     = 620004; //处方已不能购买，请联系客服处理
    const GIFT_CANNOT_REPEAT_GET            = 620005; //礼包已经领取过不能重复领取！
    const GOODS_NUMBER_OVER                 = 620006; //啊哦，商品超过可购买数量了！
    const GOODS_UNKONN_GONE                 = 620007; //走丢了，找不到该商品哦！
    const MOM_APPLY_NOT_UNIQUE              = 620008; //宝妈，您已提交过资料，无需重复申请
    const REPORT_CANNOT_REPEAT              = 620009; //宝妈，您已填写过体验报告了！
    const GIFT_NOT_GET                      = 620010; //先领取您的礼包，再填写试用报告哦！
    const GIFT_GOODS_CANNOT_GET             = 620011; //商品暂不能放进购物车，请先到礼包中心填写体验报告！
    const GIFT_ONLY_ONE_BUY                 = 620012; //不好意思，同属一个辣妈礼包的0元商品只能结算一次哦
    const OTO_ADDRESS_INVALID               = 630001; // OTO 收货地址无效


    //移动端状态码(不会使用，仅供参考)
    const EMPTY_RESULT              = 30001; //结果为空
    const ACCOUNT_FREEZE            = 31000; //该账号已冻结！
    const ACCOUNT_HAVE_EXISTED      = 31001; //账号已存在！
    const ACCOUNT_NOT_EXISTED       = 31002; //账号不存在！
    const ACCOUNT_OR_PWD_ERROR      = 31003; //账号或密码错误！
    const NOT_SALE_OR_NOT_EXISTED   = 31004; //??商品已下架或不存在！
    const EXISTED_REPEAT_PHONE      = 31005; //存在重复的手机号
    const NO_ENOUGH_STOCK           = 31009; //??商品库存不足，请修改商品数量！
    const HAVE_ASSESS_GOOD          = 31010; //你已经评价过该商品！
    const COUPON_NO_CONDITION       = 31012; //你使用的优惠券不符合使用条件！
    const ACTIVATION_CODE_ERROR     = 31013; //激活码数据错误。
    const COUPON_HAVE_CANCLED       = 31014; //优惠券已被取消。
    const COUPON_HAVE_EXPIRED       = 31015; //优惠券已过期。
    const SHIP_ADDRESS_NUM_ERROR    = 31017; //收货地址编号错误！
    const POST_CODE_ERROR           = 31018; //??邮编号码错误！
    const SHIP_ADDRESS_DET_ERROR    = 31019; //??收货地址详情格式错误！
    const RECEIVER_FORMAT_ERROR     = 31020; //??收货人格式错误！
    const CHOOSE_CORRECT_AREA       = 31021; //请选择正确的地区！
    const OVER_LIMIT_BUY_NUMBER     = 31022; //该限购商品最多只能买??件！
    const ACTIVATION_CODE_HAVE_USED = 31023; //该激活码已被使用。
    const ACTIVATION_CODE_NOT_EXIST = 31024; //激活码不存在。
    const LIMIT_BUY_ONCE            = 31025; //你已经参加过本期限购活动，每期限购一次！
    const LIMIT_BUY_MANY_ITEMS      = 31026; //你已经参加过本期限购活动，每件商品限购??件！
    const LIMIT_BUY_MANY_KINDS      = 31027; //购物车中有参加限购活动的商品，最多可购买??种！
    const OFFERS_EXPIRED            = 31028; //当前优惠已过期，商品价格将变回原价！
    const OFFERS_GOODS_EXPIRED      = 31029; //订单提交失败，优惠商品已过期，请返回购物车修改！
    const MAX_LIMIT_BUY_NUMBER      = 31030; //专场最大限购数为??！
    const MAX_MESSAGE_NUMBER        = 31031; //订单留言最多只能输入100个字符！
    const MAX_BUY_NUMBER            = 31032; //每个商品最多可以购买200件！
    const OVER_LIMIT_LENGTH         = 32001; //提交内容超过长度限制
    const ADVISORY_REQUIRED         = 32002; //请填写咨询信息后再提交
    const PAID_FAILED               = 804; //支付失败
    const PAID_SUCCESS              = 888; //支付成功
    const PAID_SYNCHROSCOPING       = 800; //支付信息正在同步


    /**
     * @desc http状态码信息
     * @author 吴俊华
     */
    public static $HttpStatusMsg = array(
        self::SUCCESS                   => '成功',
        self::NOT_LOGINED               => '未登录',
        self::PARAM_ERROR               => '参数不完整或参数错误',
        self::VERIFY_FAILED             => '校验失败',
        self::SEND_FAILED               => '发送失败',
        self::NOT_SAME_PASSWORD         => '两个密码不一致',
        self::PHONE_ERROR               => '电话号码不正确',
        self::USERID_ERROR              => '用户id不能为空',
        self::PLATFORM_ERROR            => '平台标识不能为空',
        self::PWD_CONTINUE_ERROR        => '密码连续输错',
        self::VERIFY_CODE_ERROR         => '验证码错误',
        self::SHOPPING_CART_ERROR       => '购物车商品信息不能为空',
        self::ILLEGAL_REQUEST           => '非法请求',
        self::NOT_GOOD_INFO             => '商品已过期',
        self::NO_DATA                   => '暂无数据...',
        self::NOT_FOUND                 => '未找到',
        self::FAILED                    => '系统错误',
        self::SYSTEM_ERROR              => '系统参数有误',
        self::NOT_ON_SALE               => '??已下架',
        self::OVRER_BUY_NUM             => '超过限定购买数量',
        self::GROUP_NOT_EXIST           => '套餐已过期',
        self::NOT_ENOUGH_STOCK          => '??库存不足',
        self::IS_COLLECT                => '已经收藏过该商品！',
        self::USER_NOT_EXIST            => '用户不存在！',
        self::NOT_CART_GOODS            => '购物车商品不存在',
        self::GIFT_CANNOT_ADDTOCART     => '赠品不能加入购物车',
        self::OVER_SINGLE_BUY_NUM       => '最多购买200件！',
        self::RX_CANNOT_ADD_TO_CART     => '暂不支持购买处方药',
        self::NOT_GOODS_IN_PROMOTION    => '该活动没有商品参加',
        self::INVALID_COUPON            => '优惠券无效~',
        self::NOT_PROCESSING_PROMOTION  => '没有进行中的促销活动',
        self::NOT_PROCESSING_LIMIT_BUY  => '没有进行中的限购活动',
        self::NOT_PROCESSING_LIMIT_TIME => '没有进行中的限时优惠活动',
        self::NOT_GOOD_PROMOTION        => '该商品没有参加促销活动',
        self::NOT_GOOD_LIMIT_BUY        => '该商品没有参加限购活动',
        self::PROMOTION_HAVE_ENDED      => '活动已结束',
        self::OVER_GOOD_LIMIT_BUY_NUM   => '??',
        self::RECALL_DOC                => '我们已收到你的信息，请十分钟之后再试',
        self::RECALL_DOC_ERROR          => '数据错误，请刷新页面后重试',
        self::RECALL_DOC_SUCCESS        => '信息发送成功，请耐心等待',
        self::PRESCRIPTION_CANNOT_ADDTOCART => '处方已不能购买，请联系客服处理',
        self::GIFT_CANNOT_REPEAT_GET => '礼包已经领取过不能重复领取！',
        self::GOODS_NUMBER_OVER => '啊哦，商品超过可购买数量了！',
        self::GOODS_UNKONN_GONE => '走丢了，找不到该商品哦！',
        self::MOM_APPLY_NOT_UNIQUE => '宝妈，您已提交过资料，无需重复申请',
        self::COUPON_RECEIVE_FAIL => '优惠券领取失败',
        self::COUPON_MISSING => '对应优惠券列表不存在',
        self::REPORT_CANNOT_REPEAT => '宝妈，您已填写过体验报告了！',
        self::GIFT_NOT_GET => '先领取您的礼包，再填写试用报告哦！',

        self::EMPTY_RESULT           => '结果为空',
        self::ACCOUNT_HAVE_EXISTED   => '账号已存在！',
        self::ACCOUNT_FREEZE         => '该账号已冻结',
        self::ACCOUNT_NOT_EXISTED    => '账号不存在！',
        self::ACCOUNT_OR_PWD_ERROR   => '账号或密码错误！',
        self::NOT_SALE_OR_NOT_EXISTED => '??商品已下架或不存在！',
        self::EXISTED_REPEAT_PHONE => '存在重复的手机号',
        self::COUPON_RECEIVE_OVER_QUANTITY => '你已经领取过该优惠券了',
        self::ORDER_HAVE_APPLIED_REFUND => '订单已经申请了退款！',
        self::ORDER_NOT_APPLY_REFUND => '该订单不能申请退款！',
        self::ORDER_NOT_REFUND_RECORD => '没有该退款记录！',
        self::NO_THIS_ADDRESS => '没有该地址',
        self::MAX_CONSIGNEE_ADDRESS => '收货地址不能超过??个',
        self::NO_ENOUGH_STOCK => '??商品库存不足，请修改商品数量！',
        self::HAVE_ASSESS_GOOD => '你已经评价过该商品！',
        self::COUPON_IS_LIMMITOFF => '优惠券已领取完！！',
        self::COUPON_NO_CONDITION => '你使用的优惠券不符合使用条件！',
        self::ACTIVATION_CODE_ERROR => '激活码数据错误。',
        self::COUPON_HAVE_CANCLED => '优惠券已被取消。',
        self::COUPON_HAVE_EXPIRED => '优惠券已过期。',
        self::OVER_ACTIVATION_NUM => '连续激活5次失败，请等待24小时后解锁。',
        self::SHIP_ADDRESS_NUM_ERROR => '收货地址编号错误！',
        self::POST_CODE_ERROR => '??邮编号码错误！',
        self::SHIP_ADDRESS_DET_ERROR => '??收货地址详情格式错误！',
        self::RECEIVER_FORMAT_ERROR => '??收货人格式错误！',
        self::CHOOSE_CORRECT_AREA => '请选择正确的地区！',
        self::OVER_LIMIT_BUY_NUMBER => '该限购商品最多只能买??件！',
        self::ACTIVATION_CODE_HAVE_USED => '该激活码已被使用。',
        self::ACTIVATION_CODE_NOT_EXIST => '激活码不存在。',
        self::LIMIT_BUY_ONCE => '你已经参加过本期限购活动，每期限购一次！',
        self::LIMIT_BUY_MANY_ITEMS => '你已经参加过本期限购活动，每件商品限购??件！',
        self::LIMIT_BUY_MANY_KINDS => '购物车中有参加限购活动的商品，最多可购买??种！',
        self::OFFERS_EXPIRED => '当前优惠已过期，商品价格将变回原价！',
        self::OFFERS_GOODS_EXPIRED => '订单提交失败，优惠商品已过期，请返回购物车修改！',
        self::MAX_LIMIT_BUY_NUMBER => '专场最大限购数为??！',
        self::MAX_MESSAGE_NUMBER => '订单留言最多只能输入100个字符！',
        self::MAX_BUY_NUMBER => '每个商品最多可以购买200件！',
        self::OVER_LIMIT_LENGTH => '提交内容超过长度限制',
        self::ADVISORY_REQUIRED => '请填写咨询信息后再提交',
        self::PAID_FAILED => '支付失败',
        self::PAID_SUCCESS => '支付成功',
        self::PAID_SYNCHROSCOPING => '支付信息正在同步',
		self::COUPON_EXCHANGE_OVER_LIMIT => '兑换优惠券时可兑换数量不足',
		self::COUPON_EXCHANGE_UPDATE_FAIL => '领取券码失败，更新优惠券领取数量失败',
		self::COUPON_EXCHANGE_ADDTORECORD_FAIL => '领取券码失败，添加到领取记录失败',
		self::COUPON_EXCHANGE_STATUS_FAIL => '领取券码失败，修改状态失败',
		self::COUPON_EXCHANGE_UNDEFINDED => '找不到对应的券码或者券码已经过期',
		self::NO_ORDER => '无订单！',
		self::O2O_REGION_NOT_EXIST => 'O2O配送地区不存在！',
		self::NOT_O2O_EXPRESS_TIME => '没有O2O配送时间段！',
		self::O2O_EXPRESS_TIME_INVALID => '配送时间已失效啦，请重新选择',
		self::ADDRESS_NOT_EXIST => '请填写收货地址！',
        self::IDCARD_NAME_ERROR => '姓名与身份证信息不一致!',
        self::CRAZY_OPERATE => '操作过于频繁，请明天再试！',
        self::CRAZY_OPERATE_SOON => '操作过于频繁，请稍候再试！',
        self::COMMENT_ADD_FAILED => '评论添加失败！',
        self::COMMENT_IMG_UPLOAD_FAILED => '图片上传失败！',
        self::COMMENT_ADD_SUCCESS => '图片上传成功！',
        self::COMMENT_IMG_SIZE => '图片超出可上传大小！',
        self::CALLBACKPHONE_IS_EMPTY => '请填写回拨电话！',
        self::DUMMY_OEDER_NOT_SUPPORT_O2O => '虚拟订单不支持极速配送哦~',
        self::SINCE_SHOP_NOT_EXIST => '请选择自提门店',
        self::PAY_PASSWORD_IS_EMPTY => '请输入支付密码',
        self::INVOICE_IS_EMPTY => '请输入发票信息！',
        self::COMMENT_DRUG_TYPE => '该商品是处方药，不允许评论，请见谅！',
        self::OVER_MAX_UPLOAD => '上传不能超过??张',
        self::ADD_ERROR => '添加失败，请稍后再试',
        self::EDIT_ERROR => '修改失败，请稍后再试',
        self::DELETE_ERROR => '删除失败，请稍后再试',
        self::OPERATE_ERROR => '操作失败，请稍后再试',
        self::CANCEL_ERROR => '取消失败，请稍后再试',
        self::NOT_TO_COMMENT => '订单不是待评价状态！',
        self::NOT_TIME_COMMENT => '超过九十天不能再评价！',
        self::NOT_SIGNIN_COMMENT => '未签收不能评价！',
        self::GIFT_ONLY_ONE_BUY => '不好意思，同属一个辣妈礼包的0元商品只能结算一次哦！',
        self::GIFT_GOODS_CANNOT_GET => '商品暂不能放进购物车，请先到礼包中心填写体验报告！',
        self::SYS_ERROR => '噢！系统抛出异常错误',
        self::COUPON_EXCHANGE_HADACTIVED => '该兑换码已经失效',
        self::NO_GIFT_STOCK => '赠品库存不足，是否继续提交？',
        self::NOT_ENOUGH_GIFT_STOCK => '赠品库存不足，剩余??件，是否继续提交？',
        self::COUPON_RECORD_CHANGE_FAIL => '优惠券记录变更失败',
        self::COUPON_BRING_NUMBER_FAIL => '优惠券领取数量更新失败',
        self::NOT_SELECT_GOODS => '请选择要结算的商品',
        self::NOT_SUPPORT_O2O => '商品不支持O2O配送',
        self::NOT_TO_SEND => '订单不是待发货状态！',
        self::NOT_TO_SERVICE_STATUS => '订单服务状态错误！',
        self::NOT_TO_ORDER_STATUS => '订单状态错误！',
        self::ORDER_SHIPPED => '订单已发货！',
        self::ADD_TO_CART_FAILED => '加入购物车失败，请稍后再试',
        self::GROUP_OVERDUE => '??已过期',
        self::NOT_ENOUGH_KEYWORD_FILTER => '评论中包含敏感词"??"，请重新提交',
		self::NOT_SAME_BOND =>  '不同仓库的商品，请分开提交订单',
		self::NOT_SAME_SHOP =>  '不同商家的商品，请分开提交订单',
        self::COUPON_OLD_USER_ONLY => '该兑换码只允许兑换老用户优惠券',
        self::COUPON_NEW_USER_ONLY => '该兑换码只允许兑换新用户优惠券',
        self::COUPON_PRESCRIBED_USER_ONLY => '该兑换码只允许兑换指定用户优惠券',
        self::OVER_2000  =>  '商品价格超过2000',
        self::COUPON_OLD_USER_BRING_FAIL => '该优惠券不属于老用户，领取失败',
        self::OTO_ADDRESS_INVALID=>'收货地址无效或不在配送范围内',
        self::HAVE_REFUND_SERVICE_HANDLE=>'该订单还有服务单未处理完',
        self::NOT_REFUND_RULES=>'不符合退款规则',
        self::PAY_MONEY_ERROR=>'支付金额不符',
        self::REFUND_VESION_UPGRADE=>'系统升级中，您可到{{companyName}}微信商城进行申请，带来不便深感歉意',
        self::GROUP_ID_ERROR => '拼团活动id有误',
        self::GROUP_PARAM_ERROR => '参数有误',
        self::GROUP_NOT_HAS => '拼团活动不存在',
        self::GROUP_ACT_IS_CANCEL => '活动已取消',
        self::GROUP_ACT_IS_OVER => '活动已结束',
        self::GROUP_ACT_IS_SUCCESS => '已成团,不能参团',
        self::GROUP_IS_FAIL => '该团已结束或取消',
        self::GROUP_IS_SUCCESS => '该团已结束,不能参团啦',
        self::GROUP_NOT_COD => '该订单不支持货到付款,给您带来的不便敬请谅解',
        self::GROUP_GOODS_ERROR => '订单商品有误',
        self::GROUP_NOT_GOODS => '商品不存在或已下架',
        self::GROUP_NOT_YUE => '您当前余额为 0 ,不能支付',
        self::GROUP_GOODS_PRICE_ERROR => '商品金额有误不能生成订单 , 给您带来的不便敬请谅解',
        self::GROUP_JOIN_MAX_ERROR => '该活动最多可参加 ?? 次, 不要太贪心',
        self::GROUP_JOINED => '对不起, 该团已参加过',
        self::GROUP_IS_OVER => '对不起,该团已结束',
        self::GROUP_IS_CANCEL => '对不起,该团已取消',
        self::GROUP_NOT_OPEN => '未开团成功,不能参团',
        self::GROUP_IS_OLDUSER => '对不起，您已经是老用户了，请重新开团',
        self::GROUP_OPEN_NEW => '该团已满, 给您开新团?',
        self::GROUP_ORDER_SN_ERROR => '订单号有误',
        self::GROUP_JOIN_MAX_PAYING_ERROR => '该活动可参加 ?? 次, 您有 ?? 个未付款订单',
        self::GROUP_NOT_OPEN_ACT => '没有已开团活动',
        self::GROUP_NOT_ORDER => '暂无新拼团订单',
        self::GROUP_NOSTOCK_GOODS =>'商品“??”库存不足，给您带来的不便敬请谅解',
        self::PAY_FAIL_YUE => '余额支付失败',
        self::USER_DUMMY_ERROR => '用户权限不足, 请先绑定账号',
        self::COUPON_RECORD_EXSIT => '该优惠大礼包已领取，领取失败',
        self::PACKAGE_NOT_EXSIT => '该优惠大礼包不存在',
        self::PACKAGE_BIND_ERROR => '该大礼包绑定失败',
        self::PACKAGE_BIND_EXIST => '该大礼包已绑定',
        self::RX_CANNOT_BUY_NOW => '处方药不支持立即购买',
        self::REFUND_AUDITED =>'申请已在处理中无法撤销，请重新下单',

        self::COUPON_NOT_ISSUED => '该优惠券还未开始发放，领取失败',
        self::COUPON_END_TO_ISSUE => '该优惠券已经发放结束，领取失败',
    );
}