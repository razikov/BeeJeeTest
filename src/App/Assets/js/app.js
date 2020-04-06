
function showError(e) {
    alert(e.responseText);
}

function showModal(_modal, options) {
    options.beforeShow(_modal, options);
    _modal.modal();
    _modal.on("hidden.bs.modal", () => _modal.remove());
    _modal.on("click", ".js-submit", () => submitForm(_modal, options));
    _modal.on("shown.bs.modal", () => {
        $("body").addClass("modal-open");
        if (options.afterShow) {
            options.afterShow(_modal, options);
        }
    });
}

function processResponse(response, options) {
    if (response.hasOwnProperty("form")) {
        return showModal($(response.form), options);
    }
    if (response.hasOwnProperty("model")) {
        options.afterClose(options, response);
        window.location.reload();
        return;
    }
    if (response.hasOwnProperty("location")) {
        window.location = response.location;
        return;
    }
    showError({responseText: "Invalid response\n" + response, status: 0});
    return;
}

function submitForm(_modal, options) {
    const form = $("form", _modal);
    const params = {
        type: form.attr("method"),
        url: form.attr("action"),
        data: form.serialize(),
        dataType: "json"
    };
    $.ajax(params)
            .always(() => _modal.modal("hide"))
            .done((response) => processResponse(response, options))
            .fail((e) => showError(e));
}

function fetchModal(selector, options) {
    options = $.extend({
        afterClose: () => window.location.reload(),
        beforeShow: () => {
        },
        url: null,
        data: {}
    }, options || {});
    const params = {
        type: "GET",
        url: options.url || $(selector).attr("href"),
        data: options.data,
        dataType: "json"
    };
    $.ajax(params)
            .done((response) => processResponse(response, options))
            .fail((e) => showError(e));
}

function bindModal(selector, options) {
    $(selector).click(function (e) {
        e.preventDefault();
        fetchModal(this, options || {});
        return false;
    });
}