(function($) {
	var templates, postList = [];
	$(document).ready(function(){
		_init();
	});	
	function _init() {
		$topicContainer = $("#topicContainer");
		_buildTemplates();
		loadFromTo();
		addBinds();
		
	}
	function addBinds() {
		$('#topicContainer').on('click', 'button', function(){loadFromTo();});
		$('.openEditMenu')
			//.off('click', '.openEdit')
			.on('click', '.openEdit', function (e) {
				$(this).parents('.openEditMenu').toggleClass('open')
			});

		$('.openEditMenu')
		//.off('click', '[data-action="edit"]')
		.on('click', '[data-action="edit"]', openEditPost);
	
		$('#forumAddTopic').on('click', '.submit', function(e){
			var item = {
				'action' : 'forum_saveEditPost',
				'content' : $(this).find('textarea').html(),
				'postItemId': -1,
				'postItemType': 'topic'
			};
			saveEditPost(item);
		});
	
		
	}
	
	function addPost() {

	}
	function openEditPost(e) {
		var $container, item, $html; 
		$container = $(this).parents('.forumItem');
		if($container.hasClass('inEdit'))
			return;
			
		$container.addClass('inEdit');
		if($container.attr('data-postType') === 'topic'){
			item = postList[$container.attr('data-topic-index')];
			$html = $(templates.editTopic(item));
		} else {
			item = postList[$container.attr('data-topic-index')].replyList[$container.attr('data-item-index')];
			$html = $(templates.editReply(item));
			/*if(item.isEditMode)
				$html = $(templates.editReply(item));
			else
				$html = $(templates.editReply());*/
		}
		$container.append($html);
		$html.on('keypress', 'textarea', function(e){
			var key;
			key = e.which;
			if (key != 13 && key != 10)
				return;
			$html.off('keypress', 'textarea');
			e.preventDefault();
			$container.removeClass('inEdit');
			saveEditPost(item);
			$container.remove('.edit');
		});
	}
	function saveEditPost(item){
		var param = {
			'action' : 'forum_saveEditPost',
			'content' : item.sContent,
			'postItemId': item.id,
			'postItemType': item.type
		};
		_post(param, function(rStr) {
			var r = JSON.parse(rStr)
			r.forEach(function(t, key) {
				t.index = key;
				postList.push(t);
				var $html = $(templates.topic(t));
				$topicContainer.append($html);
			});
			//addBinds();
		});
	}
	function deletePost() {

	}

	function loadFromTo(from, to) {
		var param = {
			'action' : 'forum_getTopicList',
			'param' : {
				'from' : from || 0,
				'to' : to || 20
			}
		};
		_post(param, function(rStr) {
			var r = JSON.parse(rStr)
			r.forEach(function(t, key) {
				t.index = key;
				postList.push(t);
				var $html = $(templates.topic(t));
				$topicContainer.append($html);
			});
			//addBinds();
		});
	}

	function _buildTemplates() {
		templates = {
			'topic': Handlebars.compile($('#singleTopicTpl').html()),
			'forumPost': Handlebars.compile($('#forumPostTpl').html()),
			'attaches': Handlebars.compile($('#attachmentsTpl').html()),
			'editReply': Handlebars.compile($('#editReplyTpl').html()),
			'editTopic': Handlebars.compile($('#editTopicTpl').html())
		};

		Handlebars.registerHelper('getEditReply', function() {
			this.isEditMode = false;
			this.sContent = '';
			return templates.editReply(this);
		});
		Handlebars.registerHelper('getAttachment', function() {
			return templates.attaches(this);
		});
		Handlebars.registerHelper('getSinglForumPost', function(context) {			
			if(!this.type == 'reply'){
				this.itemId = context.data.index;
				this.topicId = context.data._parent.root.index;
			} else {
				this.topicId = this.index;				
			} 
			return templates.forumPost(this);
		});
	}

	function _post(data, callback) {
		$.post(ajaxurl, data).done(callback).fail(function(r) {
			console.log(r);
			alert("error");
		});
	}

}(jQuery));
/*
(function($) {
	$(document)
			.ready(
					function() {
						// $('#tabii2slider').liquidSlider();
						// autosize($('.add_topic_form textarea'));

						/** Bind events * /
						$(".reply_content_edit_textarea").on('keypress',
								keypress_replies_handler);
						$('.single_topic_reply_form .reply-form ').on(
								"keypress", "textarea",
								custom_bbp_reply_create_keypress);

						$('.single_topic_reply').on('click',
								".addi_actions_open", single_topic_reply_open);
						$('.single_topic_header').on('click',
								".addi_actions_open", addi_actions_open);

						$(".single_topic_content_edit .edit_actions").on(
								'click', '.save', single_topic_content_edit);
						$(".single_topic_reply").on('click', '.remove_action',
								single_topic_reply_remove);
						$('.single_topic_header').on('click', ".remove_action",
								single_topic_header_remove);

						$(".reply_content_edit").on('click', '.cancel',
								reply_content_edit_cancel);
						$(".single_topic_content_edit").on('click', ' .cancel',
								single_topic_content_edit_cancel);

						$(".single_topic_header").on('click', '.edit_action',
								single_topic_header_edit);
						$(".single_topic_reply").on('click', '.edit_action',
								single_topic_reply_edit);

						$(".single_topic_reply_form").on('click',
								'.smiles_open',
								single_topic_reply_form_smiles_open);
						$(".reply_content_edit").on('click', '.smiles_open',
								reply_content_edit_smiles_open);

						$(".single_topic_actions, .single_topic_reply").on(
								'click', '.like', single_topic_actions_like);

						$(".single_topic_reply_form").on('click', '.smile',
								smiles_list);

						$(".single_topic_content .show_all").on('click',
								show_all);
						$(".load_all_replies").on('click', load_all_replies);
						$(".load_more_topics").on('click', load_more_topics);

						$(document)
								.on(
										'click',
										function(e) {
											if (!$(e.target).hasClass('opened')) {
												$(
														'.single_topic_header .addi_actions')
														.hide();
												$(
														'.single_topic_header .addi_actions_open.opened')
														.removeClass('opened');
											}
											if ($(e.target).closest(
													'.smiles_list').length == 0
													&& !$(e.target).hasClass(
															'smiles_open'))
												$('.reply-form .smiles_list')
														.hide();
										});
					});

	var opts = {
		on : {
			load : function(e, file) {
				var upl = $("#image-uploader"), type = upl.data('type'), id = upl
						.data('id'), name = file.extra.nameNoExtension, ext = file.extra.extension;

				if (ext != 'jpg' && ext != 'jpeg' && ext != 'png'
						&& ext != 'gif') {
					return;
				}
				$("#popUpForum").show()
				$
						.post(
								customjs.ajaxurl,
								{
									'action' : 'upload-forum-file',
									'type' : type,
									'file' : e.target.result,
									'id' : id,
									'ext' : ext,
									'name' : name
								},
								function(response) {
									$("#popUpForum").hide();
									var input = '';

									if (id == '') {
										if (type == 'post') {
											input = $('.add_topic_form_container .attaches-input');
											$(
													'.add_topic_form_container .add_topic_form_files')
													.append(response.content);
										} else {
											input = $('.single_topic_reply_form .attaches-input');
											var topicId = $("#image-uploader")
													.attr('data-topicId');
											$('#topic-' + topicId).find(
													'.add_reply_form_files')
													.append(response.content);
										}
									} else {
										if (type == 'post') {
											input = $('#topic-' + id
													+ ' .attaches-input');
											$(
													'#topic-'
															+ id
															+ ' .single_topic_attaches')
													.append(response.content);
										} else {
											input = $('#reply-' + id
													+ ' .attaches-input');
											$(
													'#reply-'
															+ id
															+ ' .single_reply_attaches')
													.append(response.content);
										}
									}

									input.val((input.val() != '') ? input.val()
											+ ',' + response.id : response.id);

								}, 'json').fail(function(error) {
							$("#popUpForum").hide();
							// $("#popUpForum").show().html(response);
							alert('The image is too big.');
						});
			}
		}
	};

	$("#image-uploader").fileReaderJS(opts);
	$(".add_topic_form_actions").on("click", "button", custom_bbp_topic_create)
			.on('click', ".image-load", function(e) {
				e.preventDefault();
				$("#image-uploader").attr({
					value : ''
				}).data('type', 'post').data('id', '').click();
			});
	$(".single_topic_reply_form").on(
			'click',
			".image-load",
			function(e) {
				e.preventDefault();
				var topicId = $(this).closest(".topics_list_single_topic")
						.attr('data-id');
				$("#image-uploader").attr({
					value : ''
				}).data('type', 'comment').data('id', '').attr('data-topicId',
						topicId).click();
			});

	$(".single_topic_content_edit").on(
			'click',
			".image-load",
			function(e) {
				e.preventDefault();
				$("#image-uploader").attr({
					value : ''
				}).data('type', 'post')
						.data(
								'id',
								$(this).closest(".topics_list_single_topic")
										.data('id')).click();
			});
	$(".single_topic_reply").on(
			'click',
			".image-load",
			function(e) {
				e.preventDefault();

				$("#image-uploader").attr({
					value : ''
				}).data('type', 'comment').data('id',
						$(this).closest(".single_topic_reply").data('id'))
						.click();
			});

	$(".add_topic_form_container").on('click', ".delete-attachment",
			function(e) {
				e.preventDefault();
				deleteAttachment.apply(this);
			});

	$(".single_topic_replies_container").on('click', ".delete-attachment",
			function(e) {
				e.preventDefault();
				var id = $(this).data('id');
				var ptid = $(this).closest('.single_topic_reply').data('id');
				$("#popUpForum").show()
				$.post(customjs.ajaxurl, {
					'action' : 'delete-attachment',
					'id' : id,
					'type' : 'comment',
					'ptid' : ptid
				}, function(response) {
					$("#popUpForum").hide()
				}, 'json');
				deleteAttachment.apply(this);
			});

	function deleteAttachment() {
		$(this).closest('.single_topic_reply_form').find('.attaches-input')
				.val(
						input.val().replace(new RegExp($(this).data('id')), '')
								.replace(new RegExp(',$'), '').replace(
										new RegExp('^,'), '').replace(
										new RegExp(',,'), ','));

		$(this).closest('.single_reply_single_attachment').remove();
	}

	/** * Binded functions *  /

	function keypress_replies_handler(e) {
		var key, $this;
		key = e.which;
		if (key != 13 && key != 10)
			return;
		$this = $(this);
		if (e.ctrlKey) {
			$this.val($this.val() + "\r\n").trigger('autosize.resize');
		} else {
			var reply = $this.closest('.single_topic_reply');
			$("#popUpForum").show();
			$.post(customjs.ajaxurl, {
				'action' : 'update-reply-custom',
				'id' : reply.data('id'),
				'content' : reply.find('.reply_content_edit_textarea').val(),
				'attaches' : reply.find('.attaches-input').val()
			}, function(response) {
				$("#popUpForum").hide();
				if (response.result == 'OK') {
					reply.find('.reply_content_edit').hide();
					var link = reply.find('.reply_content a').clone();

					reply.find('.reply_content').html(
							link.prop('outerHTML') + response.content).show();
					reply.find('.actions').show();
				}
			}, 'json');
		}
	}
	function custom_bbp_topic_create(e) {
		e.preventDefault();
		$el = $(e.currentTarget);
		$("#popUpForum").show();
		$.post(customjs.ajaxurl, {
			'action' : 'custom_bbp_topic_create',
			'bbp_forum_id' : $el.parents("form").attr("data-bbp_forum_id"),
			'content' : $el.parents("form").find("textarea[name=content]")
					.val(),
			'attaches' : $el.parents("form").find('.attaches-input').val()
		}, function(r) {
			$("#popUpForum").hide();
			if (jQuery.isNumeric(r)) {
				location.reload();
			}
		}, 'json');
	}
	function custom_bbp_reply_create_keypress(e) {
		var key, $this;
		key = e.which;
		if (key != 13 && key != 10)
			return;

		e.preventDefault();
		$this = $(this);

		if (e.ctrlKey)
			$this.val($this.val() + "\r\n").trigger('autosize.resize');
		else {
			e.preventDefault();
			custom_bbp_reply_create(e);
		}
	}
	function custom_bbp_reply_create(e) {
		var $el = $(e.currentTarget).parents("form");
		$("#popUpForum").show();
		$.post(customjs.ajaxurl, {
			'action' : 'custom_bbp_reply_create',
			'bbp_topic_id' : $el.attr('data-bbp_topic_id'),
			'bbp_forum_id' : $el.attr('data-bbp_forum_id'),
			'content' : $el.find('.reply-form textarea').val(),
			'attaches' : $el.find('.attaches-input').val()
		}, function(r) {
			$("#popUpForum").hide();
			if (jQuery.isNumeric(r)) {
				location.reload();
			}
		}, 'json');
	}

	function addi_actions_open(e) {
		e.preventDefault();
		var wind = $(this).closest('.single_topic_header')
				.find('.addi_actions');

		if (!$(this).hasClass('opened')) {
			$(".single_topic_header, .single_topic_reply")
					.find(".addi_actions").hide();
			$(".single_topic_header, single_topic_reply").find(
					".addi_actions_open.opened").removeClass("opened");
			wind.show();
			$(this).addClass('opened');
		} else {
			wind.hide();
			$(this).removeClass('opened');
		}
	}
	function single_topic_header_remove(e) {
		e.preventDefault();

		var topic = $(this).closest('.topics_list_single_topic');
		$("#popUpForum").show();
		$.post(customjs.ajaxurl, {
			'action' : 'remove-topic-custom',
			'id' : topic.data('id')
		}, function(response) {
			$("#popUpForum").hide();
			if (response.result == 'OK') {
				topic.remove();
			}
		}, 'json');
	}
	function single_topic_reply_open(e) {
		e.preventDefault();

		var wind = $(this).closest('.single_topic_reply').find('.addi_actions');

		if (!$(this).hasClass('opened')) {
			$('.single_topic_reply .addi_actions').hide();
			$('.single_topic_reply .addi_actions_open.opened').removeClass(
					'opened');
			$('.single_topic_header .addi_actions').hide();
			$('.single_topic_header .addi_actions_open.opened').removeClass(
					'opened');
			wind.show();
			$(this).addClass('opened');
		} else {
			wind.hide();
			$(this).removeClass('opened');
		}
	}

	function single_topic_reply_remove(e) {
		e.preventDefault();

		var reply = $(this).closest('.single_topic_reply');
		$("#popUpForum").show();
		$.post(customjs.ajaxurl, {
			'action' : 'remove-reply-custom',
			'id' : reply.data('id')
		}, function(response) {
			$("#popUpForum").hide();
			if (response.result == 'OK') {
				reply.remove();
			}
		}, 'json');
	}
	function show_all(e) {
		e.preventDefault();
		var content = $(this).closest('.single_topic_content');
		content.find('.show').hide();
		content.find('.hide').show();
	}
	function single_topic_content_edit(e) {
		e.preventDefault();

		var topic = $(this).closest('.topics_list_single_topic');
		$("#popUpForum").show();
		$.post(customjs.ajaxurl, {
			'action' : 'update-topic-custom',
			'id' : topic.data('id'),
			'content' : topic.find('.single_topic_content_edit .edit_content')
					.val(),
			'attaches' : topic.find(
					'.single_topic_content_edit .attaches-input').val()
		}, function(response) {
			$("#popUpForum").hide();
			if (response.result == 'OK') {
				topic.find('.single_topic_content .show').hide();
				topic.find('.single_topic_content_edit').hide();
				topic.find('.single_topic_content .hide')
						.html(response.content).show();
				topic.find('.single_topic_content').show();
			}
		}, 'json');
	}
	function single_topic_content_edit_cancel(e) {
		e.preventDefault();

		var topic = $(this).closest('.topics_list_single_topic');

		topic.find('.single_topic_content_edit').hide();
		topic.find('.single_topic_content').show();
	}
	function reply_content_edit_cancel(e) {
		e.preventDefault();

		var reply = $(this).closest('.single_topic_reply');

		reply.find('.reply_content_edit').hide();
		reply.find('.reply_content').show();
		reply.find('.content_wrapper > .actions').show();
	}

	function single_topic_header_edit(e) {
		e.preventDefault();

		var topic = $(this).closest('.topics_list_single_topic');

		topic.find('.single_topic_content').hide();
		topic.find('.single_topic_content_edit').show();
		topic.find('.single_topic_content_edit textarea').trigger(
				'autosize.resize');
	}

	function single_topic_reply_edit(e) {
		e.preventDefault();

		var reply = $(this).closest('.single_topic_reply');

		reply.find('.reply_content').hide();
		reply.find('.actions').hide();
		reply.find('.reply_content_edit').show();
		reply.find('.reply_content_edit textarea').trigger('autosize.resize');
	}
	function single_topic_reply_form_smiles_open(e) {
		e.preventDefault();

		var smiles_list = $(e.target).closest('.reply-form').find(
				'.smiles_list');

		if (smiles_list.length == 0) {
			$(e.target).after($('.smiles_list').first().clone());
			smiles_list = $(e.target).closest('.reply-form').find(
					'.smiles_list');
		}

		if (!smiles_list.is(':visible'))
			smiles_list.show();
		else
			smiles_list.hide();
	}
	function reply_content_edit_smiles_open(e) {
		e.preventDefault();

		var smiles_list = $(e.target).closest('.reply_content_edit').find(
				'.smiles_list');

		if (smiles_list.length == 0) {
			$(e.target).after($('.smiles_list').first().clone());
			smiles_list = $(e.target).closest('.reply_content_edit').find(
					'.smiles_list');
		}
		if (!smiles_list.is(':visible'))
			smiles_list.show();
		else
			smiles_list.hide();
	}
	function smiles_list(e) {
		e.preventDefault();

		var val = $(this).data('replace');

		var textarea = $(this).parent().siblings('textarea');

		textarea.focus();
		textarea.val(textarea.val() + val);
	}
	function single_topic_actions_like(e) {
		e.preventDefault();

		var $this = $(this);
		var $parent = $this.closest('.topics_list_single_topic');
		if (!$parent)
			$parent = $this.closest('.single_topic_reply');

		var id = $parent.data('id');

		$("#popUpForum").show();
		$.post(customjs.ajaxurl, {
			'action' : 'like-custom',
			'id' : id
		}, function(response) {
			$("#popUpForum").hide();
			if (response.result == 'OK') {
				var needles = $this.closest('.single_topic_actions');

				if (response.count == 0) {
					needles.find('.like-count').hide();
				} else {
					needles.find('.like-count').show();
				}

				needles.find('.count').text(response.count);

				if ($this.hasClass('dislike')) {
					$this.hide();
					$this.prev().show();
				} else {
					$this.hide();
					$this.next().show();
				}
			}
		}, 'json');
	}
	function load_all_replies(e) {
		e.preventDefault();

		var id = $(this).closest('.topics_list_single_topic').data('id'), $this = $(this), cont = $this
				.parent();

		$this.remove();
		$("#popUpForum").show();
		$.post(customjs.ajaxurl, {
			'action' : 'load-all-replies',
			'id' : id
		}, function(response) {
			$("#popUpForum").hide();
			if (response.result == 'OK') {
				cont.prepend(response.content);
			}
		}, 'json');
	}
	function load_more_topics(e) {
		e.preventDefault();

		var forum = $(this).closest('.topics_list').data('forum'), list = $(
				this).closest('.topics_list'), $this = $(this), cont = $this
				.parent();

		$this.remove();
		$("#popUpForum").show();
		$.post(customjs.ajaxurl, {
			'action' : 'load-more-topics',
			'forum' : forum,
			'list' : list.data('list')
		}, function(response) {
			$("#popUpForum").hide();
			if (response.result == 'OK') {
				cont.append(response.content);

				list.data('list', parseInt(list.data('list')) + 1)
			}
		}, 'json');
	}
})(jQuery);*/