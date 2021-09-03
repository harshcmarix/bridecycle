window.onbeforeunload = function () {
    var inputs = document.getElementsByTagName("BUTTON");
    for (var i = 0; i < inputs.length; i++) {
        if (inputs[i].type == "button" || inputs[i].type == "submit") {
            inputs[i].disabled = true;
        }
    }
};