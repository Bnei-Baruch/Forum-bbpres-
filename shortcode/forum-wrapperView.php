<div class="topics_list_divider"><?php _e('Недавние обсуждения', 'qode'); ?></div>
<style>
.column2 {
	position: relative;
}

#popUpForum {
	position: absolute;
	top: 0;
	bottom: 0;
	left: 0;
	right: 0;
	background: #FFF url("./ajax-loader.gif") center center no-repeat;
	opacity: 0.8;
	display: none;
	z-index: 10;
}
</style>

<div id="popUpForum"></div>
<span class="button big" id=test>tsts</span>
<div class="add_topic_form_container" id="forumAddTopic"> 
	<form action="" method="post" data-bbp_forum_id="<?php echo $forum_id ?>">
		<div class="add_topic_form_header">
			<div class="publication"><i class="icon"></i><?php _e('Publication', 'qode'); ?></div>
		</div>
		<div class="add_topic_form">
			<textarea placeholder="<?php _e('Введите текст сообщения...', 'qode'); ?>" name="content"></textarea>
		</div>
		<div class="add_topic_form_actions">
			<div class="attachment"></div>
			<div class="btn submit"><?php _e('Publish', 'qode'); ?></button>
		</div>
	</form>
</div>
<div class="topics_list_single_topic"
	id="topic-<?php echo bbp_get_topic_id(); ?>"
	data-id="<?php echo bbp_get_topic_id(); ?>"></div>
<a class="load_more_topics" href="#">Просмотреть больше обсуждений</a>