define(['jquery', 'underscore', 'util', 'bootstrap', 'jquery.wookmark', 'jquery.jplayer'], function($, _){
	var material = {
		'defaultoptions' : {
			callback : null,
			type : 'all',
			multiple : false,
			ignore : {
				'basic' : false,
				'wxcard' : true,
				'image' : false,
				'music' : false,
				'news' : false,
				'video' : false,
				'voice' : false,
				'keyword' : false
			}
		},
		'init' : function(callback, options) {
			var $this = this;
			$this.options = $.extend({}, $this.defaultoptions, options);
			$this.options.callback = callback;
			$('#material-Modal').remove();
			$(document.body).append($this.buildHtml().mainDialog);

			$this.modalobj = $('#material-Modal');
			$this.modalobj.find('.modal-header .nav li a').click(function(){
				var type = $(this).data('type');
				$this.localPage(type, 1);
				$(this).tab('show');
				return false;
			});
			if (!$(this).data('init')) {
				if($this.options.type && $this.options.type != 'all') {
					$this.modalobj.find('.modal-header .nav li.' + $this.options.type + ' a').trigger('click');
				} else {
					$this.modalobj.find('.modal-header .nav li.show:first a').trigger('click');
				}
			}
			$this.modalobj.modal('show');
			return $this.modalobj;
		},
		'localPage' : function(type, page) {
			var $this = this;
			var page = page || 1;
			$('.checkMedia').removeClass('checkedMedia');
			var $content = $this.modalobj.find('.material-content #' + type);
			$content.html('<div class="info text-center"><i class="fa fa-spinner fa-pulse fa-lg"></i> 数据加载中</div>');

			if(type == 'basic') {
				var Dialog = type + 'Dialog';
				$this.modalobj.find('#btn-select').show();
				$content.html(_.template($this.buildHtml()[Dialog]));
				$this.modalobj.find('.modal-footer .btn-primary').unbind('click').click(function(){
					var attachment = [];
					attachment.content = $('#basictext').val();
					$this.options.callback(attachment);
					$this.modalobj.modal('hide');
				});
				return false;
			}
			var url = './index.php?c=utility&a=material&do=list&type=' + type;
			if(type == 'wxcard') {
				url = './index.php?c=utility&a=coupon&do=wechat';
			}
			if(type == 'keyword') {
				url = './index.php?c=utility&a=keyword&do=keyword&type=all';
			}
			$.getJSON(url, {'page': page}, function(data){
				data = data.message;
				$this.modalobj.find('#material-list-pager').html('');
				if(!_.isEmpty(data.items)) {
					$this.modalobj.find('#btn-select').show();
					$content.data('attachment', data.items);
					$content.empty();
					var Dialog = type + 'Dialog';
					$content.html(_.template($this.buildHtml()[Dialog])(data));
					if(type == 'news') {
						setTimeout(function(){
							$('.water').wookmark({
								align: 'center',
								autoResize: false,
								container: $('#news'),
								autoResize :true
							});
						}, 100);
					}
					$this.selectMedia();
					$this.playaudio();
					$this.modalobj.find('#material-list-pager').html(data.pager);
					$this.modalobj.find('#material-list-pager .pagination a').click(function(){
						$this.localPage(type, $(this).attr('page'));
					});
				} else {
					$content.html('<div class="info text-center"><i class="fa fa-info-circle fa-lg"></i> 暂无数据</div>');
				}
			});				

			$this.modalobj.find('.modal-footer .btn-primary').unbind('click').click(function(){
				var attachment = [];
				$content.find('.checkedMedia').each(function(){
					attachment.push($content.data('attachment')[$(this).data('attachid')]);
				});
				$this.finish(attachment);
			});
			return false;
		},

		'selectMedia' : function(){
			var $this = this;
			$this.modalobj.on('click', '.checkMedia', function(){
				if(!$this.options.multiple) {
					$('.checkMedia').removeClass('checkedMedia');
				}
				$(this).addClass('checkedMedia');
				var type = $(this).data('type');
				if(type == 'news') {
					if(!$this.options.multiple) {
						$('#news .panel-group').removeClass('selected');
					}
					$(this).addClass('selected');
				} else if(type == 'image') {
					if(!$this.options.multiple) {
						$('#image div').removeClass('img-item-selected');
					}
					$(this).addClass('img-item-selected');
				} else {
					if(!$this.options.multiple) {
						$('.checkMedia').removeClass('btn-primary');
					}
					$(this).addClass('btn-primary');
				}
				if(!$this.options.multiple) {
					$this.modalobj.find('.modal-footer .btn-primary').trigger('click');
				}
			});
		},

		'playaudio' : function(){
			$("#voice, .panel").on('click', '.audio-player-play', function(){
				var src = $(this).data("attach");
				if(!src) {
					return;
				}
				if ($("#player")[0]) {
					var player = $("#player");
				} else {
					var player = $('<div id="player"></div>');
					$(document.body).append(player);
				}
				player.data('control', $(this));
				player.jPlayer({
					playing: function() {
						$(this).data('control').find("i").removeClass("fa-play").addClass("fa-stop");
					},
					pause: function (event) {
						$(this).data('control').find("i").removeClass("fa-stop").addClass("fa-play");
					},
					swfPath: "resource/components/jplayer",
					supplied: "mp3,wma,wav,amr",
					solution: "html, flash"
				});
				player.jPlayer("setMedia", {mp3: $(this).data("attach")}).jPlayer("play");
				if($(this).find("i").hasClass("fa-stop")) {
					player.jPlayer("stop");
				} else {
					$('.audio-msg').find('.fa-stop').removeClass("fa-stop").addClass("fa-play");
					player.jPlayer("setMedia", {mp3: $(this).data("attach")}).jPlayer("play");
				}
			});
		},

		'finish' : function(attachment) {
			var $this = this;
			if($.isFunction($this.options.callback)) {
				if ($this.options.multiple == false) {
					$this.options.callback(attachment[0]);
				} else {
					$this.options.callback(attachment);
				}
				$this.modalobj.modal('hide');
			}
		},

		'buildHtml' : function() {
			var dialog = {};
			dialog['mainDialog'] = '<div id="material-Modal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">\n' +
				'	<div class="modal-dialog">\n' +
				'		<div class="modal-content modal-lg">\n' +
				'			<div class="modal-header">\n' +
				'				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>\n' +
				'				<h3>'+
				'					<ul role="tablist" class="nav nav-pills" style="font-size:14px; margin-top:-20px;">'+
				'						<li role="presentation" class="basic ' + (this.options.ignore.basic ? 'hide' : 'show') + '">'+
				'							<a data-toggle="tab" data-type="basic" role="tab" aria-controls="baisc" href="#basic">文字</a>'+
				'						</li>'+
				'						<li role="presentation" class="news ' + (this.options.ignore.news ? 'hide' : 'show') + '">'+
				'							<a data-toggle="tab" data-type="news" role="tab" aria-controls="news" href="#news">图文</a>'+
				'						</li>'+
				'						<li role="presentation" class="image ' + (this.options.ignore.image ? 'hide' : 'show') + '">'+
				'							<a data-toggle="tab" data-type="image" role="tab" aria-controls="image" href="#image">图片</a>'+
				'						</li>'+
				'						<li role="presentation" class="music ' + (this.options.ignore.music ? 'hide' : 'show') + '">'+
				'							<a data-toggle="tab" data-type="music" role="tab" aria-controls="music" href="#music">音乐</a>'+
				'						</li>'+
				'						<li role="presentation" class="voice ' + (this.options.ignore.voice ? 'hide' : 'show') + '">'+
				'							<a data-toggle="tab" data-type="voice" role="tab" aria-controls="voice" href="#voice">语音</a>'+
				'						</li>'+
				'						<li role="presentation" class="video ' + (this.options.ignore.video ? 'hide' : 'show') + '">'+
				'							<a data-toggle="tab" data-type="video" role="tab" aria-controls="video" href="#video">视频</a>'+
				'						</li>'+
				'						<li role="presentation" class="wxcard ' + (this.options.ignore.wxcard ? 'hide' : 'show') + '">'+
				'							<a data-toggle="tab" data-type="wxcard" role="tab" aria-controls="wxcard" href="#wxcard">微信卡券</a>'+
				'						</li>'+
				'						<li role="presentation" class="keyword ' + (this.options.ignore.keyword ? 'hide' : 'show') + '">'+
				'							<a data-toggle="tab" data-type="keyword" role="tab" aria-controls="keyword" href="#keyword">关键字</a>'+
				'						</li>'+
				'					</ul>'+
				'					<button style="margin-top:-30px;margin-right:40px;" type="button" class="btn btn-primary active pull-right ' + (this.options.ignore.news ? 'hide' : 'show') + '">'+
				'						<a style="color:white;" href="./index.php?c=material&a=post&do=news" target="_blank">新建图文</a>'+
				'					</button>'+
				'					<button style="margin-top:-30px;margin-right:40px;" type="button" class="btn btn-primary active pull-right ' + (this.options.ignore.image ? 'hide' : 'show') + '">'+
				'						<a style="color:white;" href="./index.php?c=material&a=display&do=list&type=image" target="_blank">上传图片</a>'+
				'					</button>'+
				'					<button style="margin-top:-30px;margin-right:40px;" type="button" class="btn btn-primary active pull-right ' + (this.options.ignore.music ? 'hide' : 'show') + '">'+
				'						<a style="color:white;" href="./index.php?c=material&a=display&do=list&type=music" target="_blank">上传音乐</a>'+
				'					</button>'+
				'					<button style="margin-top:-30px;margin-right:40px;" type="button" class="btn btn-primary active pull-right ' + (this.options.ignore.voice ? 'hide' : 'show') + '">'+
				'						<a style="color:white;" href="./index.php?c=material&a=display&do=list&type=voice" target="_blank">新建语音</a>'+
				'					</button>'+
				'					<button style="margin-top:-30px;margin-right:40px;" type="button" class="btn btn-primary active pull-right ' + (this.options.ignore.video ? 'hide' : 'show') + '">'+
				'						<a style="color:white;" href="./index.php?c=material&a=display&do=list&type=video" target="_blank">新建视频</a>'+
				'					</button>'+
				'					<button style="margin-top:-30px;margin-right:40px;" type="button" class="btn btn-primary active pull-right ' + (this.options.ignore.wxcard ? 'hide' : 'show') + '">'+
				'						<a style="color:white;" href="./index.php?c=activity&a=coupon&do=display" target="_blank">新建卡券</a>'+
				'					</button>'+
				'					<button style="margin-top:-30px;margin-right:40px;" type="button" class="btn btn-primary active pull-right ' + (this.options.ignore.keyword ? 'hide' : 'show') + '">'+
				'						<a style="color:white;" href="./index.php?c=platform&a=autoreply&do=post&m=autoreply" target="_blank">新建关键字</a>'+
				'					</button>'+
				'				</h3>'+
				'			</div>\n' +
				'			<div class="modal-body material-content">\n' +
				'				<div class="tab-content">'+
				'					<div id="basic" class="tab-pane" role="tabpanel"></div>'+
				'					<div id="news" class="tab-pane material clearfix" class="active" role="tabpanel" style="position:relative"></div>'+
				'					<div id="image" class="tab-pane history" role="tabpanel"></div>'+
				'					<div id="music" class="tab-pane history" role="tabpanel"></div>'+
				'					<div id="voice" class="tab-pane" role="tabpanel"></div>'+
				'					<div id="video" class="tab-pane" role="tabpanel"></div>'+
				'					<div id="wxcard" class="tab-pane" role="tabpanel"></div>'+
				'					<div id="keyword" class="tab-pane" role="tabpanel"></div>'+
				'				</div>' +
				'			</div>\n' +
				'			<div class="modal-footer">\n' +
				'				<div style="float: left;">\n' +
				'					<nav id="material-list-pager">\n' +
				'					</nav>\n' +
				'				</div>\n' +
				'				<div id="btn-select" style="float: right; display: none">\n' +
				'					<button type="button" class="btn btn-default" data-dismiss="modal">取消</button>\n' +
				'					<button type="button" class="btn btn-primary">确认</button>\n' +
				'				</div>\n' +
				'			</div>\n'+
				'		</div>\n' +
				'	</div>\n' +
				'</div>';

			dialog['basicDialog'] = '<textarea id="basictext" cols="120" rows="10"></textarea>'+
				'						<div class="help-block">'+
				'							您还可以使用表情和链接。'+
				'							<a class="emotion-triggers" href="javascript:;" onclick="initSelectEmotion();"><i class="fa fa-github-alt"></i> 表情</a>'+
				'							<a class="emoji-triggers" href="javascript:;" onclick="initSelectEmoji()" title="添加表情"><i class="fa fa-github-alt"></i> Emoji</a>'+
				'						</div>';

			dialog['imageDialog'] = '<ul class="img-list clearfix">\n' +
				'<%var items = _.sortBy(items, function(item) {return -item.id;});%>' +
				'<%_.each(items, function(item) {%> \n' +
				'<div class="checkMedia" data-media="<%=item.media_id%>" data-type="image" data-attachid="<%=item.id%>">' +
				'	<li class="img-item" style="padding:5px">\n' +
				'		<div class="img-container" style="background-image: url(\'<%=item.attach%>\');">\n' +
				'			<div class="select-status"><span></span></div>\n' +
				'		</div>\n' +
				'	</li>\n' +
				'</div>\n' +
				'<%});%>\n' +
				'</ul>';

			dialog['musicDialog'] = '<ul class="img-list clearfix">\n' +
				'<%var items = _.sortBy(items, function(item) {return -item.id;});%>' +
				'<%_.each(items, function(item) {%> \n' +
				'<div class="checkMedia" data-media="<%=item.media_id%>" data-type="image" data-attachid="<%=item.id%>">' +
				'	<li class="img-item" style="padding:5px">\n' +
				'		<div class="img-container" style="background-image: url(\'<%=item.attach%>\');">\n' +
				'			<div class="select-status"><span></span></div>\n' +
				'		</div>\n' +
				'	</li>\n' +
				'</div>\n' +
				'<%});%>\n' +
				'</ul>'+
				'<div class="input-group">'+
				'<input type="text" value="" name="" class="form-control audio-player-media" autocomplete="off")>'+
				'<span class="input-group-btn">'+
				'<button class="btn btn-default audio-player-play" type="button" style="display:none;"><i class="fa fa-play"></i></button>'+
				'<button class="btn btn-default" type="button" onclick="showAudioDialog(this, ,);">选择媒体文件</button>'+
				'</span>'+
				'</div>'+
				'<div class="input-group audio-player"></div>';

		
		
			
			
		
	
	
			dialog['voiceDialog'] ='<table class="table table-hover table-bordered" style="margin-bottom:0">'+
				'						<thead class="navbar-inner">'+
				'							<tr>'+
				'								<th>标题</th>'+
				'								<th style="width:20%;text-align:center">创建时间</th>'+
				'								<th style="width:15%;text-align:center"></th>'+
				'							</tr>'+
				'							</thead>'+
				'							<tbody class="history-content">'+
				'							<%var items = _.sortBy(items, function(item) {return -item.createtime;});%>' +
				'							<%_.each(items, function(item) {%> \n' +
				'							<tr>'+
				'								<td><%=item.filename%></td>'+
				'								<td align="center"><%=item.createtime_cn%></td>'+
				'								<td align="center">'+
				'									<div class="btn-group">'+
				'										<a href="javascript:;" class="btn btn-default btn-sm audio-player-play audio-msg" data-attach="<%=item.attach%>"><i class="fa fa-play"></i></a>'+
				'										<a href="javascript:;" class="btn btn-default btn-sm checkMedia" data-media="<%=item.media_id%>" data-type="voice" data-attachid="<%=item.id%>">选取</a>'+
				'									</div>'+
				'								</td>'+
				'							</tr>'+
				'							<%});%>' +
				'						</tbody>'+
				'					</table>';

			dialog['videoDialog'] ='<table class="table table-hover table-bordered" style="margin-bottom:0">'+
				'						<thead class="navbar-inner">'+
				'							<tr>'+
				'								<th>标题</th>'+
				'								<th style="width:20%;text-align:center">创建时间</th>'+
				'								<th style="width:10%;text-align:center"></th>'+
				'							</tr>'+
				'							</thead>'+
				'							<tbody class="history-content">'+
				'							<%var items = _.sortBy(items, function(item) {return -item.createtime;});%>' +
				'							<%_.each(items, function(item) {%> \n' +
				'							<tr>'+
				'								<%if(item.tag.title) {var title = item.tag.title} else {var title =item.filename}%>'+
				'								<td><%=title%></td>'+
				'								<td align="center"><%=item.createtime_cn%></td>'+
				'								<td align="center">'+
				'									<div class="btn-group">'+
				'										<a href="javascript:;" class="btn btn-default btn-sm checkMedia" data-media="<%=item.media_id%>" data-type="video" data-attachid="<%=item.id%>">选取</a>'+
				'									</div>'+
				'								</td>'+
				'							</tr>'+
				'							<%});%>' +
				'						</tbody>'+
				'					</table>';

			dialog['wxcardDialog'] ='<table class="table table-hover table-bordered">\n'+
				'				<thead>\n'+
				'					<tr>\n'+
				'						<th width="130" class="text-center">标题</th>\n'+
				'						<th class="text-center">类型</th>\n'+
				'						<th width="250" class="text-center">卡券有效期</th>\n'+
				'						<th class="text-center">库存/每人限领</th>\n'+
				'						<th class="text-center">操作</th>\n'+
				'					</tr>'+
				'				</thead>'+
				'				<tbody>'+
				'					<%var items = _.sortBy(items, function(item) {return -item.couponid;});%>' +
				'					<%_.each(items, function(item) {%> \n' +
				'					<tr title="<%=item.title%>">' +
				'						<td><%=item.title%></td>' +
				'						<td><%if(item.ctype == "discount") {%><span class="label label-success">折扣券</span><%} else if(item.ctype == "cash") {%><span class="label label-danger">代金券</span><%} else if(item.ctype == "gift") {%><span class="label label-danger">礼品券</span><%} else if(item.ctype == "groupon") {%><span class="label label-danger">团购券</span><%} else if(item.ctype == "general_coupon") {%><span class="label label-danger">优惠券</span><%}%></td>' +
				'						<td><%if(item.date_info.time_type == 1) {%><%=item.date_info.time_limit_start%> ~ <%=item.date_info.time_limit_end%><%} else {%>领取后<%=item.date_info.date_info%>天后生效,<%=item.date_info.limit%>天有效期<%}%></td>' +
				'						<td><%=item.quantity%>/<strong class="text-danger"><%=item.get_limit%></strong></td>' +
				'						<td><a href="javascript:;" class="btn btn-default btn-sm checkMedia" data-title="<%=item.title%>" data-type="wxcard" data-media="<%=item.card_id%>" data-attachid="<%=item.id%>">选取</a></td>' +
				'					</tr>' +
				'					<%});%>' +
				'				</tbody>'+
				'   		</table>';

			dialog['newsDialog'] = '<%var items = _.sortBy(items, function(item) {return -item.createtime;});%>' +
				'					<%_.each(items, function(item) {%> \n' +
				'					<div class="col-md-5 col-md-5 col-md-5 water" style="display:none">'+
				'						<div class="panel-group checkMedia" data-media="<%=item.media_id%>" data-type="news" data-attachid="<%=item.id%>">'+
				'							<%var index = 0;%>\n' +
				'							<%_.each(item.items, function(data) {%>\n' +
				'								<%index++;%>\n' +
				'								<div class="panel panel-default">'+
				'									<%if(index == 1) {%>\n' +
				'									<div class="panel-body">'+
				'										<div class="img">'+
				'											<i class="default">封面图片</i>'+
				'											<img src="<%=data.thumb_url%>">'+
				'											<span class="text-left"><%=data.title%></span>'+
				'										</div>'+
				'									</div>'+
				'									<%} else {%>\n' +
				'									<div class="panel-body">'+
				'										<div class="text">'+
				'											<h4><%=data.title%></h4>'+
				'										</div>'+
				'										<div class="img">'+
				'											<img src="<%=data.thumb_url%>">'+
				'											<i class="default">缩略图</i>'+
				'										</div>'+
				'									</div>'+
				'									<%}%>\n' +
				'								</div>'+
				'							<%});%>'+
				'							<div class="mask"></div>'+
				'							<i class="fa fa-check"></i>'+
				'						</div>'+
				'					</div>'+
				'					<%});%>';
			dialog['keywordDialog'] = '<table class="table table-hover table-bordered" style="margin-bottom:0">'+
				'						<thead class="navbar-inner">'+
				'							<tr>'+
				'								<th style="width:20%;text-align:center">规则名</th>'+
				'								<th style="width:50%;text-align:center">关键词</th>'+
				'								<th style="width:10%;text-align:center">优先级</th>'+
				'								<th style="width:10%;text-align:center"></th>'+
				'							</tr>'+
				'						</thead>'+
				'						<tbody class="history-content">'+
				'							<%_.each(items, function(item) {%> \n' +
				'							<tr>'+
				'								<td align="center"><%=item.name%></td>'+
				'								<td align="center"><%_.each(item.child_items, function(child_item) {%>'+
				'										&nbsp;&nbsp;<%=child_item.content%>'+
				'								<%})%></td>'+
				'								<td align="center"><%if(item.displayorder == "255"){%>置顶<%} else {%> <%=item.displayorder%> <%}%></td>'+
				'								<td align="center">'+
				'									<div class="btn-group">'+
				'										<a href="javascript:;" class="btn btn-default btn-sm checkMedia" data-media="<%=item.media_id%>" data-type="video" data-attachid="<%=item.id%>">选取</a>'+
				'									</div>'+
				'								</td>'+
				'							</tr>'+
				'							<%});%>' +
				'						</tbody>'+
				'					</table>';

			return dialog;
		}
	};
	initSelectEmotion = function() {
		var textbox = $("#basictext").val();
		util.emotion($('.emotion-triggers'), $("#basictext"), function(txt, elm, target){
			$("#basictext").val(textbox+txt);
		});
	};
	initSelectEmoji = function() {
		var textbox = $("#basictext").val();
		util.emojiBrowser(function(emoji){
			var unshift = '[U+' + emoji.find("span").text() + ']';
			$("#basictext").val(textbox+unshift);
		});
	};
	showAudioDialog = function(elm, base64options, options) {
		// require(["util"], function(util){
			var btn = $(elm);
			var ipt = btn.parent().prev();
			var val = ipt.val();
			util.audio(val, function(url){
				if(url && url.attachment && url.url){
					btn.prev().show();
					ipt.val(url.attachment);
					ipt.attr("filename",url.filename);
					ipt.attr("url",url.url);
					setAudioPlayer();
				}
				if(url && url.media_id){
					ipt.val(url.media_id);
				}
			}, "" , "");
		// });
	};
	setAudioPlayer = function(){
		// require(["jquery", "util", "jquery.jplayer"], function($, u){
			$(function(){
				$(".audio-player").each(function(){
					$(this).prev().find("button").eq(0).click(function(){
						var src = $(this).parent().prev().val();
						if($(this).find("i").hasClass("fa-stop")) {
							$(this).parent().parent().next().jPlayer("stop");
						} else {
							if(src) {
								$(this).parent().parent().next().jPlayer("setMedia", {mp3: util.tomedia(src)}).jPlayer("play");
							}
						}
					});
				});

				$(".audio-player").jPlayer({
					playing: function() {
						$(this).prev().find("i").removeClass("fa-play").addClass("fa-stop");
					},
					pause: function (event) {
						$(this).prev().find("i").removeClass("fa-stop").addClass("fa-play");
					},
					swfPath: "resource/components/jplayer",
					supplied: "mp3"
				});
				$(".audio-player-media").each(function(){
					$(this).next().find(".audio-player-play").css("display", $(this).val() == "" ? "none" : "");
				});
			});
		// });
	};
	return material;
});