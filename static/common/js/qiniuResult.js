/*
 *   本示例演示七牛云存储表单上传
 *
 *   按照以下的步骤运行示例：
 *
 *   1. 填写token。需要您不知道如何生成token，可以点击右侧的链接生成，然后将结果复制粘贴过来。
 *   2. 填写key。如果您在生成token的过程中指定了key，则将其输入至此。否则留空。
 *   3. 姓名是一个自定义的变量，如果生成token的过程中指定了returnUrl和returnBody，
 *      并且returnBody中指定了期望返回此字段，则七牛会将其返回给returnUrl对应的业务服务器。
 *      callbackBody亦然。
 *   4. 选择任意一张照片，然后点击提交即可
 *
 *   实际开发中，您可以通过后端开发语言动态生成这个表单，将token的hidden属性设置为true并对其进行赋值。
 *
 *  **********************************************************************************
 *  * 贡献代码：
 *  * 1. git clone git@github.com:icattlecoder/jsfiddle
 *  * 2. push代码到您的github库
 *  * 3. 测试效果，访问 http://jsfiddle.net/gh/get/jquery/1.9.1/<Your GitHub Name>/jsfiddle/tree/master/ajaxupload
 *  * 4. 提pr
 *   **********************************************************************************
 */
$(document).ready( function() {
    var Qiniu_UploadUrl = "http://up.qiniu.com";
	var selfFormData = new Object();
    var progressbar = $("#progressbar"),
        progressLabel = $(".progress-label");
    progressbar.progressbar({
        value: false,
        change: function() {
            progressLabel.text(progressbar.progressbar("value") + "%");
            progressLabel.css("left", '40%');
            progressLabel.css("color", '#000');
        },
        complete: function() {
            progressLabel.text("等待上传中...");
            progressLabel.css("left", '40%');
            progressLabel.css("color", '#000');
        }
    });
    $("#btn_upload").click(function() {
		if ($("input[name='team_level']").length > 0) {
			var team_level = $("input[name='team_level']:checked").val();
			if(!team_level) {
				alert('团队组别不能为空');
				return false;
			}
			selfFormData.team_level = team_level;
		}
		
		if ($("input[name='problem_number']").length > 0) {
			var problem_number = $("input[name='problem_number']:checked").val();
			if(!problem_number) {
				alert('题目选择不能为空');
				return false;
			}
			selfFormData.problem_number = problem_number;
		}
		
		if (!$("#file")[0].files.length) {
			alert('上传文件不能为空');
			return false;
		}
        //普通上传
        var Qiniu_upload = function(f, token, key) {
            var xhr = new XMLHttpRequest();
            xhr.open('POST', Qiniu_UploadUrl, true);
            var formData, startDate;
            formData = new FormData();
            if (key !== null && key !== undefined) formData.append('key', key);
            formData.append('token', token);
            formData.append('file', f);
            var taking;
            xhr.upload.addEventListener("progress", function(evt) {
                if (evt.lengthComputable) {
                    var nowDate = new Date().getTime();
                    taking = nowDate - startDate;
                    var x = (evt.loaded) / 1024;
                    var y = taking / 1000;
                    var uploadSpeed = (x / y);
                    var formatSpeed;
                    if (uploadSpeed > 1024) {
                        formatSpeed = (uploadSpeed / 1024).toFixed(2) + "Mb\/s";
                    } else {
                        formatSpeed = uploadSpeed.toFixed(2) + "Kb\/s";
                    }
                    var percentComplete = Math.round(evt.loaded * 100 / evt.total);
                    progressbar.progressbar("value", percentComplete);
                    // console && console.log(percentComplete, ",", formatSpeed);
                }
            }, false);

            xhr.onreadystatechange = function(response) {
                if (xhr.readyState == 4 && xhr.status == 200 && xhr.responseText != "") {
                    var blkRet = JSON.parse(xhr.responseText);
					var key = blkRet.key;
					selfFormData.upload_file = key;
					console.log(selfFormData);
					$.ajax({
						url: $("#selfPostForm").attr('action'),
						type: 'POST',
						data:selfFormData,
						dataType: 'json',
						timeout: 1000,
						error: function(){alert('Error loading PHP document');},
						success: function(result){alert(result.message); location.reload();}
						});
                    //console && console.log(blkRet);
                    //$("#dialog").html(xhr.responseText).dialog();
                } else if (xhr.status != 200) {
                	progressLabel.text("文件类型不符或者文件大小超限请重新上传!");
                	progressLabel.css("left", 0);
                	progressLabel.css("color", '#F00');
                }
            };
            startDate = new Date().getTime();
            $("#progressbar").show();
            xhr.send(formData);
        };
        var token = $("#token").val();
		var tname = $("#file")[0].files[0].name;
		var ext =/\.[^\.]+/.exec(tname);
		selfFormData.ext = ext;
        var key = null;
        if ($("#key").length > 0) {
             key = $("#key").val();
             key = key + ext;
        }
        if ($("#file")[0].files.length > 0 && token != "") {
            Qiniu_upload($("#file")[0].files[0], token, key);
        } else {
            $("#dialog").html('上传文件不能为空').dialog();
        }
    })
})