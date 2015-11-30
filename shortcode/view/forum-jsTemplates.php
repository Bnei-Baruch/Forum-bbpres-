<script id="singleTopicTpl" type="text/x-handlebars-template">
<div>
	{{{getSinglForumPost}}}
	<div class="replyListContainer" data-topic-index = {{index}}>
		{{#each replyList}}
			{{{getSinglForumPost}}}
		{{/each}}
		{{{getEditReply}}}		
	</div>
</div>
</script>

<script id="forumPostTpl" type="text/x-handlebars-template">
<div class="forumItem"  data-topic-index = "{{topicId}}" data-item-index = "{{itemId}}" data-postType="{{type}}">
	<div class="header">
		<div class="photo">
			<a href="{{autor.url}}"> {{{autor.avatar}}} </a>
		</div>
		<div class="info">
			<div class="name">
				<a href="{{autor.url}}"> {{autor.name}} </a>
			</div>
			<div class="date">{{sDate}}</div>
		</div>
	</div>
	<div class="forumItemCont">
		{{{sContentShort}}}
		<div style="display: none;">{{{sContent}}}</div>
	</div>

	<div class="attachment">
		{{#each attachmentList}}
		<div>{{{getAttachment}}}</div>
		{{/each}}
	</div>
	{{#if like }}
	<div class="actions">
		<a class="like" href="#">{{translation.like}}</a>
		<a class="like dislike" href="#">{{translation.dislike}}</a>
		<div class="like-count">
			<i class="like-img"></i><span class="count">{{like}}</span>
		</div>
	</div>
	{{/if}}
</div>
</script>

<script id="attachmentsTpl" type="text/x-handlebars-template">
<div class="single_topic_single_attachment">
	<div class="attachment-image">
		<a target="_blank" href="{{urlToFull}}"> {{{img}}} </a>
	</div>
	<div class="attachment-controls">
		<a class="delete-attachment" href="#">Удалить</a>
	</div>
</div>
</script>

<script id="editTopicTpl" type="text/x-handlebars-template">	
<div class="edit">
	<textarea class="edit_content">{{{sContent}}}</textarea>
	<div class="edit_actions">
		<a class="loadImg" href="#"></a>
		<button class="cancel">{{translation.cancel}}</button>
		<button class="save">{{translation.save}}</button>
	</div>
</div>
</script>

<script id="editReplyTpl" type="text/x-handlebars-template">	
<div class="reply_content_edit">
	<textarea class="reply_content_edit_textarea">{{{sContent}}}</textarea>
		<a href="#" class="smiles_open"></a>
		{{#if isEditMode}}
		<div class="edit_actions">
			<a class="cancel" href="#">Отменить</a>
		</div>
		{{/if}}
	</div>
</div>
</script>