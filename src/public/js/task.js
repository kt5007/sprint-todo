$(function () {
    $('.btn').click(function(){
        let id = $(this).attr("id");
        let name = $(this).attr("data-task-title");
        $(".copy-message").html(name + "を最新のスプリントに複製しますか。");
        let res = id.replace(/[^0-9]/g, '');
        $("#copy-task-form").attr("action", "/task/copy/" + res);
        console.log('clicked');
    });
});
