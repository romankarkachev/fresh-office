$(document).ready(function() {
    // делаем запрос на сервер, который вернет массив, содержащий количество полноценно скачанных писем в каждом ящике
    $.get("/cemail/calc-mailboxes-messages-count", function (retval) {
        $.each(retval, function (index, element) {
            $span = $("#mbcb" + element.mailbox_id);
            badgeClass = " badge-danger";
            if (element.is_active == 0) {
                badgeClass = " badge-important";
            }

            $span.replaceWith('<span class="badge' + badgeClass + '" title="' + element.messagesCount + ' (если плохо видно)">' + element.messagesCount + '</span>');
        });

        // остальные просто очищаем, чтобы ничего не крутилось (значит нет в них писем)
        $("small[id ^= 'mbcb']").replaceWith("");
    });
});
