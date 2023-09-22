$(function () {
    // 削除ボタンを押した際の動作
    $(".delete-user").click(function () {
        let id = $(this).attr("data-id");
        let name = $(this).attr("data-name");
        $("#delete-form").attr("action", "/user/delete/" + id);
        $("#delete-message").html(name + "を削除しますか。");
    });
    console.log('read');
});
