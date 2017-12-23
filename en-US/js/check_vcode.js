function checkVcode(form) {
    var codeFormat = /^[A-Za-z0-9]{4}$/;
    if (!codeFormat.test(form.vcode.value)) {
        alert(getMsg("Incorrect validation code!"));
        form.vcode.focus();
        return false;
    }
    return true;
}