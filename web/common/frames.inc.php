<?php
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 * $sn$
 */
defined('IN_IA') or exit('Access Denied');

$ms = array(
	'platform' => array(
		array(
			'title' => '基本功能',
			'permission_name' => 'platform_basic_function',
			'items' => array(
				array(
					'title' => '文字回复',
					'url' => './index.php?c=platform&a=reply&m=basic',
					'append' => array(
						'title' => '<i class="fa fa-plus"></i>',
						'url' => './index.php?c=platform&a=reply&do=post&m=basic',
					) ,
					'permission_name' => 'platform_reply_basic',
				) ,
				array(
					'title' => '图文回复',
					'url' => './index.php?c=platform&a=reply&m=news',
					'append' => array(
						'title' => '<i class="fa fa-plus"></i>',
						'url' => './index.php?c=platform&a=reply&do=post&m=news',
					) ,
					'permission_name' => 'platform_reply_news',
				) ,
				array(
					'title' => '音乐回复',
					'url' => './index.php?c=platform&a=reply&m=music',
					'append' => array(
						'title' => '<i class="fa fa-plus"></i>',
						'url' => './index.php?c=platform&a=reply&do=post&m=music',
					) ,
					'permission_name' => 'platform_reply_music',
				) ,
				array(
					'title' => '图片回复',
					'url' => './index.php?c=platform&a=reply&m=images',
					'append' => array(
						'title' => '<i class="fa fa-plus"></i>',
						'url' => './index.php?c=platform&a=reply&do=post&m=images',
					) ,
					'permission_name' => 'platform_reply_images',
				) ,
				array(
					'title' => '语音回复',
					'url' => './index.php?c=platform&a=reply&m=voice',
					'append' => array(
						'title' => '<i class="fa fa-plus"></i>',
						'url' => './index.php?c=platform&a=reply&do=post&m=voice',
					) ,
					'permission_name' => 'platform_reply_voice',
				) ,
				array(
					'title' => '视频回复',
					'url' => './index.php?c=platform&a=reply&m=video',
					'append' => array(
						'title' => '<i class="fa fa-plus"></i>',
						'url' => './index.php?c=platform&a=reply&do=post&m=video',
					) ,
					'permission_name' => 'platform_reply_video',
				) ,
				array(
					'title' => '微信卡券回复',
					'url' => './index.php?c=platform&a=reply&m=wxcard',
					'append' => array(
						'title' => '<i class="fa fa-plus"></i>',
						'url' => './index.php?c=platform&a=reply&do=post&m=wxcard',
					) ,
					'permission_name' => 'platform_reply_wxcard',
				) ,
				array(
					'title' => '自定义接口回复',
					'url' => './index.php?c=platform&a=reply&m=userapi',
					'append' => array(
						'title' => '<i class="fa fa-plus"></i>',
						'url' => './index.php?c=platform&a=reply&do=post&m=userapi',
					) ,
					'permission_name' => 'platform_reply_userapi',
				) ,
				array(
					'title' => '系统回复',
					'url' => './index.php?c=platform&a=special&do=display&',
					'append' => array(
						'title' => '',
						'url' => '',
					) ,
					'permission_name' => 'platform_reply_system',
				) ,
				array(
					'title' => '自动回复',
					'url' => './index.php?c=platform&a=autoreply&m=autoreply',
					'append' => array(
						'title' => '',
						'url' => '',
					),
					'permission_name' => 'platform_reply_autoreply',
				),
			) ,
		) ,
		array(
			'title' => '高级功能',
			'permission_name' => 'platform_high_function',
			'items' => array(
				array(
					'title' => '常用服务接入',
					'url' => './index.php?c=platform&a=service&do=switch&',
					'append' => array(
						'title' => '',
						'url' => '',
					) ,
					'permission_name' => 'platform_service',
				) ,
				array(
					'title' => '自定义菜单',
					'url' => './index.php?c=platform&a=menu&',
					'append' => array(
						'title' => '',
						'url' => '',
					) ,
					'permission_name' => 'platform_menu',
				) ,
				array(
					'title' => '特殊消息回复',
					'url' => './index.php?c=platform&a=special&do=message&',
					'append' => array(
						'title' => '',
						'url' => '',
					) ,
					'permission_name' => 'platform_special',
				) ,
				array(
					'title' => '二维码管理',
					'url' => './index.php?c=platform&a=qr&',
					'append' => array(
						'title' => '',
						'url' => '',
					) ,
					'permission_name' => 'platform_qr',
				) ,
				array(
					'title' => '多客服接入',
					'url' => './index.php?c=platform&a=reply&m=custom',
					'append' => array(
						'title' => '',
						'url' => '',
					) ,
					'permission_name' => 'platform_reply_custom',
				) ,
				array(
					'title' => '长链接二维码',
					'url' => './index.php?c=platform&a=url2qr&',
					'append' => array(
						'title' => '',
						'url' => '',
					) ,
					'permission_name' => 'platform_url2qr',
				) ,
			) ,
		) ,
		array(
			'title' => '数据统计',
			'permission_name' => 'platform_stat',
			'items' => array(
				array(
					'title' => '聊天记录',
					'url' => './index.php?c=platform&a=stat&do=history&',
					'append' => array(
						'title' => '',
						'url' => '',
					) ,
					'permission_name' => 'platform_stat_history',
				) ,
				array(
					'title' => '回复规则使用情况',
					'url' => './index.php?c=platform&a=stat&do=rule&',
					'append' => array(
						'title' => '',
						'url' => '',
					) ,
					'permission_name' => 'platform_stat_rule',
				) ,
				array(
					'title' => '关键字命中情况',
					'url' => './index.php?c=platform&a=stat&do=keyword&',
					'append' => array(
						'title' => '',
						'url' => '',
					) ,
					'permission_name' => 'platform_stat_keyword',
				) ,
				array(
					'title' => '参数',
					'url' => './index.php?c=platform&a=stat&do=setting&',
					'append' => array(
						'title' => '',
						'url' => '',
					) ,
					'permission_name' => 'platform_stat_setting',
				) ,
			) ,
		) ,
	) ,
	'site' => array(
		array(
			'title' => '微站管理',
			'permission_name' => 'site_manage',
			'items' => array(
				array(
					'title' => '站点管理',
					'url' => './index.php?c=site&a=multi&do=display&',
					'append' => array(
						'title' => '<i class="fa fa-plus"></i>',
						'url' => './index.php?c=site&a=multi&do=post&',
					) ,
					'permission_name' => 'site_multi_display',
				) ,
				array(
					'title' => '站点添加/编辑',
					'is_permission' => 1,
					'permission_name' => 'site_multi_post',
				) ,
				array(
					'title' => '站点删除',
					'is_permission' => 1,
					'permission_name' => 'site_multi_del',
				) ,
				array(
					'title' => '模板管理',
					'url' => './index.php?c=site&a=style&do=template&',
					'append' => array(
						'title' => '',
						'url' => '',
					) ,
					'permission_name' => 'site_style_template',
				) ,
				array(
					'title' => '模块模板扩展',
					'url' => './index.php?c=site&a=style&do=module&',
					'append' => array(
						'title' => '',
						'url' => '',
					) ,
					'permission_name' => 'site_style_module',
				) ,
			) ,
		) ,
		array(
			'title' => '特殊页面管理',
			'permission_name' => 'site_special_page',
			'items' => array(
				array(
					'title' => '会员中心',
					'url' => './index.php?c=site&a=editor&do=uc&',
					'append' => array(
						'title' => '',
						'url' => '',
					) ,
					'permission_name' => 'site_editor_uc',
				) ,
				array(
					'title' => '专题页面',
					'url' => './index.php?c=site&a=editor&do=page&',
					'append' => array(
						'title' => '<i class="fa fa-plus"></i>',
						'url' => './index.php?c=site&a=editor&do=design&',
					) ,
					'permission_name' => 'site_editor_page',
				)
			) ,
		) ,
		array(
			'title' => '功能组件',
			'permission_name' => 'site_article',
			'items' => array(
				array(
					'title' => '分类设置',
					'url' => './index.php?c=site&a=category&',
					'append' => array(
						'title' => '',
						'url' => '',
					) ,
					'permission_name' => 'site_category',
				) ,
				array(
					'title' => '文章管理',
					'url' => './index.php?c=site&a=article&',
					'append' => array(
						'title' => '',
						'url' => '',
					) ,
					'permission_name' => 'site_article',
				) ,
			) ,
		) ,
	) ,
	'mc' => array(
		array(
			'title' => '粉丝管理',
			'permission_name' => 'mc_fans_manage',
			'items' => array(
				array(
					'title' => '粉丝分组',
					'url' => './index.php?c=mc&a=fangroup&',
					'append' => array(
						'title' => '',
						'url' => '',
					) ,
					'permission_name' => 'mc_fangroup',
				) ,
				array(
					'title' => '粉丝',
					'url' => './index.php?c=mc&a=fans&',
					'append' => array(
						'title' => '',
						'url' => '',
					) ,
					'permission_name' => 'mc_fans',
				) ,
			) ,
		) ,
		array(
			'title' => '会员中心',
			'permission_name' => 'mc_members_manage',
			'items' => array(
				array(
					'title' => '会员中心关键字',
					'url' => './index.php?c=platform&a=cover&do=mc&',
					'append' => array(
						'title' => '',
						'url' => '',
					) ,
					'permission_name' => 'platform_cover_mc',
				) ,
				array(
					'title' => '会员',
					'url' => './index.php?c=mc&a=member',
					'append' => array(
						'title' => '<i class="fa fa-plus"></i>',
						'url' => './index.php?c=mc&a=member&do=add',
					) ,
					'permission_name' => 'mc_member',
				) ,
				array(
					'title' => '会员组',
					'url' => './index.php?c=mc&a=group&',
					'append' => array(
						'title' => '',
						'url' => '',
					) ,
					'permission_name' => 'mc_group',
				) ,
			) ,
		) ,
		array(
			'title' => '会员卡管理',
			'permission_name' => 'mc_card_manage',
			'items' => array(
				array(
					'title' => '会员卡关键字',
					'url' => './index.php?c=platform&a=cover&do=card&',
					'append' => array(
						'title' => '',
						'url' => '',
					) ,
					'permission_name' => 'platform_cover_card',
				) ,
				array(
					'title' => '会员卡管理',
					'url' => './index.php?c=mc&a=card&do=manage',
					'append' => array(
						'title' => '',
						'url' => '',
					) ,
					'permission_name' => 'mc_card_manage',
				) ,
				array(
					'title' => '会员卡设置',
					'url' => './index.php?c=mc&a=card&do=editor',
					'append' => array(
						'title' => '',
						'url' => '',
					) ,
					'permission_name' => 'mc_card_editor',
				) ,
				array(
					'title' => '会员卡其他功能',
					'url' => './index.php?c=mc&a=card&do=other',
					'append' => array(
						'title' => '',
						'url' => '',
					) ,
					'permission_name' => 'mc_card_other',
				) ,
			) ,
		) ,
		array(
			'title' => '积分兑换',
			'permission_name' => 'activity_discount_manage',
			'items' => array(
				array(
					'title' => '卡券兑换',
					'url' => './index.php?c=activity&a=exchange&do=display&type=coupon',
					'append' => array(
						'title' => '',
						'url' => '',
					) ,
					'permission_name' => 'activity_coupon_display',
				) ,
				array(
					'title' => '真实物品兑换',
					'url' => './index.php?c=activity&a=exchange&do=display&type=goods',
					'append' => array(
						'title' => '',
						'url' => '',
					) ,
					'permission_name' => 'activity_goods_display',
				) ,
			) ,
		) ,
		array(
			'title' => '微信素材&群发',
			'permission_name' => 'material_manage',
			'items' => array(
				array(
					'title' => '素材&群发',
					'url' => './index.php?c=material&a=display',
					'append' => array(
						'title' => '',
						'url' => '',
					) ,
					'permission_name' => 'material_display',
				) ,
				array(
					'title' => '定时群发',
					'url' => './index.php?c=material&a=mass',
					'append' => array(
						'title' => '',
						'url' => '',
					) ,
					'permission_name' => 'material_mass',
				) ,
			) ,
		) ,
		array(
			'title' => '卡券管理',
			'permission_name' => 'wechat_card_manage',
			'items' => array(
				array(
						'title' => '卡券列表',
						'url' => './index.php?c=activity&a=coupon&do=display',
						'append' => array(
								'title' => '',
								'url' => '',
						) ,
						'permission_name' => 'activity_coupon_display',
				) ,
				array(
					'title' => '卡券营销',
					'url' => 'index.php?c=activity&a=market',
					'append' => array(
						'title' => '',
						'url' => '',
					) ,
					'permission_name' => 'activity_coupon_market',
				) ,
				array(
					'title' => '卡券核销',
					'url' => './index.php?c=activity&a=consume&do=display',
					'append' => array(
						'title' => '',
						'url' => '',
					) ,
					'permission_name' => 'activity_consume_coupon',
				) ,
			) ,
		) ,
		array(
			'title' => '工作台',
			'permission_name' => 'paycenter_manage',
			'items' => array(
				array(
					'title' => '门店列表',
					'url' => './index.php?c=activity&a=store',
					'append' => array(
							'title' => '',
							'url' => '',
					) ,
					'permission_name' => 'activity_store_list',
				) ,
				array(
					'title' => '店员列表',
					'url' => './index.php?c=activity&a=clerk',
					'append' => array(
							'title' => '',
							'url' => '',
					) ,
					'permission_name' => 'activity_clerk_list',
				) ,
				array(
					'title' => '微信刷卡收款',
					'url' => './index.php?c=paycenter&a=wxmicro&do=pay',
					'append' => array(
						'title' => '',
						'url' => '',
					) ,
					'permission_name' => 'paycenter_wxmicro_pay',
				) ,
				array(
					'title' => '店员操作关键字',
					'url' => './index.php?c=platform&a=cover&do=clerk',
					'append' => array(
						'title' => '',
						'url' => '',
					) ,
					'permission_name' => 'paycenter_clerk',
				) ,
			) ,
		) ,
		array(
			'title' => '统计中心',
			'permission_name' => 'stat_center',
			'items' => array(
				array(
					'title' => '会员积分统计',
					'url' => './index.php?c=stat&a=credit1',
					'append' => array(
						'title' => '',
						'url' => '',
					) ,
					'permission_name' => 'stat_credit1',
				) ,
				array(
					'title' => '会员余额统计',
					'url' => './index.php?c=stat&a=credit2',
					'append' => array(
						'title' => '',
						'url' => '',
					) ,
					'permission_name' => 'stat_credit2',
				) ,
				array(
					'title' => '会员现金消费统计',
					'url' => './index.php?c=stat&a=cash',
					'append' => array(
						'title' => '',
						'url' => '',
					) ,
					'permission_name' => 'stat_cash',
				) ,
				array(
					'title' => '会员卡统计',
					'url' => './index.php?c=stat&a=card',
					'append' => array(
						'title' => '',
						'url' => '',
					) ,
					'permission_name' => 'stat_card',
				) ,
				array(
					'title' => '收银台收款统计',
					'url' => './index.php?c=stat&a=paycenter',
					'append' => array(
						'title' => '',
						'url' => '',
					) ,
					'permission_name' => 'stat_paycenter',
				) ,
			) ,
		) ,
	) ,
	'setting' => array(
		array(
			'title' => '公众号选项',
			'permission_name' => 'account_setting',
			'items' => array(
				array(
					'title' => '支付参数',
					'url' => './index.php?c=profile&a=payment&',
					'append' => array(
						'title' => '',
						'url' => '',
					) ,
					'permission_name' => 'profile_payment',
				) ,
				array(
					'title' => '借用 oAuth 权限',
					'url' => './index.php?c=mc&a=passport&do=oauth&',
					'append' => array(
						'title' => '',
						'url' => '',
					) ,
					'permission_name' => 'mc_passport_oauth',
				) ,
				array(
					'title' => '借用 JS 分享权限',
					'url' => './index.php?c=profile&a=jsauth&',
					'append' => array(
						'title' => '',
						'url' => '',
					) ,
					'permission_name' => 'profile_jsauth',
				) ,
				array(
					'title' => '会员字段管理',
					'url' => './index.php?c=mc&a=fields',
					'append' => array(
						'title' => '',
						'url' => '',
					) ,
					'permission_name' => 'mc_fields',
				) ,
				array(
					'title' => '微信通知设置',
					'url' => './index.php?c=mc&a=tplnotice',
					'append' => array(
						'title' => '',
						'url' => '',
					) ,
					'permission_name' => 'mc_tplnotice',
				) ,
				array(
					'title' => '工作台菜单设置',
					'url' => './index.php?c=profile&a=deskmenu',
					'append' => array(
						'title' => '',
						'url' => '',
					) ,
					'permission_name' => 'profile_deskmenu',
				) ,
				array(
					'title' => '会员扩展功能',
					'url' => './index.php?c=mc&a=plugin',
					'append' => array(
						'title' => '',
						'url' => '',
					) ,
					'permission_name' => 'mc_plugin',
				) ,
			) ,
		) ,
		array(
			'title' => '会员及粉丝选项',
			'permission_name' => 'mc_setting',
			'items' => array(
				array(
					'title' => '积分设置',
					'url' => './index.php?c=mc&a=credit&',
					'append' => array(
						'title' => '',
						'url' => '',
					) ,
					'permission_name' => 'mc_credit',
				) ,
				array(
					'title' => '注册设置',
					'url' => './index.php?c=mc&a=passport&do=passport&',
					'append' => array(
						'title' => '',
						'url' => '',
					) ,
					'permission_name' => 'mc_passport_passport',
				) ,
				array(
					'title' => '粉丝同步设置',
					'url' => './index.php?c=mc&a=passport&do=sync&',
					'append' => array(
						'title' => '',
						'url' => '',
					) ,
					'permission_name' => 'mc_passport_sync',
				) ,
				array(
					'title' => 'UC站点整合',
					'url' => './index.php?c=mc&a=uc&',
					'append' => array(
						'title' => '',
						'url' => '',
					) ,
					'permission_name' => 'mc_uc',
				) ,
				array(
					'title' => '邮件通知参数',
					'url' => './index.php?c=profile&a=notify',
					'append' => array(
						'title' => '',
						'url' => '',
					) ,
					'permission_name' => 'profile_notify',
				) ,
			) ,
		) ,
		array(
			'title' => '其他功能选项',
			'permission_name' => 'others_setting',
			'items' => array() ,
		) ,
	) ,
	'ext' => array(
		array(
			'title' => '管理',
			'items' => array(
				array(
					'title' => '扩展功能管理',
					'url' => './index.php?c=profile&a=module&',
					'append' => array(
						'title' => '',
						'url' => '',
					) ,
					'permission_name' => 'profile_module',
				) ,
			) ,
		) ,
	) ,
);

return $ms;
