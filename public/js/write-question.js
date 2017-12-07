var QUESTION_URL = "http://localhost/ksc/api/Controllers/Question.php";

$(document).ready(function () {
    // 태그 입력할 때, 엔터키를 누르면 태그 라벨이 추가된다.
    $("input").on("keyup", function (e) {
        if (e.which === 13 && $("li.tags").length < 5) {
            var tagTemplateScript = $("#tag-template").html();
            var tagTemplate = Handlebars.compile(tagTemplateScript);

            $("ul.tags").append(tagTemplate($(this).val()));
            $(this).val("");
        }
    })

    // 드롭다운 이벤트
    var $dropdownMenu = $("ul.dropdown-menu");
    var $categorySpan = $("button>span.top");
    $dropdownMenu.on("click", "li", function (e) {
        this.text($(e.currentTarget).find("a").text());
    }.bind($categorySpan));

    // 질문 등록하기
    $("#btn-post-question").on("click", function () {
        var title = $("div.write.header>textarea").val();
        var content = $("div.write.content>textarea").val();
        var tags = $("li.tags>span").map(function (i, element) {
            return element.innerHTML;
        });
        var category = $categorySpan.text();

        $.ajax(QUESTION_URL, {
            type: "POST",
            contentType: "application/x-www-form-urlencoded",
            data: {
                mydata: {
                    category: category,
                    title: title,
                    content: content,
                    tags: "얍얍얍",
                    user_id: "jylee"
                }
            }
        }).then(function (res) {
            var result = JSON.parse(res);
            alert(result["message"]);
            if(result["success"]){
                location.href = "/";
            }
        })
    })
});