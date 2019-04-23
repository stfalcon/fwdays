var InlineUpload = {
    dialog: null,
    options: {
        form_class: 'inline_upload_form',
        action: '/posts/upload',
        iframe: 'inline_upload_iframe'
    },
    display: function(hash) {
        var self = this;

        this.dialog = $(document).find(".inline_upload_container");

        if (!this.dialog.size()) {
            // Create invisible form and iframe
            this.dialog = $([
                '<div style="opacity:0;position:absolute;" class="inline_upload_container"><form class="',this.options.form_class,'" action="',this.options.action,'" target="',this.options.iframe,'" method="post" enctype="multipart/form-data">',
                '<input name="upload_file" type="file" /></form>' +
                '<iframe id="',this.options.iframe,'" name="',this.options.iframe,'" class="',this.options.iframe,'" src="about:blank" width="0" height="0"></iframe></div>',
            ].join(''));
            this.dialog.appendTo(document.body);
        }

        // make 'click' action on file element right after 'Picture' selection on markItUp menu
        // to show system dialog
        $("input[name='upload_file']").focus();
        $("input[name='upload_file']").trigger('click');

        // submit hidden form after file was selected in system dialog
        $("input[name='upload_file']").on('change', function(){
            if ($(this).val() != '') {
                $('.' + self.options.form_class).submit();
            }
        });

        // response will be sent to the hidden iframe
        $('.' + this.options.iframe).bind('load', function() {
            var responseJSONStr = $(this).contents().text();
            if (responseJSONStr != '') {
                var response = $.parseJSON(responseJSONStr);
                if (response.status == 'success') {
                    var block = ['<img src="' + response.src + '" width="' + response.width + '" height="' + response.height + '" alt="" class=""/>'];
                    $.markItUp({replaceWith: block.join('')} );
                } else {
                    alert(response.msg);
                }
                self.cleanUp();
            }
        });
    },
    cleanUp: function() {
        $("input[name='upload_file']").off('change');
        this.dialog.remove();
    }
};

var InsCode = function()
{
	return {
		display: function(markItUp) {
			console.log(markItUp);
			var textareaID = markItUp.textarea.id;
			var textarea = $('#' + textareaID);
			var output = '';
			$('#ins-code-form').remove();
			$.get(markItUp.root + 'InsCode/InsCode.html', function (data) {
				$('body').append(data);
				var form = $('#ins-code-form');

				form.css({
					'top': textarea.position().top + 5,
					'left': textarea.closest('.markItUpContainer').find('li.ins-code-button').position().left - 322
				});
				form.find('#ins-code-code').val(markItUp.selection);

				form.find('a.cancel').click(function (e) {
					e.preventDefault();
					form.fadeOut(function () {
						form.remove();
					});
				});

				form.fadeIn();

				form.on('submit', function(e) {
					e.preventDefault();
					$.markItUp({target: '#' + textareaID, openWith: '<pre lang="' + $(this).find('#ins-code-language').val() + '">', closeWith: '</pre>', placeHolder:$(this).find('#ins-code-code').val()});
					form.fadeOut(function () {
						form.remove();
					});
				});
			});
		}
	}
}();

mySettings = {
    onShiftEnter:	{keepDefault:false, replaceWith:'<br />\n'},
    onCtrlEnter:	{keepDefault:false, openWith:'\n<p>', closeWith:'</p>\n'},
    onTab:			{keepDefault:false, openWith:'	 '},
    markupSet: [
        {name:'Heading 1', key:'1', openWith:'<h1(!( class="[![Class]!]")!)>', closeWith:'</h1>', placeHolder:'Your title here...' },
        {name:'Heading 2', key:'2', openWith:'<h2(!( class="[![Class]!]")!)>', closeWith:'</h2>', placeHolder:'Your title here...' },
        {name:'Heading 3', key:'3', openWith:'<h3(!( class="[![Class]!]")!)>', closeWith:'</h3>', placeHolder:'Your title here...' },
        {name:'Heading 4', key:'4', openWith:'<h4(!( class="[![Class]!]")!)>', closeWith:'</h4>', placeHolder:'Your title here...' },
        {name:'Heading 5', key:'5', openWith:'<h5(!( class="[![Class]!]")!)>', closeWith:'</h5>', placeHolder:'Your title here...' },
        {name:'Heading 6', key:'6', openWith:'<h6(!( class="[![Class]!]")!)>', closeWith:'</h6>', placeHolder:'Your title here...' },
        {name:'Paragraph', openWith:'<p(!( class="[![Class]!]")!)>', closeWith:'</p>' },
        {separator:'---------------' },
        {name:'Bold', key:'B', openWith:'(!(<strong>|!|<b>)!)', closeWith:'(!(</strong>|!|</b>)!)' },
        {name:'Italic', key:'I', openWith:'(!(<em>|!|<i>)!)', closeWith:'(!(</em>|!|</i>)!)' },
        {name:'Stroke through', key:'S', openWith:'<del>', closeWith:'</del>' },
        {separator:'---------------' },
        {name:'Ul', openWith:'<ul>\n', closeWith:'</ul>\n' },
        {name:'Ol', openWith:'<ol>\n', closeWith:'</ol>\n' },
        {name:'Li', openWith:'<li>', closeWith:'</li>' },
        {separator:'---------------' },

        //{name:'PictureSimple', key:'P', replaceWith:'<img src="[![Source:!:http://]!]" alt="[![Alternative text]!]" />' },
        {
            name:'Picture',
            key:'P',
            beforeInsert: function(markItUp) { InlineUpload.display(markItUp) }
        },

        {name:'Link', key:'L', openWith:'<a href="[![Link:!:http://]!]"(!( title="[![Title]!]")!)>', closeWith:'</a>', placeHolder:'Your text to link...' },
        {separator:'---------------' },
        {name:'Clean', className:'clean', replaceWith:function(markitup) { return markitup.selection.replace(/<(.*?)>/g, "") } },
        {name:'Preview', className:'preview', call:'preview' },
        {separator:'---------------' },
        {name:'More', className:'mMore', key:'M', openWith:'\n<!--more-->\n'},
        {name:'Code',className:'ins-code-button', key:'D', replaceWith: function(markItUp) { InsCode.display(markItUp) }}
    ]
};

$(document).ready(function() {
    $("textarea.markitup").markItUp(mySettings);
});

