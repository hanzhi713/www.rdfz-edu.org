function checkName(form){
    var nickn = form.nickname.value;
    if (nickn.length < 6 || nickn.length > 25) {
        alert(getMsg("Nickname is too short or too long!"));
        return false;
    }
    return true;
}